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
$db->prepare("UPDATE Utilisateur SET est_valide = 1 WHERE id_utilisateur = ? AND est_prestataire = 1")
   ->execute([$userId]);

setFlash('success', 'Prestataire validé.');
header('Location: ' . APP_URL . '/pages/admin/users.php'); exit;
