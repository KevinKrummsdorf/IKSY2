
document.addEventListener("DOMContentLoaded", function () {
  const toggles = document.querySelectorAll(".theme-toggle");
  const icons = document.querySelectorAll(".theme-icon");
  const labels = document.querySelectorAll(".theme-label");
  const html = document.documentElement;

  if (toggles.length === 0 || icons.length === 0 || labels.length === 0) {
    console.error("Theme-Switch-Elemente fehlen im DOM");
    return;
  }

  let theme = localStorage.getItem("theme") || getPreferredTheme();
  applyTheme(theme);

  toggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.preventDefault();
      theme = html.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
      applyTheme(theme);
      localStorage.setItem("theme", theme);
    });
  });

  function applyTheme(theme) {
    html.setAttribute("data-bs-theme", theme);
    icons.forEach((icon) => {
      icon.textContent = theme === "dark" ? "light_mode" : "dark_mode";
    });
    labels.forEach((label) => {
      label.textContent = theme === "dark" ? "Lightmode" : "Darkmode";
    });
  }

  function getPreferredTheme() {
    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  }
});
