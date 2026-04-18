<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireLogin();
verifyCsrf();

$userId  = $_SESSION['user_id'];
$nom     = trim($_POST['nom'] ?? '');
$prenom  = trim($_POST['prenom'] ?? '');
$email   = trim($_POST['email'] ?? '');
$tel     = trim($_POST['telephone'] ?? '');
$newPwd  = $_POST['new_password'] ?? '';
$db      = getDB();

$params = [$nom, $prenom, $email, $tel];
$sql    = "UPDATE Utilisateur SET nom_utilisateur=?, prenom_utilisateur=?, email_utilisateur=?, num_utilisateur=?";

if ($newPwd) {
    if (strlen($newPwd) < 8) {
        setFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
        header('Location: ' . APP_URL . '/pages/profile.php'); exit;
    }
    $sql .= ", mot_de_passe=?";
    $params[] = password_hash($newPwd, PASSWORD_DEFAULT);
}
$sql .= " WHERE id_utilisateur=?";
$params[] = $userId;

$db->prepare($sql)->execute($params);

// Rafraîchir la session
$stmt = $db->prepare("SELECT * FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$userId]);
$_SESSION['user'] = $stmt->fetch();

setFlash('success', 'Profil mis à jour.');
header('Location: ' . APP_URL . '/pages/profile.php'); exit;
