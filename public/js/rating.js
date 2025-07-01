function submitRating(materialId, rating) {
    return fetch('rate_material.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `material_id=${encodeURIComponent(materialId)}&rating=${encodeURIComponent(rating)}`
    })
    .then(res => {
        if (!res.ok) {
            return res.json().then(data => Promise.reject(data.error || 'Fehler beim Speichern der Bewertung'));
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            updateStars(materialId, rating);
        } else {
            return Promise.reject(data.error);
        }
    })
    .catch(err => alert(err || 'Fehler beim Speichern der Bewertung'));
}

function updateStars(materialId, rating) {
    const container = document.querySelectorAll(`[data-material-id="${materialId}"] .star`);
    container.forEach((star, idx) => {
        if (idx < rating) {
            star.classList.add('text-warning');
            star.classList.remove('text-secondary');
        } else {
            star.classList.add('text-secondary');
            star.classList.remove('text-warning');
        }
    });
}

