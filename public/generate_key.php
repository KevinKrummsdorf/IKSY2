<?php
require_once __DIR__ . '/../vendor/autoload.php';
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\EncryptionKey;   

// Ein neues EncryptionKey-Objekt erzeugen
$key = KeyFactory::generateEncryptionKey(); 

// In eine Datei speichern (enthält Header + Prüfsumme)
KeyFactory::save($key, __DIR__ . 'secret-key.txt');

echo "Schlüssel geschrieben nach config/secret-key.txt\n";
