<?php
/**
 * Moteur CRUD générique, piloté par TableRegistry.
 *
 * Toutes les requêtes utilisent des requêtes préparées PDO.
 * Le nom de la table et des colonnes provient UNIQUEMENT du registre
 * (jamais directement de l'input utilisateur), ce qui élimine le risque
 * d'injection SQL sur les identifiants (impossible à paramétrer nativement en SQL).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/TableRegistry.php';

final class CrudEngine
{
    private PDO $pdo;
    private string $table;
    private array $schema;

    public function __construct(string $table)
    {
        $schema = TableRegistry::get($table);
        if ($schema === null) {
            throw new InvalidArgumentException("Table inconnue dans le registre : {$table}");
        }
        $this->table  = $table;
        $this->schema = $schema;
        $this->pdo    = Database::getConnection();
    }

    public function schema(): array
    {
        return $this->schema;
    }

    /**
     * Liste paginée avec recherche optionnelle.
     */
    public function paginate(int $page = 1, int $perPage = 25, string $search = ''): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset  = ($page - 1) * $perPage;

        [$whereSql, $params] = $this->buildSearchWhere($search);

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) AS nb FROM `{$this->table}` {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetch()['nb'];

        $orderBy = $this->schema['order_by'] ?? '1';
        $sql = "SELECT * FROM `{$this->table}` {$whereSql} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows'        => $stmt->fetchAll(),
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    private function buildSearchWhere(string $search): array
    {
        $searchCols = $this->schema['search_columns'] ?? [];
        if ($search === '' || empty($searchCols)) {
            return ['', []];
        }

        $likeClauses = [];
        $params = [];
        foreach ($searchCols as $i => $col) {
            $placeholder = ':search_' . $i;
            $likeClauses[] = "`{$col}` LIKE {$placeholder}";
            $params[$placeholder] = '%' . $search . '%';
        }

        return ['WHERE ' . implode(' OR ', $likeClauses), $params];
    }

    public function find($pkValue): ?array
    {
        $pk = $this->schema['primary_key'];
        $stmt = $this->pdo->prepare("SELECT * FROM `{$this->table}` WHERE `{$pk}` = :pk LIMIT 1");
        $stmt->execute(['pk' => $pkValue]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Calcule le prochain ID pour les tables sans AUTO_INCREMENT (ex: slopes.slopeId).
     */
    public function nextManualId(): int
    {
        $pk = $this->schema['primary_key'];
        $stmt = $this->pdo->query("SELECT MAX(`{$pk}`) AS max_id FROM `{$this->table}`");
        $max = $stmt->fetch()['max_id'];
        return ((int) $max) + 1;
    }

    /**
     * Insère une nouvelle ligne. $data doit être déjà validé/filtré par FormProcessor.
     * Retourne la clé primaire de la ligne créée.
     */
    public function insert(array $data): string
    {
        $pk       = $this->schema['primary_key'];
        $pkAuto   = $this->schema['pk_auto'] ?? true;
        $columns  = array_keys($data);

        if (!$pkAuto && !isset($data[$pk])) {
            $data[$pk] = $this->nextManualId();
            $columns[] = $pk;
        }

        $placeholders = array_map(fn($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(', ', array_map(fn($c) => "`{$c}`", $columns)),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        foreach ($columns as $col) {
            $stmt->bindValue(':' . $col, $data[$col]);
        }
        $stmt->execute();

        return $pkAuto ? $this->pdo->lastInsertId() : (string) $data[$pk];
    }

    public function update($pkValue, array $data): bool
    {
        $pk = $this->schema['primary_key'];
        unset($data[$pk]); // jamais modifier la clé primaire via update()

        if (empty($data)) {
            return true;
        }

        $setSql = implode(', ', array_map(fn($c) => "`{$c}` = :{$c}", array_keys($data)));
        $sql = "UPDATE `{$this->table}` SET {$setSql} WHERE `{$pk}` = :pk_value";

        $stmt = $this->pdo->prepare($sql);
        foreach ($data as $col => $val) {
            $stmt->bindValue(':' . $col, $val);
        }
        $stmt->bindValue(':pk_value', $pkValue);

        return $stmt->execute();
    }

    public function delete($pkValue): bool
    {
        $pk = $this->schema['primary_key'];
        $stmt = $this->pdo->prepare("DELETE FROM `{$this->table}` WHERE `{$pk}` = :pk");
        return $stmt->execute(['pk' => $pkValue]);
    }

    /**
     * Pour les champs de type "lookup" (ex: weather_forecast.slope_id -> slopes.name) :
     * retourne la liste des options [valeur => libellé] pour peupler un <select>.
     */
    public function lookupOptions(string $lookupTable, string $lookupPk, string $lookupLabel): array
    {
        // lookupTable / lookupPk / lookupLabel proviennent UNIQUEMENT de TableRegistry (code statique),
        // jamais d'une entrée utilisateur — donc sûr malgré l'absence de paramétrage des identifiants.
        $stmt = $this->pdo->query(
            "SELECT `{$lookupPk}` AS pk, `{$lookupLabel}` AS label FROM `{$lookupTable}` ORDER BY `{$lookupLabel}` ASC"
        );
        $options = [];
        foreach ($stmt->fetchAll() as $row) {
            $options[$row['pk']] = $row['label'] ?: ('#' . $row['pk']);
        }
        return $options;
    }
}
