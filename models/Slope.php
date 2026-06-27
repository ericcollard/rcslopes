<?php
// ============================================================
// models/Slope.php  –  Modèle Pente
// ============================================================

namespace models;
use function getDB;
use function windSetToArray;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class Slope
{

    // ── Lecture ──────────────────────────────────────────────

    /**
     * Retourne tous les sites de vol.
     */
    public static function getAll($limit = -1, $offset = -1, $slope = 0): array
    {
        if ($limit == -1)
        {
            $sql = "SELECT * FROM slopes ORDER BY slopeId ASC";
            if ($slope == 1) $sql = "SELECT * FROM slopes where type = 'pente' ORDER BY slopeId ASC";
            $stmt = getDB()->query($sql);
        }
        else
        {
            $sql = "SELECT * FROM slopes ORDER BY slopeId ASC LIMIT " .$limit . " OFFSET ".$offset;
            if ($slope == 1) $sql = "SELECT * FROM slopes where type = 'pente' ORDER BY slopeId ASC LIMIT " .$limit . " OFFSET ".$offset;
            $stmt = getDB()->query($sql);

        }

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['orient'] = windSetToArray($row['orient']);
            $row['lat'] = (float)$row['lat'];
            $row['lng'] = (float)$row['lng'];
        }

        return $rows;
    }

    public static function getCount($slope = 0): int
    {
        $sql = "SELECT COUNT(*) as Nb FROM slopes";
        if ($slope == 1) $sql = "SELECT COUNT(*) as Nb FROM slopes where type = 'pente'";
        $stmt = getDB()->query($sql);

        $rows = $stmt->fetchAll();
        $cnt = 0;
        foreach ($rows as &$row) {
            $cnt = (int)$row['Nb'];
        }

        return $cnt;
    }

    /**
     * Retourne un site par son slopeId, ou null.
     */
    public static function getById(int $slopeId): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT *
             FROM slopes
             WHERE slopeId = ?'
        );
        $stmt->execute([$slopeId]);
        $row = $stmt->fetch();

        if (!$row) return null;

        $row['orient'] = windSetToArray($row['orient']);
        $row['lat'] = (float)$row['lat'];
        $row['lng'] = (float)$row['lng'];

        return $row;
    }

    /**
     * Retourne les sites qui correspondent anu nom.
     */
    public static function getByPartialName(string $searchStr, int $limit=10): ?array
    {
        // recherche sur le nom de la pente

        if (strlen($searchStr) < 2) return null;


        $stmt = getDB()->prepare(
            'SELECT slopeId,name, lat, lng
             FROM slopes
             WHERE type = "pente" and name like ? Limit '.$limit
        );

        if (is_numeric($searchStr))
        {
            // recherche sur le département
            $stmt = getDB()->prepare(
                'SELECT slopeId,name, lat, lng
             FROM slopes
             WHERE type = "pente" and dpt = ? '
            );
        }
        else
        {
            $searchStr = "%".$searchStr."%";
        }

        $stmt->execute([$searchStr]);
        $row = $stmt->fetchAll();
        if (!$row) return null;

        return $row;
    }

    // ── Création ─────────────────────────────────────────────

    /**
     * Crée un nouveau site. Retourne l'entité créée.
     */
    public static function create(array $data): array
    {
        $db = getDB();
        $stmt = $db->prepare(
            'INSERT INTO slopes
                (name, type, lat, lng, orient, description, weather_url)
             VALUES
                (:name, :type, :lat, :lng, :orient, :description, :weather_url)'
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':type' => $data['type'],
            ':lat' => $data['lat'],
            ':lng' => $data['lng'],
            ':orient' => implode(',', $data['orient']),
            ':description' => $data['description'] ?? null,
            ':weather_url' => $data['weather_url'] ?? null,
        ]);

        return self::getById((int)$db->lastInsertId());
    }

    // ── Mise à jour ───────────────────────────────────────────

    /**
     * Met à jour un site existant. Retourne l'entité mise à jour.
     */
    public static function update(int $slopeId, array $data): ?array
    {
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params[':name'] = $data['name'];
        }
        if (isset($data['type'])) {
            $fields[] = 'type = :type';
            $params[':type'] = $data['type'];
        }
        if (isset($data['lat'])) {
            $fields[] = 'lat = :lat';
            $params[':lat'] = $data['lat'];
        }
        if (isset($data['lng'])) {
            $fields[] = 'lng = :lng';
            $params[':lng'] = $data['lng'];
        }
        if (isset($data['orient'])) {
            $fields[] = 'orient = :orient';
            $params[':orient'] = implode(',', $data['orient']);
        }
        if (array_key_exists('description', $data)) {
            $fields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }
        if (array_key_exists('weather_url', $data)) {
            $fields[] = 'weather_url = :weather_url';
            $params[':weather_url'] = $data['weather_url'];
        }

        if (empty($fields)) return self::getById($slopeId); // rien à modifier

        $params[':slopeId'] = $slopeId;
        $sql = 'UPDATE slopes SET ' . implode(', ', $fields) . ' WHERE slopeId = :slopeId';
        getDB()->prepare($sql)->execute($params);

        return self::getById($slopeId);
    }
}
