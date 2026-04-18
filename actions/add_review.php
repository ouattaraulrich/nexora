<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireRole('client');
verifyCsrf();

$id_prestation = (int)($_POST['prestation_id'] ?? 0);
$id_commande   = (int)($_POST['commande_id']   ?? 0);
$evaluation    = (int)($_POST['evaluation']    ?? 0);
$commentaire   = trim($_POST['commentaire']    ?? '');
$userId        = $_SESSION['user_id'];
$db            = getDB();

// Vérifier que la commande appartient au client et est terminée
$stmt = $db->prepare("
    SELECT 1 FROM Commande WHERE id_commande = ? AND id_utilisateur = ? AND statut = 2
");
$stmt->execute([$id_commande, $userId]);
if (!$stmt->fetch()) {
    setFlash('error', 'Avis impossible pour cette commande.');
    header('Location: ' . APP_URL . '/pages/requests.php'); exit;
}

// Mettre à jour l'évaluation dans cibler
$stmt = $db->prepare("
    UPDATE cibler SET evaluation = ?, commentaire = ?
    WHERE id_prestation = ? AND id_commande = ?
");
$stmt->execute([$evaluation ?: null, $commentaire ?: null, $id_prestation, $id_commande]);

setFlash('success', 'Avis publié, merci !');
header('Location: ' . APP_URL . '/pages/requests.php'); exit;
