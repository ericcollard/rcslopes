<?php
namespace helpers;

class PioupiouHelper {
    private $apiUrl;

    public function __construct($apiUrl) {
        $this->apiUrl = $apiUrl;
    }

    public function fetchAllStations() {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode == 200 && $response) {
            $data = json_decode($response, true);
            return $data;
        }

        return null;
    }

    public function formatStationData($apiData) {
        $stations = [];

        if (isset($apiData['data']) && is_array($apiData['data'])) {
            foreach ($apiData['data'] as $station) {
                if ($station['status']['state']!='on' || !isset($station['measurements'] ) || $station['location']['success']!='true') {
                    continue; // Skip stations that are sniffing or have no measurements
                }

                $measurements = $station['measurements'];
                $meta = $station['location'] ?? [];

                $stationData = [
                    'station_id' => $station['id'],
                    'provider' => 'pioupiou',
                    'latitude' => $meta['latitude'] ?? null,
                    'longitude' => $meta['longitude'] ?? null,
                    'measurement_date' => isset($measurements['date']) ? date('Y-m-d H:i:s', strtotime($measurements['date'])) : null,
                    'wind_heading' => $measurements['wind_heading'] ?? null,
                    'wind_speed_avg' => $measurements['wind_speed_avg'] ?? null,
                    'wind_speed_max' => $measurements['wind_speed_max'] ?? null,
                    'wind_speed_min' => $measurements['wind_speed_min'] ?? null
                ];

                $stations[] = $stationData;
            }
        }

        return $stations;
    }
}
?>