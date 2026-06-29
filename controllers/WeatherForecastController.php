<?php
// ============================================================
// controllers/WeatherForecastController.php
// ============================================================

namespace controllers;
require_once __DIR__ . '/../models/WeatherForecast.php';
require_once __DIR__ . '/../helpers/OpenMeteoHelper.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';
require_once __DIR__ . '/../controllers/SlopeController.php';


define('WEATHER_CACHE_DURATION', 86400); // Duration in seconds


use Exception;
use models\WeatherForecast;
use models\Slope;
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


    public function getBySlopeId(int $slope_id, int $store = 1): ?array
    {
        try {

            if ($store)
            {
                // ---- récupération complète des prévisions et stockage en base de donnée -----
                // ---- puis lecture de la pente qui ous intéresse ----

                // Check if cache should be refreshed
                if ($this->cacheHelper->shouldRefreshCache(WEATHER_CACHE_DURATION)) {
                    // Fetch data from .... for all slopes
                    echo ('Weather data to be fetch from API.</br>');
                    // Supprime les données météo de la BDD
                    WeatherForecast::cleanData();
                    echo ('Weather DB data cleaned.</br>');

                    $slopeCnt = Slope::getCount(true);
                    echo ('Nombre de pentes : ' . $slopeCnt.'</br>');

                    $slopeIndex = 0;
                    $listId = 0;
                    while ($slopeIndex < $slopeCnt)
                    {
                        echo ('Get 300 from : ' . $slopeIndex.'</br>');
                        $slopes = Slope::getAll(300,$slopeIndex,true);
                        $slopesWeatherDataset = $this->meteoHelper->fetchForSlopes($slopes);
                        if ($slopesWeatherDataset) {
                            foreach ($slopesWeatherDataset as $slopeWeatherDataset) {
                                $listId = WeatherForecast::insert($slopeWeatherDataset['weatherDataSet'], $slopeWeatherDataset['slopeId']);
                                echo ('Insert SlopeId = '.$slopeWeatherDataset['slopeId'].'</br>');
                            }
                        }
                        echo ('200  slopes inserted - lastInsert : '.$listId.'</br>');
                        $slopeIndex = $slopeIndex + 300;
                    }
                    echo ('All  slopes inserted</br>');

                    $this->cacheHelper->updateCacheTime();

                }

                // Read from database
                $dbWeatherForecasts = WeatherForecast::getBySlopeId($slope_id);
                //var_dump($dbWeatherForecasts);

                // Unifie le format de donnée avec un appel direct sans stockage
                $slopesWeatherDataset = [];
                $slopeWeatherDataset = [];
                if (count($dbWeatherForecasts)>0)
                {
                    $slopeWeatherDataset['slopeId'] = $dbWeatherForecasts[0]['slopeId'];
                    $slopeWeatherDataset['weatherDataSet'] = [];

                    foreach ($dbWeatherForecasts as $dbWeatherForecast) {
                        $weatherDataset = [];
                        $weatherDataset['forecast_time'] = $dbWeatherForecast['forecast_time'];
                        $weatherDataset['wind_speed'] = $dbWeatherForecast['wind_speed'];
                        $weatherDataset['wind_heading'] = $dbWeatherForecast['wind_heading'];
                        $weatherDataset['wind_gust'] = $dbWeatherForecast['wind_gust'];
                        $weatherDataset['cloud_cover'] = $dbWeatherForecast['cloud_cover'];
                        $weatherDataset['rain'] = $dbWeatherForecast['rain'];
                        $weatherDataset['temperature'] = $dbWeatherForecast['temperature'];
                        $slopeWeatherDataset['weatherDataSet'][] = $weatherDataset;
                    }
                }
                $slopesWeatherDataset[] = $slopeWeatherDataset;
                return $slopesWeatherDataset;

            }
            else
            {
                // ---- simple interrogation directe d'une prévision sans stockage
                $slopes = [];
                $slopes[] = Slope::getById($slope_id);
                $slopesWeatherDataset = $this->meteoHelper->fetchForSlopes($slopes);
                return $slopesWeatherDataset;
            }



        } catch (Exception $e) {
            return null;
        }
    }


}


