<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/guards.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/config.php';

requireLogin();
$user = currentUser();

// Sécurité : Si l'utilisateur a déjà un rôle, on le dégage vers le dashboard
if (!empty(currentRole()) && currentRole() !== 'nouveau') {
    header('Location: ' . APP_URL . '/pages/dashboard.php'); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --glass-bg: rgba(255, 255, 255, 0.8);
        }

        body {
            background: #f8fafc;
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            align-items: center;
        }

        .main-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 40px;
            padding: 4rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* On cache le vrai input radio */
        .role-selector input[type="radio"] {
            display: none;
        }

        .role-option {
            background: white;
            border: 2px solid transparent;
            border-radius: 24px;
            padding: 2rem;
            height: 100%;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .role-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        /* Style quand coché */
        .role-selector input[type="radio"]:checked + .role-option {
            border-color: #4f46e5;
            background: #f5f3ff;
        }

        .role-selector input[type="radio"]:checked + .role-option .check-mark {
            opacity: 1;
            transform: scale(1);
        }

        .check-mark {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 24px;
            height: 24px;
            background: #4f46e5;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            opacity: 0;
            transform: scale(0);
            transition: 0.3s;
        }

        .icon-box {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            background: #f1f5f9;
            color: #64748b;
            transition: 0.3s;
        }

        .role-selector input[type="radio"]:checked + .role-option .icon-box {
            background: #4f46e5;
            color: white;
        }

        .btn-next {
            background: var(--primary-gradient);
            border: none;
            padding: 1rem 3rem;
            border-radius: 100px;
            color: white;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: 0.3s;
            margin-top: 2rem;
        }

        .btn-next:disabled {
            background: #cbd5e1;
            transform: none !important;
        }

        .btn-next:hover:not(:disabled) {
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.4);
            transform: scale(1.02);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="main-card text-center">
                <div class="brand mb-5">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">Étape finale d'inscription</span>
                </div>

                <h1 class="fw-800 mb-2">Heureux de vous voir, <?= e($user['prenom_utilisateur']) ?> !</h1>
                <p class="text-muted mb-5">Sélectionnez votre profil pour personnaliser votre expérience.</p>

                <form action="../actions/choose_role_action.php" method="POST" id="roleForm">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    
                    <div class="row g-4 role-selector text-start">
                        <div class="col-md-6">
                            <label class="w-100 h-100">
                                <input type="radio" name="role" value="client" onchange="enableButton()">
                                <div class="role-option">
                                    <div class="check-mark"><i class="bi bi-check-lg"></i></div>
                                    <div class="icon-box"><i class="bi bi-cart3"></i></div>
                                    <h4 class="fw-700">Client</h4>
                                    <p class="text-muted small mb-0">Je cherche des prestataires qualifiés pour réaliser mes projets.</p>
                                </div>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="w-100 h-100">
                                <input type="radio" name="role" value="prestataire" onchange="enableButton()">
                                <div class="role-option">
                                    <div class="check-mark"><i class="bi bi-check-lg"></i></div>
                                    <div class="icon-box"><i class="bi bi-rocket-takeoff"></i></div>
                                    <h4 class="fw-700">Expert</h4>
                                    <p class="text-muted small mb-0">Je souhaite proposer mes services et booster mes revenus.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-next" disabled>
                        Continuer vers mon espace <i class="bi bi-arrow-right-short fs-5 ms-1"></i>
                    </button>
                </form>
                
                <p class="mt-4 text-muted" style="font-size: 0.85rem;">
                    Vous pourrez switcher entre les deux profils à tout moment.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function enableButton() {
        document.getElementById('submitBtn').disabled = false;
    }
</script>

</body>
</html>