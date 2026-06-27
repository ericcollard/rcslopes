<?php
/**
 * Connexion PDO unique (singleton) à la base de données.
 */

require_once __DIR__ . '/config.php';

final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Empêche l'instanciation directe
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                if (APP_DEBUG) {
                    die('Erreur de connexion à la base de données : ' . $e->getMessage());
                }
                die('Erreur de connexion à la base de données. Contactez l\'administrateur système.');
            }
        }

        return self::$instance;
    }
}
