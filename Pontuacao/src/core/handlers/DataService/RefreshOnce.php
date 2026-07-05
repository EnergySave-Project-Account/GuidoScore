<?php

class RefreshOnce {
    /**
     * @return array
     */

    public function handle() {
        // Conectando ao banco de dados
        $mysqli = Database::Connect();
        
        // Preparando a query SQL e executando
        $sql = "SELECT * FROM `teams`";
        $stmt = $mysqli->prepare($sql);
        $stmt->execute();

        if(!$stmt) {
            throw new Exception("Erro ao executar a consulta: " . $mysqli->error);
            exit;
        }

        // Pegandoi os resultados da query
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);

        $mysqli->close();

        // Organizando os dados em um array 
        $teams = [];
        foreach ($data as $item) {
            $teamName = $item['nome'];
            $teamScore = isset($item['pontuacao']) ? (int)$item['pontuacao'] : 0;
            $teamPosition = isset($item['posicao']) ? (int)$item['posicao'] : null;
            $teamNumber = isset($item['numero']) ? $item['numero'] : null;

            $teams[$teamName] = [
                'pontuacao' => $teamScore,
                'posicao' => $teamPosition,
                'numero' => $teamNumber
            ];
        }

        return $teams;
    }

}