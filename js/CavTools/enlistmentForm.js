function validateForm() {
    var age = document.forms["enlistment"]["age"].value;
    if (age == 0) {
        x.setCustomValidity("Really bro, you're aged 0?");
        event.preventDefault();
        return false;
    } else {
        return true;
    }
}
