<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
$db = getDB();
$userId = $_SESSION['user_id'];

// Récupération du solde
$stmt = $db->prepare("SELECT solde_portefeuille FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$userId]);
$solde = $stmt->fetchColumn() ?? 0;

// Récupération de l'historique
$stmtHist = $db->prepare("SELECT * FROM Transaction WHERE id_utilisateur = ? ORDER BY date_transaction DESC");
$stmtHist->execute([$userId]);
$transactions = $stmtHist->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Portefeuille — Nexora</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --accent: #4f46e5; --bg: #f8fafc; --radius: 24px; }
        body { background-color: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        
        .wallet-header { 
            background: linear-gradient(135deg, #4f46e5, #3730a3); 
            color: white; 
            padding: 4rem 0; 
            border-radius: 0 0 50px 50px;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
        }

        .card-custom { background: white; border-radius: var(--radius); border: 1px solid #f1f5f9; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        
        /* Style des options de paiement */
        .payment-option {
            border: 2px solid #f1f5f9;
            border-radius: 15px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        .payment-option:hover { border-color: var(--accent); background: #f5f3ff; }
        .payment-option input { display: none; }
        .payment-option input:checked + .option-content { color: var(--accent); }
        .payment-option input:checked ~ .check-icon { display: block !important; }
        
        .method-logo { width: 40px; height: 40px; border-radius: 10px; object-fit: cover; }
        
        .table-clean thead th { background: #f8fafc; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; padding: 1.25rem; border: none; }
        .table-clean tbody td { padding: 1.25rem; vertical-align: middle; border-color: #f1f5f9; }
        
        .badge-gain { background: #f0fdf4; color: #16a34a; }
        .badge-recharge { background: #eff6ff; color: #3b82f6; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="wallet-header text-center">
        <div class="container">
            <h5 class="fw-600 opacity-75 mb-3">Mon Solde Actuel</h5>
            <h1 class="display-2 fw-800 mb-4"><?= number_format($solde, 0, '.', ' ') ?> <small class="fs-4">F CFA</small></h1>
            <button class="btn btn-light btn-lg rounded-pill px-5 fw-800 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRecharge">
                <i class="bi bi-plus-lg me-2"></i>Recharger le compte
            </button>
        </div>
    </div>

    <div class="container mt-n5 mb-5" style="margin-top: -40px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <?= flashHtml() ?>
                
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-800 m-0">Historique des flux</h4>
                        <div class="avatar-group d-flex">
                            <span class="badge bg-light text-muted rounded-pill px-3 border"><?= count($transactions) ?> transactions</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-clean align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date & Heure</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($transactions)): ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted">Aucune transaction pour le moment.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td class="text-muted small">
                                            <div class="fw-600 text-dark"><?= date('d M Y', strtotime($t['date_transaction'])) ?></div>
                                            <?= date('H:i', strtotime($t['date_transaction'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-700"><?= e($t['description']) ?></div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2 <?= $t['type_transaction'] == 'gain' ? 'badge-gain' : 'badge-recharge' ?>">
                                                <i class="bi <?= $t['type_transaction'] == 'gain' ? 'bi-graph-up-arrow' : 'bi-plus-circle' ?> me-1"></i>
                                                <?= ucfirst($t['type_transaction']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-800 fs-5 <?= $t['montant'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= ($t['montant'] > 0 ? '+' : '') . number_format($t['montant'], 0, '.', ' ') ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRecharge" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="../actions/process_recharge.php" method="POST" class="modal-content border-0" style="border-radius: 30px;">
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="icon-box bg-primary-subtle text-primary rounded-circle mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-lightning-charge-fill fs-3"></i>
                        </div>
                        <h4 class="fw-800">Recharger via Mobile Money</h4>
                        <p class="text-muted small">Choisissez votre opérateur et le montant</p>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-700 small text-uppercase">1. Choisir l'opérateur</label>
                        
                        <label class="payment-option w-100">
                            <input type="radio" name="methode" value="orange" checked>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/c/c8/Orange_logo.svg" class="method-logo">
                            <div class="option-content flex-grow-1 fw-700">Orange Money</div>
                            <i class="bi bi-check-circle-fill text-primary check-icon" style="display:none;"></i>
                        </label>

                        <label class="payment-option w-100">
                            <input type="radio" name="methode" value="mtn">
                            <img src="data:image/png;base64,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" class="method-logo">
                            <div class="option-content flex-grow-1 fw-700">MTN MoMo</div>
                            <i class="bi bi-check-circle-fill text-primary check-icon" style="display:none;"></i>
                        </label>

                        <label class="payment-option w-100">
                            <input type="radio" name="methode" value="wave">
                            <img src="data:image/png;base64,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" class="method-logo" style="background:#1dc1f2; padding:5px;">
                            <div class="option-content flex-grow-1 fw-700">Wave</div>
                            <i class="bi bi-check-circle-fill text-primary check-icon" style="display:none;"></i>
                        </label>

                        <label class="payment-option w-100">
                            <input type="radio" name="methode" value="moov">
                            <img src="https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEhCgOMBEYpTzIbSMXy2yk7-XhtlLXj_KVQIpGz2-WBh6sG3x0WW_VegFQrz4wHB4Pp8WoDws1vK3Krb4etptWGvj-EaD3eMj-WRJ1-HMf_GxqBng-O9rRbfGJi0QUA9ETrs5Xl5NDVOEuUJ/s882/2logo-moov-africa.jpeg" class="method-logo">
                            <div class="option-content flex-grow-1 fw-700">Moov Money</div>
                            <i class="bi bi-check-circle-fill text-primary check-icon" style="display:none;"></i>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-700 small text-uppercase">2. Montant à recharger</label>
                        <div class="input-group">
                            <input type="number" name="montant" class="form-control form-control-lg border-2 fw-800" placeholder="0.00" required>
                            <span class="input-group-text bg-light border-2 fw-bold">F CFA</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-800 fs-5 shadow">
                        Valider le paiement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Petit script pour gérer l'affichage visuel de la sélection radio
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.check-icon').forEach(icon => icon.style.display = 'none');
                document.querySelectorAll('.payment-option').forEach(opt => opt.style.borderColor = '#f1f5f9');
                
                this.querySelector('.check-icon').style.display = 'block';
                this.style.borderColor = '#4f46e5';
            });
        });
        // Activer le premier par défaut visuellement
        document.querySelector('.payment-option').click();
    </script>
</body>
</html>