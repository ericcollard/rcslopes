<?php
error_reporting(E_ALL);
require_once __DIR__ . '/helpers/OpenMeteoHelper.php';
require_once __DIR__ . '/controllers/WeatherForecastController.php';
require_once __DIR__ . '/controllers/SlopeController.php';
require_once __DIR__ . '/controllers/SlopeUpdateController.php';
require_once __DIR__ . '/models/Slope.php';
require_once __DIR__ . '/models/SlopeUpdate.php';
require_once __DIR__ . '/models/SlopePicture.php';
require_once __DIR__ . '/models/Statistic.php';
use controllers\SlopeController;
use controllers\WeatherForecastController;
use controllers\SlopeUpdateController;
use helpers\OpenMeteoHelper;
use models\Slope;
use models\SlopeUpdate;
use models\SlopePicture;
use models\WeatherForecast;
use models\Statistic;


$slopeController = new SlopeController();
var_dump($slopeController->showHtml(59));


/*


$params=[
    'newslope_lat'=> 45.2,
    'newslope_lng'=> 12.2,
    'newslope_type'=> 'pente',
    'newslope_name' => 'test avec mail',
    'newslope_orient' => ' N  NNE ',
    'newslope_email' => 'eric.collard@free.fr',
    'newslope_country'=>'france',
    'newslope_dept'=> '05',
    'newslope_slopeAIP'=> '1547',
    'newslope_slopeClub'=> 'on',
    'newslope_slopeCotisation'=> 0,
    'newslope_slopeLicence'=> 'on',
    'newslope_slopeURL'=> 'http://www.lapente.com',
    'newslope_clubName'=> 'Club de la pente',
    'newslope_slopeSize'=> 'Grand pente',
    'newslope_slopeCompatibility'=> 'Moyens planeurs',
    'newslope_slopeSurface'=> 'Herbe',
    'newslope_slopeGap'=> 'Accès au trou impossible',
    'newslope_slopePark'=> 'A plus de 60mn',
    'newslope_slopeAccess'=> 'chemin 4x4',
    'newslope_paragliding'=> 'on',
    'newslope_moulinette'=> 'Confortable',
    'newslope_season'=> 'Des inteerdcations',
    'newslope_plafond' => 150,
    'newslope_access' => "<p><strong>Commentaires</strong></p><ul><li>Par situation de NW bien établie (rafales à 35 km/h à Chabottes et au cuchon d'Ancelle) la pente est extrêmement ben alimentée (trop...). Il m'est arrivé de renoncer avec un 3 m ou de casser un peu sur un cheval de bois (10 minutes de vol, 20 minutes pour poser...). Un moustachu de F3B a fait de même hier. L'atterrissage est en effet délicat. Il semblerait qu'il y ait un site un peu plus bas, sur le chemin à la cote 1400, qui soit plus soft (09/08/2025)</li><li>Pratique strictement interdite du 1/06 au 15/11</li><li>L'interdiction à compter du 1er juin pour la pratique du parapente mentionnée par FFVL ne concerne apparemment pas la pratique modéliste. Par contre, l'interdiction de vol du sommet est confirmée.</li></ul>",
    'newslope_flight' => 'Détail des conditions de vol',
    'newslope_landing' => 'Détail du posé',
    'newslope_slopeInfo'=> 'Superbe dynamique',
    'newslope_slopeInfoEn'=> 'High pleazsure',
    'newslope_form_rendered_at' => '2026-08-05',
    'edit_slopeId' => 59,
];

$defaults = array(
    CURLOPT_URL => 'https://rcslopes.test/newslope',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($params),
);
$ch = curl_init();
curl_setopt_array($ch, $defaults);

curl_exec($ch);
if(curl_error($ch)) {
    echo curl_error($ch);
}
*/



