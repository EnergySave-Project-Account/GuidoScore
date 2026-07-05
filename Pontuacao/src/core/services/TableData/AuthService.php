<?php

class AuthService {

    public static function Login($data) {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $username = is_string($username) ? trim($username) : '';
        $password = is_string($password) ? trim($password) : '';

        if (!$username || !$password) {
            return [
                'success' => false,
                'message' => 'Usuário e senha são obrigatórios.'
            ];
        }

        if (!preg_match('/^[A-Za-z0-9_.@-]{3,100}$/', $username)) {
            return [
                'success' => false,
                'message' => 'Usuário ou senha inválidos.'
            ];
        }

        if (strlen($password) < 8 || strlen($password) > 100) {
            return [
                'success' => false,
                'message' => 'Usuário ou senha inválidos.'
            ];
        }

        if (preg_match('/\s/', $password)) {
            return [
                'success' => false,
                'message' => 'Usuário ou senha inválidos.'
            ];
        }

        $mysqli = Database::connect();

        $stmt = $mysqli->prepare("SELECT name, password FROM user WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $mysqli->close();
            return [
                'success' => false,
                'message' => 'Usuário ou senha inválidos.'
            ];
        }

        if (!password_verify($password, $user['password'])) {
            $mysqli->close();

            return [
                'success' => false,
                'message' => 'Usuário ou senha inválidos.'
            ];
        }

        $token = bin2hex(random_bytes(64));
        $url_token = bin2hex(random_bytes(64));

        $stmt = $mysqli->prepare("UPDATE user SET token = ?, url_token = ? WHERE name = ?");
        $stmt->bind_param("sss", $token, $url_token, $username);

        if (!$stmt->execute()) {
            $stmt->close();
            $mysqli->close();

            return [
                'success' => false,
                'message' => 'Erro ao realizar o procedimento.'
            ];
        }

        $stmt->close();

        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        setcookie('auth_token', $token, [
            'expires' => time() + 86400 * 30,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        setcookie('url_token', $url_token, [
            'expires' => time() + 86400 * 30,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        $mysqli->close();

        return [
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'redirect' => true,
            'url_token' => $url_token
        ];
    }

}
