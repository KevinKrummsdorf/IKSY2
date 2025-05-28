<?php
declare(strict_types=1);

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}


