<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireRole('prestataire');
verifyCsrf();

$id_commande = (int)($_POST['commande_id'] ?? 0);
$statut      = (int)($_POST['statut'] ?? 0);
$userId      = $_SESSION['user_id'];
$db          = getDB();

// Vérifier que la commande appartient bien à une prestation du prestataire connecté
$stmt = $db->prepare("
    SELECT c.id_commande FROM Commande c
    JOIN cibler ci ON ci.id_commande = c.id_commande
    JOIN Prestation p ON p.id_prestation = ci.id_prestation
    WHERE c.id_commande = ? AND p.id_utilisateur = ?
");
$stmt->execute([$id_commande, $userId]);

if (!$stmt->fetch()) {
    setFlash('error', 'Action non autorisée.');
    header('Location: ' . APP_URL . '/pages/requests.php'); exit;
}

$db->prepare("UPDATE Commande SET statut = ? WHERE id_commande = ?")
   ->execute([$statut, $id_commande]);

setFlash('success', 'Statut mis à jour.');
header('Location: ' . APP_URL . '/pages/requests.php'); exit;
