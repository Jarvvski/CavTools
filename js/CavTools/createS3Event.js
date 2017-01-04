function typeCheck() {
    if (document.getElementById('class').checked) {
        document.getElementById('class_selection').style.display = 'block';   
    } else {
        document.getElementById('class_selection').style.display = 'none';
    }

    if (document.getElementById('operation').checked) {
        document.getElementById('operation_title').style.display = 'block';
        document.getElementById('custom_text').style.display = 'block';
    } else {
        document.getElementById('operation_title').style.display = 'none';
        document.getElementById('custom_text').style.display = 'none';
    }
}

