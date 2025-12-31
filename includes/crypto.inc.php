<?php
declare(strict_types=1);

use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as HaliteCrypto;
use ParagonIE\Halite\Password;

/**
 * Erstellt einen sicheren Hash eines Passworts.
 */
function hashPassword(string $password): string
{
    return Password::hash(new HiddenString($password), getCryptoKey());
}

/**
 * Vergleicht ein Passwort mit seinem Hash.
 */
function verifyPassword(string $password, string $hash): bool
{
    try {
        return Password::verify(new HiddenString($password), $hash, getCryptoKey());
    } catch (\Throwable $ex) {
        // Log error, and return false
        return false;
    }
}

/**
 * Liefert den in der Konfiguration hinterlegten Halite-Schlüssel.
 */
function getCryptoKey(): EncryptionKey
{
    global $config;
    $key = $config['halite_key'] ?? null;
    if (!$key instanceof EncryptionKey) {
        throw new \RuntimeException('Ungültiger Halite-Key: Erwarte ein EncryptionKey-Objekt.');
    }
    return $key;
}

/**
 * Verschlüsselt Klartext mit dem Halite-Key.
 */
function encryptData(string $plainText): string
{
    return HaliteCrypto::encrypt(new HiddenString($plainText), getCryptoKey());
}

/**
 * Entschlüsselt zuvor verschlüsselte Daten.
 */
function decryptData(string $cipherText): HiddenString
{
    try {
        return HaliteCrypto::decrypt($cipherText, getCryptoKey());
    } catch (Throwable $e) {
        error_log('Halite decryptData() Fehler: ' . $e->getMessage());
        throw new RuntimeException('Entschlüsselung fehlgeschlagen: ' . $e->getMessage(), 0, $e);
    }
}
