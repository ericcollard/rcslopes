<?php
// ============================================================
// models/WindStation.php  –  Modèle Station Vent
// ============================================================

namespace models;

use function getDB;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class WindStation
{

    // ── Lecture ──────────────────────────────────────────────

    /**
     * Retourne toutes les mesures des stations.
     */
    public static function getAll(): array
    {
        $stmt = getDB()->query(
            'SELECT id, 
                    station_id,
                    provider,
                    latitude,
                    longitude,
                    widget_code,
                    measurement_date,
                    wind_heading,
                    wind_speed_avg,
                    wind_speed_max,
                    wind_speed_min
             FROM wind_station
             ORDER BY measurement_date DESC'
        );

        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['latitude'] = (float)$row['latitude'];
            $row['longitude'] = (float)$row['longitude'];
            $row['wind_heading'] = (float)$row['wind_heading'];
            $row['wind_speed_avg'] = (float)$row['wind_speed_avg'];
            $row['wind_speed_max'] = (float)$row['wind_speed_max'];
            $row['wind_speed_min'] = (float)$row['wind_speed_min'];
        }

        return $rows;
    }

/*
    public static function getLastupdate()
    {
        $stmt = getDB()->query(
            'SELECT max(measurement_date) as lastdate
             FROM wind_station'
        );

        $rows = $stmt->fetchAll();
        $dateStr = '';

        foreach ($rows as &$row) {
            $dateStr = $row['lastdate'] ;
        }

        return $dateStr;
    }
*/

    /**
     * Retourne une station par son station_id, ou null.
     */
    public static function get(int $station_id, string $provider): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT id,
                    station_id,
                    provider, 
                    widget_code,
                    latitude,
                    longitude,
                    measurement_date,
                    wind_heading,
                    wind_speed_avg,
                    wind_speed_max,
                    wind_speed_min
             FROM wind_station
             WHERE station_id = ? and provider = ?'
        );

        $stmt->execute([$station_id,$provider]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        $row['latitude'] = (float)$row['latitude'];
        $row['longitude'] = (float)$row['longitude'];
        $row['wind_heading'] = (float)$row['wind_heading'];
        $row['wind_speed_avg'] = (float)$row['wind_speed_avg'];
        $row['wind_speed_max'] = (float)$row['wind_speed_max'];
        $row['wind_speed_min'] = (float)$row['wind_speed_min'];

        return $row;
    }



    // ── Mise à jour ───────────────────────────────────────────

    /**
     * Met à jour une mesure existante.
     */
    public static function update(int $station_id, array $data): ?array
    {
        $fields = [];
        $params = [];

        if (isset($data['latitude'])) {
            $fields[] = 'latitude = :latitude';
            $params[':latitude'] = $data['latitude'];
        }

        if (isset($data['longitude'])) {
            $fields[] = 'longitude = :longitude';
            $params[':longitude'] = $data['longitude'];
        }

        if (isset($data['measurement_date'])) {
            $fields[] = 'measurement_date = :measurement_date';
            $params[':measurement_date'] = $data['measurement_date'];
        }

        if (isset($data['wind_heading'])) {
            $fields[] = 'wind_heading = :wind_heading';
            $params[':wind_heading'] = $data['wind_heading'];
        }

        if (isset($data['wind_speed_avg'])) {
            $fields[] = 'wind_speed_avg = :wind_speed_avg';
            $params[':wind_speed_avg'] = $data['wind_speed_avg'];
        }

        if (isset($data['wind_speed_max'])) {
            $fields[] = 'wind_speed_max = :wind_speed_max';
            $params[':wind_speed_max'] = $data['wind_speed_max'];
        }

        if (isset($data['wind_speed_min'])) {
            $fields[] = 'wind_speed_min = :wind_speed_min';
            $params[':wind_speed_min'] = $data['wind_speed_min'];
        }

        if (empty($fields)) {
            return self::getById($station_id);
        }

        $params[':station_id'] = $station_id;

        $sql = 'UPDATE wind_station
                SET ' . implode(', ', $fields) . '
                WHERE station_id = :station_id';

        getDB()->prepare($sql)->execute($params);

        return self::getById($station_id);
    }


    // Upsert (Insert or Update)
    public static function upsert(array $data) {

        $db = getDB();

        $query = "INSERT INTO wind_station
                (station_id, provider, widget_code, latitude, longitude, measurement_date, wind_heading, wind_speed_avg, wind_speed_max, wind_speed_min)
                VALUES
                (:station_id, :provider, :widget_code, :latitude, :longitude, :measurement_date, :wind_heading, :wind_speed_avg, :wind_speed_max, :wind_speed_min)
                ON DUPLICATE KEY UPDATE
                    widget_code = VALUES(widget_code),
                    latitude = VALUES(latitude),
                    longitude = VALUES(longitude),
                    measurement_date = VALUES(measurement_date),
                    wind_heading = VALUES(wind_heading),
                    wind_speed_avg = VALUES(wind_speed_avg),
                    wind_speed_max = VALUES(wind_speed_max),
                    wind_speed_min = VALUES(wind_speed_min)" ;

        $stmt = $db->prepare($query);

        $stmt->bindParam(":station_id", $data['station_id']);
        $stmt->bindParam(":provider", $data['provider']);
        $stmt->bindParam(":latitude", $data['latitude']);
        $stmt->bindParam(":longitude", $data['longitude']);
        $stmt->bindParam(":widget_code", $data['widget_code']);
        $stmt->bindParam(":measurement_date", $data['measurement_date']);
        $stmt->bindParam(":wind_heading", $data['wind_heading']);
        $stmt->bindParam(":wind_speed_avg", $data['wind_speed_avg']);
        $stmt->bindParam(":wind_speed_max", $data['wind_speed_max']);
        $stmt->bindParam(":wind_speed_min", $data['wind_speed_min']);


        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public static function truncateData(): bool
    {
        $db = getDB();
        $stmt = $db->prepare('TRUNCATE TABLE wind_station');
        return $stmt->execute();
    }

}