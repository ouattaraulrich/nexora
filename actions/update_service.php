<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireRole('prestataire');
verifyCsrf();

$id          = (int)($_POST['prestation_id'] ?? 0);
$titre       = trim($_POST['titre_prestation'] ?? '');
$description = trim($_POST['description_prestation'] ?? '');
$prix        = $_POST['prix_prestation'] !== '' ? (float)$_POST['prix_prestation'] : null;
$id_service  = (int)($_POST['id_service'] ?? 0);
$est_active  = isset($_POST['est_active']) ? 1 : 0;
$userId      = $_SESSION['user_id'];
$db          = getDB();

$stmt = $db->prepare("
    UPDATE Prestation
    SET titre_prestation = ?, description_prestation = ?,
        prix_prestation  = ?, id_service = ?, est_active = ?
    WHERE id_prestation = ? AND id_utilisateur = ?
");
$stmt->execute([$titre, $description, $prix, $id_service, $est_active, $id, $userId]);

setFlash('success', 'Prestation mise à jour.');
header('Location: ' . APP_URL . '/pages/services.php'); exit;
