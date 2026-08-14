<?php
// ============================================================
// models/SlopeUpdate.php  –  Modèle Pente
// ============================================================

namespace models;
use function getDB;

require_once __DIR__ . '/../config/database.php';

class SlopeUpdate
{
    public static function insert(int $slopeId, array $input): int
    {

        $db = getDB();
        $lastId = 0;

        $text = json_encode($input);


        $query = "INSERT INTO slope_updates
                (slopeId,text)
                VALUES
                (:slopeId,:text)" ;
        $stmt = $db->prepare($query);

        $stmt->bindParam(":slopeId", $slopeId);
        $stmt->bindParam(":text", $text);

        $stmt->execute();
        $lastId = $db->lastInsertId();

        return $lastId;
    }

    public static function getById(int $updateId)
    {
        $stmt = getDB()->prepare(
            "SELECT * from slope_updates
                WHERE id = ? "
        );
        $stmt->execute([$updateId]);
        $row = $stmt->fetch();

        return $row;
    }

}
