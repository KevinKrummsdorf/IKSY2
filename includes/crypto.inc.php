<?php
declare(strict_types=1);

use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as HaliteCrypto;

/**
 * Erstellt einen nativen PHP Passwort-Hash (Bcrypt/Argon2).
 * Da du sagtest, die DB hat $2y..., nutzt PHP hier korrekt Bcrypt.
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vergleicht ein Passwort mit einem nativen PHP-Hash.
 */
function verifyPassword(string $password, string $hash): bool
{
    // password_verify erkennt automatisch, ob es Bcrypt ($2y...) 
    // oder Argon2 ($argon2id...) ist.
    return password_verify($password, $hash);
}

/**
 * Liefert den Halite-Schlüssel NUR für Datenverschlüsselung (nicht für Passwörter).
 */
function getCryptoKey(): EncryptionKey
{
    global $config;
    $key = $config['halite_key'] ?? null;
    if (!$key instanceof EncryptionKey) {
        throw new \RuntimeException('Ungültiger Halite-Key für Datenverschlüsselung.');
    }
    return $key;
}

/**
 * Verschlüsselt Daten (z.B. 2FA Secrets) mit Halite.
 */
function encryptData(string $plainText): string
{
    return HaliteCrypto::encrypt(new HiddenString($plainText), getCryptoKey());
}

/**
 * Entschlüsselt Daten mit Halite.
 */
function decryptData(string $cipherText): HiddenString
{
    try {
        return HaliteCrypto::decrypt($cipherText, getCryptoKey());
    } catch (\Throwable $e) {
        error_log('Halite decryptData() Fehler: ' . $e->getMessage());
        throw new RuntimeException('Entschlüsselung fehlgeschlagen.');
    }
}