<?php

class Database {

    public static function Connect(){
        $host = defined('DBHOST') ? DBHOST : '127.0.0.1';
        $user = defined('DBUSER') ? DBUSER : '';
        $pass = defined('DBPASSWORD') ? DBPASSWORD : '';
        $db   = defined('DBNAME') ? DBNAME : '';
        $port = defined('DBPORT') ? DBPORT : 3306;

        $mysqli = new mysqli($host, $user, $pass, $db, $port);

        if ($mysqli->connect_error) {
            $msg = "Erro ao conectar com o banco de dados: " . $mysqli->connect_error;
            throw new Exception($msg);
        }

        $mysqli->set_charset('utf8mb4');

        return $mysqli;
    }

}