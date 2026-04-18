<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/guards.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

requireAdmin();
$db = getDB();

// Données
$categories = $db->query("SELECT c.*, COUNT(s.id_service) AS nb_services FROM Categorie c LEFT JOIN Service s ON s.id_categorie = c.id_categorie GROUP BY c.id_categorie ORDER BY c.nom_categorie ASC")->fetchAll();
$services = $db->query("SELECT s.*, c.nom_categorie FROM Service s JOIN Categorie c ON c.id_categorie = s.id_categorie ORDER BY c.nom_categorie ASC, s.nom_service ASC")->fetchAll();
$offres = $db->query("SELECT p.*, s.nom_service, u.nom_utilisateur, u.prenom_utilisateur FROM Prestation p JOIN Service s ON s.id_service = p.id_service JOIN Utilisateur u ON u.id_utilisateur = p.id_utilisateur ORDER BY p.id_prestation DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration Nexora — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --main-bg: #f8fafc;
            --accent: #4f46e5;
        }

        body { 
            background-color: var(--main-bg); 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
        }

        /* Sidebar reste inchangée comme demandé */
        .sidebar {
            width: 280px; background: var(--sidebar-bg); height: 100vh;
            position: fixed; color: white; padding: 1.5rem;
        }

        .main-wrapper { margin-left: 280px; padding: 2.5rem; }

        /* Style des Cartes Modernes */
        .glass-card {
            background: white;
            border-radius: 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            height: 100%;
        }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 1rem;
        }

        /* Liste de gestion élégante */
        .action-list-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1rem;
            margin-bottom: 0.75rem;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .action-list-item:hover { transform: translateX(5px); background: #f1f5f9; }

        .icon-box {
            width: 40px; height: 40px;
            background: white;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-right: 1rem;
        }

        .btn-add {
            border-radius: 10px;
            padding: 0.6rem 1.25rem;
            font-weight: 700;
        }

        .table-custom thead th {
            background: transparent;
            border-bottom: 2px solid #f1f5f9;
            color: #64748b;
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 1.25rem;
        }

        .table-custom tbody td { padding: 1.25rem; border: none; vertical-align: middle; }
    </style>
</head>
<body>

<nav class="sidebar">
    <div class="mb-5 px-2">
        <h4 class="fw-bold text-white"><?= APP_NAME ?></h4>
    </div>
    <div class="nav flex-column gap-2">
        <a href="dashboard.php" class="nav-link text-white-50 p-3"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="users.php" class="nav-link text-white-50 p-3"><i class="bi bi-people me-2"></i> Utilisateurs</a>
        <a href="categories.php" class="nav-link text-white bg-primary rounded-3 p-3 shadow-sm"><i class="bi bi-tags me-2"></i> Catégories</a>
        <hr class="text-white-50 mt-4">
        <a href="<?= APP_URL ?>/actions/logout_action.php" class="nav-link text-danger p-3"><i class="bi bi-box-arrow-left me-2"></i> Déconnexion</a>
    </div>
</nav>

<div class="main-wrapper">
    <div class="row mb-5">
        <div class="col-md-12">
            <h1 class="fw-800" style="letter-spacing: -1.5px;">Configuration Services</h1>
            <p class="text-muted">Gérez l'ossature de la plateforme Nexora</p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="glass-card">
                <div class="stat-icon bg-primary text-white"><i class="bi bi-grid-fill"></i></div>
                <h3 class="fw-800 m-0"><?= count($categories) ?></h3>
                <p class="text-muted m-0 small">Catégories actives</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card">
                <div class="stat-icon bg-success text-white"><i class="bi bi-layers-half"></i></div>
                <h3 class="fw-800 m-0"><?= count($services) ?></h3>
                <p class="text-muted m-0 small">Types de services</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card">
                <div class="stat-icon bg-warning text-white"><i class="bi bi-megaphone"></i></div>
                <h3 class="fw-800 m-0"><?= count($offres) ?></h3>
                <p class="text-muted m-0 small">Prestations en ligne</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-800 m-0 text-uppercase small text-muted">Gestion des Catégories</h5>
                    <button class="btn btn-primary btn-add btn-sm" data-bs-toggle="collapse" data-bs-target="#formCat">
                        <i class="bi bi-plus-lg"></i> Ajouter
                    </button>
                </div>

                <div class="collapse mb-4" id="formCat">
                    <form action="<?= APP_URL ?>/actions/admin/manage_categorie.php" method="POST" class="p-3 bg-light rounded-4">
                        <input type="hidden" name="action" value="add">
                        <div class="row g-2">
                            <div class="col-8"><input type="text" name="nom_categorie" class="form-control border-0 shadow-sm" placeholder="Ex: Maison..." required></div>
                            <div class="col-4"><input type="text" name="icone_categorie" class="form-control border-0 shadow-sm" placeholder="bi-house"></div>
                            <div class="col-12 mt-2"><button class="btn btn-dark w-100 rounded-3">Enregistrer la catégorie</button></div>
                        </div>
                    </form>
                </div>

                <?php foreach ($categories as $c): ?>
                <div class="action-list-item">
                    <div class="d-flex align-items-center">
                        <div class="icon-box"><i class="bi <?= e($c['icone_categorie'] ?? 'bi-tag') ?>"></i></div>
                        <div>
                            <span class="fw-700 d-block"><?= e($c['nom_categorie']) ?></span>
                            <span class="small text-muted"><?= $c['nb_services'] ?> services reliés</span>
                        </div>
                    </div>
                    <form action="<?= APP_URL ?>/actions/admin/manage_categorie.php" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_categorie" value="<?= $c['id_categorie'] ?>">
                        <button class="btn border-0 text-danger"><i class="bi bi-trash3"></i></button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="glass-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-800 m-0 text-uppercase small text-muted">Gestion des Services</h5>
                    <button class="btn btn-outline-primary btn-add btn-sm" data-bs-toggle="collapse" data-bs-target="#formServ">
                        <i class="bi bi-plus-lg"></i> Nouveau Service
                    </button>
                </div>

                <div class="collapse mb-4" id="formServ">
                    <form action="<?= APP_URL ?>/actions/admin/manage_categorie.php" method="POST" class="p-3 bg-light rounded-4">
                        <input type="hidden" name="action" value="add_service">
                        <select name="id_categorie" class="form-select mb-2 border-0 shadow-sm" required>
                            <option value="">Sélectionner Catégorie...</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id_categorie'] ?>"><?= e($c['nom_categorie']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="d-flex gap-2">
                            <input type="text" name="nom_service" class="form-control border-0 shadow-sm" placeholder="Nom du service..." required>
                            <button class="btn btn-primary rounded-3">Ajouter</button>
                        </div>
                    </form>
                </div>

                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($services as $sv): ?>
                    <div class="action-list-item">
                        <div>
                            <span class="fw-700"><?= e($sv['nom_service']) ?></span>
                            <span class="badge bg-white text-muted ms-2 border fw-600"><?= e($sv['nom_categorie']) ?></span>
                        </div>
                        <form action="<?= APP_URL ?>/actions/admin/manage_categorie.php" method="POST">
                            <input type="hidden" name="action" value="delete_service">
                            <input type="hidden" name="id_service" value="<?= $sv['id_service'] ?>">
                            <button class="btn border-0 text-muted"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="glass-card">
                <h5 class="fw-800 mb-4">Dernières Prestations Reçues</h5>
                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Prestation</th>
                                <th>Prestataire</th>
                                <th>Prix</th>
                                <th class="text-end">Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offres as $o): ?>
                            <tr>
                                <td>
                                    <div class="fw-700"><?= e($o['titre_prestation']) ?></div>
                                    <div class="small text-muted"><?= e($o['nom_service']) ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2" style="width:32px; height:32px; display:flex; align-items:center; justify-content:center; font-size: 10px; font-weight:800;"><?= strtoupper(substr($o['prenom_utilisateur'], 0, 1)) ?></div>
                                        <span class="small fw-600"><?= e($o['prenom_utilisateur']) ?></span>
                                    </div>
                                </td>
                                <td><span class="fw-800"><?= number_format($o['prix_prestation'], 0, ',', ' ') ?></span> <small>CFA</small></td>
                                <td class="text-end">
                                    <a href="details_prestation.php?id=<?= $o['id_prestation'] ?>" class="btn btn-light rounded-pill px-3 btn-sm fw-600 border">Voir</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>