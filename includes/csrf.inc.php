<?php
declare(strict_types=1);

/**
 * Validates the CSRF token from a POST request.
 *
 * This function checks if a CSRF token is present in the POST data and
 * if it matches the one stored in the current user's session.
 * To prevent timing attacks, it uses hash_equals for comparison.
 *
 * If the validation fails (token is missing or doesn't match), it throws
 * a RuntimeException. Otherwise, it returns true.
 *
 * @throws RuntimeException if the CSRF token is invalid or missing.
 * @return true if the token is valid.
 */
function validate_csrf_token(): bool
{
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) {
        throw new RuntimeException('CSRF-Token fehlt.');
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new RuntimeException('Ungültiges CSRF-Token.');
    }

    return true;
}
