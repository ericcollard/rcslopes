<?php
error_reporting(E_ALL);
require_once __DIR__ . '/helpers/OpenMeteoHelper.php';
require_once __DIR__ . '/controllers/WeatherForecastController.php';
require_once __DIR__ . '/models/Slope.php';

use controllers\WeatherForecastController;
use helpers\OpenMeteoHelper;
use models\Slope;
use models\WeatherForecast;

/*
$meteoHelper = new OpenMeteoHelper('https://api.open-meteo.com/v1/forecast?hourly=wind_speed_10m,wind_gusts_10m,wind_direction_10m,cloud_cover,rain,showers,temperature_2m&forecast_days=3&');$slope = Slope::getById(3);
$slopes = [];
$slopes[] = $slope;
$slope = Slope::getById(4);
$slopes[] = $slope;
$weatherData = $meteoHelper->fetchForSlopes($slopes);
echo '<pre>';
var_dump($weatherData);
echo '</pre>';
*/

//$slopeController = new SlopeController();
//$slopesData = $slopeController->get(10,700, 1);
//var_dump($slopesData);
//$data = $meteoHelper->fetchForSlopes($slopesData);
//var_dump($data);

$slopeWeatherData = WeatherForecast::getBySlopeId(5);
var_dump($slopeWeatherData);

//var_dump($slopeController->get());

//$controller = new WeatherForecastController();
//var_dump($controller->getBySlopeId(3,0));

/*
$slopesData = [
    ['slope_id' => 1, 'latitude' => 52.52, 'longitude' =>13.41 ],
    ['slope_id' => 2, 'latitude' => 50.12, 'longitude' =>8.68 ],
    ['slope_id' => 3, 'latitude' => 53.55, 'longitude' =>9.99 ]
];

$data = $meteoHelper->fetchForSlopes($slopesData);
$formatted = $meteoHelper->formatSlopesData($data);
var_dump($formatted);
*/


