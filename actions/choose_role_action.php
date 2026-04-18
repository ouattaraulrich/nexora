<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

// 1. Sécurité de base
requireLogin(); 
verifyCsrf();

$role = $_POST['role'] ?? '';

/**
 * 2. RÉCUPÉRATION DE L'ID (Multi-sources pour être sûr à 100%)
 */
$userId = null;
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} elseif (isset($_SESSION['user']['id_utilisateur'])) {
    $userId = $_SESSION['user']['id_utilisateur'];
}

// Si après ça on n'a toujours rien, on ne peut pas faire d'UPDATE
if (!$userId) {
    setFlash('error', 'Session perdue. Veuillez vous reconnecter.');
    header('Location: ' . APP_URL . '/pages/login.php');
    exit;
}

// 3. Validation du rôle envoyé par le formulaire
if (!in_array($role, ['client', 'prestataire'])) {
    setFlash('error', 'Choix du rôle invalide.');
    header('Location: ' . APP_URL . '/pages/choose-role.php'); 
    exit;
}

/**
 * 4. MISE À JOUR EN BASE DE DONNÉES
 * J'utilise les noms exacts de ta photo : est_prestataire et est_client
 */
$db = getDB();
try {
    if ($role === 'prestataire') {
        $stmt = $db->prepare("UPDATE Utilisateur SET est_prestataire = 1, est_client = 0 WHERE id_utilisateur = ?");
    } else {
        $stmt = $db->prepare("UPDATE Utilisateur SET est_client = 1, est_prestataire = 0 WHERE id_utilisateur = ?");
    }
    
    $success = $stmt->execute([$userId]);

    if ($success) {
        // 5. Mise à jour de la session pour le reste de la navigation
        $_SESSION['role'] = $role;
        $_SESSION['user_role'] = $role;
        
        setFlash('success', 'Votre profil est maintenant configuré !');
        header('Location: ' . APP_URL . '/pages/dashboard.php');
        exit;
    } else {
        throw new Exception("L'exécution de la requête a échoué.");
    }

} catch (Exception $e) {
    setFlash('error', 'Erreur technique : ' . $e->getMessage());
    header('Location: ' . APP_URL . '/pages/choose-role.php');
    exit;
}