<?php
$user = currentUser();
$role = currentRole();
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<div class="glass-dock-wrapper">
    <nav class="glass-dock">
        <a href="<?= APP_URL ?>/pages/index.php" class="dock-item <?= ($current_page == 'index.php') ? 'active' : '' ?>" data-tooltip="Accueil">
            <i class="bi bi-house-glow-fill"></i>
        </a>

        <div class="dock-separator"></div>

        <?php if ($role === 'prestataire'): ?>
            <a href="<?= APP_URL ?>/pages/services.php" class="dock-item <?= ($current_page == 'services.php') ? 'active' : '' ?>" data-tooltip="Mes Services">
                <i class="bi bi-collection-play"></i>
            </a>
            <a href="<?= APP_URL ?>/pages/create_prestation.php" class="dock-item <?= ($current_page == 'create_prestation.php') ? 'active' : '' ?>" data-tooltip="Publier">
                <i class="bi bi-plus-square-dotted"></i>
            </a>
        <?php else: ?>
            <a href="<?= APP_URL ?>/pages/services.php" class="dock-item <?= ($current_page == 'catalog.php') ? 'active' : '' ?>" data-tooltip="Explorer">
                <i class="bi bi-compass"></i>
            </a>
        <?php endif; ?>

        <div class="dock-separator"></div>

        <div class="dropup"> <a href="#" class="dock-item user-avatar-item" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="dock-avatar">
                    <?= strtoupper(substr($user['prenom_utilisateur'] ?? 'U', 0, 1)) ?>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark shadow-lg border-0 mb-3" style="border-radius: 20px; background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px);">
                <li><div class="dropdown-header text-dim small"><?= e($user['prenom_utilisateur']) ?> (<?= ucfirst($role) ?>)</div></li>
                <li><a class="dropdown-item py-2" href="<?= APP_URL ?>/pages/profile.php"><i class="bi bi-person-circle me-2"></i> Profil</a></li>
                <li><a class="dropdown-item py-2" href="<?= APP_URL ?>/pages/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                <li><hr class="dropdown-divider border-secondary"></li>
                <li><a class="dropdown-item py-2 text-danger" href="<?= APP_URL ?>/actions/logout_action.php"><i class="bi bi-power me-2"></i> Quitter</a></li>
            </ul>
        </div>
    </nav>
</div>

<style>
/* --- DOCK WRAPPER --- */
.glass-dock-wrapper {
    position: fixed;
    bottom: 30px; /* Positionné en bas pour un look mobile-first */
    left: 50%;
    transform: translateX(-50%);
    z-index: 2000;
    width: auto;
}

/* --- THE DOCK --- */
.glass-dock {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px) saturate(160%);
    -webkit-backdrop-filter: blur(20px) saturate(160%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    padding: 10px 15px;
    border-radius: 30px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

/* --- DOCK ITEMS --- */
.dock-item {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    color: #475569;
    text-decoration: none;
    font-size: 1.4rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.dock-item:hover {
    background: rgba(79, 70, 229, 0.1);
    color: #4f46e5;
    transform: translateY(-8px) scale(1.1);
}

.dock-item.active {
    background: #4f46e5;
    color: white;
    box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
}

/* --- SEPARATOR --- */
.dock-separator {
    width: 1px;
    height: 30px;
    background: rgba(0, 0, 0, 0.1);
    margin: 0 5px;
}

/* --- AVATAR DOCK --- */
.dock-avatar {
    width: 38px;
    height: 38px;
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: white;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.9rem;
}

/* --- TOOLTIPS (Optionnel) --- */
.dock-item::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 70px;
    background: #1e293b;
    color: white;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    opacity: 0;
    visibility: hidden;
    transition: 0.2s;
    white-space: nowrap;
}

.dock-item:hover::after {
    opacity: 1;
    visibility: visible;
}

/* --- ADAPTATION DARK MODE --- */
@media (prefers-color-scheme: dark) {
    .glass-dock {
        background: rgba(30, 41, 59, 0.8);
        border-color: rgba(255, 255, 255, 0.1);
    }
    .dock-item { color: #94a3b8; }
    .dock-separator { background: rgba(255, 255, 255, 0.1); }
}

/* Si tu préfères le dock en HAUT, change juste bottom:30px par top:30px */

/* ================================================
   DOCK RESPONSIVE
================================================ */
@media (max-width: 767px) {
    .glass-dock-wrapper {
        bottom: 16px;
        width: 95vw;
        max-width: 95vw;
    }
    .glass-dock {
        max-width: 95vw;
        overflow-x: auto;
        padding: 8px 12px;
        gap: 4px;
        justify-content: center;
        scrollbar-width: none; /* Firefox */
    }
    .glass-dock::-webkit-scrollbar { display: none; } /* Chrome/Safari */
    .dock-item {
        width: 44px;
        height: 44px;
        font-size: 1.2rem;
        border-radius: 14px;
        flex-shrink: 0;
    }
    .dock-item::after {
        display: none; /* Masquer tooltips sur mobile */
    }
    .dock-avatar {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        font-size: 0.8rem;
    }
}

@media (max-width: 380px) {
    .dock-separator { display: none !important; }
    .glass-dock { gap: 2px; padding: 6px 8px; }
    .dock-item { width: 40px; height: 40px; font-size: 1.1rem; }
}
</style>