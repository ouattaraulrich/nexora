<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$loc = getLocationData();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #0f172a; --accent: #4f46e5; --accent-hover: #4338ca;
            --gray-50: #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0;
            --gray-400: #94a3b8; --gray-600: #475569;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc;
               min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }

        .register-card { background: #fff; border-radius: 24px;
                         box-shadow: 0 20px 60px rgba(0,0,0,0.08); padding: 2.5rem 2.25rem;
                         width: 100%; max-width: 560px; }

        .brand { font-weight: 800; font-size: 1.5rem; color: var(--primary); margin-bottom: 0.25rem; }
        .brand span { color: var(--accent); }
        .page-sub { color: var(--gray-400); font-size: .9rem; margin-bottom: 2rem; }

        .form-label { font-weight: 600; font-size: 0.82rem; color: var(--gray-600);
                      text-transform: uppercase; letter-spacing: .4px; margin-bottom: .4rem; }
        .form-control, .form-select {
            border: 2px solid var(--gray-100); padding: .7rem 1rem;
            border-radius: 12px; background: var(--gray-50);
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            background: #fff; border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(79,70,229,.1); outline: none;
        }
        .form-select:disabled { opacity: .5; cursor: not-allowed; }

        .btn-register { background: var(--accent); border: none; border-radius: 12px;
                        padding: .9rem; font-weight: 700; font-size: 1rem;
                        width: 100%; color: white; transition: all .2s; }
        .btn-register:hover { background: var(--accent-hover);
                              box-shadow: 0 10px 20px rgba(79,70,229,.25); transform: translateY(-2px); }

        .divider { border: none; border-top: 1px solid var(--gray-100); margin: 1.5rem 0; }

        .alert { border: none; border-radius: 12px; font-size: .875rem; padding: .85rem 1.1rem; }
        .alert-danger  { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }

        /* === RESPONSIVE MOBILE === */
        @media (max-width: 767px) {
            body { padding: 1.25rem 0.75rem; align-items: flex-start; }
            .register-card {
                margin: 1rem auto;
                padding: 1.75rem 1.25rem;
                border-radius: 16px;
            }
            .brand { font-size: 1.25rem; }
            .btn-register { padding: 0.85rem; }
        }

        @media (max-width: 480px) {
            body { padding: 0.75rem 0.5rem; }
            .register-card {
                margin: 0.5rem auto;
                padding: 1.5rem 1rem;
                border-radius: 12px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            }
            /* Nom / Prénom → colonne */
            .row.g-3 .col-6 {
                flex: 0 0 100%;
                max-width: 100%;
                width: 100%;
            }
            .form-label { font-size: 0.78rem; }
        }
    </style>
