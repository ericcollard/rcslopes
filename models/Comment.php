<?php
// ============================================================
// models/Slope.php  –  Modèle Pente
// ============================================================

namespace models;
use function getDB;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class Comment
{


    /**
     * Retourne un site par son id, ou null.
     */
    public static function getById(int $id): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT *
             FROM comments
             WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return $row;
    }

    /**
     * Retourne les sites qui correspondent anu nom.
     */
    public static function getBySlopeId(int $slopeId): ?array
    {
        // recherche sur le nom de la pente

        if ($slopeId < 1) return null;

        $stmt = getDB()->prepare(
            'SELECT *
             FROM comments
             WHERE status != "disabled" and slopeId=  ?  '
        );

        $stmt->execute([$slopeId]);
        $row = $stmt->fetchAll();
        if (!$row) return null;

        return $row;
    }

    public static function getUnderReview(): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT *
             FROM comments
             WHERE status = "new"'
        );

        $stmt->execute();
        $row = $stmt->fetchAll();
        if (!$row) return null;

        return $row;
    }

    public static function insert(string $comment, string $email, int $slopeId): int
    {

        $db = getDB();
        $lastId = 0;


        $query = "INSERT INTO comments
                (slopeId, comment, email, status)
                VALUES
                (:slopeId, :comment, :email , :status)" ;
        $stmt = $db->prepare($query);

        $defaultStatus = "new";

        $stmt->bindParam(":slopeId", $slopeId);
        $stmt->bindParam(":comment", $comment);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":status", $defaultStatus);
        $stmt->execute();
            $lastId = $db->lastInsertId();

        return $lastId;
    }


    /**
     * Retourne un site par son id, ou null.
     */
    public static function checkBeforeInsert(string $email, int $slopeId): bool
    {
        $stmt = getDB()->prepare(
            'SELECT count(*) as Nb
             FROM comments
             WHERE slopeId = ? and status = "new"'
        );
        $stmt->execute([$slopeId]);
        $row = $stmt->fetch();
        $cnt = (int)$row['Nb'];

        if ($cnt > 3) return false;

        $stmt = getDB()->prepare(
            'SELECT count(*) as Nb
             FROM comments
             WHERE email = ? and status = "new"'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        $cnt = (int)$row['Nb'];

        if ($cnt > 3) return false;
        return true;

    }
}
