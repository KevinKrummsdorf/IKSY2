<?php
declare(strict_types=1);

/**
 * Gibt die echte Client-IP zurück (Standard: REMOTE_ADDR).
 * Optional mit Proxy-Erkennung, wenn aktiv gewünscht.
 */
function getClientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Maskiert eine IPv4- oder IPv6-Adresse:
 * - IPv4: letzte beiden Oktette z. B. 192.168.*.*
 * - IPv6: letzter Block z. B. 2001:db8::* 
 * - Sonstige Eingaben: letzte 6 Zeichen ersetzen
 */
function maskIp(string $ip): string
{
    // IPv4 prüfen
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return "{$parts[0]}.{$parts[1]}.*.*";
        }
    }

    // IPv6 prüfen
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $parts = explode(':', $ip);
        // Kürzen auf lesbares Format mit Maskierung des letzten Blocks
        $parts[count($parts) - 1] = '*';
        return implode(':', $parts);
    }

    // Fallback: letzte 6 Zeichen maskieren
    $len = mb_strlen($ip);
    $maskLen = min(6, $len);
    return mb_substr($ip, 0, $len - $maskLen) . str_repeat('*', $maskLen);
}
