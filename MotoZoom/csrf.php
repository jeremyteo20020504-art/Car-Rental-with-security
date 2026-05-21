<?php
// Turn on exceptions for PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Helper: escape output
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

// CSRF helpers
function generate_csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}
function validate_csrf_token($token): bool {
    return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], (string)$token);
}