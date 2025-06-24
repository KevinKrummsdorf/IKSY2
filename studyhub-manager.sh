#!/bin/bash

HTACCESS_DIR="public"
BASIC_HTACCESS="$HTACCESS_DIR/.htaccess"
FULL_HTACCESS="$HTACCESS_DIR/.htaccess.full"
MARKER="$HTACCESS_DIR/.pretty_urls_enabled"

function setup() {
    if [ "$EUID" -ne 0 ]; then
        echo "Keine Root-Rechte: Basiskonfiguration wird verwendet." 
        cp "$BASIC_HTACCESS" "$HTACCESS_DIR/.htaccess"
        rm -f "$MARKER"
        exit 0
    fi

    echo "Aktiviere mod_rewrite und installiere erweiterte .htaccess ..."
    a2enmod rewrite
    cp "$FULL_HTACCESS" "$HTACCESS_DIR/.htaccess"
    touch "$MARKER"
    systemctl restart apache2
    echo "Setup abgeschlossen."
}

function uninstall() {
    if [ "$EUID" -ne 0 ]; then
        echo "Deinstallation erfordert Root-Rechte"
        exit 1
    fi

    echo "Deaktiviere mod_rewrite und stelle Basiskonfiguration wieder her ..."
    a2dismod rewrite
    cp "$BASIC_HTACCESS" "$HTACCESS_DIR/.htaccess"
    rm -f "$MARKER"
    systemctl restart apache2
    echo "Konfiguration entfernt."
}

case "$1" in
    --uninstall) uninstall ;;
    *) setup ;;
esac
