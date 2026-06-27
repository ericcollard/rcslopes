<?php
// ============================================================
// controllers/SlopeController.php  –  Contrôleur Pentes
// ============================================================

namespace controllers;
use models\Slope;
use models\WeatherForecast;
use function jsonResponse;
use function sanitizeWindDirections;

require_once __DIR__ . '/../models/Slope.php';
require_once __DIR__ . '/../models/WeatherForecast.php';
require_once __DIR__ . '/../helpers/response.php';

class SlopeController
{
    // ── GET /api/slopes ───────────────────────────────────────

    public function index(): void
    {
        $slopes = Slope::getAll();
        jsonResponse(['success' => true, 'data' => $slopes]);
    }

    // ── pour WeatherForecastController───────────────────────────────────────

    public function get($limit = -1, $offset = -1,$slope = 0): array
    {
        $slopes = Slope::getAll($limit, $offset,$slope);
        $data = [];
        foreach ($slopes as $key => $slope) {
            if ($key < $limit) {
                $data[] = [
                    'slope_id' => $slope['slopeId'],
                    'latitude' => $slope['lat'],
                    'longitude' => $slope['lng'],
                ];
            }

        }
        return $data;
    }

    public function getCount($slope = 0):int {
        return Slope::getCount($slope);
    }


    // ── GET /api/slopes/{slopeId} ──────────────────────────────────

    public function show(int $slopeId): void
    {
        $slope = Slope::getById($slopeId);
        if (!$slope) {
            jsonResponse(['success' => false, 'error' => 'Site introuvable.'], 404);
        }
        jsonResponse(['success' => true, 'data' => $slope]);
    }

    public function generateWindDirectionSVG(float $heading)
    {

        $svg = '<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">' .
            '<circle r="2" cx="10" cy="10" fill="black" />' .
            '<polygon points="13,10 7,10 10,0" style="fill:black" transform="rotate(' . ($heading + 180) . ',10,10)"/>' .
            '</svg>';
        return $svg;
    }

    public function showHtml(int $slopeId): void
    {
        $slope = Slope::getById($slopeId);
        if (!$slope) {
            jsonResponse(['success' => false, 'error' => 'Site introuvable.'], 404);
        }
        $orientations = join(", ",$slope['orient']);
        $data['title'] = $slope['name'];



        $data['html'] = "<div class='container-fluid'>";
        $data['html'] .= "<div class='row'>";
        $data['html'] .= "<div class='col-lg'>";
        $data['html'] .= "<p class='slope-coordinates'>Latitude : {$slope['lat']} / Longitude : {$slope['lng']}</p>";
        $data['html'] .= "<p class='slope-orient'>Orientations exploitables : {$orientations}</p>";
        if ($slope['aip'])
            $data['html'] .= "<div class='alert alert-success' role='alert'>Numéro AIP : {$slope['aip']}</div>";
        else
            $data['html'] .= "<div class='alert alert-danger' role='alert'>Cette pente ne possède pas d'AIP</div>";
        if ($slope['url'])
            $data['html'] .= "<p class='slope-gestion'>URL du club gestionnaire : {$slope['url']}</p>";
        $data['html'] .= "<h2>Description et accès</h2>";
        $data['html'] .= "<p class='slope-description'>{$slope['description']}</p>";
        $data['html'] .= "</div>"; // du row
        $data['html'] .= "<div class='col-lg'>";
        $slopeWeatherData = WeatherForecast::getBySlopeId($slopeId);
        if ($slopeWeatherData) {
            $weatherStr = "<table class='table table-success table-striped-columns slope-forecast'><tr><td>Date</td><td>Orientation</td><td>Vitesse</td><td>Rafales</td><td>Nuages</td><td>Pluie</td><td>Température</td></tr>";
            foreach ($slopeWeatherData as $key => $slopeWeatherDataItem) {
                $date = new \DateTime($slopeWeatherDataItem['forecast_time']);
                $dateStr = date_format($date,'d m Y H:i');
                $orient = $this->generateWindDirectionSVG($slopeWeatherDataItem['wind_heading']) . " (".$slopeWeatherDataItem['wind_heading'].")";
                $speed = number_format($slopeWeatherDataItem['wind_speed'], 0, '.', ''). " km/h";
                $gust = number_format($slopeWeatherDataItem['wind_gust'], 0, '.', ''). " km/h";
                $cloud = number_format($slopeWeatherDataItem['cloud_cover'], 0, '.', ''). " %";
                $temperature = number_format($slopeWeatherDataItem['temperature'], 0, '.', ''). " °C";
                $rain = number_format($slopeWeatherDataItem['rain'], 0, '.', ''). " mm";
                $weatherStr .= "<tr><td>{$dateStr}</td><td>{$orient}</td><td>{$speed}</td><td>{$gust}</td><td>{$cloud}</td><td>{$rain}</td><td>{$temperature}</td></tr>";
            }
            $weatherStr .= "</table>";
            $data['html'] .= "<h2>Prévisions météo à 3 jours</h2>";
            $data['html'] .= $weatherStr;
        }
        $data['html'] .= "</div>";  // du col
        $data['html'] .= "</div>";  // du row
        $data['html'] .= "</div>";  // du container
        $data['html'] .= "<hr><h2>Données techniques</h2><p>Identifiant du site : " . $slopeId . "</p>";

        jsonResponse(['success' => true, 'data' => $data]);




    }

