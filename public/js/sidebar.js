//sidebar.js

const sidebar = document.getElementById('sidebarOffcanvas');
if (sidebar) {
  const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(sidebar);

  sidebar.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', (e) => {
      const href = link.getAttribute('href');
      if (href && href !== '#' && href !== '') {
        bsOffcanvas.hide();
      } else {
        e.preventDefault(); // Kein echtes Ziel? Kein Schließen
      }
    });
  });
}