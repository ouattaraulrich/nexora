<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/config.php';

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireRole(string ...$roles): void {
    requireLogin();
    if (!in_array(currentRole(), $roles, true)) {
        header('Location: ' . APP_URL . '/pages/dashboard.php');
        exit;
    }
}

function requireAdmin(): void {
    requireRole('admin');
}

function redirectIfLoggedIn(): void {
    if (isLoggedIn()) {
        $role = currentRole();
        if ($role === 'admin') {
            header('Location: ' . APP_URL . '/pages/admin/dashboard.php');
        } elseif ($role === 'nouveau') {
            header('Location: ' . APP_URL . '/choose-role.php');
        } else {
            header('Location: ' . APP_URL . '/pages/dashboard.php');
        }
        exit;
    }
}
