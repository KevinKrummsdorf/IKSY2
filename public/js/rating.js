function submitRating(materialId, rating) {
    fetch('rate_material.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `material_id=${materialId}&rating=${rating}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error);
        }
    })
    .catch(() => alert('Fehler beim Speichern der Bewertung'));
}
