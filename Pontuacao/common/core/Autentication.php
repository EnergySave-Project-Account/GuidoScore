<?php

class Autentication {

    public static function checkAuth() {
        $mysqli = Database::connect();

        if (!isset($_COOKIE["auth_token"])) {
            header("Location: /pontuacao/login");
            exit;
        }

        $token = $_COOKIE["auth_token"];

        $stmt = $mysqli->prepare("SELECT token FROM user WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $mysqli->close();

            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            setcookie('auth_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'secure' => $secure,
                'samesite' => 'Strict'
            ]);

            header("Location: /pontuacao/login");
            exit;
        }

        $stmt->close();
        $mysqli->close();
    }

    public static function authNeeded($token) {
        $mysqli = Database::connect();

        $stmt = $mysqli->prepare("SELECT token FROM user WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $mysqli->close();

            return false;
        }

        $stmt->close();
        $mysqli->close();

        return true;
    }

    public static function autenticateUrl(){
        $mysqli = Database::Connect();

        $token = $_GET["token"];

        $stmt = $mysqli->prepare("SELECT url_token FROM user WHERE url_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();  
        $url_row = $result->fetch_assoc();

        if(!$url_row || !isset($url_row["url_token"])){
            header("Location: /pontuacao/login");
            exit;
        }

        if (!hash_equals($url_row["url_token"], $token)) {
            header("Location: /pontuacao/login");
            exit;
        }

        if (!isset($_COOKIE["url_token"])) {
            header("Location: /pontuacao/login");
            exit;
        }

        if (!hash_equals($_COOKIE["url_token"], $token)) {
            $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            setcookie('url_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'secure' => $secure,
                'samesite' => 'Strict'
            ]);

            header("Location: /pontuacao/login");
            exit;
        }
    }

}
