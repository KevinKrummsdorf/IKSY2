document.addEventListener('DOMContentLoaded', () => {
  const alertEl = document.getElementById('twofa-success');
  if (alertEl) {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
    setTimeout(() => bsAlert.close(), 10000);
  }
});
