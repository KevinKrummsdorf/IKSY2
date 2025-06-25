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
## Repo aktualisieren
  
Verzeichnis öffnen  
```
cd /var/www/html/iksy05/StudyHub/
```
Aktualisierung durchführen   
```
git pull origin main
```
Abhängigkeiten aktualisieren
```
composer update
```  
## Access Key erzeugen
1. Bei github einloggen
2. Rechts oben auf das Profilbild klicken und dann Settings
3. Developer Settings links auswählen
4. Personal access tokens -> Tokens (classic)
6. Generate new token -> generate new token (classic)
7. a. dem token einen Namen geben  
   b. Expiration date setzen (aus Sicherheitsgründen keine unlimitierte Laufzeit auswählen)  
   c. Select scopes -> alles auswählen  
   d. Generate token klicken
     
**Wichtig**  
    Der Access Token sollte **sicher** abgelegt werden, da dieser nicht wieder eingesehen werden kann. Nach Beendigung der Projekts sollte dieser auch wieder gelöscht werden
       
## Änderungen nach github pushen 

Verzeichnis öffnen  
```
cd /var/www/html/iksy05/StudyHub/
```
Staus abfragen  
```
git status
```
Alle Datein hinzufügen
```
git add .
```
commit erstellen
```
git commit -m "Kurze Beschreibung der Änderungen"
```
commit pushen  
```
git push origin test
```  
User = Username  
Passwort = Acces Key  

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
4. Nutzt ohne Root-Rechte eine einfache `.htaccess` ohne Rewrite-Regeln und entfernt die Datei
5. Startet den Apache-Webserver automatisch per `sudo` neu

Nach der Installation sind wichtige Bereiche unter diesen Pfaden erreichbar:
- Eigenes Profil: `/profile/my`
- Profil eines anderen Nutzers: `/profile/<username>`
- Gruppen: `/groups/<gruppenname>`
- Dashboard: `/dashboard`

**Nach der Ausführung ist der Webserver korrekt für die Nutzung benutzerdefinierter Fehlerseiten konfiguriert.**

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

# Support bei Fehlern erhalten (Issues erstellen)

Bevor ein neues Issue eröffnet wird, prüfen Sie bitte, ob das betreffende Problem oder der gewünschte Vorschlag bereits existiert. Neue *Fehler* (z. B. Abstürze, Datenverlust, Sicherheitslücken, weiße Seiten etc.) werden **ausschließlich** über GitHub Issues entgegengenommen und bearbeitet.

Beim Erstellen eines neuen Issues ist das folgende Template zu verwenden. **Alle** Punkte sollten vollständig und präzise ausgefüllt werden. Unvollständige Issues können nicht bearbeitet werden und werden geschlossen.

## 1. Kurze Beschreibung
Eine knappe Beschreibung des Problems oder der gewünschten Funktion.

> **Beispiel Fehler:** „Die Anwendung stürzt ab, wenn die Startseite aufgerufen wird.“
> **Beispiel Feature:** „Eine Dark-Mode-Option in den Einstellungen wäre hilfreich.“

## 2. Reproduktionsschritte (nur bei Fehlern)
Schritt-für-Schritt-Anleitung, wie das Problem reproduziert werden kann:

1. Repository klonen und `composer install` ausführen  
2. Im Browser `http://127.0.0.1/studyhub/` aufrufen
3. …

## 3. Erwartetes Verhalten
Was sollte idealerweise passieren?

> **Fehler:** „Es sollte eine JSON-Antwort mit einem gültigen Token zurückgegeben werden.“  
> **Feature:** „In den Einstellungen sollte ein Dark Mode auswählbar sein.“

## 4. Tatsächliches Verhalten (nur bei Fehlern)
Was passiert stattdessen konkret?

> **Beispiel:** „Es erscheint ein 500 Internal Server Error ohne weitere Fehlermeldung.“

## 5. Log-Dateien & Screenshots (nur bei Fehlern)
Relevante Log-Dateien (z. B. `register.log`, Browser-Konsole etc.) **müssen** beigefügt werden. Screenshots können zusätzlich hilfreich sein, ersetzen jedoch keine Logs.

## 6. Error-Requests
Für **fehlerhafte** oder **unerwartete Requests** sollte ein entsprechendes Issue mit dem Label `bug` erstellt werden und `KevinKrummsdorf` zugeordnet werden.

## 7. Feature-Requests
Für **neue Funktionen** oder Verbesserungsvorschläge sollte ein entsprechendes Issue mit dem Label `enhancement` erstellt werden.  
Nur klar und vollständig beschriebene Vorschläge können berücksichtigt werden.

## **Hinweis:**  
- Es werden ausschließlich vollständig ausgefüllte Issues bearbeitet.  
- Unvollständige Issues werden ohne weiteren Kommentar geschlossen.

# Verwendete Bibliotheken

[Bootstrap](https://github.com/twbs/bootstrap)

[PHPMailer](https://github.com/PHPMailer/PHPMailer)

[monolog](https://github.com/Seldaek/monolog)

[phpdotenv](https://github.com/vlucas/phpdotenv)    

[Smarty](https://github.com/smarty-php/smarty)  

[Halite](https://github.com/paragonie/halite)

# Verwendete Mediaquellen  
[mixkit](https://mixkit.co)

# Verwendete APIs
[Google reCaptcha](https://cloud.google.com/security/products/recaptcha)  
[Google Fronts](https://fonts.google.com/icons) (lokal eingebunden)
