<?php

class WebSocket {

    public static function sendMessage($message) {
        $ch = curl_init("http://localhost:8000/atualizar-dados");

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "Erro: " . curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }

}