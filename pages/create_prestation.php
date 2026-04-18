<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';
requireRole('prestataire');

$user = currentUser();
if (!$user['est_valide']) {
    setFlash('warning', 'Votre compte doit être validé par un administrateur avant de créer des prestations.');
    header('Location: ' . APP_URL . '/pages/dashboard.php'); exit;
}

$db         = getDB();
$categories = $db->query("SELECT * FROM Categorie ORDER BY nom_categorie")->fetchAll();
$services   = $db->query("SELECT * FROM Service ORDER BY nom_service")->fetchAll();

$svcByCat = [];
foreach ($services as $sv) {
    $svcByCat[$sv['id_categorie']][] = $sv;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une prestation — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-body: #f4f7ff;
            --primary: #4f46e5;
            --primary-light: #eef2ff;
            --dark: #1e293b;
            --text-muted: #64748b;
            --card-shadow: 0 10px 40px rgba(0,0,0,0.04);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body);
            color: var(--dark);
        }

        /* Layout */
        .wrapper { max-width: 900px; margin: 40px auto; padding: 0 20px; }

        /* Navigation de retour stylisée */
        .back-nav { margin-bottom: 2rem; }
        .back-link { 
            text-decoration: none; color: var(--text-muted); font-weight: 600; 
            display: inline-flex; align-items: center; transition: 0.2s;
        }
        .back-link:hover { color: var(--primary); transform: translateX(-5px); }

        /* Header */
        .form-header { margin-bottom: 3rem; }
        .form-header h1 { font-weight: 800; font-size: 2.2rem; letter-spacing: -1px; }
        .form-header p { color: var(--text-muted); font-size: 1.1rem; }

        /* Section Cards */
        .form-section {
            background: white; border-radius: 24px; padding: 2.5rem;
            box-shadow: var(--card-shadow); border: 1px solid rgba(255,255,255,0.8);
            margin-bottom: 2rem;
        }
        .section-title {
            font-size: 1rem; font-weight: 800; color: var(--primary);
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2rem;
            display: flex; align-items: center; gap: 10px;
        }

        /* Inputs Modernisés */
        .form-label { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.6rem; color: #475569; }
        
        .form-control, .form-select {
            border: 1.5px solid #e2e8f0; padding: 0.8rem 1rem;
            border-radius: 12px; transition: all 0.2s ease;
            background-color: #fff;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        textarea.form-control { min-height: 120px; }

        /* Input Group spécifique au prix */
        .price-input-wrapper {
            position: relative; display: flex; align-items: center;
        }
        .price-input-wrapper input { padding-right: 60px; font-weight: 700; color: var(--primary); font-size: 1.2rem; }
        .price-badge {
            position: absolute; right: 15px; font-weight: 800; color: var(--text-muted); font-size: 0.8rem;
        }

        /* Switch Custom */
        .form-switch .form-check-input {
            width: 3em; height: 1.5em; cursor: pointer;
        }
        .form-switch .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }

        /* Footer Actions */
        .form-actions {
            display: flex; align-items: center; justify-content: flex-end; gap: 1rem; margin-top: 3rem;
        }
        .btn-create {
            background: var(--primary); color: white; border: none;
            padding: 1rem 2.5rem; border-radius: 15px; font-weight: 700;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3); transition: 0.3s;
        }
        .btn-create:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(79, 70, 229, 0.4); color: white; }
        
        .btn-cancel { color: var(--text-muted); text-decoration: none; font-weight: 600; }
        .btn-cancel:hover { color: #ef4444; }

        /* Responsive */
        @media (max-width: 768px) {
            .form-section { padding: 1.5rem; }
            .form-header h1 { font-size: 1.7rem; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="wrapper">
    <div class="back-nav">
        <a href="services.php" class="back-link">
            <i class="bi bi-arrow-left me-2"></i> Retour au catalogue
        </a>
    </div>

    <div class="form-header">
        <h1>Créer une offre</h1>
        <p>Présentez votre expertise à la communauté <?= APP_NAME ?>.</p>
    </div>

    <?= flashHtml() ?>

    <form action="<?= APP_URL ?>/actions/create_service.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-hash"></i> 1. Catégorisation
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Domaine d'activité</label>
                    <select name="id_categorie" id="selCat" class="form-select" required onchange="filterServices(this.value)">
                        <option value="">Choisir un domaine...</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id_categorie'] ?>"><?= e($c['nom_categorie']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type de service</label>
                    <select name="id_service" id="selSvc" class="form-select" required disabled>
                        <option value="">Sélectionnez le domaine d'abord</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-pencil-square"></i> 2. Détails de l'offre
            </div>
            <div class="mb-4">
                <label class="form-label">Titre accrocheur</label>
                <input type="text" name="titre_prestation" class="form-control" 
                       placeholder="Ex: Plomberie express et installation sanitaire" required>
            </div>
            <div class="mb-0">
                <label class="form-label">Description de vos services</label>
                <textarea name="description_prestation" class="form-control" 
                          placeholder="Quelles sont vos compétences ? Vos outils ? Pourquoi vous choisir ?"></textarea>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <i class="bi bi-currency-exchange"></i> 3. Prix & Visibilité
            </div>
            <div class="row align-items-center g-4">
                <div class="col-md-6">
                    <label class="form-label">Tarif de base (indicatif)</label>
                    <div class="price-input-wrapper">
                        <input type="number" name="prix_prestation" class="form-control" placeholder="0" min="0" step="500">
                        <span class="price-badge">FCFA</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch mt-md-4">
                        <input class="form-check-input" type="checkbox" name="est_active" id="estActive" value="1" checked>
                        <label class="form-check-label ms-3 fw-600" for="estActive">Publier l'offre en ligne</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="services.php" class="btn-cancel">Annuler</a>
            <button type="submit" class="btn-create">
                Lancer ma prestation <i class="bi bi-rocket-takeoff ms-2"></i>
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const svcByCat = <?= json_encode($svcByCat) ?>;
function filterServices(catId) {
    const sel = document.getElementById('selSvc');
    sel.innerHTML = '<option value="">Précisez le service...</option>';
    sel.disabled = !catId;
    if (!catId) return;
    const items = svcByCat[catId] || [];
    items.forEach(s => {
        const o = document.createElement('option');
        o.value = s.id_service;
        o.textContent = s.nom_service;
        sel.appendChild(o);
    });
}
</script>
</body>
</html>