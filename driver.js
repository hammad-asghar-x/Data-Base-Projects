function showContent(contentId) {
    let sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    document.getElementById(contentId).style.display = 'block';
}

function showRideInfo(rideType) {
    let completedRides = document.getElementById('completed-rides');
    let pendingRides = document.getElementById('pending-rides');

    completedRides.style.display = 'none';
    pendingRides.style.display = 'none';

    if (rideType === 'completed') {
        completedRides.style.display = 'block';
    } else if (rideType === 'pending') {
        pendingRides.style.display = 'block';
    }
}