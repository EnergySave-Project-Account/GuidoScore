import { request, BASE_URL } from './lib/lib.js';

document.addEventListener('DOMContentLoaded', async () => {
    const scoreForm = document.getElementById('formScore');
    const teamSelector = document.getElementById('teamSelector');
    const scoreValue = document.getElementById('scoreValue');
    const decreaseScore = document.getElementById('decreaseScore');
    const increaseScore = document.getElementById('increaseScore');

    decreaseScore.addEventListener('click', (e) => {
        e.preventDefault();
        const currentValue = parseInt(scoreValue.value) || 0;
        scoreValue.value = currentValue - 1;
    });

    increaseScore.addEventListener('click', (e) => {
        e.preventDefault();
        const currentValue = parseInt(scoreValue.value) || 0;
        scoreValue.value = currentValue + 1;
    });

    scoreValue.addEventListener('input', (e) => {
        const min = parseInt(e.target.min) || -9999;
        const max = parseInt(e.target.max) || 9999;
        let val = parseInt(e.target.value);
        if (isNaN(val)) val = 0;
        if (val < min) val = min;
        if (val > max) val = max;
        e.target.value = val;
    });

    const btnAddPoints = document.getElementById('btnAddPoints');
    const btnUpdatePoints = document.getElementById('btnUpdatePoints');
    const submitButton = scoreForm.querySelector('.btn-submit');

    let currentMode = 'add';

    function setMode(mode) {
        currentMode = mode;

        btnAddPoints.classList.toggle('active', mode === 'add');
        btnUpdatePoints.classList.toggle('active', mode === 'update');
        submitButton.textContent = mode === 'add' ? 'Confirmar' : 'Atualizar';

    }

    btnAddPoints.addEventListener('click', () => setMode('add'));
    btnUpdatePoints.addEventListener('click', () => setMode('update'));

    scoreForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const team = teamSelector.value;
        const score = parseInt(scoreValue.value) || 0;

        if (!team) {
            alert('Selecione um time.');
            return;
        }

        console.log(`Enviando pontuação: Time: ${team}, Pontos: ${score}, modo: ${currentMode}`);
        
        let res = null;
        if(currentMode === 'add') {
            res = await request(BASE_URL + "/increment-points", "POST", {
                body: {
                    team: team,
                    score: score
                }
            });
        }
        else if(currentMode === 'update') {
            res = await request(BASE_URL + "/update-points", "POST", {
                body: {
                    team: team,
                    score: score
                }
            });
        }

        console.log(res);

        scoreForm.reset();
    });

    const resetBtn = document.querySelector('.btn-reset');

    resetBtn.addEventListener('click', () => {
        scoreValue.value = 0;
        teamSelector.value = '';
    });
});
