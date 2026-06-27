<?php
namespace helpers;

class OpenMeteoHelper {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    public function fetchForSlopes($slopesData) {

        /*
         *
         *
         $slopesData = [
            ['slope_id' => 1, 'latitude' => 52.52, 'longitude' =>13.41 ],
            ['slope_id' => 2, 'latitude' => 50.12, 'longitude' =>8.68 ],
            ['slope_id' => 3, 'latitude' => 53.55, 'longitude' =>9.99 ]
        ];
         *
         *
         */

        $slopesWeatherData= [];

        $url = $this->apiUrl;

        $latSetStr = '';
        $longSetStr = '';
        foreach ($slopesData as $key => $slopeData)
        {
            if ($key === array_key_first($slopesData)) {
                // FIRST ELEMENT!
                $latSetStr.= number_format($slopeData['latitude'], 2, '.', '');
                $longSetStr.= number_format($slopeData['longitude'], 2, '.', '');
            }
            else
            {
                $latSetStr.= ','.number_format($slopeData['latitude'], 2, '.', '');
                $longSetStr.= ','.number_format($slopeData['longitude'], 2, '.', '');
            }
        }

        $url = $url . 'latitude=' . $latSetStr . '&longitude=' . $longSetStr;

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
            foreach ($apiSlopesWeatherData as $key => $apiSlopeWeatherData) {
                $slopeWeatherData = $apiSlopeWeatherData;
                $slopeWeatherData['slope_id'] = $slopesData[$key]['slope_id'];
                $slopesWeatherData[] = $slopeWeatherData;
            }
            return $slopesWeatherData;
        }
        else
        {
            echo ('$httpCode : '.$httpCode.'</br>');
            echo ('$response : '.$response.'</br>');
        }

        return null;
    }

    public function formatSlopesData($slopesWeatherData) {
        $slopesData = [];

        if (isset($slopesWeatherData) && is_array($slopesWeatherData)) {
            foreach ($slopesWeatherData as $slopeWeatherData) {

                $slopeData = [
                    'slope_id' => $slopeWeatherData['slope_id'],
                    'latitude' => $slopeWeatherData['latitude'] ?? null,
                    'longitude' => $slopeWeatherData['longitude'] ?? null,
                ];

                $weatherDataSet = [];
                foreach ($slopeWeatherData['hourly']['time'] as $key => $timeStr) {
                    $wind_heading = $slopeWeatherData['hourly']['wind_direction_10m'][$key];
                    $wind_speed = $slopeWeatherData['hourly']['wind_speed_10m'][$key];
                    $wind_gust = $slopeWeatherData['hourly']['wind_gusts_10m'][$key];
                    $cloud_cover = $slopeWeatherData['hourly']['cloud_cover'][$key];
                    $rain = $slopeWeatherData['hourly']['rain'][$key];
                    $temperature = $slopeWeatherData['hourly']['temperature_2m'][$key];
                    if ($timeStr) {
                        $forecast_timestamp = strtotime($timeStr);
                        $hour = intval(date ("H",$forecast_timestamp));
                        if ($hour > 9 and $hour < 21) {
                            $weatherDataSet[] = [
                                'wind_heading' => $wind_heading,
                                'wind_speed' => $wind_speed,
                                'wind_gust' => $wind_gust,
                                'cloud_cover' => $cloud_cover,
                                'rain' => $rain,
                                'temperature' => $temperature,
                                'forecast_time' => date('Y-m-d H:i:s', strtotime($timeStr))
                            ];
                        }
                    }
                }

                $slopeData['weatherDataSet'] = $weatherDataSet;
                $slopesData[] = $slopeData;
            }
        }

        return $slopesData;
    }
}
?>