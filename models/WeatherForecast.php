<?php
// ============================================================
// models/WindStation.php  –  Modèle Station Vent
// ============================================================

namespace models;

use function getDB;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

class WeatherForecast
{

    // ── Lecture ──────────────────────────────────────────────

    /**
     * Retourne les données pour une station par son station_id, ou null.
     */
    public static function getBySlopeId(int $slope_id): ?array
    {
        $stmt = getDB()->prepare(
            'SELECT *
             FROM weather_forecast
             WHERE slope_id = ? ORDER BY forecast_time'
        );

        $stmt->execute([$slope_id]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            return null;
        }

        foreach ($rows as $row) {
            $row['wind_speed'] = (float)$row['wind_speed'];
            $row['wind_heading'] = (float)$row['wind_heading'];
            $row['wind_gust'] = (float)$row['wind_gust'];
            $row['cloud_cover'] = (float)$row['cloud_cover'];
            $row['rain'] = (float)$row['rain'];
            $row['temperature'] = (float)$row['temperature'];
        }


        return $rows;
    }


    // Upsert (Insert or Update)
    public static function insert(array $weather_forecast_dataset, int $slope_id)
    {

        $db = getDB();
        $lastId = 0;


        $query = "INSERT INTO weather_forecast
                (slope_id, wind_heading, wind_speed, forecast_time, wind_gust, cloud_cover,rain, temperature)
                VALUES
                (:slope_id, :wind_heading, :wind_speed, :forecast_time , :wind_gust, :cloud_cover,:rain, :temperature)" ;
        $stmt = $db->prepare($query);

        foreach ($weather_forecast_dataset as $weather_forecast_dataItem) {
            $stmt->bindParam(":slope_id", $slope_id);
            $stmt->bindParam(":wind_heading", $weather_forecast_dataItem['wind_heading']);
            $stmt->bindParam(":wind_speed", $weather_forecast_dataItem['wind_speed']);
            $stmt->bindParam(":forecast_time", $weather_forecast_dataItem['forecast_time']);
            $stmt->bindParam(":wind_gust", $weather_forecast_dataItem['wind_gust']);
            $stmt->bindParam(":cloud_cover", $weather_forecast_dataItem['cloud_cover']);
            $stmt->bindParam(":rain", $weather_forecast_dataItem['rain']);
            $stmt->bindParam(":temperature", $weather_forecast_dataItem['temperature']);
            $stmt->execute();
            $lastId = $db->lastInsertId();
        }

        return $lastId;
    }



    // Upsert (Insert or Update)
    public static function upsert(array $weather_forecast_dataset, int $slope_id)
    {

        $db = getDB();

        // Suppression des anciennes données
        $stmt = getDB()->prepare(
            'DELETE FROM weather_forecast
             WHERE slope_id = ?'
        );
        $stmt->execute([$slope_id]);


        self::insert($weather_forecast_dataset,$slope_id);

        return true;
    }


    // Upsert (Insert or Update)
    public static function cleanData()
    {

        $db = getDB();

        // Suppression des anciennes données
        $stmt = getDB()->prepare(
            'TRUNCATE TABLE weather_forecast;'
        );
        $stmt->execute();

        return true;
    }

}