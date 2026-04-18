<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/guards.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin(); // Sécurité
verifyCsrf();   // Protection CSRF

$userId = $_POST['user_id'] ?? null;

if ($userId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE Utilisateur SET est_valide = 1 WHERE id_utilisateur = ?");
    
    if ($stmt->execute([$userId])) {
        setFlash('success', 'Le prestataire a été validé avec succès.');
    } else {
        setFlash('error', 'Erreur lors de la validation.');
    }
}

header('Location: ' . $_SERVER['HTTP_REFERER']); // Retour à la page précédente
exit;