# StudyHub – Projektübersicht

StudyHub ist ein webbasiertes Lernportal, das Studierenden die Organisation von Lerngruppen und den Austausch von Materialien erleichtert. Die Anwendung entstand im Rahmen des Moduls **IKSY2** und dient als zentrale Plattform, um Aufgaben zu koordinieren und Lernfortschritte zu verfolgen.

## Ziele

- Bereitstellung einer gemeinsamen Umgebung für Lernmaterialien
- Einfache Verwaltung von Gruppen und Terminen
- Unterstützung des kooperativen Lernens durch ToDo-Listen und Kalenderfunktionen

## Funktionsüberblick

- **Benutzerkonten**  
  Registrierung und Login erfolgen über ein eigenes Account-System. Auf Wunsch kann eine Zwei-Faktor-Authentifizierung aktiviert werden. Ein Passwort-Reset per E-Mail steht ebenfalls bereit.

- **Gruppenverwaltung**  
  Nutzer erstellen Lerngruppen, verschicken Einladungen und vergeben Rollen wie *Administrator* oder *Moderator*, sodass Verantwortlichkeiten klar verteilt sind.

- **Material-Upload und Bewertung**  
  Skripte, Präsentationen und andere Dateien lassen sich direkt hochladen. Kommentare und Bewertungen helfen bei der Qualitätssicherung der bereitgestellten Materialien.

- **Persönlicher Kalender mit ToDo-Verwaltung**  
  Termine und Aufgaben werden zentral im Dashboard verwaltet. Wiederkehrende Einträge und farbliche Hervorhebungen erleichtern die Planung.
- **PDF-Export**
  Sowohl der Stundenplan als auch hochgeladene Materialien lassen sich bequem als PDF herunterladen.

- **Kontaktformular**  
  Über das Formular erreichst du die Betreuer; neue Nachrichten werden automatisch per E-Mail weitergeleitet.

## Anleitung im Umgang mit git und github

