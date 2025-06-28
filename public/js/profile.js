document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert').forEach(el => {
    const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
    setTimeout(() => bsAlert.close(), 10000);
  });
});
