# Anleitung im Umgang mit git und github

Diese Anwendung verwendet [Composer](https://getcomposer.org) zur einfachen Installation und Verwaltung der benötigten Bibliotheken. Diese Anleitung geht davon aus, das Composer sowie git bereits intalliert und konfiguriert sind.  
  
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


# Verwendete Bibliotheken

[Bootstrap](https://github.com/twbs/bootstrap)

[PHPMailer](https://github.com/PHPMailer/PHPMailer)


[phpdotenv](https://github.com/vlucas/phpdotenv)    

[Smarty](https://github.com/smarty-php/smarty)  

[Halite](https://github.com/paragonie/halite)

# Verwendete APIs
[Google reCaptcha](https://cloud.google.com/security/products/recaptcha)  
[Google Fronts](https://fonts.google.com/icons) (lokal eingebunden)
