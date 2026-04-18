<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/guards.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../config/config.php';

requireAdmin();

$db     = getDB();
$search = trim($_GET['q'] ?? '');
$rRole  = $_GET['role'] ?? '';
$valide = isset($_GET['valide']) ? (int)$_GET['valide'] : -1;

$sql = "SELECT u.*, q.nom_quartier, v.nom_ville
        FROM Utilisateur u
        LEFT JOIN Quartier q ON q.id_quartier = u.id_quartier
        LEFT JOIN Ville v ON v.id_ville = q.id_ville
        WHERE 1";
$params = [];

if ($search) { 
    $sql .= " AND (u.nom_utilisateur LIKE ? OR u.prenom_utilisateur LIKE ? OR u.email_utilisateur LIKE ?)";
    $params = array_merge($params, ["%$search%","%$search%","%$search%"]); 
}
if ($rRole === 'prestataire') { $sql .= " AND u.est_prestataire = 1"; }
if ($rRole === 'client')      { $sql .= " AND u.est_prestataire = 0 AND u.est_admin = 0"; }
if ($valide === 0)            { $sql .= " AND u.est_prestataire = 1 AND u.est_valide = 0"; }
if ($valide === 1)            { $sql .= " AND u.est_valide = 1"; }

$sql .= " ORDER BY u.id_utilisateur DESC";
$stmt = $db->prepare($sql); 
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs — <?= APP_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --main-bg: #f1f5f9;
            --accent: #4f46e5;
            --font-main: 'Plus Jakarta Sans', sans-serif; /* Définition de la police */
        }

        body { 
            background-color: var(--main-bg); 
            font-family: var(--font-main); /* Application ici */
            margin: 0; 
            color: #1e293b;
        }
        
        /* Correction pour les titres et éléments de formulaire */
        h1, h2, h3, h4, h5, h6, .fw-bold, .btn, .form-control, .form-select {
            font-family: var(--font-main);
        }

        .sidebar {
            width: 280px; background: var(--sidebar-bg); height: 100vh;
            position: fixed; color: white; padding: 1.5rem;
        }

        .main-wrapper { margin-left: 280px; padding: 2rem; }

        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.025em; }

        .custom-card {
            background: white; border-radius: 1.25rem; border: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;
        }

        .avatar-ui {
            width: 40px; height: 40px; border-radius: 10px;
            background: #eef2ff; color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.85rem;
        }

        .table thead th {
            background: #f8fafc; border-bottom: 1px solid #f1f5f9;
            color: #64748b; font-size: 0.75rem; text-transform: uppercase;
            letter-spacing: 0.05em; padding: 1rem 1.5rem; font-weight: 700;
        }
        
        .table tbody td { padding: 1rem 1.5rem; vertical-align: middle; }

        .badge-pill-custom {
            padding: 0.4rem 0.8rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600;
        }
        
        .bg-status-valide { background: #dcfce7; color: #15803d; }
        .bg-status-attente { background: #fef3c7; color: #b45309; }

        .filter-container {
            background: white; border-radius: 1rem; padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05); margin-bottom: 1.5rem;
        }

        .btn-action {
            width: 34px; height: 34px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            border: none; transition: 0.2s;
        }
        .btn-validate { background: #dcfce7; color: #166534; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
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

<main class="main-wrapper">
    <header class="page-header">
        <h1 class="page-title">Utilisateurs</h1>
        <p class="text-muted m-0"><?= count($users) ?> membres au total</p>
    </header>

    <?= flashHtml() ?>

    <div class="filter-container">
        <form method="GET" class="row g-3">
            <div class="col-lg-4">
                <input type="text" name="q" class="form-control" placeholder="Rechercher un nom ou email..." value="<?= e($search) ?>">
            </div>
            <div class="col-lg-3">
                <select name="role" class="form-select">
                    <option value="">Tous les rôles</option>
                    <option value="client" <?= $rRole === 'client' ? 'selected' : '' ?>>Clients</option>
                    <option value="prestataire" <?= $rRole === 'prestataire' ? 'selected' : '' ?>>Prestataires</option>
                </select>
            </div>
            <div class="col-lg-3">
                <select name="valide" class="form-select">
                    <option value="-1">Tous les statuts</option>
                    <option value="0" <?= $valide === 0 ? 'selected' : '' ?>>En attente</option>
                    <option value="1" <?= $valide === 1 ? 'selected' : '' ?>>Validés</option>
                </select>
            </div>
            <div class="col-lg-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-600">Filtrer</button>
            </div>
        </form>
    </div>

    <div class="custom-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Coordonnées</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-ui"><?= initials($u['prenom_utilisateur'].' '.$u['nom_utilisateur']) ?></div>
                                <div>
                                    <div class="fw-700 text-dark"><?= e($u['prenom_utilisateur'].' '.$u['nom_utilisateur']) ?></div>
                                    <div class="text-muted small">ID: #<?= $u['id_utilisateur'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-500"><?= e($u['email_utilisateur']) ?></div>
                            <div class="text-muted small"><?= e($u['num_utilisateur']) ?></div>
                        </td>
                        <td>
                            <span class="badge <?= $u['est_admin'] ? 'bg-dark' : ($u['est_prestataire'] ? 'bg-info bg-opacity-10 text-info' : 'bg-light text-muted') ?> px-2">
                                <?= $u['est_admin'] ? 'Admin' : ($u['est_prestataire'] ? 'Prestataire' : 'Client') ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['est_prestataire']): ?>
                                <span class="badge-pill-custom <?= $u['est_valide'] ? 'bg-status-valide' : 'bg-status-attente' ?>">
                                    <?= $u['est_valide'] ? 'Vérifié' : 'À valider' ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">Actif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <?php if ($u['est_prestataire'] && !$u['est_valide']): ?>
                                    <form action="<?= APP_URL ?>/pages/admin/validate_provider.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id_utilisateur'] ?>">
                                        <button type="submit" class="btn-action btn-validate"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                <?php endif; ?>
                                <?php if (!$u['est_admin']): ?>
                                    <form action="<?= APP_URL ?>/actions/admin/delete_user.php" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id_utilisateur'] ?>">
                                        <button type="submit" class="btn-action btn-delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>