<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (file_exists(__DIR__ . '/../includes/guards.php')) {
    require_once __DIR__ . '/../includes/guards.php';
}

requireLogin();

$db = getDB();
$user = currentUser();
$id_prestation = (int)($_GET['id_prestation'] ?? 0);

if ($id_prestation > 0 && currentRole() === 'client') {
    try {
        $db->beginTransaction();

        // 1. RÉCUPÉRATION DU QUARTIER DU CLIENT (Indispensable pour ta contrainte SQL)
        $stmtUser = $db->prepare("SELECT id_quartier FROM Utilisateur WHERE id_utilisateur = ?");
        $stmtUser->execute([$user['id_utilisateur']]);
        $userData = $stmtUser->fetch();
        $id_quartier = $userData['id_quartier'] ?? null;

        // Remplace 'En cours' par 0 (Nouveau/En attente)
$stmt = $db->prepare("
    INSERT INTO Commande (date_commande, statut, id_utilisateur, id_quartier)
    VALUES (NOW(), 0, ?, ?)
"); //

        $stmt->execute([$user['id_utilisateur'], $id_quartier]);
        $id_cmd = $db->lastInsertId();

        // 3. LIAISON PRESTATION
        $stmt2 = $db->prepare("INSERT INTO cibler (id_prestation, id_commande) VALUES (?, ?)");
        $stmt2->execute([$id_prestation, $id_cmd]);

        // 4. INFOS POUR WHATSAPP
        $stmtTel = $db->prepare("
            SELECT u.num_utilisateur, p.titre_prestation
            FROM Prestation p
            JOIN Utilisateur u ON u.id_utilisateur = p.id_utilisateur
            WHERE p.id_prestation = ?
        ");
        $stmtTel->execute([$id_prestation]);
        $data = $stmtTel->fetch();

        $db->commit();

        // 5. REDIRECTION WHATSAPP
        if ($data && !empty($data['num_utilisateur'])) {
            $telClean = preg_replace('/[^0-9]/', '', $data['num_utilisateur']);
            if (strlen($telClean) === 10) $telClean = "225" . $telClean;

            $msg = rawurlencode("Bonjour, je viens de commander votre service : " . $data['titre_prestation'] . " (Commande #$id_cmd)");
            header("Location: https://wa.me/" . $telClean . "?text=" . $msg);
            exit();
        } else {
            header("Location: ../pages/dashboard.php?status=success");
            exit();
        }

    } catch (Exception $e) {
        $db->rollBack();
        die("Erreur lors de la commande : " . $e->getMessage());
    }
} else {
    header("Location: ../pages/catalogue.php");
    exit();
}