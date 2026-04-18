<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/guards.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

requireAdmin(); 

$db = getDB();

// 1. Récupération des compteurs pour les statistiques
$stats = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM Utilisateur WHERE est_prestataire=0 AND est_admin=0) AS nb_clients,
        (SELECT COUNT(*) FROM Utilisateur WHERE est_prestataire=1) AS nb_prestataires,
        (SELECT COUNT(*) FROM Utilisateur WHERE est_prestataire=1 AND est_valide=0) AS nb_en_attente,
        (SELECT COUNT(*) FROM Commande) AS nb_commandes
")->fetch();

// 2. Derniers inscrits pour le tableau
$recents = $db->query("
    SELECT u.*, q.nom_quartier 
    FROM Utilisateur u
    LEFT JOIN Quartier q ON q.id_quartier = u.id_quartier
    ORDER BY u.id_utilisateur DESC LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — <?= APP_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --main-bg: #f1f5f9;
            --accent: #4f46e5;
            --font-main: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background-color: var(--main-bg); 
            font-family: var(--font-main); 
            margin: 0; 
            color: #1e293b;
        }

        h1, h2, h3, h4, h5, h6, .fw-bold, .btn, .form-control, .form-select {
            font-family: var(--font-main);
        }
        
        /* Sidebar Navigation */
        .sidebar {
            width: 280px; background: var(--sidebar-bg); height: 100vh;
            position: fixed; color: white; padding: 1.5rem;
        }

        .main-wrapper { margin-left: 280px; padding: 2rem; }

        .page-header { margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; }
        .page-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em; margin: 0; }

        /* Stat Cards */
        .stat-card {
            background: white; border-radius: 1.25rem; padding: 1.5rem; border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s ease;
        }
        .stat-card:hover { transform: translateY(-4px); }
        
        .icon-box {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem; margin-bottom: 1rem;
        }

        /* Lists & Tables */
        .custom-card {
            background: white; border-radius: 1.25rem; border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;
        }
        
        .table thead th {
            background: #f8fafc; color: #64748b; font-size: 0.75rem; 
            text-transform: uppercase; letter-spacing: 0.05em; 
            padding: 1rem 1.5rem; font-weight: 700;
        }
        .table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; }

        .avatar-ui {
            width: 38px; height: 38px; border-radius: 10px;
            background: #eef2ff; color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.8rem;
        }

        .badge-soft { padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.7rem; font-weight: 700; }
        .bg-soft-warning { background: #fef3c7; color: #92400e; }
        .bg-soft-primary { background: #e0e7ff; color: #4338ca; }
    </style>
</head>
<body>

<nav class="sidebar">
    <div class="mb-5 px-2">
        <h4 class="fw-bold text-white"><?= APP_NAME ?> <span class="text-primary text-opacity-75">Admin</span></h4>
    </div>
    <div class="nav flex-column gap-2">
        <a href="dashboard.php" class="nav-link text-white bg-primary rounded-3 p-3 shadow-sm">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a href="users.php" class="nav-link text-white-50 p-3">
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

<main class="main-wrapper">
    <header class="page-header">
        <div>
            <h1 class="page-title">Tableau de Bord</h1>
            <p class="text-muted m-0">Statistiques globales de la plateforme</p>
        </div>
        <div class="bg-white px-3 py-2 rounded-pill shadow-sm border small fw-600">
            <i class="bi bi-calendar3 me-2 text-primary"></i> <?= date('d M Y') ?>
        </div>
    </header>

    <?= flashHtml() ?>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-primary text-white shadow-sm"><i class="bi bi-people-fill"></i></div>
                <div class="text-muted small fw-700 uppercase">Clients</div>
                <div class="h2 fw-800 m-0"><?= $stats['nb_clients'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-success text-white shadow-sm"><i class="bi bi-briefcase-fill"></i></div>
                <div class="text-muted small fw-700">Prestataires</div>
                <div class="h2 fw-800 m-0"><?= $stats['nb_prestataires'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-warning text-white shadow-sm"><i class="bi bi-clock-history"></i></div>
                <div class="text-muted small fw-700">À Valider</div>
                <div class="h2 fw-800 m-0"><?= $stats['nb_en_attente'] ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-info text-white shadow-sm"><i class="bi bi-cart-check-fill"></i></div>
                <div class="text-muted small fw-700">Commandes</div>
                <div class="h2 fw-800 m-0"><?= $stats['nb_commandes'] ?></div>
            </div>
        </div>
    </div>

    <div class="custom-card">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <h5 class="m-0 fw-800">Inscriptions Récentes</h5>
            <a href="users.php" class="btn btn-sm btn-light rounded-pill px-3 fw-600">Voir tout</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Utilisateur</th>
                        <th>Rôle</th>
                        <th>Quartier</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recents as $u): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-ui"><?= initials($u['prenom_utilisateur'].' '.$u['nom_utilisateur']) ?></div>
                                <div>
                                    <div class="fw-700 text-dark"><?= e($u['prenom_utilisateur'].' '.$u['nom_utilisateur']) ?></div>
                                    <div class="text-muted extra-small" style="font-size: 0.7rem;"><?= e($u['email_utilisateur']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-soft <?= $u['est_prestataire'] ? 'bg-soft-primary' : 'bg-light text-muted' ?>">
                                <?= $u['est_prestataire'] ? 'Prestataire' : 'Client' ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?= e($u['nom_quartier'] ?? 'Non précisé') ?></td>
                        <td>
                            <?php if($u['est_prestataire']): ?>
                                <span class="badge-soft <?= $u['est_valide'] ? 'bg-success bg-opacity-10 text-success' : 'bg-soft-warning' ?>">
                                    <?= $u['est_valide'] ? 'Validé' : 'En attente' ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">Actif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <a href="users.php?q=<?= urlencode($u['email_utilisateur']) ?>" class="btn btn-sm btn-light border-0"><i class="bi bi-chevron-right"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>