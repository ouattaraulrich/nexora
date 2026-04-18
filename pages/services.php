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
$search = trim($_GET['q'] ?? '');
$catId  = (int)($_GET['categorie'] ?? 0);
$svcId  = (int)($_GET['service'] ?? 0);

// 1. Récupération pour les listes déroulantes
$categories = $db->query("SELECT * FROM Categorie ORDER BY nom_categorie")->fetchAll();
$services   = $db->query("SELECT * FROM Service ORDER BY nom_service")->fetchAll();

if ($role === 'prestataire') {
    $sql  = "SELECT p.*, s.nom_service, c.nom_categorie,
                    u.nom_utilisateur, u.prenom_utilisateur, u.est_valide
             FROM Prestation p
             JOIN Service s    ON s.id_service    = p.id_service
             JOIN Categorie c  ON c.id_categorie  = s.id_categorie
             JOIN Utilisateur u ON u.id_utilisateur = p.id_utilisateur
             WHERE p.id_utilisateur = ?";
    $params = [$user['id_utilisateur']];
    if ($search) { 
        $sql .= " AND (p.titre_prestation LIKE ? OR p.description_prestation LIKE ?)"; 
        $params[] = "%$search%"; $params[] = "%$search%"; 
    }
    if ($catId)  { $sql .= " AND c.id_categorie = ?"; $params[] = $catId; }
    if ($svcId)  { $sql .= " AND s.id_service = ?";   $params[] = $svcId; }
    $sql .= " ORDER BY p.datecrea_prestation DESC";
} else {
    $sql  = "SELECT p.*, s.nom_service, c.nom_categorie,
                    u.nom_utilisateur, u.prenom_utilisateur,
                    q.nom_quartier, v.nom_ville,
                    ROUND(AVG(ci.evaluation),1) AS note_moy,
                    COUNT(ci.evaluation) AS nb_avis
             FROM Prestation p
             JOIN Service s    ON s.id_service    = p.id_service
             JOIN Categorie c  ON c.id_categorie  = s.id_categorie
             JOIN Utilisateur u ON u.id_utilisateur = p.id_utilisateur AND u.est_valide = 1
             LEFT JOIN Quartier q ON q.id_quartier = u.id_quartier
             LEFT JOIN Ville v    ON v.id_ville    = q.id_ville
             LEFT JOIN cibler ci  ON ci.id_prestation = p.id_prestation
             WHERE p.est_active = 1";
    $params = [];
    if ($search) { 
        $sql .= " AND (p.titre_prestation LIKE ? OR p.description_prestation LIKE ?)"; 
        $params[] = "%$search%"; $params[] = "%$search%"; 
    }
    if ($catId)  { $sql .= " AND c.id_categorie = ?"; $params[] = $catId; }
    if ($svcId)  { $sql .= " AND s.id_service = ?";   $params[] = $svcId; }
    $sql .= " GROUP BY p.id_prestation ORDER BY p.datecrea_prestation DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$prestations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue Services — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --dark-text: #1e293b;
            --light-bg: #f8fafc;
            --border-color: #e2e8f0;
        }
        body { background-color: #ffffff; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--dark-text); }
        .filter-section { background: var(--light-bg); border-radius: 24px; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 3rem; }
        .search-input-group { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 5px 15px; display: flex; align-items: center; }
        .search-input-group input, .search-input-group select { border: none; background: transparent; padding: 10px; font-weight: 500; width: 100%; }
        .service-row { border-bottom: 1px solid var(--border-color); padding: 2rem; transition: all 0.3s ease; border-radius: 16px; margin-bottom: 0.5rem; }
        .service-row:hover { background-color: var(--light-bg); transform: translateX(10px); }
        .cat-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: var(--primary-color); background: rgba(79, 70, 229, 0.1); padding: 4px 10px; border-radius: 6px; display: inline-block; }
        .price-display { font-size: 1.5rem; font-weight: 800; }
        .btn-action { background: var(--primary-color); color: white; border: none; padding: 12px 30px; border-radius: 12px; font-weight: 700; transition: all 0.2s; }
        .btn-action:hover { background: #4338ca; transform: translateY(-1px); }

        /* === RESPONSIVE MOBILE === */
        @media (max-width: 767px) {
            .container { padding-left: 1rem !important; padding-right: 1rem !important; }
            .container.py-5 { padding-bottom: 6rem !important; } /* dock space */

            /* Filter section */
            .filter-section { padding: 1rem; border-radius: 16px; margin-bottom: 1.5rem; }

            /* Service rows: single column */
            .service-row {
                display: block !important;
                padding: 1.25rem;
            }
            .service-row:hover { transform: none; } /* disable on mobile */

            /* Price section: no side border on mobile */
            .service-row > div:last-child {
                border-left: none !important;
                border-top: 1px solid var(--border-color);
                padding: 1rem 0 0 0 !important;
                min-width: auto !important;
                margin-top: 0.75rem;
            }
            .price-display { font-size: 1.25rem; }
            .btn-action { padding: 10px 20px; width: 100%; margin-top: 0.5rem; }

            /* Page title */
            h1.display-5 { font-size: 1.8rem; }
        }

        @media (max-width: 480px) {
            .filter-section { padding: 0.85rem; }
            .search-input-group { padding: 4px 10px; }
            .service-row { padding: 1rem; border-radius: 12px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-5">
    <div class="mb-5 text-center text-md-start">
        <h1 class="fw-800 display-5">Catalogue des services</h1>
        <p class="text-muted">Trouvez l'expertise dont vous avez besoin.</p>
    </div>

    <div class="filter-section shadow-sm">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <div class="search-input-group">
                    <i class="bi bi-search text-muted"></i>
                    <input type="text" name="q" placeholder="Rechercher..." value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="search-input-group">
                    <select name="categorie">
                        <option value="0">Toutes catégories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id_categorie'] ?>" <?= $catId == $c['id_categorie'] ? 'selected' : '' ?>><?= e($c['nom_categorie']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn-action w-100">Appliquer les filtres</button>
            </div>
        </form>
    </div>

    <div class="service-list px-2">
        <?php foreach ($prestations as $p): ?>
        <div class="service-row d-md-flex align-items-center justify-content-between">
            <div class="flex-grow-1 pe-md-4">
                <span class="cat-label"><?= e($p['nom_categorie']) ?></span>
                <h2 class="fw-800 h4 mt-2 mb-2"><?= e($p['titre_prestation']) ?></h2>
                <p class="text-muted small"><?= e(mb_strimwidth($p['description_prestation'], 0, 160, "...")) ?></p>
                <div class="d-flex gap-3 small fw-600 mt-2">
                    <span><i class="bi bi-person me-1"></i><?= e($p['prenom_utilisateur']) ?></span>
                    <span><i class="bi bi-geo-alt me-1"></i><?= e($p['nom_ville'] ?? 'Cote d\'Ivoire') ?></span>
                </div>
            </div>
            
            <div class="text-md-end mt-4 mt-md-0 border-start ps-md-4" style="min-width: 200px;">
                <div class="price-display mb-3"><?= number_format($p['prix_prestation'], 0, ',', ' ') ?> <span class="small text-muted">FCFA</span></div>
                
                <?php if ($role === 'client'): ?>
                    <button onclick="commander(<?= $p['id_prestation'] ?>, '<?= addslashes(e($p['titre_prestation'])) ?>')" class="btn-action w-100">Commander</button>
                <?php else: ?>
                    <a href="edit_prestation.php?id=<?= $p['id_prestation'] ?>" class="btn btn-outline-dark w-100 rounded-3">Gérer</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function commander(id, titre) {
    if(confirm("Confirmer la commande de : " + titre + " ?")) {
        window.location.href = "<?= APP_URL ?>/actions/create_order.php?id_prestation=" + id;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>