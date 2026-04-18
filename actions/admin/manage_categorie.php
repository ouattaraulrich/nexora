<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/guards.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

requireAdmin();
verifyCsrf();

$db     = getDB();
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $nom   = trim($_POST['nom_categorie'] ?? '');
    // On récupère l'icône, si vide on met une icône par défaut
    $icone = trim($_POST['icone_categorie'] ?? 'bi-tag'); 
    
    if ($nom) {
        // Ajout de la colonne icone_categorie dans l'INSERT
        $db->prepare("INSERT INTO Categorie (nom_categorie, icone_categorie) VALUES (?, ?)")
           ->execute([$nom, $icone]);
        setFlash('success', 'Catégorie ajoutée avec son icône.');
    }

} elseif ($action === 'delete') {
    $id = (int)($_POST['id_categorie'] ?? 0);
    // Note : Attention si des services sont liés, selon tes contraintes SQL 
    // il faudra peut-être supprimer les services d'abord ou utiliser un ON DELETE CASCADE
    $db->prepare("DELETE FROM Categorie WHERE id_categorie = ?")->execute([$id]);
    setFlash('success', 'Catégorie supprimée.');

} elseif ($action === 'add_service') {
    $nom   = trim($_POST['nom_service'] ?? '');
    $catId = (int)($_POST['id_categorie'] ?? 0);
    if ($nom && $catId) {
        $db->prepare("INSERT INTO Service (nom_service, id_categorie) VALUES (?,?)")->execute([$nom, $catId]);
        setFlash('success', 'Service ajouté.');
    }

} elseif ($action === 'delete_service') {
    $id = (int)($_POST['id_service'] ?? 0);
    $db->prepare("DELETE FROM Service WHERE id_service = ?")->execute([$id]);
    setFlash('success', 'Service supprimé.');
}

header('Location: ' . APP_URL . '/pages/admin/categories.php'); 
exit;