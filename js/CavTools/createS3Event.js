function typeCheck() {
    if (document.getElementById('class').checked) {
        document.getElementById('class_selection').style.display = 'block';
        document.getElementById('operation_title').style.display = 'none';
        document.getElementById('custom_text').style.display = 'none';
    } else {
        document.getElementById('class_selection').style.display = 'none';
    }

    if (document.getElementById('operation').checked) {
        document.getElementById('operation_title').style.display = 'block';
        document.getElementById('custom_text').style.display = 'block';
        document.getElementById('class_selection').style.display = 'none';
    } else {
        document.getElementById('operation_title').style.display = 'none';
        document.getElementById('custom_text').style.display = 'none';
    }
}

function gameCheck(game) {
    // var game = document.getElementByName('game').value;
    var selector = document.getElementById("selector");

    for (var i=0; i<selector.length; i++) {
        if (selector.options[i].id.indexOf(game) >= 0) {
            selector.options[i].style.display="block";
        } else {
            selector.options[i].style.display="none";
        }
    }
}

function onLoad() {
    var selector = document.getElementById("selector");

    for (var i=0; i<selector.length; i++) {
        selector.options[i].style.display="none";
    }
}
