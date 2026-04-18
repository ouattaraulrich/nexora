<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../config/config.php';

// Sécurité : Seul un prestataire peut accéder à cette page
requireRole('prestataire');

$user = currentUser();
$db   = getDB();
$id   = (int)($_GET['id'] ?? 0);

// Récupération de la prestation avec sa catégorie actuelle
$stmt = $db->prepare("
    SELECT p.*, s.id_categorie FROM Prestation p
    JOIN Service s ON s.id_service = p.id_service
    WHERE p.id_prestation = ? AND p.id_utilisateur = ?
");
$stmt->execute([$id, $user['id_utilisateur']]);
$prestation = $stmt->fetch();

if (!$prestation) {
    setFlash('error', 'Prestation introuvable.');
    header('Location: ' . APP_URL . '/pages/services.php'); 
    exit;
}

// Données pour les listes déroulantes
$categories = $db->query("SELECT * FROM Categorie ORDER BY nom_categorie")->fetchAll();
$services   = $db->query("SELECT * FROM Service ORDER BY nom_service")->fetchAll();

// Groupage des services par catégorie pour le JavaScript
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
    <title>Modifier la prestation — <?= APP_NAME ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary: #1e293b;
            --accent: #4f46e5;
            --accent-soft: #eef2ff;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-600: #475569;
            --radius: 18px;
        }

        body { 
            background-color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--primary);
        }

        .page-header {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .page-title {
            font-weight: 800;
            font-size: 1.8rem;
            letter-spacing: -1px;
            margin: 0;
        }

        .card {
            border: 1px solid var(--gray-100);
            border-radius: var(--radius);
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            border: none;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-600);
            margin-bottom: 0.6rem;
        }

        .form-control, .form-select {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--accent-soft);
        }

        .btn {
            padding: 0.7rem 1.5rem;
            font-weight: 700;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--accent);
            border: none;
        }

        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }

        .btn-light {
            background: white;
            border: 1px solid var(--gray-200);
        }

        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container py-5" style="max-width: 700px;">
    <?= flashHtml() ?>

    <div class="page-header">
        <div>
            <h1 class="page-title">Modifier votre service</h1>
            <p class="text-muted m-0">Ajustez vos tarifs et descriptions en quelques clics.</p>
        </div>
        <a href="services.php" class="btn btn-light btn-sm"><i class="bi bi-x-lg"></i></a>
    </div>

    <div class="card">
        <div class="card-body p-4 p-md-5">
            <form action="<?= APP_URL ?>/actions/update_service.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="prestation_id" value="<?= $prestation['id_prestation'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Catégorie</label>
                        <select name="id_categorie" id="selCat" class="form-select" required onchange="filterServices(this.value)">
                            <option value="">Choisir...</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id_categorie'] ?>" <?= $prestation['id_categorie'] == $c['id_categorie'] ? 'selected' : '' ?>>
                                    <?= e($c['nom_categorie']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Type de service</label>
                        <select name="id_service" id="selSvc" class="form-select" required>
                            <?php foreach ($services as $sv): ?>
                                <?php if($sv['id_categorie'] == $prestation['id_categorie']): ?>
                                    <option value="<?= $sv['id_service'] ?>" <?= $prestation['id_service'] == $sv['id_service'] ? 'selected' : '' ?>>
                                        <?= e($sv['nom_service']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Titre commercial</label>
                        <input type="text" name="titre_prestation" class="form-control" placeholder="Ex: Ménage complet villa 4 pièces" value="<?= e($prestation['titre_prestation']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description détaillée</label>
                        <textarea name="description_prestation" class="form-control" rows="5" placeholder="Décrivez votre savoir-faire..."><?= e($prestation['description_prestation'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tarif (FCFA)</label>
                        <div class="input-group">
                            <input type="number" name="prix_prestation" class="form-control" value="<?= $prestation['prix_prestation'] ?>" min="0" step="500">
                            <span class="input-group-text bg-white">F</span>
                        </div>
                    </div>

                    <div class="col-md-6 d-flex align-items-center mt-md-5">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="est_active" id="estActive" value="1" <?= $prestation['est_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label ms-2 fw-600" for="estActive">Service en ligne</label>
                        </div>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Sauvegarder les modifications
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Chargement des données JSON des services par catégorie
    const svcByCat = <?= json_encode($svcByCat) ?>;

    function filterServices(catId) {
        const sel = document.getElementById('selSvc');
        sel.style.opacity = '0.4';
        
        // Petit délai pour l'effet visuel
        setTimeout(() => {
            sel.innerHTML = '<option value="">Choisir un service...</option>';
            
            if (svcByCat[catId]) {
                svcByCat[catId].forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.id_service;
                    o.textContent = s.nom_service;
                    sel.appendChild(o);
                });
            }
            sel.style.opacity = '1';
        }, 150);
    }
</script>

</body>
</html>