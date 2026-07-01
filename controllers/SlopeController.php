<?php
// ============================================================
// controllers/SlopeController.php  –  Contrôleur Pentes
// ============================================================

namespace controllers;
use IntlDateFormatter;
use controllers\WeatherForecastController;
use models\Slope;
use models\WeatherForecast;
use function jsonResponse;
use function sanitizeWindDirections;


require_once __DIR__ . '/../models/Slope.php';
require_once __DIR__ . '/../models/WeatherForecast.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../controllers/WeatherForecastController.php';

class SlopeController
{
    // ── GET /api/slopes ───────────────────────────────────────

    public function index(): void
    {
        $slopes = Slope::getAll();
        jsonResponse(['success' => true, 'data' => $slopes]);
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

        $svg = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">' .
        '<polygon fill="white" stroke="black" stroke-width="0.25" points="18.87,46.68 31.13,46.68 36.14,3.32 13.86,3.32"' . ' transform="rotate(' . ($heading) . ',25,25)"'. '/>' .
        '<polygon fill="red" stroke="black" stroke-width="0.25"  points="36.14,3.32 35.14,11.99 14.86,11.99 13.86,3.32"' . ' transform="rotate(' . ($heading) . ',25,25)"'. '/>' .
        '<polygon fill="red" stroke="black" stroke-width="0.25"   points="15.86,20.66 34.14,20.66 33.14,29.34 16.86,29.34"' . ' transform="rotate(' . ($heading) . ',25,25)"'. '/>' .
        '<polygon fill="red" stroke="black" stroke-width="0.25"   points="32.14,38.01 17.86,38.01 18.87,46.68 31.13,46.68"' . ' transform="rotate(' . ($heading) . ',25,25)"'. '/>' .
        '</svg>';

        return $svg;
        /*
        $svg = '<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">' .
            '<circle r="2" cx="10" cy="10" fill="black" />' .
            '<polygon points="13,10 7,10 10,0" style="fill:black" transform="rotate(' . ($heading + 180) . ',10,10)"/>' .
            '</svg>';
        return $svg;*/
    }

    public function getCouldCoverImg(float $cloudCover, float $rainQty)
    {
        $imgFileName = "cloud-0";
        if ($cloudCover > 15.0) $imgFileName = "cloud-1";
        if ($cloudCover > 30.0) $imgFileName = "cloud-2";
        if ($cloudCover > 60.0) $imgFileName = "cloud-3";
        if ($cloudCover > 80.0) $imgFileName = "cloud-4";
        if ($rainQty > 0.0) $imgFileName = "cloud-5";
        return "<img src='/assets/".$imgFileName.".png'>";
    }

    public function getDayName($date) {
        $jours= array("", "Lundi", "Mardi",
            "Mercredi", "Jeudi", "Vendredi",
            "Samedi","Dimanche");
        $today = new \DateTime();
        $todaySinceStart = intdiv($today->getTimestamp(), 60*60*24);
        $daySinceStart = intdiv($date->getTimestamp(), 60*60*24);
        if ($daySinceStart == $todaySinceStart) return "Aujourd'hui";
        if ($daySinceStart == ($todaySinceStart + 1)) return "Demain";
        if ($daySinceStart == ($todaySinceStart - 1)) return "Hier";
        return $jours[date_format($date,'N')];
    }

    public function getDayCellHtml($date) {
        $html = '<td rowspan="7" class="day-cell align-top">';
        $html .= '<div class="contend"><span class="dayOfWeek">'.$this->getDayName($date).'</span></br>';
        $html .= '<span class="date">'.date_format($date,'j/m/Y').'</span></div>';
        $html .= '</td>';
        return $html;
    }

