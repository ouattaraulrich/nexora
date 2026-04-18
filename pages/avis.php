<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/guards.php';

requireLogin();

$db = getDB();
$id_commande = $_GET['id_commande'] ?? null;
$userId = $_SESSION['user']['id_utilisateur'];

// Sécurité : Commande client terminée (Statut 2)
$stmt = $db->prepare("
    SELECT c.*, p.titre_prestation 
    FROM Commande c
    JOIN cibler ci ON ci.id_commande = c.id_commande
    JOIN Prestation p ON p.id_prestation = ci.id_prestation
    WHERE c.id_commande = ? AND c.id_utilisateur = ? AND c.statut = 2
");
$stmt->execute([$id_commande, $userId]);
$commande = $stmt->fetch();

if (!$commande) {
    setFlash("Accès refusé ou commande non terminée.", "danger");
    header("Location: dashboard.php");
    exit;
}

// Doublon
$checkAvis = $db->prepare("SELECT id_avis FROM Avis WHERE id_commande = ?");
$checkAvis->execute([$id_commande]);
if ($checkAvis->fetch()) {
    setFlash("Avis déjà enregistré.", "info");
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noter la prestation</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { --accent: #4f46e5; --bg: #f4f7fe; }
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        
        .rating-card { 
            background: white; border-radius: 32px; padding: 3rem; max-width: 550px; width: 90%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.02);
        }

        .prestation-tag {
            background: #f0f3ff; color: var(--accent); padding: 6px 16px; 
            border-radius: 100px; font-weight: 700; font-size: 0.8rem; text-transform: uppercase;
        }

        /* Système d'étoiles */
        .star-rating-box { margin: 2rem 0; padding: 1.5rem; background: #fafbff; border-radius: 20px; border: 1px solid #f1f5f9; text-align: center; }
        .stars { display: flex; flex-direction: row-reverse; justify-content: center; gap: 12px; }
        .stars input { display: none; }
        .stars label { font-size: 2.8rem; color: #e2e8f0; cursor: pointer; transition: 0.2s; }
        
        .stars label:hover, .stars label:hover ~ label, .stars input:checked ~ label { color: #f59e0b; transform: scale(1.1); }
        
        #rating-label { display: block; margin-top: 10px; font-weight: 700; font-size: 0.8rem; color: #94a3b8; text-transform: uppercase; }

        /* Champs formulaire */
        .form-control-custom { border-radius: 16px; border: 1px solid #e2e8f0; padding: 1rem; transition: 0.3s; }
        .form-control-custom:focus { border-color: var(--accent); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); }

        .btn-submit { 
            background: var(--accent); color: white; border: none; border-radius: 100px; 
            padding: 1rem; font-weight: 700; width: 100%; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }
        .btn-submit:hover { background: #4338ca; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(79, 70, 229, 0.3); }
    </style>
</head>
<body>

<div class="rating-card">
    <div class="text-center mb-4">
        <span class="prestation-tag"><?= e($commande['titre_prestation']) ?></span>
        <h2 class="fw-800 mt-3 mb-1">Votre avis</h2>
        <p class="text-muted small">Partagez votre expérience avec la communauté.</p>
    </div>

    <form action="<?= APP_URL ?>/actions/save_avis.php" method="POST">
        <input type="hidden" name="id_commande" value="<?= $id_commande ?>">

        <div class="star-rating-box">
            <div class="stars">
                <input type="radio" id="s5" name="note" value="5" required/><label for="s5" class="bi bi-star-fill" data-txt="Excellent !"></label>
                <input type="radio" id="s4" name="note" value="4"/><label for="s4" class="bi bi-star-fill" data-txt="Très bien"></label>
                <input type="radio" id="s3" name="note" value="3"/><label for="s3" class="bi bi-star-fill" data-txt="Satisfaisant"></label>
                <input type="radio" id="s2" name="note" value="2"/><label for="s2" class="bi bi-star-fill" data-txt="Décevant"></label>
                <input type="radio" id="s1" name="note" value="1"/><label for="s1" class="bi bi-star-fill" data-txt="À éviter"></label>
            </div>
            <span id="rating-label">Cliquer pour noter</span>
        </div>

        <div class="mb-4">
            <label class="form-label fw-700 small text-uppercase ms-1">Commentaire</label>
            <textarea name="commentaire" class="form-control form-control-custom" rows="4" placeholder="Qu'avez-vous pensé du service ?" required></textarea>
        </div>

        <button type="submit" class="btn btn-submit mb-3">Envoyer l'avis</button>
        <div class="text-center">
            <a href="dashboard.php" class="text-muted small text-decoration-none fw-600">Plus tard</a>
        </div>
    </form>
</div>

<script>
    const labels = document.querySelectorAll('.stars label');
    const text   = document.getElementById('rating-label');

    labels.forEach(l => {
        l.addEventListener('mouseover', () => {
            text.innerText = l.getAttribute('data-txt');
            text.style.color = "#f59e0b";
        });
        l.addEventListener('mouseout', () => {
            const checked = document.querySelector('.stars input:checked');
            if(checked) {
                const checkedLabel = document.querySelector(`label[for="${checked.id}"]`);
                text.innerText = checkedLabel.getAttribute('data-txt');
            } else {
                text.innerText = "Cliquer pour noter";
                text.style.color = "#94a3b8";
            }
        });
    });
</script>

</body>
</html>