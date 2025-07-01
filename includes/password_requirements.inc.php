<?php
function password_meets_requirements(string $password): bool {
    $lengthOk = preg_match('/^.{8,128}$/', $password) === 1;
    $hasNumber = preg_match('/[0-9]/', $password) === 1;
    $hasLower  = preg_match('/[a-z]/', $password) === 1;
    $hasUpper  = preg_match('/[A-Z]/', $password) === 1;
    $hasSpecial= preg_match('/[^A-Za-z0-9]/', $password) === 1;
    return $lengthOk && $hasNumber && $hasLower && $hasUpper && $hasSpecial;
}
