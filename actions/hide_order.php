<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérification de la session et du jeton de sécurité si nécessaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande'])) {
    $db = getDB();
    $id_commande = $_POST['id_commande'];
    $userId = $_SESSION['user']['id_utilisateur'];
    $role = $_SESSION['user']['role']; // On récupère le rôle stocké en session

    if ($role === 'prestataire') {
        // Le prestataire masque la commande s'il en est bien l'auteur via la prestation
        $stmt = $db->prepare("
            UPDATE Commande c
            JOIN cibler ci ON ci.id_commande = c.id_commande
            JOIN Prestation p ON p.id_prestation = ci.id_prestation
            SET c.masquee_prestataire = 1
            WHERE c.id_commande = ? AND p.id_utilisateur = ?
        ");
        $params = [$id_commande, $userId];
    } else {
        // Le client masque la commande si c'est bien lui qui l'a passée
        $stmt = $db->prepare("
            UPDATE Commande 
            SET masquee_client = 1
            WHERE id_commande = ? AND id_utilisateur = ?
        ");
        $params = [$id_commande, $userId];
    }

    try {
        if ($stmt->execute($params)) {
            setFlash("Commande masquée de votre historique.", "info");
        } else {
            setFlash("Erreur lors de l'opération.", "danger");
        }
    } catch (PDOException $e) {
        // En cas de colonne manquante dans la base de données
        setFlash("Erreur technique : assurez-vous que les colonnes de masquage existent.", "danger");
    }
}

// Redirection vers le tableau de bord
header("Location: ../pages/dashboard.php");
exit;