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
 * Validates the submitted CSRF token.
 * Kills the script with a 403 error if validation fails.
 */
function validate_csrf_token(): void
{
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Token is invalid or missing, kill the script.
        error_log('CSRF token validation failed.');
        http_response_code(403);
        die('Erro de seguranca: Token CSRF invalido.');
    }
    
    // Token is valid, unset it to prevent reuse (optional but good practice)
    unset($_SESSION['csrf_token']);
}
