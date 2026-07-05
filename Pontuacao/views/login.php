<?php

require_once __DIR__ . '/../bootstrap.php';
CSRFService::generateToken();
$csrfData = CSRFService::getToken();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ScoreHub - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800&family=JetBrains+Mono:wght@700&family=Inter:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/pontuacao/views/static/css/home.css"/>
    <script src="/pontuacao/views/static/js/login.js" type="module" defer></script>
</head>

<body>
    <div id="csrf-data" data-csrf='<?= htmlspecialchars(json_encode($csrfData), ENT_QUOTES, 'UTF-8') ?>'></div>
    <main class="page-layout">
        <h1>Login</h1>
        <div class="divider"></div>

        <div class="form-panel">
            <form id="loginForm" method="POST" class="score-form">

                <div class="form-group">
                    <label for="username" class="form-label">Usuário</label>
                    <input type="text" id="username" name="username" class="team-selector" required maxlength="100" />
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" id="password" name="password" class="score-value" required maxlength="100" />
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Entrar</button>
                </div>
            </form>
        </div>

        <footer>
            ScoreHub © 2026 - Placar em Tempo Real
        </footer>
    </main>
</body>
</html>