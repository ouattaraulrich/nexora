<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/guards.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

requireAdmin();
$db = getDB();

$id_prestation = $_GET['id'] ?? null;

if (!$id_prestation) {
    header('Location: categories.php');
    exit;
}

$stmt = $db->prepare("
    SELECT p.*, s.nom_service, c.nom_categorie, 
           u.nom_utilisateur, u.prenom_utilisateur, u.email_utilisateur, u.num_utilisateur,
           q.nom_quartier
    FROM Prestation p
    JOIN Service s ON s.id_service = p.id_service
    JOIN Categorie c ON c.id_categorie = s.id_categorie
    JOIN Utilisateur u ON u.id_utilisateur = p.id_utilisateur
    LEFT JOIN Quartier q ON q.id_quartier = u.id_quartier
    WHERE p.id_prestation = ?
");
$stmt->execute([$id_prestation]);
$prestation = $stmt->fetch();

if (!$prestation) {
    die("Prestation introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails Prestation #<?= $id_prestation ?> — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --main-bg: #f8fafc;
            --accent: #4f46e5;
            --font-main: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background-color: var(--main-bg); 
            font-family: var(--font-main); 
            color: #1e293b;
        }

        /* SIDEBAR (Inchangée selon ta demande) */
        .sidebar {
            width: 280px; background: var(--sidebar-bg); height: 100vh;
            position: fixed; color: white; padding: 1.5rem;
        }
        .main-wrapper { margin-left: 280px; padding: 2rem; min-height: 100vh; }

        /* NOUVEAU DESIGN DU CONTENU */
        .page-header { margin-bottom: 2rem; }
        
        .custom-card {
            background: white; 
            border-radius: 1.5rem; 
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.04);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: #0f172a;
        }

        .price-tag {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: -1px;
        }

        .category-badge {
            background: #eef2ff;
            color: var(--accent);
            padding: 0.5rem 1rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-block;
        }

        .img-placeholder {
            background: #f1f5f9;
            border-radius: 1rem;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #cbd5e1;
            color: #94a3b8;
        }

        .avatar-circle {
            width: 70px; height: 70px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 auto 1rem;
        }

        .btn-pill {
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 700;
            transition: all 0.3s;
        }
    </style>
</head>
<body>

<nav class="sidebar">
    <div class="mb-5 px-2">
        <h4 class="fw-bold text-white"><?= APP_NAME ?> <span class="text-primary text-opacity-75">Admin</span></h4>
    </div>
    <div class="nav flex-column gap-2">
        <a href="dashboard.php" class="nav-link text-white-50 p-3">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="users.php" class="nav-link text-white bg-primary rounded-3 p-3 shadow-sm">
            <i class="bi bi-people me-2"></i> Utilisateurs
        </a>
        <a href="categories.php" class="nav-link text-white-50 p-3">
            <i class="bi bi-tags me-2"></i> Catégories
        </a>
        <hr class="text-white-50 mt-4">
        <a href="<?= APP_URL ?>/actions/logout_action.php" class="nav-link text-danger p-3">
            <i class="bi bi-box-arrow-left me-2"></i> Déconnexion
        </a>
    </div>
</nav>

<div class="main-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="categories.php" class="text-decoration-none text-muted fw-600">
                <i class="bi bi-chevron-left"></i> Retour
            </a>
            <h2 class="fw-800 mt-2 mb-0">Détails de l'offre</h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger btn-pill">Supprimer</button>
            <button class="btn btn-primary btn-pill px-4 shadow-sm">Valider la prestation</button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="custom-card">
                <div class="mb-4">
                    <div class="category-badge mb-3">
                        <?= e($prestation['nom_categorie']) ?> &bull; <?= e($prestation['nom_service']) ?>
                    </div>
                    <h1 class="fw-800 mb-2" style="font-size: 2.25rem; letter-spacing: -1px;"><?= e($prestation['titre_prestation']) ?></h1>
                    <div class="text-muted"><i class="bi bi-calendar-event me-2"></i> Publiée le 04 Avril 2026</div>
                </div>

                <div class="mb-5">
                    <div class="info-label">Description du service</div>
                    <p class="info-value text-muted" style="line-height: 1.8; font-size: 1.05rem;">
                        <?= nl2br(e($prestation['description_prestation'])) ?>
                    </p>
                </div>

                <div class="row g-4 pt-4 border-top">
                    <div class="col-md-6">
                        <div class="info-label">Localisation</div>
                        <div class="info-value d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-danger me-2 fs-5"></i>
                            <?= e($prestation['nom_quartier'] ?? 'Abidjan, Côte d\'Ivoire') ?>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="info-label">Prix de la prestation</div>
                        <div class="price-tag">
                            <?= number_format($prestation['prix_prestation'], 0, ',', ' ') ?> 
                            <span style="font-size: 1.2rem; font-weight: 600;">FCFA</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="custom-card">
                <h5 class="fw-800 mb-4">Galerie photos</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="img-placeholder">
                            <i class="bi bi-images fs-1"></i>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="img-placeholder">
                            <i class="bi bi-images fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="custom-card text-center">
                <div class="info-label mb-4">Informations Prestataire</div>
                <div class="avatar-circle">
                    <?= strtoupper(substr($prestation['prenom_utilisateur'], 0, 1)) ?>
                </div>
                <h4 class="fw-800 mb-1"><?= e($prestation['prenom_utilisateur']) ?> <?= e($prestation['nom_utilisateur']) ?></h4>
                <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill mb-4" style="font-size: 0.7rem;">
                    <i class="bi bi-patch-check-fill"></i> COMPTE VÉRIFIÉ
                </div>

                <div class="text-start space-y-3">
                    <div class="mb-3">
                        <div class="info-label">Email Professionnel</div>
                        <div class="info-value text-truncate"><?= e($prestation['email_utilisateur']) ?></div>
                    </div>
                    <div class="mb-3">
                        <div class="info-label">Numéro de téléphone</div>
                        <div class="info-value"><?= e($prestation['num_utilisateur'] ?? 'Non renseigné') ?></div>
                    </div>
                </div>

                <a href="user_details.php?id=<?= $prestation['id_utilisateur'] ?>" class="btn btn-light w-100 btn-pill mt-3">
                    Voir le profil complet
                </a>
            </div>

            <div class="custom-card bg-primary text-white">
                <h6 class="fw-700 mb-3">Statut de l'offre</h6>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="bg-white bg-opacity-25 p-2 rounded-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="small opacity-75">État actuel</div>
                        <div class="fw-bold">En attente de revue</div>
                    </div>
                </div>
                <p class="small opacity-75">Cette offre ne sera visible sur la plateforme qu'après validation par un administrateur.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>