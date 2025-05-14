<?php
declare(strict_types=1);

use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\Halite\Password;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as HaliteCrypto;

/**
 * Gibt den in config.inc.php als EncryptionKey geladenen Halite-Key zurück.
 *
 * @return EncryptionKey
 * @throws \RuntimeException Wenn der Key fehlt oder nicht den richtigen Typ hat.
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
 * Erzeugt einen sicheren Passwort-Hash mit Halite.
 *
 * @param string $password Klartext-Passwort
 * @return string          Der Hash, wie er in der DB gespeichert wird.
 */
function hashPassword(string $password): string
{
    return Password::hash(
        new HiddenString($password),
        getCryptoKey()
    );
}

/**
 * Verifiziert ein Klartext-Passwort gegen einen Halite-Hash.
 *
 * @param string $password Klartext-Passwort
 * @param string $hash     Passwort-Hash aus der DB
 * @return bool            True, wenn das Passwort stimmt.
 */
function verifyPassword(string $password, string $hash): bool
{
    return Password::verify(
        new HiddenString($password),
        $hash,
        getCryptoKey()
    );
}

/**
 * Verschlüsselt einen Klartext-String mit dem Halite-Key.
 *
 * @param string $plainText Der zu verschlüsselnde Klartext
 * @return string           Der verschlüsselte Wert
 */
function encryptData(string $plainText): string
{
    return HaliteCrypto::encrypt(new HiddenString($plainText), getCryptoKey());
}

/**
 * Entschlüsselt einen mit Halite verschlüsselten Wert.
 *
 * @param string $cipherText Der verschlüsselte Text
 * @return string            Der entschlüsselte Klartext (nicht als HiddenString)
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