    public function search($search_str) {
        $slopes = Slope::getByPartialName($search_str);
        jsonResponse(['success' => true, 'data' => $slopes]);
    }


    // ── POST /api/slopes ──────────────────────────────────────

    public function store(): void
    {
        $body = $this->parseBody();
        $errors = $this->validateRequired($body);

        if (!empty($errors)) {
            jsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        $data = $this->sanitize($body);
        $slope = Slope::create($data);
        jsonResponse(['success' => true, 'data' => $slope], 201);
    }

    // ── PUT /api/slopes/{slopeId} ──────────────────────────────────

    public function update(int $slopeId): void
    {
        if (!Slope::getById($slopeId)) {
            jsonResponse(['success' => false, 'error' => 'Site introuvable.'], 404);
        }

        $body = $this->parseBody();
        $errors = $this->validatePartial($body);

        if (!empty($errors)) {
            jsonResponse(['success' => false, 'errors' => $errors], 422);
        }

        $data = $this->sanitize($body, partial: true);
        $slope = Slope::update($slopeId, $data);
        jsonResponse(['success' => true, 'data' => $slope]);
    }

    // ── Validation ────────────────────────────────────────────

    private function validateRequired(array $body): array
    {
        $errors = [];

        if (empty($body['name'])) {
            $errors[] = 'Le champ "name" est obligatoire.';
        }
        if (empty($body['type'])) {
            $errors[] = 'Le champ "type" est obligatoire.';
        }
        if (!isset($body['latitude']) || !is_numeric($body['lat'])) {
            $errors[] = 'Le champ "lat" doit être un nombre.';
        }
        if (!isset($body['lng']) || !is_numeric($body['lng'])) {
            $errors[] = 'Le champ "lng" doit être un nombre.';
        }
        if (empty($body['orient']) || !is_array($body['orient'])) {
            $errors[] = 'Le champ "orient" doit être un tableau non vide.';
        } else {
            if (sanitizeWindDirections($body['orient']) === null) {
                $errors[] = 'Orientations invalides. Valeurs acceptées : ' . implode(', ', validWindDirections());
            }
        }

        return $errors;
    }

    private function validatePartial(array $body): array
    {
        $errors = [];

        if (isset($body['lat']) && !is_numeric($body['lat'])) {
            $errors[] = 'Le champ "lat" doit être un nombre.';
        }
        if (isset($body['lng']) && !is_numeric($body['lng'])) {
            $errors[] = 'Le champ "lng" doit être un nombre.';
        }
        if (isset($body['orient'])) {
            if (!is_array($body['orient'])) {
                $errors[] = '"orient" doit être un tableau.';
            } elseif (sanitizeWindDirections($body['orient']) === null) {
                $errors[] = 'Orientations invalides. Valeurs acceptées : ' . implode(', ', validWindDirections());
            }
        }

        return $errors;
    }

    // ── Helpers ───────────────────────────────────────────────

    private function parseBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function sanitize(array $body, bool $partial = false): array
    {
        $data = [];

        if (isset($body['name'])) {
            $data['name'] = trim(strip_tags($body['name']));
        }
        if (isset($body['lat'])) {
            $data['lat'] = (float)$body['lat'];
        }
        if (isset($body['lng'])) {
            $data['lng'] = (float)$body['lng'];
        }
        if (isset($body['orient'])) {
            $data['orient'] = sanitizeWindDirections($body['orient']);
        }
        if (array_key_exists('description', $body)) {
            // On autorise le HTML – filtrer selon tes besoins de sécurité
            $data['description'] = $body['description'] !== null
                ? trim($body['description'])
                : null;
        }
        if (array_key_exists('weather_url', $body)) {
            $url = filter_var($body['weather_url'], FILTER_VALIDATE_URL);
            $data['weather_url'] = $url !== false ? $url : null;
        }

        return $data;
    }
}
