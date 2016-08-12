function typeCheck() {
    if (document.getElementById('game-Change').checked) {
        document.getElementById('change-game').style.display = 'block';
    } else {
        document.getElementById('change-game').style.display = 'none';
    }

    if (document.getElementById('time-change').checked) {
        document.getElementById('change-time').style.display = 'block';
    } else {
        document.getElementById('change-time').style.display = 'none';
    }
}
