<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/guards.php';

requireLogin();
$db = getDB();
$userId = $_SESSION['user']['id_utilisateur'];

// Récupérer tous les avis destinés à ce prestataire
$query = "SELECT a.*, c.date_commande, p.titre_prestation, u.nom_utilisateur, u.prenom_utilisateur
          FROM Avis a
          JOIN Commande c ON a.id_commande = c.id_commande
          JOIN cibler ci ON ci.id_commande = c.id_commande
          JOIN Prestation p ON p.id_prestation = ci.id_prestation
          JOIN Utilisateur u ON u.id_utilisateur = a.id_utilisateur
          WHERE p.id_utilisateur = ?
          ORDER BY a.date_avis DESC";

$stmt = $db->prepare($query);
$stmt->execute([$userId]);
$avisListe = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Avis — Nexora</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .avis-card { background: white; border-radius: 20px; border: 1px solid #f1f5f9; padding: 1.5rem; margin-bottom: 1rem; }
        .star-active { color: #f59e0b; }
        .star-inactive { color: #e2e8f0; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-5">
    <h2 class="fw-800 mb-4">Ce que vos clients disent de vous</h2>

    <?php if (empty($avisListe)): ?>
        <div class="text-center py-5">
            <i class="bi bi-chat-left-dots fs-1 text-muted"></i>
            <p class="text-muted mt-3">Vous n'avez pas encore reçu d'avis.</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($avisListe as $a): ?>
                <div class="col-md-6">
                    <div class="avis-card shadow-sm">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-800 m-0"><?= e($a['prenom_utilisateur'] . ' ' . $a['nom_utilisateur']) ?></h6>
                                <small class="text-primary fw-600"><?= e($a['titre_prestation']) ?></small>
                            </div>
                            <div class="text-end">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="bi bi-star-fill <?= $i <= $a['note'] ? 'star-active' : 'star-inactive' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="text-muted small mt-3 italic">"<?= e($a['commentaire']) ?>"</p>
                        <hr class="opacity-05">
                        <small class="text-muted" style="font-size: 0.7rem;">Le <?= date('d/m/Y', strtotime($a['date_avis'])) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>