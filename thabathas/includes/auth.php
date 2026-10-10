<?php
// Funciones compartidas para sesión, autenticación, permisos por rol y protección CSRF.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    ]);
    session_start();
}

function usuarioAutenticado(): bool {
    return isset($_SESSION['usuario']);
}

function exigirLogin(): void {
    if (!usuarioAutenticado()) {
        header('Location: /thabathas/login.php');
        exit;
    }
}

function exigirRol(string $rol): void {
    exigirLogin();
    if (($_SESSION['usuario']['rol'] ?? '') !== $rol) {
        header('Location: /thabathas/index.php');
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificarCsrf(?string $token): bool {
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
