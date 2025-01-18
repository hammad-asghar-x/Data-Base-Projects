document.getElementById('passenger-btn').addEventListener('click', function() {
    document.getElementById('passenger-form').style.display = 'block';
    document.getElementById('driver-form').style.display = 'none';
});

document.getElementById('driver-btn').addEventListener('click', function() {
    document.getElementById('driver-form').style.display = 'block';
    document.getElementById('passenger-form').style.display = 'none';
});
