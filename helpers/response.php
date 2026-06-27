<?php
// ============================================================
// helpers/response.php  –  Fonctions utilitaires
// ============================================================

/**
 * Envoie une réponse JSON avec le code HTTP souhaité.
 */
function jsonResponse(mixed $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Orientations de vent valides.
 */
function validWindDirections(): array
{
    return ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
}

/**
 * Valide et nettoie un tableau d'orientations.
 * Retourne un tableau filtré ou null si une valeur est invalide.
 */
function sanitizeWindDirections(array $directions): ?array
{
    $valid = validWindDirections();
    $clean = [];
    foreach ($directions as $d) {
        $d = strtoupper(trim((string)$d));
        if (!in_array($d, $valid, true)) {
            return null;
        }
        $clean[] = $d;
    }
    return array_values(array_unique($clean));
}

/**
 * Convertit la chaîne SET MySQL en tableau PHP.
 * Exemple : "N,NNE,NW" → ["N", "NNE", "NW"]
 */
function windSetToArray(string $set): array
{
    if ($set === '') return [];
    return explode(',', $set);
}
