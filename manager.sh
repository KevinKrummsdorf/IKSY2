#!/bin/bash

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
HTACCESS_SIMPLE="$ROOT_DIR/config/htaccess.simple"
HTACCESS_FULL="$ROOT_DIR/config/htaccess.full"
PUBLIC_DIR="$ROOT_DIR/public"
MARKER="$ROOT_DIR/config/pretty_urls_enabled"

function apply_simple() {
    cp "$HTACCESS_SIMPLE" "$PUBLIC_DIR/.htaccess"
    rm -f "$MARKER"
    echo "Simple configuration applied."
    restart_apache
}

function apply_full() {
    cp "$HTACCESS_FULL" "$PUBLIC_DIR/.htaccess"
    touch "$MARKER"
    echo "Full configuration applied."
    if command -v a2enmod >/dev/null; then
        sudo a2enmod rewrite
    fi
    restart_apache
}

function uninstall_conf() {
    cp "$HTACCESS_SIMPLE" "$PUBLIC_DIR/.htaccess"
    rm -f "$MARKER"
    if command -v a2dismod >/dev/null; then
        sudo a2dismod rewrite
    fi
    restart_apache
    echo "Configuration reverted."
}

function restart_apache() {
    if command -v systemctl >/dev/null; then
        echo "Restarting Apache via systemctl..."
        sudo systemctl restart apache2
    elif command -v service >/dev/null; then
        echo "Restarting Apache via service..."
        sudo service apache2 restart
    else
        echo "Could not restart Apache automatically."
    fi
}

if [[ "$1" == "--uninstall" ]]; then
    uninstall_conf
elif [ "$(id -u)" -eq 0 ]; then
    apply_full
else
    apply_simple
fi