Diese Anwendung verwendet [Composer](https://getcomposer.org) zur einfachen Installation und Verwaltung der benötigten Bibliotheken. Diese Anleitung geht davon aus, dass Composer sowie git bereits installiert und konfiguriert sind.
  
## Repo clonen
```
git clone https://github.com/KevinKrummsdorf/IKSY2 /var/www/html/iksy05/StudyHub
```
## Abhängigkeiten installieren
```
cd /var/www/html/iksy05/StudyHub
composer install
```
Nach dem Klonen funktioniert die Anwendung sofort mit einer einfachen
`.htaccess`-Datei. Diese deaktiviert Pretty URLs und eigene Fehlerseiten,
damit der Webserver auch ohne besondere Rechte korrekt arbeitet.
Ohne Setup-Skript liefern geschützte Bereiche lediglich den passenden
HTTP-Statuscode, sodass Apache seine Standardfehlerseiten anzeigt. Erweiterte
Funktionen können später mit `manager.sh` aktiviert werden.  

## **Hinweis**  
Aufgrund der größe des Projekts, sind in dieser Abgabe die Abhängigkeiten **nicht** installiert. Vor dem ersten Start der Anwenung muss daher ```composer install``` im root Verzeichnis ausgeführt werden.   
Im Ordner ```sql``` finden Sie den gewünschten MySQL dump.  
In der Datei ```user.txt``` finden sie den Username sowie das zugehörige Passwort für einen normalen User und einen Admin.

# Custom Fehlerseiten konfigurieren

Um das System für die Verwendung eigener Fehlerseiten (z. B. `401.tpl`, `403.tpl`, `404.tpl` etc.) korrekt einzurichten, kann das mitgelieferte Setup-Skript verwendet werden.
Nicht eingeloggte Nutzer sehen dabei einen 401-Fehler. Meldet sich ein normaler Benutzer an und ruft einen nur für Admins oder Moderatoren vorgesehenen Bereich auf, erscheint stattdessen ein 403-Fehler. Die 403-Seite weist nun ausdrücklich darauf hin, dass für die angeforderte Ressource die erforderlichen Rechte fehlen und bietet lediglich einen Button zurück zur Startseite an.

## Setup ausführen

Im Root-Verzeichnis dieses Repositories befindet sich das Skript [`manager.sh`](./manager.sh), das die notwendigen Konfigurationsschritte automatisch durchführt.

#### Skript ausführbar machen

```bash
chmod +x manager.sh
```

#### Skript ausführen

```bash
sudo ./manager.sh
```

##  Das Skript erledigt automatisch:

1. Prüft, ob es mit Root-Rechten ausgeführt wird
2. Kopiert bei Root-Rechten die erweiterte `.htaccess` und aktiviert `mod_rewrite`
3. Legt eine Datei `config/pretty_urls_enabled` an, um Pretty URLs zu markieren
4. Startet den Apache-Webserver automatisch per `sudo` neu

Nach der Installation sind wichtige Bereiche unter diesen Pfaden erreichbar:
- Eigenes Profil: `/profile/my`
- Profil eines anderen Nutzers: `/profile/<username>`
- Gruppen: `/groups/<gruppenname>`
- Dashboard: `/dashboard`

**Nach der Ausführung ist der Webserver korrekt für die Nutzung benutzerdefinierter Fehlerseiten sowie Pretty URLs konfiguriert.**

## Funktion testen

Rufen Sie eine nicht vorhandene URL wie

```
http://127.0.0.1/studyhub/irgendwas
```

auf. Es sollte nun die eigene `404.tpl`-Fehlerseite angezeigt werden.

# Fehlerseiten-Konfiguration entfernen

Falls Sie die benutzerdefinierte Fehlerseitenkonfiguration wieder entfernen möchten, kann das mit dem bereitgestellten Deinstallationsskript erledigt werden.

## Deinstallation ausführen

Im Root-Verzeichnis dieses Repositories befindet sich das Skript [`manager.sh`](./manager.sh), das **auch eine Rücknahme der Konfiguration unterstützt**, wenn es im Deinstallationsmodus ausgeführt wird.

### Skript mit Deinstallations-Flag ausführen

```bash
sudo ./manager.sh --uninstall
```

## Das Skript macht folgende Änderungen rückgängig:

1. Stellt die einfache `.htaccess` wieder her
2. Entfernt die Datei `config/pretty_urls_enabled` und deaktiviert `mod_rewrite` (falls aktiviert). Anschließend wird Apache automatisch per `sudo` neu gestartet
     
**Stellen Sie sicher, dass keine anderen Webanwendungen auf den Symlink oder die Apache-Konfiguration angewiesen sind, bevor du die Deinstallation durchführt wird.**

# Hinweis zur Darstellung von HTTP-Fehler (z.B. 401 - Unauthorized)

Beim Aufruf geschützter Seiten ohne vorherige Anmeldung kann es in bestimmten Browser-Umgebungen zu einer **weißen Seite** kommen, während andere Browser eine Fehlermeldung wie `401 Unauthorized` anzeigen.

**Dies ist kein Fehler im Code**, sondern ein **browserabhängiges Verhalten**:

Der Server liefert in diesem Fall bewusst nur den HTTP-Statuscode `401` ohne weiteren HTML-Inhalt. Einige Browser (z. B. Firefox, Chrome etc.) zeigen bei einer solchen Antwort eine eigene Fehlerseite an, während andere lediglich eine leere (weiße) Seite darstellen, wenn kein Inhalt (Body) geliefert wird.

**Technischer Hintergrund:**  
Gemäß [RFC 7235](https://datatracker.ietf.org/doc/html/rfc7235#section-3.1) ist es **zulässig**, bei z.B. einem `401 Unauthorized`-Status **keine** Antwortdaten zu senden. Das Verhalten bei der Darstellung liegt dann im Verantwortungsbereich des Browsers. Unterschiede können auch durch Profil-Einstellungen, Themes oder systembedingte UI-Eigenheiten entstehen.

**Hinweis zur Projektkonfiguration:**  
Über das Skript [`manager.sh`](./manager.sh) werden benutzerdefinierte Fehlerseiten eingerichtet, die dieses Verhalten umgehen können, indem sie bei Bedarf einen eigenen HTML-Inhalt für solche Fehler bereitstellen.

Für konsistentes Verhalten über alle Systeme hinweg wird empfohlen, die Fehlerseitenkonfiguration aus [`manager.sh`](./manager.sh) zu übernehmen.



# Verwendete Bibliotheken

[Bootstrap](https://github.com/twbs/bootstrap)

[PHPMailer](https://github.com/PHPMailer/PHPMailer)

[TCPDF](https://github.com/tecnickcom/TCPDF)

[phpdotenv](https://github.com/vlucas/phpdotenv)    

[Smarty](https://github.com/smarty-php/smarty)  

[Halite](https://github.com/paragonie/halite)

# Verwendete APIs
[Google reCaptcha](https://cloud.google.com/security/products/recaptcha)  
[Google Fonts](https://fonts.google.com/icons) (lokal eingebunden)
