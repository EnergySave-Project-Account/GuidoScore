<?php

    class CSRFService {

        public static function generateToken() {
            // Se a sessão ainda não foi iniciada, inicia a sessão
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Gera um token aleatório baseado na API KEY e armazena o hash na sessão
            $token = bin2hex(random_bytes(32));
            $hash = hash_hmac('sha256', $token, API_KEY);
            $_SESSION['csrf_token'] = $hash;
            $_SESSION['token_generated_csrf'] = $token;

            return [
                "csrf_token" => $hash,
                "generated" => $token,
            ];
        }   

        public static function getToken() {
            // Se a sessão ainda não foi iniciada, inicia a sessão
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Se o token CSRF ainda não existe na sessão, gera um novo token
            if (!isset($_SESSION["csrf_token"]) || !isset($_SESSION["token_generated_csrf"])) {
                return self::generateToken();
            }

            return [
                "csrf_token" => $_SESSION["csrf_token"],
                "generated" => $_SESSION["token_generated_csrf"],
            ];
        }

        public static function validateToken() {
            // Iniciando a sessão se ainda não tiver sido iniciada
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Tentando obter o token CSRF do cabeçalho da requisição
            $headerValue = null;

            if (function_exists('getallheaders')) {
                $headers = getallheaders();

                if (is_array($headers)) {
                    foreach (["X-CSRF-Header", "X-CSRF-HEADER", "X-Csrf-Header", "x-csrf-header"] as $headerName) {
                        if (isset($headers[$headerName])) {
                            // Setando o valor do token CSRF a partir do cabeçalho encontrado
                            $headerValue = $headers[$headerName];
                            break;
                        }
                    }
                }
            }

            if ($headerValue === null) {
                $headerValue = $_SERVER["HTTP_X_CSRF_HEADER"] ?? $_SERVER["X_CSRF_HEADER"] ?? null;
            }

            $payload = null;
            if ($headerValue) {
                $decoded = json_decode($headerValue, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $payload = $decoded;
                }
            }

            $headerToken = $payload["csrf_token"] ?? $payload["token"] ?? $headerValue;
            $headerGenerated = $payload["generated"] ?? null;
            $sessionToken = $_SESSION["csrf_token"] ?? null;
            $sessionGenerated = $_SESSION["token_generated_csrf"] ?? null;
            $expected = hash_hmac('sha256', $headerGenerated ?? $sessionGenerated ?? '', API_KEY);

            $isValid = false;

            if ($sessionToken && $sessionGenerated) {
                if ($headerGenerated !== null && hash_equals($sessionGenerated, $headerGenerated)) {
                    $isValid = hash_equals($sessionToken, $expected) || hash_equals($headerToken ?? '', $expected);
                }

                if (!$isValid && $headerToken !== null) {
                    $isValid = hash_equals($sessionToken, $headerToken) || hash_equals($sessionToken, $expected);
                }
            }

            if ($isValid) {
                return true;
            }

            http_response_code(403);
            echo json_encode([
                "success" => false,
                "message" => "Token CSRF inválido",
                "action" => "SHOW_ERROR"
            ]);
            exit;
        }
        
    }