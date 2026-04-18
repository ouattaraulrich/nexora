<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/guards.php';

requireLogin();

if (currentRole() !== 'prestataire') {
    header("Location: ../pages/dashboard.php");
    exit;
}

$db = getDB();
$id_commande = $_POST['id_commande'] ?? null;
$id_prestataire = $_SESSION['user_id']; 

if ($id_commande) {
    try {
        // 1. On vérifie que la commande appartient au prestataire, qu'elle est en cours (statut 1)
        // ET surtout que le paiement a été sécurisé (paiement_securise = 1)
        $check = $db->prepare("
            SELECT c.id_commande, p.prix_prestation, p.titre_prestation
            FROM Commande c
            JOIN cibler ci ON ci.id_commande = c.id_commande
            JOIN Prestation p ON p.id_prestation = ci.id_prestation
            WHERE c.id_commande = ? 
            AND p.id_utilisateur = ? 
            AND c.statut = 1 
            AND c.paiement_securise = 1
        ");
        $check->execute([$id_commande, $id_prestataire]);
        $commandeData = $check->fetch();
        
        if ($commandeData) {
            $db->beginTransaction();

            // 2. Mise à jour du statut de la commande à 2 (Terminé)
            $updateStatut = $db->prepare("UPDATE Commande SET statut = 2 WHERE id_commande = ?");
            $updateStatut->execute([$id_commande]);

            // 3. Transfert des fonds : Crédit du solde
            $montant = $commandeData['prix_prestation'];
            $updatePortefeuille = $db->prepare("
                UPDATE Utilisateur 
                SET solde_portefeuille = solde_portefeuille + ? 
                WHERE id_utilisateur = ?
            ");
            $updatePortefeuille->execute([$montant, $id_prestataire]);

            // 4. NOUVEAU : Enregistrement dans l'historique du portefeuille (Provenance des fonds)
            $stmtTrans = $db->prepare("
                INSERT INTO Transaction (id_utilisateur, montant, type_transaction, description) 
                VALUES (?, ?, 'gain', ?)
            ");
            $description = "Gain mission : " . $commandeData['titre_prestation'] . " (REF-CMD-" . $id_commande . ")";
            $stmtTrans->execute([$id_prestataire, $montant, $description]);

            $db->commit();

            setFlash("✅ Mission terminée ! " . number_format($montant, 0, '.', ' ') . " F ont été ajoutés à votre portefeuille.", "success");
        } else {
            setFlash("Impossible de terminer : la commande doit être en cours et le paiement doit être sécurisé.", "warning");
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        setFlash("Erreur lors de la finalisation : " . $e->getMessage(), "danger");
    }
}

header("Location: ../pages/dashboard.php");
exit;