<?php
/**
 * Gestion des comptes administrateurs (CRUD), réservée au rôle ROLE_ADMIN.
 * Toute vérification de privilège est faite par l'appelant via Auth::requireAdminRole().
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

final class AdminManager
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT admin_id, email, full_name, role, is_active, last_login_at, created_at
             FROM administrators ORDER BY full_name ASC'
        );
        return $stmt->fetchAll();
    }

    public function find(int $adminId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT admin_id, email, full_name, role, is_active, last_login_at, created_at
             FROM administrators WHERE admin_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $adminId]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) AS nb FROM administrators WHERE email = :email';
        $params = ['email' => $email];
        if ($excludeId !== null) {
            $sql .= ' AND admin_id != :id';
            $params['id'] = $excludeId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetch()['nb'] > 0;
    }

    /**
     * @return array{success: bool, message: string, id?: int}
     */
    public function create(string $email, string $password, string $fullName, string $role, bool $isActive): array
    {
        $email = trim(mb_strtolower($email));
        $fullName = trim($fullName);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Adresse email invalide.'];
        }
        if (mb_strlen($password) < 10) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 10 caractères.'];
        }
        if ($fullName === '') {
            return ['success' => false, 'message' => 'Le nom complet est obligatoire.'];
        }
        if (!in_array($role, [ROLE_EDITOR, ROLE_ADMIN], true)) {
            return ['success' => false, 'message' => 'Rôle invalide.'];
        }
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Cette adresse email est déjà utilisée.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare(
            'INSERT INTO administrators (email, password_hash, full_name, role, is_active)
             VALUES (:email, :hash, :name, :role, :active)'
        );
        $stmt->execute([
            'email'  => $email,
            'hash'   => $hash,
            'name'   => $fullName,
            'role'   => $role,
            'active' => $isActive ? 1 : 0,
        ]);

        return ['success' => true, 'message' => 'Administrateur créé.', 'id' => (int) $this->pdo->lastInsertId()];
    }

    /**
     * Met à jour un administrateur. Le mot de passe n'est changé que si $password n'est pas vide.
     */
    public function update(int $adminId, string $email, string $fullName, string $role, bool $isActive, ?string $password = null): array
    {
        $email = trim(mb_strtolower($email));
        $fullName = trim($fullName);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Adresse email invalide.'];
        }
        if ($fullName === '') {
            return ['success' => false, 'message' => 'Le nom complet est obligatoire.'];
        }
        if (!in_array($role, [ROLE_EDITOR, ROLE_ADMIN], true)) {
            return ['success' => false, 'message' => 'Rôle invalide.'];
        }
        if ($this->emailExists($email, $adminId)) {
            return ['success' => false, 'message' => 'Cette adresse email est déjà utilisée par un autre compte.'];
        }

        $sql = 'UPDATE administrators SET email = :email, full_name = :name, role = :role, is_active = :active';
        $params = [
            'email'  => $email,
            'name'   => $fullName,
            'role'   => $role,
            'active' => $isActive ? 1 : 0,
            'id'     => $adminId,
        ];

        if ($password !== null && $password !== '') {
            if (mb_strlen($password) < 10) {
                return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 10 caractères.'];
            }
            $sql .= ', password_hash = :hash';
            $params['hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $sql .= ' WHERE admin_id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Administrateur mis à jour.'];
    }

    public function delete(int $adminId, int $currentAdminId): array
    {
        if ($adminId === $currentAdminId) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'];
        }

        // Empêche de supprimer le dernier compte admin restant
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS nb FROM administrators WHERE role = 'admin' AND admin_id != :id");
        $stmt->execute(['id' => $adminId]);
        $remainingAdmins = (int) $stmt->fetch()['nb'];

        $target = $this->find($adminId);
        if ($target && $target['role'] === ROLE_ADMIN && $remainingAdmins === 0) {
            return ['success' => false, 'message' => 'Impossible de supprimer le dernier compte administrateur.'];
        }

        $stmt = $this->pdo->prepare('DELETE FROM administrators WHERE admin_id = :id');
        $stmt->execute(['id' => $adminId]);

        return ['success' => true, 'message' => 'Administrateur supprimé.'];
    }
}
