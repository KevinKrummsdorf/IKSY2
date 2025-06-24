
document.addEventListener("DOMContentLoaded", function () {
  const toggle = document.getElementById("theme-toggle");
  const icon = document.getElementById("theme-icon");
  const label = document.getElementById("theme-label");
  const html = document.documentElement;

  if (!toggle || !icon || !label) {
    return;
  }

  let theme = localStorage.getItem("theme") || getPreferredTheme();
  applyTheme(theme);

  toggle.addEventListener("click", function (e) {
    e.preventDefault();
    theme = html.getAttribute("data-bs-theme") === "dark" ? "light" : "dark";
    applyTheme(theme);
    localStorage.setItem("theme", theme);
  });

  function applyTheme(theme) {
    html.setAttribute("data-bs-theme", theme);
    icon.textContent = theme === "dark" ? "light_mode" : "dark_mode";
    label.textContent = theme === "dark" ? "Lightmode" : "Darkmode";
  }

  function getPreferredTheme() {
    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  }
});
