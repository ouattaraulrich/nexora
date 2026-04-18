<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/dashboard.php");
    exit;
}

$db = getDB();
$id_commande = $_POST['id_commande'];
$note = (int)$_POST['note'];
$commentaire = trim($_POST['commentaire']);
$userId = $_SESSION['user']['id_utilisateur'];

try {
    // 1. Vérifier à nouveau la légitimité (Sécurité)
    $check = $db->prepare("SELECT id_commande FROM Commande WHERE id_commande = ? AND id_utilisateur = ? AND statut = 2");
    $check->execute([$id_commande, $userId]);
    
    if ($check->fetch()) {
        // 2. Insérer l'avis
        $ins = $db->prepare("INSERT INTO Avis (note, commentaire, id_commande, id_utilisateur, date_avis) VALUES (?, ?, ?, ?, NOW())");
        $ins->execute([$note, $commentaire, $id_commande, $userId]);

        setFlash("Merci ! Votre avis a été publié.", "success");
    } else {
        setFlash("Action non autorisée.", "danger");
    }
} catch (PDOException $e) {
    setFlash("Erreur lors de l'enregistrement de l'avis.", "danger");
}

header("Location: ../pages/dashboard.php");
exit;