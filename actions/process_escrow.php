<?php
/**
 * Traitement du paiement Escrow (Séquestre)
 * Ce fichier sécurise les fonds du client avant le début de la prestation.
 */

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

// Sécurité : Seul un utilisateur connecté peut accéder à ce script
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'])) {
    $db = getDB();
    $id_commande = (int)$_POST['id_commande'];
    $userId = $_SESSION['user_id'];

    try {
        // 1. Vérification : Est-ce que cette commande appartient bien au client ?
        $check = $db->prepare("SELECT id_commande FROM Commande WHERE id_commande = ? AND id_utilisateur = ?");
        $check->execute([$id_commande, $userId]);
        $commande = $check->fetch();

        if (!$commande) {
            setFlash("Action non autorisée ou commande inexistante.", "danger");
            header("Location: ../pages/dashboard.php");
            exit();
        }

        /**
         * 2. LOGIQUE DE PAIEMENT
         * Dans une version de production, c'est ici qu'on appellerait l'API Stripe ou PayPal.
         * Pour l'instant, nous confirmons le paiement en base de données.
         */
        
        $sql = "UPDATE Commande SET paiement_securise = 1 WHERE id_commande = ?";
        $stmt = $db->prepare($sql);
        
        if ($stmt->execute([$id_commande])) {
            // Message de succès avec une icône parlante
            setFlash("🛡️ <strong>Fonds sécurisés !</strong> L'argent est maintenant bloqué en séquestre. Le prestataire a été notifié et peut commencer le travail.", "success");
        } else {
            throw new Exception("Erreur lors de la mise à jour de la transaction.");
        }

    } catch (Exception $e) {
        setFlash("Erreur technique : " . $e->getMessage(), "danger");
    }

    // Redirection vers le dashboard pour voir le changement de statut
    header("Location: ../pages/dashboard.php");
    exit();
} else {
    // Si accès direct sans POST
    header("Location: ../pages/dashboard.php");
    exit();
}