<?php
declare(strict_types=1);

use ParagonIE\Halite\Symmetric\EncryptionKey;
use ParagonIE\HiddenString\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto as HaliteCrypto;

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function getCryptoKey(): EncryptionKey
{
    global $config;
    $key = $config['halite_key'] ?? null;
    if (!$key instanceof EncryptionKey) {
        throw new \RuntimeException('Ungültiger Halite-Key: Erwarte ein EncryptionKey-Objekt.');
    }
    return $key;
}

function encryptData(string $plainText): string
{
    return HaliteCrypto::encrypt(new HiddenString($plainText), getCryptoKey());
}

function decryptData(string $cipherText): HiddenString
{
    try {
        return HaliteCrypto::decrypt($cipherText, getCryptoKey());
    } catch (Throwable $e) {
        throw new RuntimeException('Entschlüsselung fehlgeschlagen: ' . $e->getMessage(), 0, $e);
    }
}
