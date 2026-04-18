<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: ' . APP_URL . 'login.php'); 
    exit; 
}

verifyCsrf();

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$result = loginUser($email, $password);

if (!$result['success']) {
    setFlash('error', $result['message']);
    header('Location: ' . APP_URL . '/pages/login.php'); // Chemin corrigé
    exit;
}

// Redirection intelligente selon le rôle
if ($result['success']) {
    $role = $_SESSION['role'];

    if ($role === 'admin') {
        // Attention au chemin ici !
        header('Location: ' . APP_URL . '/pages/admin/dashboard.php');
    } elseif ($role === 'nouveau') {
        header('Location: ' . APP_URL . '/pages/choose-role.php');
    } else {
        header('Location: ' . APP_URL . '/pages/dashboard.php');
    }
    exit;
}