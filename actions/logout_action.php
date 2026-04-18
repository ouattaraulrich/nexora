<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';

// Destruction complète de la session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p["path"], $p["domain"], $p["secure"], $p["httponly"]
    );
}
session_destroy();

// Redirection selon la destination
$dest = $_GET['dest'] ?? 'login';
header('Location: ' . APP_URL  . ($dest === 'register' ? '/pages/register.php' : '/pages/login.php'));
exit;