<?php
declare(strict_types=1);

use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as HaliteCrypto;

/**
 * Erstellt einen sicheren Hash eines Passworts.
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vergleicht ein Passwort mit seinem Hash.
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
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
