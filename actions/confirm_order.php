<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/guards.php';

// Sécurité : Seul un prestataire connecté peut confirmer une commande
requireLogin();
if (currentRole() !== 'prestataire') {
    setFlash("Accès refusé.", "danger");
    header("Location: ../pages/dashboard.php");
    exit;
}

$db = getDB();
$id_commande = $_POST['id_commande'] ?? null;
$id_prestataire = $_SESSION['user']['id_utilisateur'];

if ($id_commande) {
    try {
        // Sécurité supplémentaire : On vérifie que la commande appartient bien à une prestation de CE prestataire
        $check = $db->prepare("
            SELECT c.id_commande 
            FROM Commande c
            JOIN cibler ci ON ci.id_commande = c.id_commande
            JOIN Prestation p ON p.id_prestation = ci.id_prestation
            WHERE c.id_commande = ? AND p.id_utilisateur = ? AND c.statut = 0
        ");
        $check->execute([$id_commande, $id_prestataire]);
        
        if ($check->fetch()) {
            // Mise à jour du statut à 1 (Confirmé)
            $update = $db->prepare("UPDATE Commande SET statut = 1 WHERE id_commande = ?");
            $update->execute([$id_commande]);

            setFlash("La commande a été confirmée avec succès !", "success");
        } else {
            setFlash("Action impossible ou commande déjà traitée.", "warning");
        }
    } catch (PDOException $e) {
        setFlash("Erreur lors de la confirmation : " . $e->getMessage(), "danger");
    }
}

// Redirection vers le dashboard pour voir le changement de statut
header("Location: ../pages/dashboard.php");
exit;