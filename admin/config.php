<?php
/**
 * Configuration centrale de l'interface d'administration RC Slopes.
 *
 * ⚠️ Ne jamais committer ce fichier avec des identifiants réels dans un dépôt public.
 *    En production, préfère charger ces valeurs depuis des variables d'environnement.
 */

// ---------------------------------------------------------------------------
// Connexion base de données
// ---------------------------------------------------------------------------
define('DB_HOST', getenv('RCS_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('RCS_DB_PORT') ?: '3306');
define('DB_NAME', getenv('RCS_DB_NAME') ?: 'windfoil_rcslopes');
define('DB_USER', getenv('RCS_DB_USER') ?: 'windfoil_user');
define('DB_PASS', getenv('RCS_DB_PASS') ?: 'Colbas13');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------------
// Chemins du projet
// ---------------------------------------------------------------------------
define('ROOT_PATH', dirname(__DIR__));                       // /admin
define('IMAGES_DIR', '../assets/images');           // /admin/assets/images (stockage disque)
define('IMAGES_URL', '/assets/images');                 // URL publique correspondante — adapte si l'admin n'est pas servi à la racine /admin

// ---------------------------------------------------------------------------
// Sécurité
// ---------------------------------------------------------------------------
define('SESSION_NAME', 'rcs_admin_sess');
define('SESSION_LIFETIME', 60 * 60 * 4);  // 4h d'inactivité max
define('MAX_LOGIN_ATTEMPTS', 5);          // tentatives autorisées
define('LOGIN_LOCKOUT_MINUTES', 15);      // durée de blocage après dépassement

// ---------------------------------------------------------------------------
// Upload d'images
// ---------------------------------------------------------------------------
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5 Mo
define('UPLOAD_ALLOWED_MIME', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
]);

// ---------------------------------------------------------------------------
// Rôles applicatifs
// ---------------------------------------------------------------------------
// editor : CRUD complet sur les données (slopes, weather_forecast, wind_station) + gestion des images
// admin  : tout ce que fait 'editor' + gestion des comptes administrateurs
define('ROLE_EDITOR', 'editor');
define('ROLE_ADMIN', 'admin');

// ---------------------------------------------------------------------------
// Fuseau horaire / locale
// ---------------------------------------------------------------------------
date_default_timezone_set('Europe/Paris');

// ---------------------------------------------------------------------------
// Affichage des erreurs (À DÉSACTIVER EN PRODUCTION)
// ---------------------------------------------------------------------------
// define('APP_DEBUG', getenv('RCS_DEBUG') === '1');
define('APP_DEBUG', 1);
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
