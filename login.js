function showPassengerForm() {
    document.getElementById('passenger-form').style.display = 'block';
    document.getElementById('driver-form').style.display = 'none';
}

function showDriverForm() {
    document.getElementById('driver-form').style.display = 'block';
    document.getElementById('passenger-form').style.display = 'none';
}

window.onload = showPassengerForm;
