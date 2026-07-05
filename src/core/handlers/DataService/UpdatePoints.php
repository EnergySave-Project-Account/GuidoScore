<?php

class UpdatePoints {
    /**
     * @param array $data
     * @return array
     */

    public function handle($data) {
        // Validando os dados recebidos
        $team = $data['team'] ?? null;
        $score = $data['score'] ?? null;

        if ($team === null || $score === null) {
            throw new Exception("Dados inválidos fornecidos para atualização.");
        }

        // conectando ao banco de dados
        $mysqli = Database::Connect();

        // Verificando se o nome do time é válido e convertendo para o nome completo
        switch($team){
            case 'branco':
                $team = "Time Branco";
                break;
            case 'amarelo':
                $team = "Time Amarelo";
                break;
            case 'verde':
                $team = "Time Verde";
                break;
            case 'azul':
                $team = "Time Azul";
                break;
            default:
                throw new Exception("Nome do time inválido fornecido.");
                exit;
                break;
        }     
                
        // Verificando se é int
        $score = filter_var($score, FILTER_VALIDATE_INT);
        if ($score === false) {
            throw new Exception("Pontuação inválida fornecida.");
        }
        
        // Preparando a query SQL
        $sql = "UPDATE `teams` SET `pontuacao` = ? WHERE `nome` = ?";
        $stmt = $mysqli->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erro ao preparar a consulta: " . $mysqli->error);
        }

        $stmt->bind_param("is", $score, $team);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar a consulta: " . $stmt->error);
        }
        
        // Rodando a query
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        $mysqli->close();
        
        // Enviando mensagem via WebSocket para atualizar a pontuação em tempo real
        WebSocket::sendMessage("dados_atualizados");

        return [
            'status' => 'success',
            'affectedRows' => $affectedRows,
            'message' => "Pontuação do time '$team' atualizada para $score."
        ];
    }

}