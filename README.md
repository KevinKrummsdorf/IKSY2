# Anleitung

Diese Anwendung verwendet [Composer](https://getcomposer.org) zur einfachen Installation und Verwaltung der benötigten Bibliotheken. Diese Anleitung geht davon aus, das Composer sowie git bereits intalliert und konfiguriert ist.  
  
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
[Google Fronts](https://fonts.google.com/icons)
