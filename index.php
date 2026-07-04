<?php

// ============================================================
// index.php  –  Point d'entrée : page d'accueil + API REST
// ============================================================
error_reporting(E_ALL);

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$fullUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
    === 'on' ? "https" : "http") .
    "://" . $_SERVER['HTTP_HOST'] .
    $_SERVER['REQUEST_URI'];
$serverName = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
    === 'on' ? "https" : "http") .
    "://" . $_SERVER['HTTP_HOST'];
// Supprime le préfixe si l'API est dans un sous-dossier
$uri = preg_replace('#^/api#', '', $uri);
$uri = rtrim($uri, '/');


// ── Page d'accueil (GET /) ────────────────────────────────────
$request = null;
$requestedSlopeId = 0;
if ($method === 'GET' && ($uri === '' || preg_match('#^/(\d+)$#', $uri, $request))) {

    if ($request) {
        $requestedSlopeId = (int)$request[1];
        //var_dump($requestedSlopeId);
    }
    /*
    la redirection vers une pente donnée est gérée dans le js
    */

    $slope = null;
    $og_title = "RcSlopeS";
    $og_description = "La base de donnée des sites de vol de pente planeur Rc";
    $og_image = $serverName."/assets/preview.png";
    if ($requestedSlopeId > 0)
    {
        require_once __DIR__ . '/models/SLope.php';
        $slope = \models\Slope::getById($requestedSlopeId);
        $og_title = "RcSlopeS"." - pente ".$slope['name'];
        //var_dump($slope);
    }
    include_once('main.php');
exit;
}
/*
 *
 * preg_match('#^/slope/(\d+)$#', $uri, $m)
 *
// ── GET /slopes ──────────────────────────────────────────────
if ($method === 'GET' && preg_match('#^/slope/(\d+)$#', $uri, $m)) {
    include_once('main.html');
    exit;
}
*/

// ── Routes API ────────────────────────────────────────────────
// En-têtes JSON + CORS (uniquement pour les routes /api/*)

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');          // ← restreindre en production
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Réponse aux pre-flight OPTIONS (AJAX cross-origin)
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

use controllers\CommentController;
use controllers\SlopeController;
use controllers\WindStationController;
require_once __DIR__ . '/controllers/SlopeController.php';
require_once __DIR__ . '/controllers/CommentController.php';
require_once __DIR__ . '/controllers/WindStationController.php';
require_once __DIR__ . '/helpers/response.php';

$slopeController = new SlopeController();
$windStationController = new WindStationController();
$commentController = new CommentController();

// ── GET /slopes ──────────────────────────────────────────────
if ($method === 'GET' && $uri === '/slopes') {
    $slopeController->index();

// ── GET /stations ──────────────────────────────────────────────
} elseif ($method === 'GET' && $uri === '/stations') {
    $windStationController->index();

    // ── GET /slopes/desc/{id} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/slopes/desc/(\d+)$#', $uri, $m)) {
    $slopeController->showHtml((int) $m[1]);

    // ── GET /slopes/search/{txt} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/slopes/search/([A-Za-z0-9_]{2,30})$#', $uri, $m)) {
    $slopeController->search($m[1]);

// ── GET /slopes/{id} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/slopes/(\d+)$#', $uri, $m)) {
    $slopeController->show((int) $m[1]);

// ── GET /stations/{id} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/stations/(\d+)$#', $uri, $m)) {
    $windStationController->show((int) $m[1]);

} elseif ($method === 'POST' && $uri === '/comment') {
    $commentController->store();
// ── Route inconnue ───────────────────────────────────────────
} else {
    jsonResponse(['success' => false, 'error' => 'Route introuvable.'], 404);
}
