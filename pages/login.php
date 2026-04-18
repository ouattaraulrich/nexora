<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

// On ne redirige plus automatiquement pour laisser l'utilisateur se connecter
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion — <?= APP_NAME ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    :root {
      --primary: #0f172a; --accent: #4f46e5; --accent-hover: #4338ca;
      --gray-50: #f8fafc; --gray-100: #f1f5f9; --gray-200: #e2e8f0;
      --gray-400: #94a3b8; --gray-600: #475569;
      --transition: all .2s ease;
    }
    * { box-sizing: border-box; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fff;
           min-height: 100vh; overflow-x: hidden; margin: 0; }

    .login-container { min-height: 100vh; display: flex; }

    /* Panneau gauche */
    .login-sidebar {
      flex: 1;
      background: linear-gradient(rgba(15,23,42,.8), rgba(79,70,229,.6)),
                  url('https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&q=80');
      background-size: cover; background-position: center;
      display: flex; flex-direction: column; justify-content: center;
      padding: 4rem; color: white;
    }
    .sidebar-content h2 { font-weight: 800; font-size: 3rem; line-height: 1.1; margin-bottom: 1.5rem; }
    .sidebar-content p  { font-size: 1.1rem; opacity: .9; max-width: 400px; }

    /* Panneau droit */
    .login-form-side { width: 550px; display: flex; align-items: center;
                       justify-content: center; padding: 3rem; }
    .form-wrapper { width: 100%; max-width: 380px; }

    /* Style du Logo cliquable */
    .brand-top { margin-bottom: 3rem; }
    .logo-link { 
        font-weight: 800; 
        font-size: 1.5rem; 
        color: var(--primary) !important; 
        text-decoration: none !important;
        display: inline-block;
    }
    .logo-link span { color: var(--accent); }
    .logo-link:hover { opacity: 0.8; }

    .login-title    { font-weight: 800; font-size: 1.75rem; letter-spacing: -.02em; margin-bottom: .5rem; }
    .login-subtitle { color: var(--gray-400); margin-bottom: 2.5rem; }

    .form-label { font-weight: 600; font-size: .85rem; color: var(--gray-600); margin-bottom: .5rem; }
    .form-control { border: 2px solid var(--gray-100); padding: .8rem 1.2rem;
                    border-radius: 12px; background: var(--gray-50); transition: var(--transition); }
    .form-control:focus { background: #fff; border-color: var(--accent);
                          box-shadow: 0 0 0 4px rgba(79,70,229,.1); outline: none; }

    .btn-login { background: var(--accent); border: none; border-radius: 12px; padding: .9rem;
                 font-weight: 700; width: 100%; color: white; margin-top: 1rem; transition: var(--transition); }
    .btn-login:hover { background: var(--accent-hover); transform: translateY(-2px);
                       box-shadow: 0 10px 20px rgba(79,70,229,.2); }

    .alert { border: none; border-radius: 12px; font-size: .875rem; padding: .85rem 1.1rem; }
    
    @media (max-width: 992px) { 
        .login-sidebar { display: none; } 
        .login-form-side { width: 100%; } 
    }

    /* === RESPONSIVE MOBILE === */
    @media (max-width: 767px) {
        .login-form-side {
            width: 100% !important;
            padding: 2rem 1.25rem;
        }
        .form-wrapper {
            padding: 0;
            max-width: 100%;
        }
        .login-title { font-size: 1.5rem; }
        .login-subtitle { font-size: 0.875rem; margin-bottom: 1.75rem; }
        .brand-top { margin-bottom: 2rem; }
        .btn-login { padding: 0.85rem; }
    }

    @media (max-width: 480px) {
        .login-form-side { padding: 1.75rem 1rem; }
        .login-title { font-size: 1.35rem; }
        .form-control { padding: .7rem 1rem; }
    }
  </style>
</head>
<body>
<div class="login-container">

  <div class="login-sidebar">
    <div class="sidebar-content">
      <div class="badge bg-white text-primary rounded-pill px-3 py-2 mb-4 fw-bold small">
        ✨ Nouveau : Version 2.0 disponible
      </div>
      <h2>Connectez-vous à la réussite.</h2>
      <p>Rejoignez des milliers de professionnels qui développent leur activité sur notre plateforme.</p>
      <div class="mt-5 d-flex align-items-center gap-3">
        <div class="d-flex">
          <img src="https://i.pravatar.cc/100?img=12" style="width:45px;height:45px;border-radius:50%;border:2px solid #fff">
          <img src="https://i.pravatar.cc/100?img=32" style="width:45px;height:45px;border-radius:50%;border:2px solid #fff;margin-left:-15px">
          <img src="https://i.pravatar.cc/100?img=44" style="width:45px;height:45px;border-radius:50%;border:2px solid #fff;margin-left:-15px">
        </div>
        <span class="small">+500 experts en ligne actuellement</span>
      </div>
    </div>
  </div>

  <div class="login-form-side">
    <div class="form-wrapper">

      <div class="brand-top">
        <a href="index.php" class="logo-link">
            <i class="bi bi-lightning-charge-fill text-primary"></i> <?= APP_NAME ?><span>.</span>
        </a>
      </div>

      <h1 class="login-title">Bon retour !</h1>
      <p class="login-subtitle">Entrez vos accès pour continuer.</p>

      <?= flashHtml() ?>

      <form action="../actions/login_action.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="mb-3">
          <label class="form-label">E-mail</label>
          <input type="email" name="email" class="form-control"
                 placeholder="nom@exemple.com" required
                 value="<?= e($_GET['email'] ?? '') ?>">
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center">
            <label class="form-label">Mot de passe</label>
          </div>
          <div class="position-relative">
            <input type="password" name="password" id="pwd" class="form-control"
                   placeholder="••••••••" required>
            <button type="button"
                    class="btn position-absolute end-0 top-50 translate-middle-y border-0 text-muted"
                    onclick="togglePwd()">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login">Se connecter</button>
      </form>

      <p class="text-center mt-4 small text-muted">
        Pas encore membre ?
        <a href="register.php" style="color:var(--accent);font-weight:700;text-decoration:none">
          Créer un compte
        </a>
      </p>

    </div>
  </div>

</div>
<script>
function togglePwd() {
  const p = document.getElementById('pwd'), i = document.getElementById('eyeIcon');
  p.type = p.type === 'password' ? 'text' : 'password';
  i.className = p.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>
</body>
</html>