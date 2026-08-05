<?php
// ============================================================
// controllers/SlopeController.php  –  Contrôleur Pentes
// ============================================================

namespace controllers;
use IntlDateFormatter;
use controllers\WeatherForecastController;
use models\Comment;
use models\Slope;
use models\SlopePicture;
use models\Statistic;
use models\WeatherForecast;
use function jsonResponse;

require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../models/Slope.php';
require_once __DIR__ . '/../models/Statistic.php';
require_once __DIR__ . '/../models/SlopePicture.php';
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

        $slopeWeatherData = $slopesWeatherData[0]['weatherDataSet'];

        $separation = "<tr class='separation'><td colspan='8'></td></tr>";

        $html = "<div class='weather-forecast-wrapper'>";
        $html .= "<table class='weather-forecast-table'>";
        $html .= "<tr>";
        $html .= "<th></th><th></th><th></th><th>Vitesse</th><th>Rafales</th><th>Nuages</th><th>Pluie</th><th>Température</th>";
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

    }

    public function getCommentsHtml(int $slopeId): string
    {
        $comments = Comment::getBySlopeId($slopeId);
        if (!$comments) {
            return "";
        }
        $html = "";
        $html .= "<div class='slope-comment mb-4'>";
        $html .= "<h2>Commentaires</h2>";
        $html .= "<table class='slope-comments'>";
        foreach ($comments as $key => $comment) {
            $html .= "<tr>";
            $html .= "<td class='slope-comment-email'>".substr(strip_tags($comment['email']),0,10)."...</td>";
            $html .= "<td rowspan='3' class='slope-comment-text'>".strip_tags($comment['comment'])."</td>";
            $html .= "</tr>";
            $created = new \DateTime($comment['created_at']);
            $html .= "<tr>";
            $html .= "<td class='slope-comment-date'>".date_format($created,'d/m/Y')."</td>";
            $html .= "</tr>";
            $html .= "<tr>";
            $html .= "<td class='slope-comment-status'>";
            if ($comment['status'] == 'new') {
                $html .= "Nouveau !";
            }
            $html .= "<span  class='slope-comment-status-edit'><a target='_blank' href='".$this->getEditUrl('comments',$comment['id'])."'><i class='bi bi-pencil-square'></i></a></span>";
            $html .= "</td>";
            $html .= "</tr>";
        }
        $html .= "</table>";
        $html .= "</div>";


        return $html;
    }

    public function getPictureCarousselHtml(int $slopeId): string {
        $pictures = SlopePicture::getBySlopeId($slopeId);
        if ($pictures) {
            $pictHtml = "";
            if (!empty($pictures)) {

                $pictHtml .= '<div id="slopePictureCarouselCaptions" class="carousel slide  mb-4" data-bs-ride="carousel">';
                $pictHtml .= '    <div class="carousel-indicators">';
                //..Boutons
                foreach ($pictures as $key => $picture) {
                    $pictHtml .= '<button type="button" data-bs-target="#slopePictureCarouselCaptions" data-bs-slide-to="'.$key.'" aria-current="true" aria-label="Photo '.($key+1).'"';
                    if ($key == 0) {
                        $pictHtml .= ' class="active" ';
                    }
                    $pictHtml .= '></button>';
                }
                $pictHtml .= '    </div>';
                $pictHtml .= '    <div class="carousel-inner">';
                //.. photos avec légende et date
                foreach ($pictures as $key => $picture) {
                    $pictHtml .= '        <div class="carousel-item ';
                    if ($key == 0) {
                        $pictHtml .= 'active';
                    }
                    $pictHtml .= '">';
                    $pictHtml .= '            <img src="'.$picture['path'].'" class="d-block w-100" alt="'.$picture['alt'].'">';
                    $pictHtml .= '            <div class="carousel-caption d-none d-md-block">';

                    $created = new \DateTime($picture['created_at']);
                    $createdHtml = date_format($created,'d/m/Y');

                    $pictHtml .= '                <h5>'.$picture['alt'].' - date: '.$createdHtml.'</h5>';
                    $pictHtml .= '            </div>';
                    $pictHtml .= '        </div>';
                }
                $pictHtml .= '    </div>';

                $pictHtml .= '    <button class="carousel-control-prev" type="button" data-bs-target="#slopePictureCarouselCaptions" data-bs-slide="prev">';
                $pictHtml .= '    <span class="carousel-control-prev-icon" aria-hidden="true"></span>';
                $pictHtml .= '    <span class="visually-hidden">Previous</span>';
                $pictHtml .= '    </button>';
                $pictHtml .= '    <button class="carousel-control-next" type="button" data-bs-target="#slopePictureCarouselCaptions" data-bs-slide="next">';
                $pictHtml .= '    <span class="carousel-control-next-icon" aria-hidden="true"></span>';
                $pictHtml .= '    <span class="visually-hidden">Next</span>';
                $pictHtml .= '    </button>';
                $pictHtml .= '</div>';
            }


            return $pictHtml;
        }
        else
        {
            return "";
        }

    }

    public function getEditUrl($datatable,$id) {
        //https://rcslopes.test/admin/table.php?t=comments&mode=edit&pk=368
        $serverName = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
            === 'on' ? "https" : "http") .
            "://" . $_SERVER['HTTP_HOST'];

        return $serverName."/admin/table.php?t=".$datatable."&mode=edit&pk=".$id;
    }

    public function getRateHtml(): string {
        $formRenderedAt = time();
        $html = "    <div class='row'>";
        $html .= "        <div class='col-6 text-end'>";
        $html .= "              <h4>Notez cette pente : </h4>";
        $html .= "        </div>";
        $html .= "        <div class='col-6'>";
        $html .= "            <form id='form_rating'>";
        $html .= "                <div class='star-rating'>";
        $html .= "                    <input type='radio' name='rating' value='1'><i></i>";
        $html .= "                    <input type='radio' name='rating' value='2'><i></i>";
        $html .= "                    <input type='radio' name='rating' value='3'><i></i>";
        $html .= "                    <input type='radio' name='rating' value='4'><i></i>";
        $html .= "                    <input type='radio' name='rating' value='5'><i></i>";
        $html .= "                    <input type='hidden' id='slope_rendered_at' name='slope_rendered_at' value=".$formRenderedAt.">";
        $html .= "                </div>";
        //$html .= "                <div class='mt-3'>";
        //$html .= "                    <span id='rating-value' class='h4'>0</span> out of 5 stars";
        //$html .= "                </div>";
        $html .= "            </form>";
        $html .= "        </div>";
        $html .= "    </div>";
        return $html;
    }

    public function showHtml(int $slopeId): void
    {
        // update statistics
        Statistic::register($slopeId,0);

        $serverName = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
            === 'on' ? "https" : "http") .
            "://" . $_SERVER['HTTP_HOST'];
        $slopeUrl = $serverName . "/" . $slopeId;

        $slope = Slope::getById($slopeId);
        if (!$slope) {
            jsonResponse(['success' => false, 'error' => 'Site introuvable.'], 404);
        }
        $data['title'] = $slope['name'];
        if ($slope['dpt']) $data['title'] .= '-'.$slope['dpt'];

        if ($slope['type'] == 'pente')
        {
            $orientations = join(", ",$slope['orient']);
            $nbViews = Statistic::countViewBySlopeId($slopeId);
            $rating = Statistic::getRateBySlopeId($slopeId);

            $data['html'] = "<div class='container-fluid'>";

            // --- Première ligne
            // ------------------
            $data['html'] .= "<div class='row'>";
            // --- Première colonne
            $data['html'] .= '<div class="col-6">';
            $data['html'] .= '    <div  class="logo">';
            $data['html'] .= '        <img src="./assets/logo.png" alt="Logo RcSlopes" width="150px"/>';
            $data['html'] .= '        </div>';
            $data['html'] .= '</div>';
            // --- Deuxième colonne
            $data['html'] .= "<div class='col-6  slope-coordinates'>";
            $data['html'] .= "<p>Latitude : {$slope['lat']}</p>";
            $data['html'] .= "<p>Longitude : {$slope['lng']}</p>";
            $created = new \DateTime($slope['created_at']);
            $data['html'] .= "<p>Créé le ".date_format($created,'d/m/Y')."</p>";
            $data['html'] .= "</div>";
            $data['html'] .= "</div>";  // fin row


            // --- Orientations
            // ------------------

            $data['html'] .= '<div class="row mb-2">';
            $data['html'] .= '    <div class="col-12">';
            $orientationsStr = ($orientations == ""? 'nc.': $orientations);
            $data['html'] .= "        <div class='divider'>Orientations de vent exploitables : {$orientationsStr}</div>";
            $data['html'] .= '    </div>';
            $data['html'] .= '</div> ' ;

            // --- Description générale
            // ------------------
            $data['html'] .= "<div class='row'>";

            // --- Première colonne
            $data['html'] .= "<div class='col-md-6'>";

            if ($slope['aip'])
                $data['html'] .= "<div class='alert alert-success mb-4' role='alert'>Numéro AIP : {$slope['aip']}</div>";
            else
                $data['html'] .= "<div class='alert alert-danger mb-4' role='alert'>Cette pente ne possède pas d'AIP</div>";


            $data['html'] .= "<div class='container-fluid mb-4' style='padding-left:0;padding-right:0'>";
            $data['html'] .=   "<div class='row gx-5 mb-2'>";
            $data['html'] .=     "<div class='col'>";
            $data['html'] .=       "<div class='bg-light p-2 rounded statistics'>";
            $data['html'] .=         "<span class='icon'>👁️</span>";
            $data['html'] .=         "<span class='value'>{$nbViews}</span>";
            $data['html'] .=        " <span class='label'> Vues</span>";
            $data['html'] .=       "</div>";
            $data['html'] .=     "</div>";
            $data['html'] .=    "<div class='col'>";
            $data['html'] .=      "<div class='bg-light p-2 rounded statistics'>";
            $data['html'] .=       " <span class='icon'>⭐</span>";
            $data['html'] .=        "<span class='value'>".($rating<0 ? 'nc':number_format($rating, 1, '.', ''))."</span>";
            $data['html'] .=        "<span class='label'>/5</span>";
            $data['html'] .=      "</div>";
            $data['html'] .=    "</div>";
            $data['html'] .=  "</div>";

            $data['html'] .= $this->getRateHtml();

            $data['html'] .= "</div>";



            $data['html'] .= "<div class='slope-summary mb-4'>";
            $data['html'] .= "<h2>En bref</h2>";
            $data['html'] .= ($slope['desc_summary_fr'] ? $slope['desc_summary_fr'] : 'Nc.');
            if ($slope['rating'] > 0)
                $data['html'] .= "<p>Rating : ".number_format($slope['rating'], 1, ',', ' ')."</p>";
            $data['html'] .= "</div>";

            $data['html'] .= "<div class='slope-gestion mb-4'>";
            $data['html'] .= "<h2>Gestion</h2>";
            $data['html'] .= "<p>Pente gérée par un club ? ".($slope['club'] == 1 ? 'Oui' : 'Non')."</p>";
            $data['html'] .= "<p>Nom du club gestionnaire : ".($slope['club_name'] ? $slope['club_name'] : 'Nc.')."</p>";
            $data['html'] .= "<p>Costisation club nécessaire ? ".($slope['cotisation'] == 1 ? 'Oui' : 'Non')."</p>";
            $data['html'] .= "<p>License FFAM obligatoire ? ".($slope['licence'] == 1 ? 'Oui' : 'Non')."</p>";
            if ($slope['url'])
                $data['html'] .= '<p class="slope-gestion">Lien club gestionnaire : <a target="_blank" href = '.$slope["url"].'>'.$slope["url"].'</a></p>';
            $data['html'] .= "</div>";

            $data['html'] .= "<div class='slope-summary mb-4'>";
            $data['html'] .= "<h2>En Détail</h2>";
            $data['html'] .= ($slope['desc_fr'] ? $slope['desc_fr'] : 'Nc.');
            $data['html'] .= "</div>";
            $data['html'] .= "<div class='slope-summary mb-4'>";
            $data['html'] .= "<h2>En anglais</h2>";
            $data['html'] .= ($slope['desc_en'] ? $slope['desc_en'] : 'Nc.');
            $data['html'] .= "</div>";
            $data['html'] .= "</div>"; // du col

            // Deuxième colonne
            $data['html'] .= "<div class='col-md-6'>";
            //> images
            $pictureHtml = $this->getPictureCarousselHtml($slopeId);
            if (strlen($pictureHtml)>0)
            {
                $data['html'] .= $pictureHtml;
            }
            //> commentaires
            $commentsHtml = $this->getCommentsHtml($slopeId);
            if (strlen($commentsHtml)>0)
            {
                $data['html'] .= $commentsHtml;
            }
            //> Prévisions météo
            $weatherForecastController = new WeatherForecastController();
            $slopeWeatherData = $weatherForecastController->getBySlopeId($slopeId,0);
            if ($slopeWeatherData) {
                $weatherStr = $this->getWeatherforecastHtml($slopeWeatherData);
                $data['html'] .= "<div class='slope-weather mb-4'>";
                $data['html'] .= "<h2>Prévisions météo à 3 jours</h2>";
                $data['html'] .= $weatherStr;
                $data['html'] .= "</div>";
            }

            $data['html'] .= "</div>";  // du col
            $data['html'] .= "</div>";  // du row
            $data['html'] .= "</div>";  // du container
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

    public function store(bool $controls = true): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // N'accepter que la méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false,'errors'  => ['Méthode non autorisée.']], 405);
            exit;
        }

        // Lecture du corps JSON envoyé par fetch()
        $rawInput = file_get_contents('php://input');
        $input    = json_decode($rawInput, true);

        if (!is_array($input)) {
            jsonResponse(['success' => false,'errors'  => ['Requête invalide.']], 400);
            exit;
        }

        if ($controls)
        {
            // ============================================================
            // 1) VÉRIFICATION DU TOKEN CSRF
            // ============================================================

            $submittedToken = $input['newslope_csrf_token'] ?? '';

            if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
                http_response_code(419); // "Page Expired" (convention courante pour CSRF invalide)
                echo json_encode([
                    'success' => false,
                    'errors'  => ['Session expirée, veuillez recharger la page et réessayer.']
                ]);
                exit;
            }

            // ============================================================
            // 2) HONEYPOT — un bot remplit généralement tous les champs
            // ============================================================
            if (!empty($input['newslope_website'])) {
                // On répond "succès" pour ne pas indiquer au bot qu'il a été détecté,
                // mais on n'insère rien en base.
                http_response_code(201);
                echo json_encode(['success' => true]);
                exit;
            }

            // ============================================================
            // 3) DÉLAI MINIMUM DE SOUMISSION — un bot soumet quasi instantanément
            // ============================================================
            $renderedAt = (int) ($input['newslope_form_rendered_at'] ?? 0);
            $elapsed    = time() - $renderedAt;

            if ($renderedAt <= 0 || $elapsed < 2) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'errors'  => ['Soumission trop rapide, veuillez réessayer.']
                ]);
                exit;
            }

            // ============================================================
            // 4) RATE LIMITING SIMPLE PAR SESSION (ex : 5 commentaires / 10 min)
            // ============================================================
            $now            = time();
            $window         = 600; // 10 minutes
            $maxSubmissions = 5;

            if (!array_key_exists('slope_submissions_timestamp', $_SESSION))
                $_SESSION['slope_submissions_timestamp'] = $now;

            if (!array_key_exists('slope_submissions_count', $_SESSION))
                $_SESSION['slope_submissions_count'] = 1;
            else
                $_SESSION['slope_submissions_count'] = $_SESSION['slope_submissions_count'] + 1;

            if ($now - $_SESSION['slope_submissions_timestamp'] > $window)
            {
                $_SESSION['slope_submissions_timestamp'] = $now;
                $_SESSION['slope_submissions_count'] = 0;
            }

            if (count($_SESSION['slope_submissions_count']) >= $maxSubmissions) {
                http_response_code(429); // Too Many Requests
                echo json_encode([
                    'success' => false,
                    'errors'  => ['Trop de pentes envoyées récemment, veuillez patienter avant de réessayer.']
                ]);
                exit;
            }

        }


        $errors = $this->validateRequired($input);
        if (empty($errors)) $input = $this->sanitize($input);

        if (!empty($errors)) {
            jsonResponse(['success' => false,'errors'  => $errors], 422);
            exit;
        }

        try {
            $slopeId = slope::insert($input);

            // pente enregistrée avec succès > envoi d'un mail récapitulatif à l'émetteur
            require_once 'helpers/mailer.php';

            $serverName = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
                === 'on' ? "https" : "http") .
                "://" . $_SERVER['HTTP_HOST'];
            $slopeUrl = $serverName . "/" . $slopeId;

            if (!empty($input['addBy'])) {
                // Construction contenu email
                $email_vars = array(
                    'NAME' => $input['name'],
                    'SLOPEID' => $slopeId,
                    'EMAIL' => $input['addBy'],
                    'MAILADMIN' => MAIL_ADMIN,   // défini dans le fichier config du mailer
                    'SLOPEURL' => $slopeUrl
                );
                $body = file_get_contents('./mail-templates/newslope.html');
                if(isset($email_vars)){
                    foreach($email_vars as $k=>$v){
                        $body = str_replace('{'.strtoupper($k).'}', $v, $body);
                    }
                }
                $altBody = strip_tags($body);

                // Expédition email
                $mail = getMailer();
                $mail->setFrom('rcslopesadmin@windfoilfan.com', 'FinessePlus');
                $mail->addAddress($input['addBy'], $input['addBy']);
                $mail->addCC(MAIL_ADMIN);
                $mail->Subject = 'FinessePlus - Enregistrement site #'.$slopeId;
                $mail->isHTML(TRUE);
                $mail->Body = $body;
                $mail->AltBody = $altBody;
                $mail->send();
            }

            jsonResponse(['success' => true,'id'  => $slopeId], 201);


        } catch (PDOException $e) {
            // Ne jamais renvoyer le message d'erreur SQL brut au client en production
            error_log('Erreur insertion pente : ' . $e->getMessage());
            jsonResponse(['success' => false,'errors'  => ['Une erreur serveur est survenue, veuillez réessayer plus tard.']], 500);
        }
    }

    // ── POST /api/rate ──────────────────────────────────────

    public function rate(bool $controls = true): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // N'accepter que la méthode POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false,'errors'  => ['Méthode non autorisée.']], 405);
            exit;
        }

        // Lecture du corps JSON envoyé par fetch()
        $rawInput = file_get_contents('php://input');
        $input    = json_decode($rawInput, true);

        if (!is_array($input)) {
            jsonResponse(['success' => false,'errors'  => ['Requête invalide.']], 400);
            exit;
        }

        if ($controls)
        {
            // ============================================================
            // 3) DÉLAI MINIMUM DE SOUMISSION — un bot soumet quasi instantanément
            // ============================================================
            $renderedAt = (int) ($input['slope_rendered_at'] ?? 0);
            $elapsed    = time() - $renderedAt;

            if ($renderedAt <= 0 || $elapsed < 2) {
                http_response_code(422);
                echo json_encode([
                    'success' => false,
                    'errors'  => ['Soumission trop rapide, veuillez réessayer.']
                ]);
                exit;
            }

            // ============================================================
            // 4) RATE LIMITING SIMPLE PAR SESSION (ex : 5 commentaires / 10 min)
            // ============================================================
            $now = time();
            $window         = 120; // 2 minutes
            $maxSubmissions = 5;
            if (!array_key_exists('slope_rating_timestamp', $_SESSION))
                $_SESSION['slope_rating_timestamp'] = $now;

            if (!array_key_exists('slope_rating_count', $_SESSION))
                $_SESSION['slope_rating_count'] = 1;
            else
                $_SESSION['slope_rating_count'] = $_SESSION['slope_rating_count'] + 1;

            if ($now - $_SESSION['slope_rating_timestamp'] > $window)
            {
                $_SESSION['slope_rating_timestamp'] = $now;
                $_SESSION['slope_rating_count'] = 0;
            }

            if ($_SESSION['slope_rating_count'] > $maxSubmissions)
            {
                http_response_code(429); // Too Many Requests
                echo json_encode([
                    'success' => false,
                    'errors'  => ['Trop de votes envoyés récemment, veuillez patienter quelques minutes avant de réessayer.']
                ]);
                exit;
            }
        }

        $errors = [];
        if (empty($input['slope_slopeId'])) {
            $errors[] = 'L identifiant est obligatoire.';
        }
        if (empty($input['slope_rating'])) {
            $errors[] = 'La note est obligatoire.';
        }

        if (empty($errors))
        {
            $data = [];
            if (isset($input['slope_slopeId'])) {
                $data['slopeId'] = intval($input['slope_slopeId']);
            }
            if (isset($input['slope_rating'])) {
                $data['rate'] = intval($input['slope_rating']);
            }
        }

        if (!empty($errors)) {
            jsonResponse(['success' => false,'errors'  => $errors], 422);
            exit;
        }

        try {
            Statistic::register($data['slopeId'], 1, $data['rate']);

           jsonResponse(['success' => true], 201);


        } catch (PDOException $e) {
            // Ne jamais renvoyer le message d'erreur SQL brut au client en production
            error_log('Erreur rate : ' . $e->getMessage());
            jsonResponse(['success' => false,'errors'  => ['Une erreur serveur est survenue, veuillez réessayer plus tard.']], 500);
        }
    }

    // ── Validation ────────────────────────────────────────────

    private function validateRequired(array $body): array
    {
        $errors = [];

        if (empty($body['newslope_name'])) {
            $errors[] = 'Le champ "nom" est obligatoire.';
        }
        if (empty($body['newslope_type'])) {
            $errors[] = 'Le champ "type" est obligatoire.';
        }
        if (!isset($body['newslope_lat']) || !is_numeric($body['newslope_lat'])) {
            $errors[] = 'Le champ "lattitude" doit être un nombre.';
        }
        if (!isset($body['newslope_lng']) || !is_numeric($body['newslope_lng'])) {
            $errors[] = 'Le champ "longitude" doit être un nombre.';
        }
        $types = array('pente','interdit','parking');
        if (!isset($body['newslope_type']) || !in_array($body['newslope_type'], $types)) {
            $errors[] = 'Le type n\'est pas correct.';
        }
        if (empty($body['newslope_email'])) {
            $errors[] = 'Le champ "email" est obligatoire.';
        }
        else
        {
            if (!filter_var($body['newslope_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "L'adresse email n'est pas valide.";
            }
        }
        if (isset($body['newslope_orient']) &&  strlen($body['newslope_orient'])>0 ) {
            $orientStr = trim(preg_replace ("/\s+/", " ", $body['newslope_orient'])); // suppression des doubles espaces
            $orients = explode(" ", $orientStr);
            $orients = $this->sanitizeWindDirections($orients);
            if (!$orients) {
                $errors[] = 'Valeur invalide dans l\'orientation';
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

    private function sanitize(array $body): array
    {
        $data = [];

        if (isset($body['newslope_name'])) {
            $data['name'] = trim(strip_tags($body['newslope_name']));
        }
        if (isset($body['newslope_lat'])) {
            $data['lat'] = (float)$body['newslope_lat'];
        }
        if (isset($body['newslope_lng'])) {
            $data['lng'] = (float)$body['newslope_lng'];
        }
        if (isset($body['newslope_type'])) {
            $data['type'] = trim(strip_tags($body['newslope_type']));
        }
        if (isset($body['newslope_email'])) {
            $data['addBy'] = trim(strip_tags($body['newslope_email']));
        }
        if (isset($body['newslope_orient']) &&  strlen($body['newslope_orient'])>0) {
            /**
             * Exemple : "N  NNE NW"
             */

            $orientStr = trim(preg_replace ("/\s+/", " ", $body['newslope_orient'])); // suppression des doubles espaces
            $orients = explode(" ", $orientStr);
            $data['orient'] =  $this->windSetFromArray($this->sanitizeWindDirections($orients));
        }

        if (isset($body['newslope_country'])) {
            $data['country'] = trim(strip_tags($body['newslope_country']));
        }
        if (isset($body['newslope_dept'])) {
            $data['dpt'] = trim(strip_tags($body['newslope_dept']));
        }
        if (isset($body['newslope_slopeAIP'])) {
            $data['aip'] = trim(strip_tags($body['newslope_slopeAIP']));
        }
        if (isset($body['newslope_slopeClub']) && $body['newslope_slopeClub'] == 'on') {
            $data['club'] = 1;
        }
        else
        {
            $data['club'] = 0;
        }
        if (isset($body['newslope_slopeCotisation']) && $body['newslope_slopeCotisation'] == 'on') {
            $data['cotisation'] = 1;
        }
        else
        {
            $data['cotisation'] = 0;
        }
        if (isset($body['newslope_slopeLicence']) && $body['newslope_slopeLicence'] == 'on') {
            $data['licence'] = 1;
        }
        else
        {
            $data['licence'] = 0;
        }
        if (isset($body['newslope_slopeURL'])) {
            $data['url'] = trim(strip_tags($body['newslope_slopeURL']));
        }
        if (isset($body['newslope_clubName'])) {
            $data['club_name'] = trim(strip_tags($body['newslope_clubName']));
        }

        $infoStr = "<h3>Conditions de vol</h3>";
        if (isset($body['newslope_slopeSize'])) {
            $infoStr .= "<p>Hauteur de la pente : " . trim($body['newslope_slopeSize']) . "</p>";
        }
        if (isset($body['newslope_slopeCompatibility'])) {
            $infoStr .= "<p>Compatible type de planeurs : " . trim($body['newslope_slopeCompatibility']) . "</p>";
        }
        if (isset($body['newslope_moulinette'])) {
            $infoStr .= "<p>Planeur motorisé : " . trim($body['newslope_moulinette']) . "</p>";
        }
        if (isset($body['newslope_slopeSurface'])) {
            $infoStr .= "<p>Etat de surface zone de posé : " . trim($body['newslope_slopeSurface']) . "</p>";
        }
        if (isset($body['newslope_slopeGap'])) {
            $infoStr .= "<p>Accès au trou : " . trim($body['newslope_slopeGap']) . "</p>";
        }
        $infoStr .= "<h3>Conditions d'accès à la pente</h3>";
        if (isset($body['newslope_slopePark'])) {
            $infoStr .= "<p>Accès pente depuis le stationnement : " . trim($body['newslope_slopePark']) . "</p>";
        }
        if (isset($body['newslope_slopeAccess'])) {
            $infoStr .= "<p>Accès zone de stationnement : " . trim($body['newslope_slopeAccess']) . "</p>";
        }

        $data['desc_summary_fr'] = $infoStr;

        $infoStr = "";
        if (isset($body['newslope_slopeInfo'])) {
            $infoStr .= trim($body['newslope_slopeInfo']);
        }
        $data['desc_fr'] = $infoStr;

        if (isset($body['newslope_slopeInfoEn'])) {
            $data['desc_en'] = trim($body['newslope_slopeInfoEn']);
        }


        /*
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
*/
        return $data;
    }

    /**
     * Orientations de vent valides.
     */
    private function validWindDirections(): array
    {
        return ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'];
    }

    /**
     * Valide et nettoie un tableau d'orientations.
     * Retourne un tableau filtré ou null si une valeur est invalide.
     */
    private function sanitizeWindDirections(array $directions): ?array
    {
        $valid = $this->validWindDirections();
        $clean = [];
        foreach ($directions as $d) {
            $d = strtoupper(trim((string)$d));
            if (!in_array($d, $valid, true)) {
                return null;
            }
            $clean[] = $d;
        }

        return array_values(array_unique($clean));
    }

    /**
     * Convertit la chaîne SET MySQL en tableau PHP.
     * Exemple : "N,NNE,NW" → ["N", "NNE", "NW"]
     */
    private function windSetToArray(string $set): array
    {
        if ($set === '') return [];
        return explode(',', $set);
    }

    /**
     * Convertit le tableau en chaine SET.
     * Exemple : ["N", "NNE", "NW"] -> "N,NNE,NW"
     */
    private function windSetFromArray(array $windArray): string
    {
        if (empty($windArray)) return '';
        return implode(',',$windArray);
    }

}
