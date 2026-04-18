<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
    header('Location: ' . APP_URL . '/pages/register.php'); 
    exit; 
}

verifyCsrf();

$nom         = trim($_POST['nom'] ?? '');
$prenom      = trim($_POST['prenom'] ?? '');
$email       = trim($_POST['email'] ?? '');
$tel         = trim($_POST['telephone'] ?? '');
$id_quartier = (int)($_POST['id_quartier'] ?? 0);
$password    = $_POST['password'] ?? '';
$confirm     = $_POST['password_confirm'] ?? '';

if (!$nom || !$prenom || !$email || !$tel || !$id_quartier || !$password) {
    setFlash('error', 'Tous les champs sont obligatoires.');
    header('Location: ' . APP_URL . '/pages/register.php'); exit;
}

if ($password !== $confirm) {
    setFlash('error', 'Les mots de passe ne correspondent pas.');
    header('Location: ' . APP_URL . '/pages/register.php'); exit;
}

$result = registerUser([
    'email'      => $email,
    'password'   => $password,
    'nom'        => $nom,
    'prenom'     => $prenom,
    'telephone'  => $tel,
    'id_quartier'=> $id_quartier,
]);

if (!$result['success']) {
    setFlash('error', $result['message']);
    header('Location: ' . APP_URL . '/pages/register.php'); // Chemin corrigé
    exit;
}

loginUser($email, $password);
setFlash('success', 'Bienvenue ! Veuillez sélectionner votre profil.');
header('Location: ' . APP_URL . '/pages/choose-role.php'); // Chemin corrigé
exit;