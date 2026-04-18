<?php
/**
 * config.php — Configuration centralisée
 * Priorité : Variables d'environnement (production) → valeurs locales (développement)
 */

// --- Chargement du .env si disponible (dev local sans variables d'env système) ---
$dotEnvPath = __DIR__ . '/../.env';
if (file_exists($dotEnvPath)) {
    $lines = file($dotEnvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_ENV) && !getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// --- Helper : lire une variable d'env avec fallback ---
function env(string $key, mixed $default = null): mixed {
    $val = $_ENV[$key] ?? getenv($key);
    return ($val !== false && $val !== null && $val !== '') ? $val : $default;
}

// --- Base de données ---
define('DB_HOST',    env('DB_HOST',    'localhost'));
define('DB_NAME',    env('DB_NAME',    'service_db'));
define('DB_USER',    env('DB_USER',    'root'));
define('DB_PASS',    env('DB_PASS',    ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// --- Application ---
define('APP_NAME', env('APP_NAME', 'Nexora'));
define('APP_URL',  env('APP_URL',  'http://localhost/service-marketplace'));

// --- API ---
define('OPENAI_API_KEY', env('OPENAI_API_KEY', ''));

// --- Upload ---
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');

// --- Affichage des erreurs (désactivé en production) ---
if (env('APP_ENV', 'local') === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}