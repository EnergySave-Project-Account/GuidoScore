<?php

require_once __DIR__ . '/../bootstrap.php';
Autentication::checkAuth();
Autentication::autenticateUrl();
CSRFService::generateToken();
$csrfData = CSRFService::getToken();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ScoreHub - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=JetBrains+Mono:wght@700&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/pontuacao/views/static/css/home.css"/>
    <script src="/pontuacao/views/static/js/home.js" type="module" defer></script>
</head>

<body>
    <div id="csrf-data" data-csrf='<?= htmlspecialchars(json_encode($csrfData), ENT_QUOTES, 'UTF-8') ?>'></div>
    
    <main class="page-layout">
        <h1>GERENCIAR PONTUAÇÃO</h1>
        <div class="divider"></div>

        <div class="mode-panel">
            <div class="mode-actions">
                <button type="button" id="btnAddPoints" class="btn-mode active">Ajustar pontos</button>
                <button type="button" id="btnUpdatePoints" class="btn-mode">Atualizar pontos</button>
            </div>
        </div>

        <div class="form-panel">
            <form id="formScore" class="score-form">

                <div class="form-group">

                    <label for="teamSelector" class="form-label">SELECIONE O TIME</label>

                    <select id="teamSelector" class="team-selector" required>
                        <option value="">Escolha um time</option>
                        <option value="amarelo" data-color="yellow">AMARELO</option>
                        <option value="verde" data-color="green">VERDE</option>
                        <option value="azul" data-color="blue">AZUL</option>
                        <option value="branco" data-color="white">BRANCO</option>
                    </select>

                </div>

                <div class="form-group">

                    <label for="scoreValue" class="form-label">PONTUAÇÃO</label>
                    <div class="score-control">
                        <button type="button" class="button-step button-decrement" id="decreaseScore">−</button>
                        <input type="number" id="scoreValue" class="score-value" value="0" min="-9999" max="9999" required>
                        <button type="button" class="button-step button-increment" id="increaseScore">+</button>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">CONFIRMAR</button>
                    <button type="reset" class="btn-reset">LIMPAR</button>
                </div>
            </form>
        </div>

        <footer>
            ScoreHub © 2026 - Placar em Tempo Real
        </footer>
    </div>
</body>
</html>