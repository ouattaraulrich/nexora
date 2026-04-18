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

// Récupération pour les selects
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
    if ($search) { $sql .= " AND (p.titre_prestation LIKE ? OR p.description_prestation LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
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
    if ($search) { $sql .= " AND (p.titre_prestation LIKE ? OR p.description_prestation LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
    if ($catId)  { $sql .= " AND c.id_categorie = ?"; $params[] = $catId; }
    if ($svcId)  { $sql .= " AND s.id_service = ?";   $params[] = $svcId; }
    $sql .= " GROUP BY p.id_prestation ORDER BY p.datecrea_prestation DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$prestations = $stmt->fetchAll(); // C'est cette variable qu'on utilise
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prestations — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        
        /* Card Design */
        .service-card {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem;
            height: 100%;
            transition: all 0.3s ease;
            position: relative;
        }
        .service-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        
        .cat-tag {
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.65rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 8px;
            text-transform: uppercase;
        }

        .price-text { font-weight: 800; color: var(--primary); font-size: 1.1rem; }
        
        /* Avatar & Footer */
        .provider-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }
        .mini-avatar {
            width: 32px; height: 32px;
            background: #e2e8f0;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.7rem;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-4">
    <?= flashHtml() ?>

    <div class="page-header">
        <div>
            <h2 class="fw-800 text-primary mb-0"><?= $role === 'prestataire' ? 'Mes prestations' : 'Catalogue' ?></h2>
            <p class="text-muted small"><?= count($prestations) ?> prestation(s) disponible(s)</p>
        </div>
        <?php if ($role === 'prestataire' && $user['est_valide']): ?>
            <a href="create_prestation.php" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-plus-lg me-2"></i> Créer
            </a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control bg-light border-0" placeholder="Rechercher..." value="<?= e($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="categorie" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="0">Toutes catégories</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id_categorie'] ?>" <?= $catId == $c['id_categorie'] ? 'selected' : '' ?>>
                                <?= e($c['nom_categorie']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="service" class="form-select bg-light border-0">
                        <option value="0">Tous les services</option>
                        <?php foreach ($services as $sv): ?>
                            <option value="<?= $sv['id_service'] ?>" <?= $svcId == $sv['id_service'] ? 'selected' : '' ?>>
                                <?= e($sv['nom_service']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-accent w-100 fw-bold">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if (empty($prestations)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-1 text-light"></i>
                <p class="text-muted mt-3">Aucune prestation ne correspond à vos critères.</p>
            </div>
        <?php else: ?>
            <?php foreach ($prestations as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="cat-tag"><?= e($p['nom_categorie']) ?></span>
                            <?php if ($role === 'prestataire'): ?>
                                <span class="badge <?= $p['est_active'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> border-0">
                                    <?= $p['est_active'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            <?php elseif ($p['note_moy']): ?>
                                <span class="fw-bold text-warning small"><i class="bi bi-star-fill me-1"></i><?= $p['note_moy'] ?></span>
                            <?php endif; ?>
                        </div>

                        <h5 class="fw-700 text-primary mb-1"><?= e($p['titre_prestation']) ?></h5>
                        <p class="text-muted small mb-3"><?= e(mb_strimwidth($p['description_prestation'], 0, 80, "...")) ?></p>
                        
                        <div class="price-text mt-auto">
                            <?= number_format($p['prix_prestation'], 0, ',', ' ') ?> <small class="fw-600 fs-6 text-muted">FCFA</small>
                        </div>

                        <div class="provider-info">
                            <div class="mini-avatar">
                                <?= initials($p['prenom_utilisateur'].' '.$p['nom_utilisateur']) ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-600 small"><?= e($p['prenom_utilisateur'].' '.$p['nom_utilisateur']) ?></div>
                                <div class="text-muted extra-small" style="font-size: 0.7rem;">
                                    <i class="bi bi-geo-alt"></i> <?= e($p['nom_ville'] ?? 'Local') ?>
                                </div>
                            </div>
                            <div class="actions">
                                <?php if ($role === 'prestataire'): ?>
                                    <a href="edit_prestation.php?id=<?= $p['id_prestation'] ?>" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a>
                                <?php else: ?>
                                    <button onclick="openRequest(<?= $p['id_prestation'] ?>, '<?= e(addslashes($p['titre_prestation'])) ?>', <?= $p['prix_prestation'] ?>)" 
                                            class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                                        Commander
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($role === 'client'): ?>
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <h5 class="fw-800 mb-3">Confirmer la commande</h5>
                <p class="text-muted small">Vous allez envoyer une demande de service pour :</p>
                <div class="bg-light p-3 rounded-3 mb-4">
                    <h6 class="fw-700 text-primary mb-1" id="reqPrestName"></h6>
                    <div class="fw-800 text-accent fs-5" id="reqPrix"></div>
                </div>
                <form action="<?= APP_URL ?>/actions/send_request.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="prestation_id" id="reqPrestId">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold">Envoyer la demande</button>
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openRequest(id, title, prix) {
    document.getElementById('reqPrestId').value = id;
    document.getElementById('reqPrestName').textContent = title;
    document.getElementById('reqPrix').textContent = new Intl.NumberFormat('fr-FR').format(prix) + ' FCFA';
    new bootstrap.Modal(document.getElementById('requestModal')).show();
}
</script>
</body>
</html>