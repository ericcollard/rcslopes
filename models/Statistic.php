<?php
// ============================================================
// models/SlopePicture.php  –  Modèle Pente
// ============================================================

namespace models;
use Exception;
use function getDB;
use helpers\CacheHelper;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';

class Statistic
{

    /**
     * Retourne les images site par son slopeId, ou null.
     */
    public static function getBySlopeId(int $slopeId): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT *
             FROM statistics
             WHERE slopeId = ?'
        );
        $stmt->execute([$slopeId]);
        $row = $stmt->fetchAll();

        if (!$row) return null;

        return $row;
    }

    public static function clean() {
        // suppression de toutes les stats supérieures à 1 an glissant

        $stmt = getDB()->prepare(
            'DELETE FROM statistics WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)'
        );
        $stmt->execute();

    }

    public static function register(int $slopeId, int $typeId) {
        $db = getDB();

        // $typeId ... 0 = vue
        // $typeId ... 1 = like


        $cacheHelper = new CacheHelper("cache/last_stat.txt");
        try {
            // Check if cache should be refreshed
            if ($cacheHelper->shouldRefreshCache(86400)) {
                // Clean Statistics every day
                echo "Clean";
                self::clean();
                $cacheHelper->updateCacheTime();
            }
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }

        $query = "INSERT INTO statistics
                (slopeId, typeId)
                VALUES
                (:slopeId, :typeId)" ;
        $stmt = $db->prepare($query);

        if ($slopeId > 0)
            $stmt->bindParam(":slopeId", $slopeId);
        if ($typeId >= 0)
            $stmt->bindParam(":typeId", $typeId);
        $stmt->execute();
    }

}
