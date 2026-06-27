<?php
// ============================================================
// controllers/WeatherForecastController.php
// ============================================================

namespace controllers;
require_once __DIR__ . '/../models/WeatherForecast.php';
require_once __DIR__ . '/../helpers/OpenMeteoHelper.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';
// require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/SlopeController.php';


define('WEATHER_CACHE_DURATION', 86400); // Duration in seconds


use Exception;
use models\WeatherForecast;
use helpers\CacheHelper;
use helpers\OpenMeteoHelper;



class WeatherForecastController
{
    private $cacheHelper;
    private $meteoHelper;

    public function __construct() {
        $this->cacheHelper = new CacheHelper('cache/last_weather_call.txt');
        $this->meteoHelper = new OpenMeteoHelper('https://api.open-meteo.com/v1/forecast?hourly=wind_speed_10m,wind_gusts_10m,wind_direction_10m,cloud_cover,rain,showers,temperature_2m&forecast_days=3&');
        //https://api.open-meteo.com/v1/forecast?latitude=43.3357&longitude=5.482&hourly=wind_speed_10m,wind_gusts_10m,wind_direction_10m,cloud_cover,rain,showers,temperature_2m,weather_code
    }


    public function getBySlopeId(int $slope_id): ?array
    {
        try {
            // Check if cache should be refreshed
            if ($this->cacheHelper->shouldRefreshCache(WEATHER_CACHE_DURATION)) {
                // Fetch data from .... for all slopes
                echo ('Weather data to be fetch from API.</br>');
                // Supprime les données météo de la BDD
                WeatherForecast::cleanData();
                echo ('Weather DB data cleaned.</br>');

                $slopeController = new SlopeController();
                $slopeCnt = $slopeController->getCount(1);
                echo ('Nombre de pentes : ' . $slopeCnt.'</br>');

                $slopeIndex = 0;
                $listId = 0;
                while ($slopeIndex < $slopeCnt)
                {
                    echo ('Get 300 from : ' . $slopeIndex.'</br>');
                    $slopesData = $slopeController->get(300,$slopeIndex,1);
                    var_dump($slopesData);
                    $data = $this->meteoHelper->fetchForSlopes($slopesData);
                    $slopesWeatherDataset = $this->meteoHelper->formatSlopesData($data);
                    if ($slopesWeatherDataset) {
                        foreach ($slopesWeatherDataset as $slopeWeatherDataset) {
                            $listId = WeatherForecast::insert($slopeWeatherDataset['weatherDataSet'], $slopeWeatherDataset['slope_id']);
                            echo ('Insert SlopeId = '.$slopeWeatherDataset['slope_id'].'</br>');
                        }
                    }
                    echo ('200  slopes inserted - lastInsert : '.$listId.'</br>');
                    $slopeIndex = $slopeIndex + 300;
                }
                echo ('All  slopes inserted</br>');

                $this->cacheHelper->updateCacheTime();

            }

            // Read from database
            $forecasts = WeatherForecast::getBySlopeId($slope_id);

            return $forecasts;

        } catch (Exception $e) {
            return null;
        }
    }


}


