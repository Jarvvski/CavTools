function validateForm() {
    var age = document.forms["enlistment"]["age"].value;
    if (age == 0) {
        alert("Really bro, you're aged 0?");
        return false;
    } else {
        return true;
    }
}
