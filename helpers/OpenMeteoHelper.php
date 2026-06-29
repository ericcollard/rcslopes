<?php
namespace helpers;

class OpenMeteoHelper {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }



    public function fetchForSlopes($slopes) {
        // --- $slopes est un array de slope

        $numberOfSlopes = count($slopes);

        $slopesWeatherData= [];
        $url = $this->apiUrl;

        $latSetStr = '';
        $longSetStr = '';
        // --- construction de l'URL de request
        $firstReg = true;
        foreach ($slopes as $slope)
        {
            if (!$firstReg)
            {
                $latSetStr.= ',';
                $longSetStr.= ',';
            }
            $latSetStr.= number_format($slope['lat'], 2, '.', '');
            $longSetStr.= number_format($slope['lng'], 2, '.', '');
            $firstReg = false;
        }

        $url = $url . 'latitude=' . $latSetStr . '&longitude=' . $longSetStr;

        // --- requetage des données brutes
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode == 200 && $response) {
            $apiSlopesWeatherData = json_decode($response, true);
            if ($numberOfSlopes > 1)
            {
                foreach ($apiSlopesWeatherData as $key => $apiSlopeWeatherData) {
                    $apiSlopeWeatherData['slopeId'] = $slopes[$key]['slopeId']; // o suppose que les résultats sont dans le même ordre que la requete
                    $slopesWeatherData[] = $apiSlopeWeatherData;
                }
            }
            else
            {
                $apiSlopesWeatherData['slopeId'] = $slopes[0]['slopeId'];
                $slopesWeatherData[] = $apiSlopesWeatherData;
            }

            return $this->formatSlopesData($slopesWeatherData);
        }
        else
        {
            echo ('$httpCode : '.$httpCode.'</br>');
            echo ('$response : '.$response.'</br>');
        }

        return null;
    }



    /*
     *   FORMATTAGE DES DONNEES METEO
     *   On en profite par filtrer pour ne garder que 1 heure sur 2, et en journée uniquement
     */
    public function formatSlopesData($slopesRawWeatherData) {
        $slopesWeatherData = [];

        if (isset($slopesRawWeatherData) && is_array($slopesRawWeatherData)) {
            foreach ($slopesRawWeatherData as $slopeRawWeatherData) {
                $slopeWeatherData = [
                    'slopeId' => $slopeRawWeatherData['slopeId'],
                    'lat' => $slopeRawWeatherData['latitude'] ?? null,
                    'lng' => $slopeRawWeatherData['longitude'] ?? null,
                ];

                $weatherDataSet = [];
                foreach ($slopeRawWeatherData['hourly']['time'] as $key => $timeStr) {
                    $wind_heading = $slopeRawWeatherData['hourly']['wind_direction_10m'][$key];
                    $wind_speed = $slopeRawWeatherData['hourly']['wind_speed_10m'][$key];
                    $wind_gust = $slopeRawWeatherData['hourly']['wind_gusts_10m'][$key];
                    $cloud_cover = $slopeRawWeatherData['hourly']['cloud_cover'][$key];
                    $rain = $slopeRawWeatherData['hourly']['rain'][$key];
                    $temperature = $slopeRawWeatherData['hourly']['temperature_2m'][$key];
                    if ($timeStr) {
                        $forecast_timestamp = strtotime($timeStr);
                        $hour = intval(date ("H",$forecast_timestamp));
                        if ($hour > 8 and $hour < 22 and $hour&1) {
                            $weatherDataSet[] = [
                                'forecast_time' => date('Y-m-d H:i:s', strtotime($timeStr)),
                                'wind_heading' => $wind_heading,
                                'wind_speed' => $wind_speed,
                                'wind_gust' => $wind_gust,
                                'cloud_cover' => $cloud_cover,
                                'rain' => $rain,
                                'temperature' => $temperature
                            ];
                        }
                    }
                }
                $slopeWeatherData['weatherDataSet'] = $weatherDataSet;
                $slopesWeatherData[] = $slopeWeatherData;
            }
        }

        return $slopesWeatherData;
    }
}