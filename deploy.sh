#!/bin/bash
set -e

# Absoluter Pfad zum Projekt
SOURCE_DIR="/home/kevin/projects/StudyHub"
TARGET_DIR="/var/www/studyhub"
PRIVATE_DIR="$TARGET_DIR/private"

echo ".env in $PRIVATE_DIR kopiert"
mkdir -p "$PRIVATE_DIR"
cp "$SOURCE_DIR/.env" "$PRIVATE_DIR/.env"

echo "Code wird nach $TARGET_DIR synchronisiert..."
# Alles kopieren, nur .git ausschließen
rsync -av --exclude='.git' "$SOURCE_DIR/" "$TARGET_DIR/"

echo "Rechte werden gesetzt..."
# Sicherstellen, dass www-data Zugriff hat
chgrp -R www-data "$TARGET_DIR" || true
chmod -R g+rwX "$TARGET_DIR" || true

echo "Deployment abgeschlossen!"