<?php
// ============================================================
// models/SlopePicture.php  –  Modèle Pente
// ============================================================

namespace models;
use function getDB;


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class SlopePicture
{

    /**
     * Retourne les images site par son slopeId, ou null.
     */
    public static function getBySlopeId(int $slopeId): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT *
             FROM slope_pictures
             WHERE slopeId = ?'
        );
        $stmt->execute([$slopeId]);
        $row = $stmt->fetchAll();

        if (!$row) return null;

        return $row;
    }

}
