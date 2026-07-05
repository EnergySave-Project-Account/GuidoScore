import { request, BASE_URL } from './lib/lib.js';

// Iniciando as variáveis
let yellowRankPos, greenRankPos, blueRankPos, whiteRankPos;
let yellowRankPoints, greenRankPoints, blueRankPoints, whiteRankPoints;
let yellowRankLabel, greenRankLabel, blueRankLabel, whiteRankLabel;
let grid;
let teamCards = {};
let amarelo, azul, verde, branco;

document.addEventListener("DOMContentLoaded", async () => {
    console.log("tabelas.js carregado");

    grid = document.querySelector('.grid');
    teamCards = {
        'Time Amarelo': document.querySelector('.yellow'),
        'Time Verde': document.querySelector('.green'),
        'Time Azul': document.querySelector('.blue'),
        'Time Branco': document.querySelector('.white')
    };

    // Pegando os elementos do HTML
    yellowRankPos = document.querySelector('.yellow .position-value');
    greenRankPos = document.querySelector('.green .position-value');
    blueRankPos = document.querySelector('.blue .position-value');
    whiteRankPos = document.querySelector('.white .position-value');

    yellowRankPoints = document.querySelector('.yellow .score-value');
    greenRankPoints = document.querySelector('.green .score-value');
    blueRankPoints = document.querySelector('.blue .score-value');
    whiteRankPoints = document.querySelector('.white .score-value');

    yellowRankLabel = document.querySelector('.yellow .position-tag');
    greenRankLabel = document.querySelector('.green .position-tag');
    blueRankLabel = document.querySelector('.blue .position-tag');
    whiteRankLabel = document.querySelector('.white .position-tag');

    // Atualizando os dados da tabela ao carregar a página
    refreshTableData();

    // Começando a conexão WebSocket (dinâmico: wss em production, ws em development)
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const host = window.location.host;
    const wsUrl = `${protocol}//${host}:8000/ws`;
    const ws = new WebSocket(wsUrl);

    // Quando se conectar ao servidor websocket
    ws.onopen = () => {
        console.log('WebSocket conectado');
    };

    // Quando os dados forem atualizados
    ws.onmessage = (event) => {
        console.log(event.data);

        if (event.data === 'dados_atualizados') {
            refreshTableData();
        }
    };

    // Se der erro
    ws.onerror = (error) => {
        console.error('WebSocket erro:', error);
    };

    // Se desconectar
    ws.onclose = () => {
        console.warn('WebSocket desconectado');
    };
})

async function refreshTableData() {
    // Faz a requisição para resgatar as informações dos times
    const url = `${BASE_URL}/refresh-once?t=${Date.now()}`;
    const teams = await request(url, "GET", { cache: 'no-store' });

    // Informações dos times retornadas pelo servidor
    amarelo = teams['Time Amarelo'];
    azul = teams['Time Azul'];
    verde = teams['Time Verde'];
    branco = teams['Time Branco'];

    // Atualizando a posição dos times na tabela
    yellowRankPos.textContent = amarelo.posicao;
    greenRankPos.textContent = verde.posicao;
    blueRankPos.textContent = azul.posicao;
    whiteRankPos.textContent = branco.posicao;

    // Atualizando e animando a pontuação dos times na tabela
    animateScore(yellowRankPoints, yellowRankPoints.textContent, amarelo.pontuacao);
    animateScore(greenRankPoints, greenRankPoints.textContent, verde.pontuacao);
    animateScore(blueRankPoints, blueRankPoints.textContent, azul.pontuacao);
    animateScore(whiteRankPoints, whiteRankPoints.textContent, branco.pontuacao);

    // Atualizando a label de posição dos times na tabela
    yellowRankLabel.textContent = "#" + amarelo.numero;
    greenRankLabel.textContent = "#" + verde.numero;
    blueRankLabel.textContent = "#" + azul.numero;
    whiteRankLabel.textContent = "#" + branco.numero;

    // Reorganizando os cards dos times na tabela de acordo com a pontuação
    reorderTeamCardsByScore();

    // Atualizando a cor do grid baseado na equipe ganhadora
    updateGridColor();
}

function reorderTeamCardsByScore() {
    // Criando um array com os times e suas pontuações
    const teams = [
        { name: 'Time Amarelo', score: amarelo.pontuacao },
        { name: 'Time Verde', score: verde.pontuacao },
        { name: 'Time Azul', score: azul.pontuacao },
        { name: 'Time Branco', score: branco.pontuacao }
    ];

    // Ordenando os times pelo score
    teams.sort((a, b) => b.score - a.score);

    // Guardando as posições iniciais dos cards
    const firstRects = {};
    Object.entries(teamCards).forEach(([name, card]) => {
        firstRects[name] = card.getBoundingClientRect();
    });

    // Atualizando a ordem dos cards no grid
    teams.forEach((team, index) => {
        const card = teamCards[team.name];
        if (card) {
            card.style.order = index;
        }
    });

    const cards = Object.entries(teamCards).map(([name, card]) => ({ name, card }));
    cards.forEach(({ name, card }) => {
        const firstRect = firstRects[name];
        const lastRect = card.getBoundingClientRect();
        const dx = firstRect.left - lastRect.left;
        const dy = firstRect.top - lastRect.top;

        if (dx !== 0 || dy !== 0) {
            card.style.transform = `translate(${dx}px, ${dy}px)`;
            card.style.transition = 'transform 0s';
        }
    });

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            cards.forEach(({ card }) => {
                if (card.style.transform) {
                    card.style.transition = 'transform 0.35s ease';
                    card.style.transform = '';
                }
            });
        });
    });

    teams.forEach((team, index) => {
        const card = teamCards[team.name];
        const positionElement = card.querySelector('.position-value');
        if (positionElement) {
            positionElement.textContent = `${index + 1}º`;
        }
    });
}

function animateScore(element, fromValue, toValue) {
    const from = Number(fromValue) || 0;

    const to = Number(toValue) || 0;
    if (from === to) {
        element.textContent = to;
        return;
    }

    const duration = 600;
    const start = performance.now();
    const isIncrease = to > from;
    element.classList.add('score-value-updating', isIncrease ? 'score-increase' : 'score-decrease');

    function easeOutQuad(t) {
        return t * (2 - t);
    }

    function update(now) {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const eased = easeOutQuad(progress);
        const current = Math.round(from + (to - from) * eased);
        element.textContent = current;

        if (progress < 1) {
            requestAnimationFrame(update);
            return;
        }

        element.textContent = to;
        element.classList.remove('score-value-updating', 'score-increase', 'score-decrease');
    }

    requestAnimationFrame(update);
}

function updateGridColor() {
    // Determinando qual equipe tem mais pontos
    const scores = {
        'grid-yellow': amarelo.pontuacao,
        'grid-green': verde.pontuacao,
        'grid-blue': azul.pontuacao,
        'grid-white': branco.pontuacao
    };

    // Encontrando a equipe com maior pontuação
    let maxScore = Math.max(...Object.values(scores));
    let winningTeam = Object.keys(scores).find(team => scores[team] === maxScore);

    // Removendo todas as classes de cor de grid
    document.body.classList.remove('grid-yellow', 'grid-green', 'grid-blue', 'grid-white');

    // Adicionando a classe da equipe ganhadora
    if (winningTeam) {
        document.body.classList.add(winningTeam);
    }
}

