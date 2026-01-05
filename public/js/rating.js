function showAlert(htmlMessage, type = 'danger', autoCloseMs = 5000) {
    const container = document.getElementById('AlertContainer');
    if (!container) {
        alert(htmlMessage);
        return;
    }
    container.innerHTML = `
      <div class="container mt-3">
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
          ${htmlMessage}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
        </div>
      </div>`;
    const alertEl = container.querySelector('.alert');
    const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
    if (autoCloseMs > 0) {
        setTimeout(() => bsAlert.close(), autoCloseMs);
    }
}

function submitRating(materialId, rating) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    return fetch('rate_material.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `material_id=${encodeURIComponent(materialId)}&rating=${encodeURIComponent(rating)}&csrf_token=${encodeURIComponent(csrfToken)}`
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
            updateAverage(materialId, data.average_rating, data.total_ratings);
        } else {
            return Promise.reject(data.error);
        }
    })
    .catch(err => showAlert(err || 'Fehler beim Speichern der Bewertung'));
}

function updateStars(materialId, rating) {
    const stars = document.querySelectorAll(`.star[data-material-id="${materialId}"]`);
    stars.forEach((star, idx) => {
        if (idx < rating) {
            star.classList.add('text-warning');
            star.classList.remove('text-secondary');
        } else {
            star.classList.add('text-secondary');
            star.classList.remove('text-warning');
        }
    });
}

function updateAverage(materialId, average, total) {
    const info = document.getElementById(`rating-info-${materialId}`);
    if (!info) return;
    if (total > 0) {
        info.textContent = `Durchschnitt: ${Number(average).toFixed(1)} ★ (${total} Bewertungen)`;
    } else {
        info.textContent = 'Durchschnitt: Noch keine Bewertung';
    }
}

