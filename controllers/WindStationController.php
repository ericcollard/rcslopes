<?php
// ============================================================
// controllers/WindStationController.php
// ============================================================

namespace controllers;

require_once __DIR__ . '/../models/WindStation.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/CacheHelper.php';
require_once __DIR__ . '/../helpers/PioupiouHelper.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

use Exception;
use models\WindStation;
use helpers\CacheHelper;
use helpers\PioupiouHelper;
use function jsonResponse;

class WindStationController
{
    private $cacheHelper;
    public $pioupiouHelper;

    public function __construct() {
        $this->cacheHelper = new CacheHelper();
        $this->pioupiouHelper = new PioupiouHelper(PIOUPIOU_API_URL);
    }


    // ── GET /api/wind-stations ───────────────────────────────

    public function index(): void
    {
        try {
            // Check if cache should be refreshed
            if ($this->cacheHelper->shouldRefreshCache(CACHE_DURATION)) {
                // Fetch data from Pioupiou API
                $apiData = $this->pioupiouHelper->fetchAllStations();

                if ($apiData) {
                    // Format and update database
                    $stations = $this->pioupiouHelper->formatStationData($apiData);

                    foreach ($stations as $stationData) {
                        WindStation::upsert($stationData);
                    }

                    // Update cache time
                    $this->cacheHelper->updateCacheTime();
                }
            }

            // Read from database
            $stations = WindStation::getAll();


            jsonResponse([
                'success' => true,
                'data' => $stations,
                "cache_time" => $this->cacheHelper->getLastCacheTime()
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }

    // ── GET /api/wind-stations/{station_id} ──────────────────

    public function show(int $station_id): void
    {
        $station = WindStation::getById($station_id);

        if (!$station) {
            jsonResponse([
                'success' => false,
                'error' => 'Station introuvable.'
            ], 404);
        }

        jsonResponse([
            'success' => true,
            'data' => $station
        ]);
    }

    // ── POST /api/wind-stations ──────────────────────────────

    public function store(): void
    {
        $body = $this->parseBody();

        $errors = $this->validateRequired($body);

        if (!empty($errors)) {
            jsonResponse([
                'success' => false,
                'errors' => $errors
            ], 422);
        }

        $data = $this->sanitize($body);

        $station = WindStation::create($data);

        jsonResponse([
            'success' => true,
            'data' => $station
        ], 201);
    }

    // ── PUT /api/wind-stations/{station_id} ──────────────────

    public function update(int $station_id): void
    {
        if (!WindStation::getById($station_id)) {
            jsonResponse([
                'success' => false,
                'error' => 'Station introuvable.'
            ], 404);
        }

        $body = $this->parseBody();

        $errors = $this->validatePartial($body);

        if (!empty($errors)) {
            jsonResponse([
                'success' => false,
                'errors' => $errors
            ], 422);
        }

        $data = $this->sanitize($body, partial: true);

        $station = WindStation::update($station_id, $data);

        jsonResponse([
            'success' => true,
            'data' => $station
        ]);
    }

    // ── Validation ───────────────────────────────────────────

    private function validateRequired(array $body): array
    {
        $errors = [];

        if (!isset($body['latitude']) || !is_numeric($body['latitude'])) {
            $errors[] = 'Le champ "latitude" doit être un nombre.';
        }

        if (!isset($body['longitude']) || !is_numeric($body['longitude'])) {
            $errors[] = 'Le champ "longitude" doit être un nombre.';
        }

        if (empty($body['measurement_date'])) {
            $errors[] = 'Le champ "measurement_date" est obligatoire.';
        }

        if (!isset($body['wind_heading']) || !is_numeric($body['wind_heading'])) {
            $errors[] = 'Le champ "wind_heading" doit être un nombre.';
        }

        if (!isset($body['wind_speed_avg']) || !is_numeric($body['wind_speed_avg'])) {
            $errors[] = 'Le champ "wind_speed_avg" doit être un nombre.';
        }

        if (!isset($body['wind_speed_max']) || !is_numeric($body['wind_speed_max'])) {
            $errors[] = 'Le champ "wind_speed_max" doit être un nombre.';
        }

        if (!isset($body['wind_speed_min']) || !is_numeric($body['wind_speed_min'])) {
            $errors[] = 'Le champ "wind_speed_min" doit être un nombre.';
        }

        return $errors;
    }

    private function validatePartial(array $body): array
    {
        $errors = [];

        if (isset($body['latitude']) && !is_numeric($body['latitude'])) {
            $errors[] = 'Le champ "latitude" doit être un nombre.';
        }

        if (isset($body['longitude']) && !is_numeric($body['longitude'])) {
            $errors[] = 'Le champ "longitude" doit être un nombre.';
        }

        if (isset($body['wind_heading']) && !is_numeric($body['wind_heading'])) {
            $errors[] = 'Le champ "wind_heading" doit être un nombre.';
        }

        if (isset($body['wind_speed_avg']) && !is_numeric($body['wind_speed_avg'])) {
            $errors[] = 'Le champ "wind_speed_avg" doit être un nombre.';
        }

        if (isset($body['wind_speed_max']) && !is_numeric($body['wind_speed_max'])) {
            $errors[] = 'Le champ "wind_speed_max" doit être un nombre.';
        }

        if (isset($body['wind_speed_min']) && !is_numeric($body['wind_speed_min'])) {
            $errors[] = 'Le champ "wind_speed_min" doit être un nombre.';
        }

        return $errors;
    }

    // ── Helpers ──────────────────────────────────────────────

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    private function sanitize(array $body, bool $partial = false): array
    {
        $data = [];

        if (isset($body['latitude'])) {
            $data['latitude'] = (float)$body['latitude'];
        }

        if (isset($body['longitude'])) {
            $data['longitude'] = (float)$body['longitude'];
        }

        if (isset($body['measurement_date'])) {
            $data['measurement_date'] = trim($body['measurement_date']);
        }

        if (isset($body['wind_heading'])) {
            $data['wind_heading'] = (float)$body['wind_heading'];
        }

        if (isset($body['wind_speed_avg'])) {
            $data['wind_speed_avg'] = (float)$body['wind_speed_avg'];
        }

        if (isset($body['wind_speed_max'])) {
            $data['wind_speed_max'] = (float)$body['wind_speed_max'];
        }

        if (isset($body['wind_speed_min'])) {
            $data['wind_speed_min'] = (float)$body['wind_speed_min'];
        }

        return $data;
    }
}