</head>
<body>
<div class="register-card">
    <div class="brand"><i class="bi bi-lightning-charge-fill text-primary"></i> <?= APP_NAME ?><span>.</span></div>
    <p class="page-sub">Créez votre compte gratuitement</p>

    <?= flashHtml() ?>

    <form action="../actions/register_action.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <!-- Nom / Prénom -->
        <div class="row g-3 mb-3">
            <div class="col-6">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" placeholder="Koné" required>
            </div>
            <div class="col-6">
                <label class="form-label">Prénom</label>
                <input type="text" name="prenom" class="form-control" placeholder="Awa" required>
            </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Adresse e-mail</label>
            <div class="input-group">
                <span class="input-group-text bg-white"
                      style="border:2px solid var(--gray-100);border-right:none;border-radius:12px 0 0 12px">
                    <i class="bi bi-envelope text-muted"></i>
                </span>
                <input type="email" name="email" class="form-control"
                       style="border-left:none;border-radius:0 12px 12px 0;border:2px solid var(--gray-100)"
                       placeholder="vous@exemple.com" required>
            </div>
        </div>

        <!-- Téléphone -->
        <div class="mb-3">
            <label class="form-label">Téléphone</label>
            <div class="input-group">
                <span class="input-group-text bg-white"
                      style="border:2px solid var(--gray-100);border-right:none;border-radius:12px 0 0 12px">
                    <i class="bi bi-phone text-muted"></i>
                </span>
                <input type="tel" name="telephone" class="form-control"
                       style="border-left:none;border-radius:0 12px 12px 0;border:2px solid var(--gray-100)"
                       placeholder="07 00 00 00 00" maxlength="15" required>
            </div>
        </div>

        <!-- Localisation cascadée Région → Département → Ville → Quartier -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label">Région</label>
                <select id="sReg" class="form-select"
                        onchange="cascade('sReg','sDep',deps,'id_region','id_departement','nom_departement')">
                    <option value="">— Région —</option>
                    <?php foreach ($loc['regions'] as $r): ?>
                        <option value="<?= $r['id_region'] ?>"><?= e($r['nom_region']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label">Département</label>
                <select id="sDep" class="form-select" disabled
                        onchange="cascade('sDep','sVil',villes,'id_departement','id_ville','nom_ville')">
                    <option value="">— Département —</option>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label">Ville</label>
                <select id="sVil" class="form-select" disabled
                        onchange="cascade('sVil','sQua',quartiers,'id_ville','id_quartier','nom_quartier')">
                    <option value="">— Ville —</option>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label">Quartier</label>
                <select id="sQua" name="id_quartier" class="form-select" disabled required>
                    <option value="">— Quartier —</option>
                </select>
            </div>
        </div>

        <!-- Mot de passe -->
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <div class="input-group">
                <span class="input-group-text bg-white"
                      style="border:2px solid var(--gray-100);border-right:none;border-radius:12px 0 0 12px">
                    <i class="bi bi-lock text-muted"></i>
                </span>
                <input type="password" name="password" id="pwd" class="form-control"
                       style="border-left:none;border-right:none;border-radius:0;border:2px solid var(--gray-100)"
                       placeholder="Min. 8 caractères" minlength="8" required>
                <button type="button" class="btn btn-outline-secondary"
                        style="border:2px solid var(--gray-100);border-left:none;border-radius:0 12px 12px 0;background:#f8fafc"
                        onclick="togglePwd()">
                    <i class="bi bi-eye" id="eyeIco"></i>
                </button>
            </div>
        </div>

        <!-- Confirmer -->
        <div class="mb-4">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirm" class="form-control"
                   placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-register">
            Créer mon compte <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <hr class="divider">
    <p class="text-center small text-muted mb-0">
        Déjà un compte ? <a href="login.php" style="color:var(--accent);font-weight:700;text-decoration:none">Se connecter</a>
    </p>
</div>

<script>
const deps      = <?= json_encode($loc['departements']) ?>;
const villes    = <?= json_encode($loc['villes']) ?>;
const quartiers = <?= json_encode($loc['quartiers']) ?>;

function cascade(parentId, childId, data, matchKey, valKey, labelKey) {
    const pVal = document.getElementById(parentId).value;
    const ch   = document.getElementById(childId);
    ch.innerHTML = '<option value="">— sélectionner —</option>';
    ch.disabled  = !pVal;
    // Réinitialiser les sélects suivants
    const chain = ['sDep', 'sVil', 'sQua'];
    const idx   = chain.indexOf(childId);
    chain.slice(idx + 1).forEach(id => {
        const el = document.getElementById(id);
        el.innerHTML = '<option value="">—</option>';
        el.disabled  = true;
    });
    if (!pVal) return;
    data.filter(i => i[matchKey] == pVal).forEach(i => {
        const o = document.createElement('option');
        o.value = i[valKey]; o.textContent = i[labelKey];
        ch.appendChild(o);
    });
}

function togglePwd() {
    const p = document.getElementById('pwd'), i = document.getElementById('eyeIco');
    p.type      = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'text'     ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>
</body>
</html>