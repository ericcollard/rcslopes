<?php
require_once __DIR__ . '/models/WindStation.php';
require_once __DIR__ . '/helpers/PioupiouHelper.php';
require_once __DIR__ . '/controllers/WindStationController.php';
define('PIOUPIOU_API_URL', 'http://api.pioupiou.fr/v1/live-with-meta/all');

?>
<!DOCTYPE html>
<html>
<body>

<?php
use models\WindStation;
use helpers\PioupiouHelper;
use controllers\WindStationController;


echo 'Test Windstation Class</br>';
echo 'Création d\'une ligne donnée pioupiou</br>';
$windstationData = ['station_id' => 1, 'provider' => 'pioupiou', 'latitude' =>43.166672, 'longitude' => 5.6, 'measurement_date' => '2026-05-28 12:00:00', 'wind_heading' => 120, 'wind_speed_avg' => 5.2, 'wind_speed_max' => 11.554, 'wind_speed_min' => 1.1 ];
echo 'Retour Upsert : '.WindStation::upsert($windstationData).'<br>';
echo 'Création d\'une ligne donnée holfy</br>';
$windstationData = ['station_id' => 1, 'provider' => 'holfly', 'latitude' =>43.166672, 'longitude' => 5.8,  'widget_code' => '<p>Widget</p>'];
echo 'Retour Upsert : '.WindStation::upsert($windstationData).'<br>';
echo 'Test GetAll : </br>';
var_dump(WindStation::getAll());
echo '<br>';
echo 'Test Get : </br>';
var_dump(WindStation::get(1,'pioupiou'));
echo '<br>';
echo 'Nettoyage : </br>';
echo 'Retour Truncate : '.WindStation::truncateData().'<br>';

echo 'Fin test Windstation Class</br>';
echo '<br>';
echo '<br>';

echo 'Test PioupiouHelper Class</br>';
$pioupiouHelper = new PioupiouHelper(PIOUPIOU_API_URL);
echo 'fetchAllStations : </br>';
$res = $pioupiouHelper->fetchAllStations();
if (!$res) {
    echo 'fetchAllStations failed </br>';
}
else
{
    $data = $pioupiouHelper->formatStationData($res);
    echo 'Données traduites :</br>';
    var_dump($data);
}


echo 'Test WindstationCoontroller Class</br>';
$windstationController = new WindStationController();



?>

</body>
</html>