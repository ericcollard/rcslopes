<?php
/**
 * Gestion de l'authentification, de la session et des privilèges.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

final class Auth
{
    private static bool $sessionStarted = false;

    /**
     * Démarre la session avec des paramètres sécurisés.
     * À appeler en tout début de chaque page (avant tout output).
     */
    public static function startSession(): void
    {
        if (self::$sessionStarted || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Expiration par inactivité
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            self::logout();
        }
        $_SESSION['last_activity'] = time();

        self::$sessionStarted = true;
    }

    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['admin_id']);
    }

    /**
     * Bloque l'accès à la page si l'utilisateur n'est pas connecté.
     * Redirige vers la page de login.
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header('Location: /admin/login.php?redirect=' . $redirect);
            exit;
        }
    }

    /**
     * Bloque l'accès si l'utilisateur connecté n'a pas le rôle 'admin'
     * (réservé à la gestion des comptes administrateurs).
     */
    public static function requireAdminRole(): void
    {
        self::requireLogin();
        if (($_SESSION['admin_role'] ?? null) !== ROLE_ADMIN) {
            http_response_code(403);
            require __DIR__ . '/../pages/403.php';
            exit;
        }
    }

    public static function currentAdminId(): ?int
    {
        return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
    }

    public static function currentAdminName(): string
    {
        return $_SESSION['admin_name'] ?? 'Administrateur';
    }

    public static function currentAdminRole(): string
    {
        return $_SESSION['admin_role'] ?? ROLE_EDITOR;
    }

    public static function isAdminRole(): bool
    {
        return self::currentAdminRole() === ROLE_ADMIN;
    }

    /**
     * Vérifie les identifiants et ouvre la session si valides.
     * Applique une protection basique contre le brute-force.
     *
     * @return array{success: bool, message: string}
     */
    public static function attemptLogin(string $email, string $password): array
    {
        $pdo = Database::getConnection();
        $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (self::isLockedOut($email, $ip)) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Trop de tentatives échouées. Réessayez dans %d minutes.',
                    LOGIN_LOCKOUT_MINUTES
                ),
            ];
        }

        $stmt = $pdo->prepare(
            'SELECT admin_id, email, password_hash, full_name, role, is_active
             FROM administrators WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        $success = false;

        if ($admin && (int) $admin['is_active'] === 1 && password_verify($password, $admin['password_hash'])) {
            $success = true;

            $_SESSION['admin_id']   = (int) $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];

            session_regenerate_id(true);

            $upd = $pdo->prepare('UPDATE administrators SET last_login_at = NOW() WHERE admin_id = :id');
            $upd->execute(['id' => $admin['admin_id']]);
        }

        self::recordAttempt($email, $ip, $success);

        if (!$success) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect.'];
        }

        return ['success' => true, 'message' => 'Connexion réussie.'];
    }

    private static function isLockedOut(string $email, string $ip): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS nb FROM login_attempts
             WHERE (email = :email OR ip_address = :ip)
               AND success = 0
               AND attempted_at > (NOW() - INTERVAL :minutes MINUTE)"
        );
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':minutes', LOGIN_LOCKOUT_MINUTES, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetch()['nb'] >= MAX_LOGIN_ATTEMPTS;
    }

    private static function recordAttempt(string $email, string $ip, bool $success): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO login_attempts (email, ip_address, success) VALUES (:email, :ip, :success)'
        );
        $stmt->execute([
            'email'   => $email,
            'ip'      => $ip,
            'success' => $success ? 1 : 0,
        ]);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(SESSION_NAME, '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    /**
     * Génère/vérifie un jeton CSRF par session.
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function checkCsrf(?string $token): bool
    {
        return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Stoppe l'exécution avec un code 419 si le jeton CSRF est invalide.
     */
    public static function requireValidCsrf(?string $token): void
    {
        if (!self::checkCsrf($token)) {
            http_response_code(419);
            die('Jeton de sécurité invalide ou expiré. Merci de recharger la page et réessayer.');
        }
    }
}
