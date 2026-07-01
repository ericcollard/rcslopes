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
    public static function getAll($limit = -1, $offset = -1, $slopeType = false, $status = 'active'): array
    {
        if ($limit == -1)
        {
            $sql = "SELECT * FROM slopes where status='".$status."' ORDER BY slopeId ASC";
            if ($slopeType) $sql = "SELECT * FROM slopes where status='".$status."' and type = 'pente' ORDER BY slopeId ASC";
            $stmt = getDB()->query($sql);
        }
        else
        {
            $sql = "SELECT * FROM slopes where status='".$status."' ORDER BY slopeId ASC LIMIT " .$limit . " OFFSET ".$offset;
            if ($slopeType) $sql = "SELECT * FROM slopes where status='".$status."' and type = 'pente' ORDER BY slopeId ASC LIMIT " .$limit . " OFFSET ".$offset;
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

    public static function getCount($slopeType = false, $status = 'active'): int
    {
        $sql = "SELECT COUNT(*) as Nb FROM slopes where status='".$status."'";
        if ($slopeType) $sql = "SELECT COUNT(*) as Nb FROM slopes where status='".$status."' and type = 'pente'";
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
    public static function getByPartialName(string $searchStr, int $limit=10, $status = 'active'): ?array
    {
        // recherche sur le nom de la pente

        if (strlen($searchStr) < 2) return null;


        $stmt = getDB()->prepare(
            'SELECT slopeId,name, lat, lng
             FROM slopes
             WHERE status="'.$status.'" and type = "pente" and name like ? Limit '.$limit
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


}
