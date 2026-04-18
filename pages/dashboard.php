<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';

requireLogin();

$user   = currentUser();
$role   = currentRole(); 
$db     = getDB();
$userId = $user['id_utilisateur'];
$prenom = (string)($user['prenom_utilisateur'] ?? 'Utilisateur');

/**
 * 1. RÉCUPÉRATION DU SOLDE À JOUR
 */
$stmtUser = $db->prepare("SELECT solde_portefeuille FROM Utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$userId]);
$userAccount = $stmtUser->fetch();
$solde = $userAccount['solde_portefeuille'] ?? 0;

/**
 * 2. INITIALISATION ET FILTRES SQL
 */
$moyenne = 0; 
$nbAvis  = 0;
$stats = ['total' => 0, 'en_attente' => 0, 'acceptees' => 0, 'terminees' => 0];

if ($role === 'client') {
    $baseSql = "FROM Commande c WHERE c.id_utilisateur = ?";
    $params  = [$userId];
} else {
    $baseSql = "FROM Commande c 
                JOIN cibler ci ON ci.id_commande = c.id_commande 
                JOIN Prestation p ON p.id_prestation = ci.id_prestation 
                WHERE p.id_utilisateur = ? AND c.masquee_prestataire = 0";
    $params  = [$userId];

    $stmtMoyenne = $db->prepare("
        SELECT AVG(a.note) as moyenne, COUNT(a.id_avis) as nb_avis
        FROM Avis a
        JOIN Commande c ON a.id_commande = c.id_commande
        JOIN cibler ci ON ci.id_commande = c.id_commande
        JOIN Prestation p ON p.id_prestation = ci.id_prestation
        WHERE p.id_utilisateur = ?
    ");
    $stmtMoyenne->execute([$userId]);
    $ratingInfo = $stmtMoyenne->fetch();
    $moyenne = round($ratingInfo['moyenne'] ?? 0, 1);
    $nbAvis  = $ratingInfo['nb_avis'] ?? 0;
}

/**
 * 3. RÉCUPÉRATION DES STATISTIQUES
 */
$stmtTotal = $db->prepare("SELECT COUNT(DISTINCT c.id_commande) $baseSql");
$stmtTotal->execute($params);
$stats['total'] = (int)$stmtTotal->fetchColumn();

$stmtWait = $db->prepare("SELECT COUNT(DISTINCT c.id_commande) $baseSql AND c.statut = 0");
$stmtWait->execute($params);
$stats['en_attente'] = (int)$stmtWait->fetchColumn();

$stmtOk = $db->prepare("SELECT COUNT(DISTINCT c.id_commande) $baseSql AND c.statut = 1");
$stmtOk->execute($params);
$stats['acceptees'] = (int)$stmtOk->fetchColumn();

$stmtDone = $db->prepare("SELECT COUNT(DISTINCT c.id_commande) $baseSql AND c.statut = 2");
$stmtDone->execute($params);
$stats['terminees'] = (int)$stmtDone->fetchColumn();

/**
 * 4. RÉCUPÉRATION DE L'HISTORIQUE RÉCENT
 */
if ($role === 'client') {
    $query = "SELECT c.*, p.titre_prestation, p.prix_prestation, 
                     u.nom_utilisateur AS partner_nom, u.prenom_utilisateur AS partner_prenom,
                     av.id_avis
              FROM Commande c
              JOIN cibler ci ON ci.id_commande = c.id_commande
              JOIN Prestation p ON p.id_prestation = ci.id_prestation
              JOIN Utilisateur u ON u.id_utilisateur = p.id_utilisateur
              LEFT JOIN Avis av ON av.id_commande = c.id_commande
              WHERE c.id_utilisateur = ? AND c.masquee_client = 0
              ORDER BY c.date_commande DESC LIMIT 8";
} else {
    $query = "SELECT c.*, p.titre_prestation, p.prix_prestation, 
                     u.nom_utilisateur AS partner_nom, u.prenom_utilisateur AS partner_prenom
              FROM Commande c
              JOIN cibler ci ON ci.id_commande = c.id_commande
              JOIN Prestation p ON p.id_prestation = ci.id_prestation
              JOIN Utilisateur u ON u.id_utilisateur = c.id_utilisateur
              WHERE p.id_utilisateur = ? AND c.masquee_prestataire = 0
              ORDER BY c.date_commande DESC LIMIT 8";
}

$recent = $db->prepare($query);
$recent->execute([$userId]);
$recentItems = $recent->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { --accent: #4f46e5; --bg: #f8fafc; --radius: 18px; }
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        
        .stat-card { background: white; padding: 1.25rem; border-radius: var(--radius); border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 1rem; transition: 0.3s; height: 100%; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        
        /* Style Portefeuille Cliquable */
        .wallet-card { background: linear-gradient(135deg, #4f46e5, #3730a3); color: white; border: none; cursor: pointer; }
        .wallet-card:hover { background: linear-gradient(135deg, #4338ca, #312e81); }

        .blue { background: #eef2ff; color: #4f46e5; }
        .amber { background: #fffbeb; color: #d97706; }
        .green { background: #f0fdf4; color: #16a34a; }
        .purple { background: #faf5ff; color: #7c3aed; }
        .gold { background: #fffceb; color: #f59e0b; }
        
        .activity-card { background: white; border-radius: 24px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card-header-clean { padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        
        .table-clean thead th { background: #f8fafc; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; padding: 1rem 1.5rem; border: none; }
        .table-clean tbody td { padding: 1.25rem 1.5rem; vertical-align: middle; border-color: #f1f5f9; }
        
        .avatar-circle { width: 35px; height: 35px; background: #eef2ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem; }

        /* === RESPONSIVE MOBILE === */
        @media (max-width: 767px) {
            .container { padding-left: 1rem !important; padding-right: 1rem !important; }
            .container.py-5 { padding-top: 2rem !important; padding-bottom: 6rem !important; } /* Space for dock */

            /* Stat cards: adjust on very small screens */
            .stat-card { padding: 1rem; gap: 0.75rem; }
            .stat-icon { width: 38px; height: 38px; font-size: 1rem; }

            /* Activity card header */
            .card-header-clean { padding: 1rem; flex-wrap: wrap; gap: 0.5rem; }
            .table-clean thead th { padding: 0.75rem 1rem; font-size: 0.65rem; }
            .table-clean tbody td { padding: 0.9rem 1rem; font-size: 0.85rem; }

            /* Dashboard title */
            h1 { font-size: 1.8rem !important; }
        }

        @media (max-width: 480px) {
            .stat-card { padding: 0.85rem 0.75rem; }
            .card-header-clean h5 { font-size: 0.95rem; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-5">
    <?= flashHtml() ?>

    <div class="text-center mb-5">
        <h1 class="fw-800" style="letter-spacing: -1.5px;">Bonjour, <?= e($prenom) ?> 👋</h1>
        <p class="text-muted">Résumé de vos activités en tant que <span class="badge bg-primary-subtle text-primary rounded-pill px-3"><?= ucfirst($role) ?></span></p>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-12 col-md-6 col-lg-4">
            <a href="wallet.php" class="text-decoration-none">
                <div class="stat-card wallet-card">
                    <div class="stat-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <span class="d-block fw-800 fs-4 text-white"><?= number_format($solde, 0, '.', ' ') ?> F</span>
                        <small class="text-white-50 fw-700 text-uppercase" style="font-size:0.65rem">Mon Portefeuille</small>
                    </div>
                    <div class="ms-auto">
                        <i class="bi bi-arrow-right-circle-fill text-white opacity-50 fs-4"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-cart"></i></div>
                <div><span class="d-block fw-800 fs-5"><?= $stats['total'] ?></span><small class="text-muted fw-700 text-uppercase" style="font-size:0.55rem">Total</small></div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
                <div><span class="d-block fw-800 fs-5"><?= $stats['en_attente'] ?></span><small class="text-muted fw-700 text-uppercase" style="font-size:0.55rem">Attente</small></div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-play-circle"></i></div>
                <div><span class="d-block fw-800 fs-5"><?= $stats['acceptees'] ?></span><small class="text-muted fw-700 text-uppercase" style="font-size:0.55rem">En cours</small></div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="stat-card">
                <div class="stat-icon <?= ($role === 'prestataire') ? 'gold' : 'purple' ?>"><i class="bi <?= ($role === 'prestataire') ? 'bi-star-fill' : 'bi-check-all' ?>"></i></div>
                <div>
                    <span class="d-block fw-800 fs-5"><?= ($role === 'prestataire') ? $moyenne.'/5' : $stats['terminees'] ?></span>
                    <small class="text-muted fw-700 text-uppercase" style="font-size:0.55rem"><?= ($role === 'prestataire') ? 'Note' : 'Terminées' ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="activity-card">
        <div class="card-header-clean">
            <h5 class="fw-800 m-0"><i class="bi bi-clock-history me-2 text-primary"></i>Historique récent</h5>
            <span class="badge bg-light text-dark border rounded-pill px-3"><?= count($recentItems) ?> opérations</span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Prestation</th>
                        <th><?= ($role === 'client') ? 'Prestataire' : 'Client' ?></th>
                        <th>Date</th>
                        <th>Prix</th>
                        <th class="text-end pe-4">Statut / Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentItems)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Aucune activité enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentItems as $r): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-700 text-dark"><?= e($r['titre_prestation']) ?></div>
                                <div class="text-muted" style="font-size: 0.7rem;">REF: CMD-<?= $r['id_commande'] ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle"><?= strtoupper(substr($r['partner_prenom'], 0, 1)) ?></div>
                                    <span class="fw-600 small"><?= e($r['partner_prenom'] . ' ' . $r['partner_nom']) ?></span>
                                </div>
                            </td>
                            <td class="text-muted small"><?= date('d/m/Y', strtotime($r['date_commande'])) ?></td>
                            <td class="fw-800 text-dark"><?= number_format($r['prix_prestation'], 0, '.', ' ') ?> F</td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-3">
                                    <?= statusBadge((int)$r['statut']) ?>

                                    <?php if ($role === 'client'): ?>
                                        <?php if ($r['statut'] == 0 && !($r['paiement_securise'] ?? 0)): ?>
                                            <form action="<?= APP_URL ?>/actions/process_escrow.php" method="POST">
                                                <input type="hidden" name="id_commande" value="<?= $r['id_commande'] ?>">
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-700 px-3" style="font-size: 0.7rem;">
                                                    <i class="bi bi-shield-lock-fill me-1"></i> Payer (Escrow)
                                                </button>
                                            </form>
                                        <?php elseif ($r['statut'] == 0 && ($r['paiement_securise'] ?? 0)): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3" style="font-size: 0.7rem;">
                                                <i class="bi bi-lock-fill me-1"></i> Fonds sécurisés
                                            </span>
                                        <?php elseif ($r['statut'] == 2): ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (isset($r['id_avis']) && $r['id_avis']): ?>
                                                    <span class="badge bg-light text-muted border rounded-pill px-3">Avis laissé</span>
                                                <?php else: ?>
                                                    <a href="avis.php?id_commande=<?= $r['id_commande'] ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-700 px-3" style="font-size: 0.7rem;">Noter</a>
                                                <?php endif; ?>
                                                <form action="<?= APP_URL ?>/actions/hide_order.php" method="POST" onsubmit="return confirm('Masquer ?')">
                                                    <input type="hidden" name="id_commande" value="<?= $r['id_commande'] ?>">
                                                    <button type="submit" class="btn btn-link text-muted p-0"><i class="bi bi-eye-slash-fill"></i></button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($role === 'prestataire'): ?>
                                        <?php if ($r['statut'] == 0): ?>
                                            <?php if ($r['paiement_securise'] ?? 0): ?>
                                                <form action="<?= APP_URL ?>/actions/confirm_order.php" method="POST">
                                                    <input type="hidden" name="id_commande" value="<?= $r['id_commande'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm rounded-pill fw-700 px-3" style="font-size: 0.7rem;">Confirmer</button>
                                                </form>
                                            <?php else: ?>
                                                <small class="text-muted" style="font-size: 0.65rem;"><i>En attente du paiement...</i></small>
                                            <?php endif; ?>
                                        <?php elseif ($r['statut'] == 1): ?>
                                            <form action="<?= APP_URL ?>/actions/complete_order.php" method="POST">
                                                <input type="hidden" name="id_commande" value="<?= $r['id_commande'] ?>">
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill fw-700 px-3" style="font-size: 0.7rem;">Terminer</button>
                                            </form>
                                        <?php elseif ($r['statut'] == 2): ?>
                                            <form action="<?= APP_URL ?>/actions/hide_order.php" method="POST" onsubmit="return confirm('Masquer ?')">
                                                <input type="hidden" name="id_commande" value="<?= $r['id_commande'] ?>">
                                                <button type="submit" class="btn btn-link text-muted p-0"><i class="bi bi-eye-slash-fill"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>