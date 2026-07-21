<?php
error_reporting(E_ALL);
require_once __DIR__ . '/helpers/OpenMeteoHelper.php';
require_once __DIR__ . '/controllers/WeatherForecastController.php';
require_once __DIR__ . '/controllers/SlopeController.php';
require_once __DIR__ . '/models/Slope.php';

use controllers\SlopeController;
use controllers\WeatherForecastController;
use helpers\OpenMeteoHelper;
use models\Slope;
use models\WeatherForecast;


/*
require_once 'helpers/mailer.php';
$mail = getMailer();


$mail->setFrom('admin@finesseplus.org', 'FinessPlus');
$mail->addAddress('destinataire@gmail.com', 'Moi');
$mail->Subject = 'Test envoi mail 7';
// Définir le HTML
$mail->isHTML(TRUE);
$mail->Body = '<html>Bonjour, nous sommes heureux de <br>confirmer votre réservation.</br> Veuillez vérifier le document en pièce jointe.</html>';
$mail->AltBody = 'Bonjour, nous sommes heureux de confirmer votre réservation. Veuillez vérifier le document en pièce jointe.';

// envoyer le message
if (!$mail->send()) {
    echo 'Le message n\'a pas pu être envoyé . ';
    echo 'Erreur du Mailer : ' . $mail->ErrorInfo;
} else {
    echo 'Le message a été envoyé';
}
*/





$params=[
    'newslope_lat'=> 45.2,
    'newslope_lng'=> 12.2,
    'newslope_type'=> 'pente',
    'newslope_name' => 'test avec mail',
    'newslope_orient' => ' N  NNE ',
    'newslope_email' => 'eric.collard@free.fr'
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


//$slopes = Slope::getUnderReview();
//var_dump($slopes);
//$slopeController = new SlopeController();
//var_dump($slopeController->showHtml(3));
//$slopesData = $slopeController->get(10,700, 1);
//var_dump($slopesData);
//$data = $meteoHelper->fetchForSlopes($slopesData);
//var_dump($data);

//$slopeWeatherData = WeatherForecast::getBySlopeId(3);
//var_dump($slopeWeatherData);

//var_dump($slopeController->get());

//$controller = new WeatherForecastController();
//$slopeWeatherData =  $controller->getBySlopeId(3,1);
//var_dump($slopeWeatherData);
//$weatherStr = $slopeController->getWeatherforecastHtml($slopeWeatherData);
//var_dump($weatherStr);
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


