<?php
// ============================================================
// config/database.php  –  Configuration de la base de données
// ============================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'rcslopes');
define('DB_USER',     'rcslopes_user');       // ← à modifier
define('DB_PASSWORD', 'rcslopes_pss58*');           // ← à modifier
define('DB_CHARSET',  'utf8mb4');

/**
 * Retourne une connexion PDO (singleton basique).
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Connexion base de données impossible.']);
            exit;
        }
    }

    return $pdo;
}


