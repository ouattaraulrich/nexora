<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path'     => '/',
        'secure'   => false, // Mettre à true si tu es en HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array {
    // On s'assure de retourner les données en session
    return $_SESSION['user'] ?? null;
}

function currentRole(): ?string {
    // Retourne 'client', 'prestataire', 'admin' ou 'nouveau'
    return $_SESSION['role'] ?? null;
}