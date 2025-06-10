#!/bin/bash

TARGET_DIR="/var/www/html/iksy05/StudyHub/public"
SYMLINK="/var/www/html/studyhub"
HTACCESS="$TARGET_DIR/.htaccess"
APACHE_CONF="/etc/apache2/sites-available/000-default.conf"
BACKUP_CONF="${APACHE_CONF}.bak"

function setup() {
    echo "Starte Setup für StudyHub unter $SYMLINK ..."

    # 1. Symlink erstellen
    if [ ! -L "$SYMLINK" ]; then
        sudo ln -s "$TARGET_DIR" "$SYMLINK"
        echo "Symlink erstellt: $SYMLINK → $TARGET_DIR"
    else
        echo "Symlink existiert bereits: $SYMLINK"
    fi

    # 2. .htaccess RewriteBase setzen
    if [ -f "$HTACCESS" ]; then
        sudo sed -i 's|^RewriteBase .*|RewriteBase /studyhub/|' "$HTACCESS"
        echo "RewriteBase in .htaccess auf /studyhub/ gesetzt"
    else
        echo ".htaccess nicht gefunden unter $HTACCESS"
    fi

    # 3. Apache-Konfiguration anpassen
    if ! grep -q "/var/www/html/studyhub" "$APACHE_CONF"; then
        echo "Füge <Directory> Block zu Apache-Konfiguration hinzu"
        sudo cp "$APACHE_CONF" "$BACKUP_CONF"
        echo "<Directory /var/www/html/studyhub>
    AllowOverride All
    Require all granted
</Directory>" | sudo tee -a "$APACHE_CONF" > /dev/null
    else
        echo "Apache-Konfiguration enthält bereits den Block für /studyhub"
    fi

    # 4. Apache neu starten
    echo "Starte Apache neu ..."
    sudo systemctl restart apache2
    echo "Setup abgeschlossen. → http://127.0.0.1/studyhub/"
}

function uninstall() {
    echo "Starte Deinstallation von StudyHub-Symlink-Setup ..."

    # 1. Symlink entfernen
    if [ -L "$SYMLINK" ]; then
        sudo rm "$SYMLINK"
        echo "Symlink $SYMLINK entfernt."
    else
        echo "Kein Symlink $SYMLINK vorhanden."
    fi

    # 2. Apache-Konfiguration bereinigen
    if [ -f "$APACHE_CONF" ]; then
        sudo cp "$APACHE_CONF" "$BACKUP_CONF"
        echo "Entferne <Directory /var/www/html/studyhub> Block ..."
        sudo sed -i '/<Directory \/var\/www\/html\/studyhub>/,/<\/Directory>/d' "$APACHE_CONF"
    else
        echo "Apache-Konfig $APACHE_CONF nicht gefunden."
    fi

    # 3. .htaccess RewriteBase zurücksetzen
    if [ -f "$HTACCESS" ]; then
        sudo sed -i 's|^RewriteBase .*|RewriteBase /iksy05/StudyHub/public/|' "$HTACCESS"
        echo "RewriteBase in .htaccess zurückgesetzt"
    else
        echo ".htaccess nicht gefunden: $HTACCESS"
    fi

    # 4. Apache neu starten
    echo "Starte Apache neu ..."
    sudo systemctl restart apache2

    echo "Deinstallation abgeschlossen. Die App ist wieder unter dem alten Pfad erreichbar."
}

# Hauptlogik
if [[ "$1" == "--uninstall" ]]; then
    uninstall
else
    setup
fi
