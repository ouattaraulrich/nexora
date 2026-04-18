<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireRole('client');
verifyCsrf();

$id_prestation = (int)($_POST['prestation_id'] ?? 0);
$userId        = $_SESSION['user_id'];
$user          = currentUser();
$db            = getDB();

// Récupérer le prix de la prestation
$stmt = $db->prepare("SELECT prix_prestation FROM Prestation WHERE id_prestation = ? AND est_active = 1");
$stmt->execute([$id_prestation]);
$prestation = $stmt->fetch();

if (!$prestation || !$id_prestation) {
    setFlash('error', 'Prestation introuvable.');
    header('Location: ' . APP_URL . '/pages/services.php'); exit;
}

$prix = $prestation['prix_prestation'] ?? 0;

// Créer la commande
$stmt = $db->prepare("
    INSERT INTO Commande (date_commande, montant_total, statut, id_quartier, id_utilisateur)
    VALUES (NOW(), ?, 0, ?, ?)
");
$stmt->execute([$prix, $user['id_quartier'], $userId]);
$id_commande = $db->lastInsertId();

// Lier prestation ↔ commande via cibler
$stmt = $db->prepare("
    INSERT INTO cibler (id_prestation, id_commande, prix_unitaire, quantite)
    VALUES (?, ?, ?, 1)
");
$stmt->execute([$id_prestation, $id_commande, $prix]);

setFlash('success', 'Demande envoyée avec succès !');
header('Location: ' . APP_URL . '/pages/requests.php'); exit;
