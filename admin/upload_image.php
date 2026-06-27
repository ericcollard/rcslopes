<?php
/**
 * Endpoint d'upload d'image appelé en AJAX par TinyMCE
 * (images_upload_handler dans assets/js/admin.js).
 *
 * Réponse JSON attendue par TinyMCE : { "location": "url" } en cas de succès,
 * on renvoie aussi success/message/url pour rester cohérent avec le reste de l'app.
 */

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Authentification obligatoire : pas d'upload anonyme.
if (!Auth::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Vérification CSRF (le token est envoyé en POST par admin.js)
$csrfToken = $_POST['csrf_token'] ?? null;
if (!Auth::checkCsrf($csrfToken)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Jeton de sécurité invalide ou expiré.']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aucune image reçue.']);
    exit;
}

$result = ImageManager::upload($_FILES['image']);

if (!$result['success']) {
    http_response_code(422);
    echo json_encode($result);
    exit;
}

echo json_encode([
    'success'  => true,
    'message'  => $result['message'],
    'location' => $result['url'], // clé attendue nativement par TinyMCE
    'url'      => $result['url'],
    'filename' => $result['filename'],
]);
