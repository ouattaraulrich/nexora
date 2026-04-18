<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireRole('prestataire');
verifyCsrf();

$titre       = trim($_POST['titre_prestation'] ?? '');
$description = trim($_POST['description_prestation'] ?? '');
$prix        = $_POST['prix_prestation'] ?? null;
$id_service  = (int)($_POST['id_service'] ?? 0);
$est_active  = isset($_POST['est_active']) ? 1 : 0;
$userId      = $_SESSION['user_id'];

if (!$titre || !$id_service) {
    setFlash('error', 'Le titre et le service sont obligatoires.');
    header('Location: ' . APP_URL . '/pages/create_prestation.php'); exit;
}

$db = getDB();
$stmt = $db->prepare("
    INSERT INTO Prestation (titre_prestation, description_prestation, prix_prestation,
                            id_service, id_utilisateur, est_active, datecrea_prestation)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$titre, $description, $prix ?: null, $id_service, $userId, $est_active]);

setFlash('success', 'Prestation créée avec succès.');
header('Location: ' . APP_URL . '/pages/services.php'); exit;
