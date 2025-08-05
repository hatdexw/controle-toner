<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generates and stores a CSRF token in the session.
 * @return string The generated token.
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies the submitted CSRF token against the one in the session.
 * @param string $token The token submitted by the user.
 * @return bool True if the token is valid, false otherwise.
 */
function verify_csrf_token(string $token): bool
{
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        error_log('CSRF token validation failed.');
        return false;
    }
    
    // Token is valid, unset it to prevent reuse
    unset($_SESSION['csrf_token']);
    return true;
}
