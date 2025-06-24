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
}

function apply_full() {
    cp "$HTACCESS_FULL" "$PUBLIC_DIR/.htaccess"
    touch "$MARKER"
    echo "Full configuration applied."
    if command -v a2enmod >/dev/null; then
        a2enmod rewrite
    fi
    if command -v systemctl >/dev/null; then
        systemctl restart apache2
    fi
}

function uninstall_conf() {
    cp "$HTACCESS_SIMPLE" "$PUBLIC_DIR/.htaccess"
    rm -f "$MARKER"
    if command -v a2dismod >/dev/null; then
        a2dismod rewrite
    fi
    if command -v systemctl >/dev/null; then
        systemctl restart apache2
    fi
    echo "Configuration reverted."
}

if [[ "$1" == "--uninstall" ]]; then
    uninstall_conf
elif [ "$(id -u)" -eq 0 ]; then
    apply_full
else
    apply_simple
fi
