<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/guards.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/config.php';
requireAdmin();
verifyCsrf();

$userId = (int)($_POST['user_id'] ?? 0);
$db = getDB();

// Ne pas supprimer l'admin lui-même
if ($userId === $_SESSION['user_id']) {
    setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    header('Location: ' . APP_URL . '/pages/admin/users.php'); exit;
}

$db->prepare("DELETE FROM Utilisateur WHERE id_utilisateur = ?")
   ->execute([$userId]);

setFlash('success', 'Utilisateur supprimé.');
header('Location: ' . APP_URL . '/pages/admin/users.php'); exit;
