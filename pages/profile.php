<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireLogin();

$user = currentUser();
$role = currentRole();
$db   = getDB();
$loc  = getLocationData();

// Avis reçus (prestataire)
$avis = [];
if ($role === 'prestataire') {
    $stmt = $db->prepare("
        SELECT ci.evaluation, ci.commentaire, c.date_commande,
               u.nom_utilisateur, u.prenom_utilisateur,
               p.titre_prestation, s.nom_service
        FROM cibler ci
        JOIN Commande c    ON c.id_commande   = ci.id_commande
        JOIN Prestation p  ON p.id_prestation = ci.id_prestation
        JOIN Service s     ON s.id_service    = p.id_service
        JOIN Utilisateur u ON u.id_utilisateur = c.id_utilisateur
        WHERE p.id_utilisateur = ? AND ci.evaluation IS NOT NULL
        ORDER BY c.date_commande DESC LIMIT 10
    ");
    $stmt->execute([$user['id_utilisateur']]);
    $avis = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil — <?= APP_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/css/style.css">

    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }

        .profile-card {
            background: white;
            border: 1px solid var(--gray-100);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .profile-header-bg {
            height: 100px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%);
        }

        .profile-avatar-wrapper {
            margin-top: -50px;
            padding: 0 2rem;
            display: flex;
            align-items: flex-end;
            gap: 1.5rem;
        }

        .avatar-huge {
            width: 100px;
            height: 100px;
            border-radius: 30px;
            background: white;
            padding: 5px;
            box-shadow: var(--shadow);
        }

        .avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 25px;
            background: var(--accent-soft);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
        }

        .section-title {
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            background-color: var(--gray-50);
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            background-color: white;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .review-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-50);
            transition: var(--transition);
        }

        .review-item:hover { background-color: var(--gray-50); }

        .star-active { color: #fbbf24; }
        .star-inactive { color: #e5e7eb; }

        .badge-role {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-5" style="max-width: 850px;">
    <?= flashHtml() ?>

    <div class="profile-card mb-5">
        <div class="profile-header-bg"></div>
        <div class="profile-avatar-wrapper mb-4">
            <div class="avatar-huge">
                <div class="avatar-inner">
                    <?= initials($user['prenom_utilisateur'].' '.$user['nom_utilisateur']) ?>
                </div>
            </div>
            <div class="mb-2">
                <h2 class="fw-800 mb-1" style="letter-spacing: -0.02em;">
                    <?= e($user['prenom_utilisateur'].' '.$user['nom_utilisateur']) ?>
                </h2>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge-role bg-primary text-white">
                        <?= $role === 'admin' ? 'Administrateur' : ($role === 'prestataire' ? 'Prestataire' : 'Client') ?>
                    </span>
                    <?php if ($role === 'prestataire'): ?>
                        <span class="badge-role <?= $user['est_valide'] ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?>">
                            <i class="bi <?= $user['est_valide'] ? 'bi-patch-check-fill' : 'bi-clock-history' ?> me-1"></i>
                            <?= $user['est_valide'] ? 'Compte Validé' : 'En attente' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="px-4 pb-4">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-envelope-at fs-5 text-accent"></i>
                        <?= e($user['email_utilisateur']) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-phone fs-5 text-accent"></i>
                        <?= e($user['num_utilisateur']) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php if (!empty($user['nom_quartier'])): ?>
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-geo-alt fs-5 text-accent"></i>
                        <?= e($user['nom_quartier'].', '.$user['nom_ville']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-12">
            <div class="profile-card p-4">
                <h5 class="section-title"><i class="bi bi-person-gear text-accent"></i> Paramètres du compte</h5>
                <form action="<?= APP_URL ?>/actions/update_profile.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nom</label>
                            <input type="text" name="nom" class="form-control" value="<?= e($user['nom_utilisateur']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Prénom</label>
                            <input type="text" name="prenom" class="form-control" value="<?= e($user['prenom_utilisateur']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Adresse e-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= e($user['email_utilisateur']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" value="<?= e($user['num_utilisateur']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Changer le mot de passe</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Laisser vide pour ne pas modifier" minlength="8">
                        </div>
                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold rounded-pill">
                                Mettre à jour mon profil
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($role === 'prestataire'): ?>
        <div class="col-lg-12">
            <div class="profile-card">
                <div class="p-4 border-bottom">
                    <h5 class="section-title mb-0">
                        <i class="bi bi-stars text-warning"></i> 
                        Avis de vos clients (<?= count($avis) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($avis)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-star display-4 text-light"></i>
                            <p class="text-muted mt-3">Vous n'avez pas encore reçu d'avis.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($avis as $av): ?>
                        <div class="review-item">
                            <div class="d-flex gap-3">
                                <div class="avatar-circle" style="width:40px; height:40px; background:var(--gray-100); border-radius:12px; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem;">
                                    <?= initials($av['prenom_utilisateur'].' '.$av['nom_utilisateur']) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <h6 class="fw-bold mb-0"><?= e($av['prenom_utilisateur'].' '.$av['nom_utilisateur']) ?></h6>
                                        <span class="text-muted extra-small"><?= date('d/m/Y', strtotime($av['date_commande'])) ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <i class="bi bi-star-fill <?= $i <= (int)$av['evaluation'] ? 'star-active' : 'star-inactive' ?> small"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-muted small mb-2 italic">"<?= e($av['commentaire'] ?? 'Client satisfait.') ?>"</p>
                                    <div class="badge bg-light text-muted fw-normal" style="font-size: 0.7rem;">
                                        <i class="bi bi-tag me-1"></i> <?= e($av['titre_prestation']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>