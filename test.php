<?php
require_once __DIR__ . '/controllers/WeatherForecastController.php';
//require_once __DIR__ . '/helpers/OpenMeteoHelper.php';
//require_once __DIR__ . '/controllers/SlopeController.php';
//require_once __DIR__ . '/models/Slope.php';
//require_once __DIR__ . '/config/database.php';
//require_once __DIR__ . '/models/WeatherForecast.php';
//require_once __DIR__ . '/helpers/CacheHelper.php';
//require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html>
<body>

<?php


use controllers\WeatherForecastController;
use controllers\SlopeController;
use helpers\OpenMeteoHelper;
use models\Slope;

//$meteoHelper = new OpenMeteoHelper('https://api.open-meteo.com/v1/forecast?hourly=wind_speed_10m,wind_direction_10m&forecast_days=3&');


//$slopeController = new SlopeController();
//$slopesData = $slopeController->get(10,700, 1);
//var_dump($slopesData);
//$data = $meteoHelper->fetchForSlopes($slopesData);
//var_dump($data);


//var_dump($slopeController->get());

$controller = new WeatherForecastController();
var_dump($controller->getBySlopeId(3));

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



?>

</body>
</html>