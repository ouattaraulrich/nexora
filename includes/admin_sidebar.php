<?php
$user    = currentUser();
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="brand"><?= APP_NAME ?><span>.</span>
    <div style="font-size:.6rem;color:rgba(255,255,255,.3);font-family:'DM Sans',sans-serif;margin-top:.1rem;letter-spacing:1.5px">ADMINISTRATION</div>
  </div>

  <nav class="flex-grow-1">
    <a href="<?= APP_URL ?>/pages/admin/dashboard.php" class="nav-link <?= $current==='dashboard.php'?'active':'' ?>">
      <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>
    <a href="<?= APP_URL ?>/pages/admin/users.php" class="nav-link <?= $current==='users.php'?'active':'' ?>">
      <i class="bi bi-people"></i> Utilisateurs
    </a>
    <a href="<?= APP_URL ?>/pages/admin/categories.php" class="nav-link <?= $current==='categories.php'?'active':'' ?>">
      <i class="bi bi-tag"></i> Catégories &amp; Services
    </a>
  </nav>

  <div style="border-top:1px solid rgba(255,255,255,.1);padding-top:1rem;margin-top:1rem">
    <div class="d-flex align-items-center gap-2 px-1 mb-2">
      <div style="width:32px;height:32px;border-radius:50%;background:rgba(79,110,247,.25);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700">
        <?= initials($user['prenom_utilisateur'].' '.$user['nom_utilisateur']) ?>
      </div>
      <div>
        <div style="font-size:.78rem;color:#fff;font-weight:500"><?= e($user['prenom_utilisateur']) ?></div>
        <div style="font-size:.68rem;color:rgba(255,255,255,.35)">Administrateur</div>
      </div>
    </div>
    <a href="<?= APP_URL ?>/actions/logout.php" class="nav-link" style="color:rgba(255,100,100,.65) !important">
      <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a>
  </div>
</aside>
