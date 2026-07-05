<?php

class IncrementPoints {
    /**
     * @param array $data
     * @return array
     */

    public function handle($data) {
        // Pegando as informações do time e da pontuação do array de dados
        $team = $data['team'] ?? null;
        $score = $data['score'] ?? null;

        // Verificando se as informações são válidas
        if ($team === null || $score === null) {
            throw new Exception("Dados inválidos fornecidos para atualização.");
        }

        // Conectando ao banco de dados
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
        
        // Vendo se é int
        $score = filter_var($score, FILTER_VALIDATE_INT);
        if ($score === false) {
            throw new Exception("Pontuação inválida fornecida.");
        }
        
        // Buscando a pontuação atual para evitar underflow (menor que zero)
        $selectSql = "SELECT `pontuacao` FROM `teams` WHERE `nome` = ? LIMIT 1";
        $selectStmt = $mysqli->prepare($selectSql);
        if (!$selectStmt) {
            throw new Exception("Erro ao preparar a consulta de seleção: " . $mysqli->error);
        }
        $selectStmt->bind_param("s", $team);
        if (!$selectStmt->execute()) {
            $selectStmt->close();
            throw new Exception("Erro ao executar a consulta de seleção: " . $selectStmt->error);
        }
        $selectStmt->bind_result($currentScore);
        if (!$selectStmt->fetch()) {
            $selectStmt->close();
            $mysqli->close();
            throw new Exception("Time não encontrado: $team");
        }
        $selectStmt->close();

        $currentScore = intval($currentScore);
        $newScore = $currentScore + intval($score);
        if ($newScore < 0) {
            $newScore = 0;
        }

        // Atualiza com o valor final calculado (evita operação pontuacao + negativo em coluna unsigned)
        $sql = "UPDATE `teams` SET `pontuacao` = ? WHERE `nome` = ?";
        $stmt = $mysqli->prepare($sql);

        if (!$stmt) {
            throw new Exception("Erro ao preparar a consulta de atualização: " . $mysqli->error);
        }

        $stmt->bind_param("is", $newScore, $team);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar a consulta de atualização: " . $stmt->error);
        }

        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        $mysqli->close();

        WebSocket::sendMessage("Dados atualizados: Time: $team, Ajuste: $score, Total: $newScore");

        return [
            'status' => 'success',
            'affectedRows' => $affectedRows,
            'message' => "Pontuação do time '$team' ajustada em $score (total: $newScore)."
        ];
    }

}