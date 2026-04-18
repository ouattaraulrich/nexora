<?php
require_once __DIR__ . '/db.php'; // CRUCIAL : pour que getDB() soit reconnu

function e(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function initials(string $name): string {
    $parts = explode(' ', trim($name));
    $init  = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    if (isset($parts[1])) $init .= strtoupper(substr($parts[1], 0, 1));
    return $init;
}

function formatDate(string $dt, string $fmt = 'd/m/Y'): string {
    return date($fmt, strtotime($dt));
}

function timeAgo(string $dt): string {
    $timestamp = strtotime($dt);
    if (!$timestamp) return "Date invalide";
    $d = time() - $timestamp;
    if ($d < 60)    return 'À l\'instant';
    if ($d < 3600)  return floor($d / 60) . ' min';
    if ($d < 86400) return floor($d / 3600) . 'h';
    return date('d/m/Y', $timestamp);
}

function statusBadge(int $s): string {
    $map = [
        0 => ['badge-pending',  'En attente'],
        1 => ['badge-accepted', 'Acceptée'],
        2 => ['badge-done',     'Terminée'],
    ];
    [$cls, $label] = $map[$s] ?? ['badge-inactive', '—'];
    return "<span class='badge $cls'>$label</span>";
}

function flashHtml(): string {
    $flash = getFlash(); // getFlash() est définie dans session.php
    if (!$flash) return '';

    $map = [
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        'info'    => 'alert-info',
    ];
    $cls = $map[$flash['type']] ?? 'alert-info';
    $msg = e($flash['message']);

    return "<div class='alert $cls d-flex align-items-center gap-2 mb-3'>
                <i class='bi bi-info-circle-fill'></i> $msg
            </div>";
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token']))
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Token CSRF invalide');
    }
}

function getLocationData(): array {
    try {
        $db = getDB();
        return [
            'regions'      => $db->query("SELECT * FROM Region ORDER BY nom_region")->fetchAll(),
            'departements' => $db->query("SELECT * FROM Departement ORDER BY nom_departement")->fetchAll(),
            'villes'       => $db->query("SELECT * FROM Ville ORDER BY nom_ville")->fetchAll(),
            'quartiers'    => $db->query("SELECT * FROM Quartier ORDER BY nom_quartier")->fetchAll(),
        ];
    } catch (Exception $e) {
        return ['regions' => [], 'departements' => [], 'villes' => [], 'quartiers' => []];
    }
}
