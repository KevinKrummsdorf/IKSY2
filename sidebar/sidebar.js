// sidebar.js

// DOM-Elemente abrufen
const sidebar = document.getElementById('sidebar'); // Das Sidebar-Element
const mainContent = document.querySelector('.central-content'); // Der Hauptinhaltsbereich
// Buttons zum Umschalten der Sidebar für verschiedene Bildschirmgrößen
const toggleButtonMd = document.getElementById('sidebar-toggle-md'); // Button für mittelgroße (md) und größere Bildschirme
const toggleButtonSm = document.getElementById('sidebar-toggle-sm'); // Button für kleine Bildschirme

// --- Initialisierung und Event-Handler ---

// DOM ready: Sobald das HTML vollständig geladen und geparst ist
document.addEventListener('DOMContentLoaded', function () {
    // Event Listener für den Button auf mittelgroßen/großen Bildschirmen einrichten
    setupToggleButton(toggleButtonMd);
    // Event Listener für den Button auf kleinen Bildschirmen einrichten
    setupToggleButton(toggleButtonSm);

    // Layout-Klassen initial setzen, um den Startzustand (offen/geschlossen) zu bestimmen
    updateLayoutClasses();
    // Layout-Klassen bei jeder Größenänderung des Fensters aktualisieren (responsiv)
    window.addEventListener('resize', updateLayoutClasses);
});

// --- Funktionen ---

/**
 * Aktualisiert die Bootstrap-Klassen auf der Sidebar und dem Hauptinhaltsbereich
 * basierend auf der Bildschirmgröße und dem 'collapsed'-Zustand der Sidebar.
 * Dies steuert die Sichtbarkeit und die Spaltenbreiten im Bootstrap Grid.
 */
function updateLayoutClasses() {
    // Prüfen, ob die Sidebar die Klasse 'collapsed' hat (dies ist unser Zustandsindikator)
    const isCollapsed = sidebar.classList.contains('collapsed');
    // Prüfen, ob die aktuelle Bildschirmbreite kleiner als der 'md'-Breakpoint von Bootstrap ist (768px)
    const isSmallScreen = window.innerWidth < 768; // Bootstrap's 'md' breakpoint

    // --- Logik für den sichtbaren Toggle-Button und dessen Titel ---

    let visibleToggleButton = null;
    // Bestimmen, welcher Toggle-Button gerade sichtbar sein sollte (abhängig von der Bildschirmgröße)
    if (isSmallScreen && toggleButtonSm) {
        visibleToggleButton = toggleButtonSm;
    } else if (!isSmallScreen && toggleButtonMd) {
        visibleToggleButton = toggleButtonMd;
    }

    // Den Tooltip/Titel des gerade sichtbaren Buttons aktualisieren
    const buttonTitle = isCollapsed ? 'Sidebar öffnen' : 'Sidebar schließen';

    if (visibleToggleButton) {
        visibleToggleButton.setAttribute('title', buttonTitle);

        // Optional: Zustand eines Icons im Button aktualisieren (z.B. für Animationen)
        // Suche nach einem Element mit der Klasse 'sidebar-icon' innerhalb des Buttons
        const icon = visibleToggleButton.querySelector('.sidebar-icon');
        if (icon) {
            // Füge/Entferne die Klasse 'active' basierend darauf, ob die Sidebar NICHT collapsed ist
            icon.classList.toggle('active', !isCollapsed);
        }
    }

    // --- Logik zum Anpassen der Sidebar- und Hauptinhalts-Klassen ---

    // Bestimmen, ob die Sidebar ausgeblendet werden soll
    // Dies ist der Fall, wenn der Bildschirm klein ist ODER die Sidebar den 'collapsed'-Zustand hat.
    const shouldHideSidebar = isSmallScreen || isCollapsed;

    if (shouldHideSidebar) {
        // Zustand: Sidebar ausgeblendet, Hauptinhaltsbereich volle Breite (col-12)

        // Sidebar: Ausblenden mit d-none, responsive Anzeige und Grid-Spalten entfernen
        sidebar.classList.add('d-none');
        sidebar.classList.remove('d-md-block', 'col-md-3', 'col-lg-2'); // Annahme der Bootstrap-Spalten

        // Hauptinhalt: Responsive Grid-Spalten entfernen, volle Breite setzen
        mainContent.classList.remove('col-md-9', 'col-lg-10'); // Annahme der Bootstrap-Spalten
        mainContent.classList.add('col-12');
    } else {
        // Zustand: Sidebar sichtbar (nur auf md+ Bildschirmen und wenn NICHT collapsed), Hauptinhaltsbereich reduzierte Breite

        // Sidebar: d-none entfernen, responsive Anzeige und Grid-Spalten hinzufügen
        sidebar.classList.remove('d-none');
        sidebar.classList.add('d-md-block', 'col-md-3', 'col-lg-2'); // Annahme der Bootstrap-Spalten

        // Hauptinhalt: Volle Breite entfernen, responsive Grid-Spalten für reduzierte Breite setzen
        mainContent.classList.remove('col-12');
        mainContent.classList.add('col-md-9', 'col-lg-10'); // Annahme der Bootstrap-Spalten
    }
}

/**
 * Richte einen Klick-Event-Listener für einen Sidebar-Toggle-Button ein.
 *
 * @param {Element} button - Das DOM-Element des Buttons.
 */
function setupToggleButton(button) {
    // Prüfen, ob der Button existiert, bevor ein Listener hinzugefügt wird
    if (!button) return;

    // Listener für das 'click'-Event hinzufügen
    button.addEventListener('click', function () {
        // Den 'collapsed'-Zustand auf der Sidebar umschalten
        sidebar.classList.toggle('collapsed');
        // Nach dem Umschalten die Layout-Klassen basierend auf dem neuen Zustand aktualisieren
        updateLayoutClasses();
    });
}