    public function getWeatherforecastHtml($slopesWeatherData) {


        /*
         *  $slopeWeatherData
         *
         *
         */

        $slopeWeatherData = $slopesWeatherData[0]['weatherDataSet'];

        $separation = "<tr class='separation'><td colspan='8'></td></tr>";

        $html = "<div class='weather-forecast-wrapper'>";
        $html .= "<table class='weather-forecast-table'>";
        $html .= "<tr>";
        $html .= "<td></td><td></td><td></td><td>Vitesse</td><td>Rafales</td><td>Nuages</td><td>Pluie</td><td>Température</td>";
        $html .= "</tr>";

        $daySinceStart = 0;
        foreach ($slopeWeatherData as $key => $slopeWeatherDataItem) {

            $date = new \DateTime($slopeWeatherDataItem['forecast_time']);
            $currentDaySinceStart = intdiv($date->getTimestamp(), 60*60*24);
            $dayHtml = "";
            if ($currentDaySinceStart != $daySinceStart)
            {
                // nouvelle prévision pour un nouveau jour
                $dayHtml = $this->getDayCellHtml($date);
                $html .=$separation;
                $daySinceStart = $currentDaySinceStart;
            }
            $html .= "<tr>";
            $html .= $dayHtml;
            // nouvelle prévision pour le même jour
            $orient = $this->generateWindDirectionSVG($slopeWeatherDataItem['wind_heading']);
            $speed = number_format($slopeWeatherDataItem['wind_speed'], 0, '.', ''). "<span class='unit'> km/h</span>";
            $gust = number_format($slopeWeatherDataItem['wind_gust'], 0, '.', ''). "<span class='unit'> km/h</span>";
            $cloud = $this->getCouldCoverImg($slopeWeatherDataItem['cloud_cover'],$slopeWeatherDataItem['rain']);
            $temperature = number_format($slopeWeatherDataItem['temperature'], 0, '.', ''). "<span class='unit'> °C</span>";
            $rain = number_format($slopeWeatherDataItem['rain'], 0, '.', ''). "<span class='unit'> mm</span>";
            $timeStr = date_format($date,'H:i');
            $html .= "<td>{$timeStr}</td><td>{$orient}</td><td>{$speed}</td><td>{$gust}</td><td>{$cloud}</td><td>{$rain}</td><td>{$temperature}</td>";

            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "</div>";
        return $html;

        /*
         *
         *      $weatherStr = "<table class='table table-success table-striped-columns slope-forecast'><tr><td>Date</td><td>Orientation</td><td>Vitesse</td><td>Rafales</td><td>Nuages</td><td>Pluie</td><td>Température</td></tr>";

                foreach ($slopeWeatherData as $key => $slopeWeatherDataItem) {
                    $date = new \DateTime($slopeWeatherDataItem['forecast_time']);
                    $dateStr = date_format($date,'d m Y H:i');
                    $orient = $this->generateWindDirectionSVG($slopeWeatherDataItem['wind_heading']) . " (".$slopeWeatherDataItem['wind_heading'].")";
                    $speed = number_format($slopeWeatherDataItem['wind_speed'], 0, '.', ''). " km/h";
                    $gust = number_format($slopeWeatherDataItem['wind_gust'], 0, '.', ''). " km/h";
                    $cloud = $this->getCouldCoverImg($slopeWeatherDataItem['cloud_cover'],$slopeWeatherDataItem['rain']);
                    $temperature = number_format($slopeWeatherDataItem['temperature'], 0, '.', ''). " °C";
                    $rain = number_format($slopeWeatherDataItem['rain'], 0, '.', ''). " mm";
                    $weatherStr .= "<tr><td>{$dateStr}</td><td>{$orient}</td><td>{$speed}</td><td>{$gust}</td><td>{$cloud}</td><td>{$rain}</td><td>{$temperature}</td></tr>";
                }

                $weatherStr .= "</table>";
         *
         */

    }

    public function showHtml(int $slopeId): void
    {

        $slope = Slope::getById($slopeId);
        if (!$slope) {
            jsonResponse(['success' => false, 'error' => 'Site introuvable.'], 404);
        }
        $data['title'] = $slope['name'];

        if ($slope['type'] == 'pente')
        {
            $orientations = join(", ",$slope['orient']);

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
            $data['html'] .= "<p class='slope-description'>{$slope['desc_fr']}</p>";
            $data['html'] .= "</div>"; // du row
            $data['html'] .= "<div class='col-lg'>";
            $weatherForecastController = new WeatherForecastController();
            $slopeWeatherData = $weatherForecastController->getBySlopeId($slopeId,0);
            if ($slopeWeatherData) {
                $weatherStr = $this->getWeatherforecastHtml($slopeWeatherData);
                $data['html'] .= "<h2>Prévisions météo à 3 jours</h2>";
                $data['html'] .= $weatherStr;
            }

            $data['html'] .= "</div>";  // du col
            $data['html'] .= "</div>";  // du row
            $data['html'] .= "</div>";  // du container
            $data['html'] .= "<hr><h2>Données techniques</h2><p>Identifiant du site : " . $slopeId . "</p>";
        }

        if ($slope['type'] == 'meteo')
        {
            $data['html'] = $slope['desc_fr'];
        }

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
        if (array_key_exists('desc_fr', $body)) {
            // On autorise le HTML – filtrer selon tes besoins de sécurité
            $data['desc_fr'] = $body['desc_fr'] !== null
                ? trim($body['desc_fr'])
                : null;
        }
        if (array_key_exists('weather_url', $body)) {
            $url = filter_var($body['weather_url'], FILTER_VALIDATE_URL);
            $data['weather_url'] = $url !== false ? $url : null;
        }

        return $data;
    }
}
