# Anleitung im Umgang mit git und github

Diese Anwendung verwendet [Composer](https://getcomposer.org) zur einfachen Installation und Verwaltung der benötigten Bibliotheken. Diese Anleitung geht davon aus, das Composer sowie git bereits intalliert und konfiguriert sind.  
  
## Repo clonen
```
sudo git clone https://github.com/KevinKrummsdorf/IKSY2 /var/www/html/iksy05/StudyHub
```
## Abhängigkeiten installieren
```
cd /var/www/html/iksy05/StudyHub
composer install
```
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

Um das System für die Verwendung eigener Fehlerseiten (z. B. `404.tpl`, `403.tpl` etc.) korrekt einzurichten, kann das mitgelieferte Setup-Skript verwendet werden.

## Setup ausführen

Im Root-Verzeichnis dieses Repositories befindet sich das Skript [`studyhub-manager.sh`](./studyhub-manager.sh), das die notwendigen Konfigurationsschritte automatisch durchführt.

#### Skript ausführbar machen

```bash
chmod +x studyhub-manager.sh
```

#### Skript ausführen

```bash
./studyhub-manager.sh
```

##  Das Skript erledigt automatisch:

1. Erstellt einen symbolischen Link nach `/var/www/html/studyhub`
2. Setzt die `RewriteBase` in `public/.htaccess` auf `/studyhub/`
3. Aktualisiert die Apache-Konfiguration (z. B. virtuelle Hosts)
4. Startet den Apache-Webserver neu

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

Im Root-Verzeichnis dieses Repositories befindet sich das Skript [`studyhub-manager.sh`](./studyhub-manager.sh), das **auch eine Rücknahme der Konfiguration unterstützt**, wenn es im Deinstallationsmodus ausgeführt wird.

### Skript ausführbar machen

```bash
chmod +x studyhub-manager.sh
```

### Skript mit Deinstallations-Flag ausführen

```bash
./studyhub-manager.sh --uninstall
```

## Das Skript macht folgende Änderungen rückgängig:

1. Entfernt den symbolischen Link `/var/www/html/studyhub`
2. Setzt die `RewriteBase` in `public/.htaccess` zurück (z. B. auf `/` oder entfernt sie)
3. Stellt die ursprüngliche Apache-Konfiguration wieder her (sofern gesichert)
4. Startet den Apache-Webserver neu

**Stellen Sie sicher, dass keine anderen Webanwendungen auf den Symlink oder die Apache-Konfiguration angewiesen sind, bevor du die Deinstallation durchführt wird.**

# Support bei Fehlern erhalten (Issues erstellen)

Bevor ein neues Issue eröffnet wird, prüfen Sie bitte, ob das betreffende Problem oder der gewünschte Vorschlag bereits existiert. Neue *Fehler* (z. B. Abstürze, Datenverlust, Sicherheitslücken, weiße Seiten etc.) werden **ausschließlich** über GitHub Issues entgegengenommen und bearbeitet.

Beim Erstellen eines neuen Issues ist das folgende Template zu verwenden. **Alle** Punkte sollten vollständig und präzise ausgefüllt werden. Unvollständige Issues können nicht bearbeitet werden und werden geschlossen.

## 1. Kurze Beschreibung
Eine knappe Beschreibung des Problems oder der gewünschten Funktion.

> **Beispiel Fehler:** „Die Anwendung stürzt ab, wenn `/public/index.php` aufgerufen wird.“  
> **Beispiel Feature:** „Eine Dark-Mode-Option in den Einstellungen wäre hilfreich.“

## 2. Reproduktionsschritte (nur bei Fehlern)
Schritt-für-Schritt-Anleitung, wie das Problem reproduziert werden kann:

1. Repository klonen und `composer install` ausführen  
2. Im Browser `http://127.0.0.1/studyhub/index.php` aufrufen  
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

# Verwendete Mediaquellen  
[mixkit](https://mixkit.co)

# Verwendete APIs
[Google reCaptcha](https://cloud.google.com/security/products/recaptcha)  
[Google Fronts](https://fonts.google.com/icons) (lokal eingebunden)
