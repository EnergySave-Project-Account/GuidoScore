<?php

class RateLimitService {
    
    private static string $storageDir = __DIR__ . '/../../runtime/rate-limits';

    /**
     * Verifica rate limit para uma rota/usuário
     * 
     * @param string $routeName Nome da rota
     * @param int $maxAttempts Máximo de tentativas na janela
     * @param int $windowSeconds Janela de tempo (segundos)
     * @param int $banSeconds Tempo de banimento (segundos)
     * @return void - Encerra com HTTP 429 se excedido
     */
    public static function verify(
        string $routeName, 
        int $maxAttempts = 10, 
        int $windowSeconds = 60, 
        int $banSeconds = 300
    ): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $identifier = self::getIdentifier();
        $storageFile = self::getStorageFile($routeName, $identifier);
        $lockFile = $storageFile . '.lock';

        // Garante diretório de armazenamento
        if (!is_dir(dirname($storageFile))) {
            if (!mkdir(dirname($storageFile), 0777, true) && !is_dir(dirname($storageFile))) {
                http_response_code(500);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro ao processar segurança da requisição.'
                ]);
                exit;
            }
        }

        // File lock para evitar race conditions
        $handle = fopen($lockFile, 'c');
        if ($handle === false) {
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar segurança da requisição.'
            ]);
            exit;
        }

        if (!flock($handle, LOCK_EX)) {
            fclose($handle);
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar segurança da requisição.'
            ]);
            exit;
        }

        $state = self::loadState($storageFile);
        $now = time();

        // Verifica se está banido
        if (!empty($state['banned_until']) && (int)$state['banned_until'] > $now) {
            $remaining = (int)$state['banned_until'] - $now;
            flock($handle, LOCK_UN);
            fclose($handle);

            http_response_code(429);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => "Muitas tentativas. Aguarde {$remaining}s.",
                'retryAfter' => $remaining
            ]);
            exit;
        }

        // Remove banimento expirado
        if (!empty($state['banned_until']) && (int)$state['banned_until'] <= $now) {
            $state['banned_until'] = null;
            $state['attempts'] = [];
        }

        // Remove tentativas fora da janela de tempo
        $state['attempts'] = array_values(array_filter(
            $state['attempts'] ?? [],
            function ($entry) use ($now, $windowSeconds) {
                return ((int)($entry['ts'] ?? 0) + $windowSeconds) > $now;
            }
        ));

        // Registra nova tentativa
        $state['attempts'][] = [
            'ts' => $now,
            'ip' => self::getClientIp(),
            'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)
        ];

        // Verifica se excedeu limite
        if (count($state['attempts']) >= $maxAttempts) {
            $state['banned_until'] = $now + $banSeconds;
            $state['attempts'] = [];

            self::saveState($storageFile, $state);
            flock($handle, LOCK_UN);
            fclose($handle);

            http_response_code(429);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => "Bloqueado por {$banSeconds}s.",
                'retryAfter' => $banSeconds
            ]);
            exit;
        }

        self::saveState($storageFile, $state);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * Obtém identificador único do cliente (IP + Session)
     */
    private static function getIdentifier(): string {
        $ip = self::getClientIp();
        $sessionId = session_id() ?: 'anonymous';
        return hash('sha256', $ip . '::' . $sessionId);
    }

    /**
     * Obtém IP do cliente considerando proxies
     */
    private static function getClientIp(): string {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Caminho do arquivo de armazenamento
     */
    private static function getStorageFile(string $routeName, string $identifier): string {
        $routeSafe = preg_replace('#[^a-z0-9_-]#', '_', strtolower($routeName));
        return self::$storageDir . '/' . $routeSafe . '/' . $identifier . '.json';
    }

    /**
     * Carrega estado do arquivo
     */
    private static function loadState(string $filePath): array {
        if (!file_exists($filePath)) {
            return [
                'attempts' => [],
                'banned_until' => null,
                'created_at' => time()
            ];
        }

        $content = @file_get_contents($filePath);
        return $content ? json_decode($content, true) : ['attempts' => [], 'banned_until' => null];
    }

    /**
     * Salva estado no arquivo
     */
    private static function saveState(string $filePath, array $state): void {
        $state['updated_at'] = time();
        @file_put_contents($filePath, json_encode($state), LOCK_EX);
    }
}
