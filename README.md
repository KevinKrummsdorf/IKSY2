# Anleitung

Diese Anwendung verwendet [Composer](https://getcomposer.org) zur einfachen Installation und Verwaltung der benötigten Bibliotheken. Diese Anleitung geht davon aus, das Composer sowie git bereits intalliert und konfiguriert sind.  
  
**Schritt 1: Repo clonen und Verzeichniss öffnen**
```
git clone https://github.com/KevinKrummsdorf/IKSY2 ~/IKSY2 && cd ~/IKSY2
```
**Schritt 2: Abhängigkeiten installieren**
```
composer install
```
**Schritt 3: Repo aktualisieren** 
  
git Verzeichnis öffnen  
```
cd ~/IKSY2
```
Aktualisierung durchführen   
```
git pull origin main
```
Abhängigkeiten aktualisieren
```
composer update
```  
**Schritt 4: Access Key erzeugen**  
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
       
**Schritt 5: Änderungen nach github pushen** 

git Verzeichnis öffnen  
```
cd ~/IKSY2
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

## Issue erstellen

Bevor du ein Issue eröffnest, prüfe bitte, ob dein Problem oder Wunsch nicht schon existiert. Neue, *größere Fehler* (z. B. Abstürze, Datenverlust, Sicherheitslücken) werden **ausschließlich** über GitHub Issues bearbeitet. Kleinere Fragen bitte über die Whatsapp Gruppe und mich taggen.

Wenn du ein neues Issue anlegst, verwende bitte das folgende Template und fülle **alle** Punkte aus. Issues ohne alle notwendigen Details können nicht bearbeitet werden und werden geschlossen.

---

### 1. Kurze Beschreibung
Beschreibe knapp, was nicht funktioniert bzw. was du dir als neue Funktion wünschst.

> **Beispiel Fehler:** „Die Anwendung stürzt ab, wenn ich `/public/index.php` aufrufe.“  
> **Beispiel Feature:** „Ich hätte gerne eine Dark-Mode-Option.“

---

### 2. Reproduktions-Schritte (bei Fehlern)
Führe Schritt für Schritt auf, wie man den Fehler reproduzieren kann:
1. Repository klonen und `composer install` ausführen    
2. Im Browser `http://localhost:3000/public/index` aufrufen  
4. …

---

### 3. Erwartetes Verhalten
Was sollte stattdessen passieren?

> **Fehler:** „Ich erwarte eine JSON-Antwort mit einem gültigen Token.“  
> **Feature:** „Erwartet wird ein Dark Mode in den Einstellungen.“

---

### 4. Tatsächliches Verhalten (bei Fehlern)
Was passiert stattdessen genau?

> **Beispiel:** „500 Internal Server Error ohne weitere Fehlermeldung.“

---

### 5. Log-Dateien & Screenshots (bei Fehlern)
Füge **unbedingt** die relevanten Log-Dateien als Anhang hinzu (z. B. `register.log`, Browser-Konsole etc.). Screenshots können helfen, sind aber **kein** Ersatz für Logs.

---

### 6. Feature-Requests
Wenn du eine **neue Funktion** oder Verbesserung vorschlagen möchtest, erstelle bitte ein Issue und versehe es mit dem Label `enhancement`.  
Nur vollständige Requests mit klarer Beschreibung werden berücksichtigt.

---

> **Wichtig:**  
> - Nur vollständig ausgefüllte Issues werden bearbeitet.  
> - Unvollständige Issues werden kommentarlos geschlossen.  

# Verwendete Bibliotheken

[halite](https://github.com/paragonie/halite)

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
