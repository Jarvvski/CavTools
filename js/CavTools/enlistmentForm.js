function validateForm() {
    var x = document.forms["enlistment"]["age"];
    var y = 115;

    if (Number(x.value) > Number(y)) {
        x.setCustomValidity("Really, older than 115?");
        event.preventDefault();
        return false;
    } else {
        return true;
    }
}