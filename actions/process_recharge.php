<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['montant'])) {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    $montant = (float)$_POST['montant'];

    if ($montant <= 0) {
        setFlash("Le montant doit être supérieur à 0.", "danger");
        header("Location: /pages/wallet.php");
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. On recharge le solde de l'utilisateur
        $stmt = $db->prepare("UPDATE Utilisateur SET solde_portefeuille = solde_portefeuille + ? WHERE id_utilisateur = ?");
        $stmt->execute([$montant, $userId]);

        // 2. On enregistre la trace dans l'historique
        $stmtHist = $db->prepare("INSERT INTO Transaction (id_utilisateur, montant, type_transaction, description) VALUES (?, ?, 'rechargement', 'Rechargement de compte manuel')");
        $stmtHist->execute([$userId, $montant]);

        $db->commit();
        setFlash("Compte rechargé avec succès de " . number_format($montant, 0, '.', ' ') . " F !", "success");
    } catch (Exception $e) {
        $db->rollBack();
        setFlash("Erreur lors du rechargement.", "danger");
    }

    header("Location: /pages/wallet.php");
    exit;
}