<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireRole('prestataire');
verifyCsrf();

$id    = (int)($_POST['prestation_id'] ?? 0);
$userId = $_SESSION['user_id'];
$db    = getDB();

$stmt = $db->prepare("DELETE FROM Prestation WHERE id_prestation = ? AND id_utilisateur = ?");
$stmt->execute([$id, $userId]);

setFlash('success', 'Prestation supprimée.');
header('Location: ' . APP_URL . '/pages/services.php'); exit;
