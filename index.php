<?php

// ============================================================
// index.php  –  Point d'entrée : page d'accueil + API REST
// ============================================================

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Supprime le préfixe si l'API est dans un sous-dossier
$uri = preg_replace('#^/api#', '', $uri);
$uri = rtrim($uri, '/');


// ── Page d'accueil (GET /) ────────────────────────────────────
if ($method === 'GET' && ($uri === '' || $uri === '/')) {
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>RC Slopes – Sites de vol de pente</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/leaflet-gps.css" />
    <link rel="stylesheet" href="css/main.css" />





</head>
<body  class="rc-body">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark rc-navbar">
    <div class="container-fluid rc-navbar-inner">

        <!-- Logo + titre -->
        <a class="navbar-brand d-flex align-items-center" href="#">
            <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                 width="64" height="32" viewBox="0 0 1280.000000 640.000000"
                 preserveAspectRatio="xMidYMid meet" class="rc-logo me-2">
                <g transform="translate(0.000000,640.000000) scale(0.100000,-0.100000)"
                   fill="#7ecfff" stroke="none">
                    <path d="M12526 6334 c-13 -13 -16 -44 -16 -187 0 -216 -26 -335 -86 -389 -11
-9 -1102 -693 -2425 -1521 -2316 -1448 -2408 -1505 -2465 -1511 -176 -22 -430
-100 -924 -284 -629 -234 -1108 -371 -2345 -667 -338 -81 -373 -85 -424 -48
-38 28 -78 91 -283 448 -329 572 -430 755 -425 768 3 7 177 104 388 215 211
110 393 208 404 216 18 14 14 15 -54 16 l-74 0 -979 -422 c-751 -324 -974
-424 -956 -429 62 -18 111 -7 371 81 146 49 273 90 281 90 8 0 17 -8 20 -17 3
-10 84 -295 181 -633 97 -338 180 -619 185 -624 12 -12 401 9 595 34 697 87
1454 174 1461 167 4 -4 5 -10 1 -13 -12 -13 -4313 -1493 -4338 -1494 -38 0
-183 101 -349 244 -79 69 -164 136 -187 148 l-43 23 -22 -22 c-20 -21 -21 -24
-7 -65 21 -66 103 -181 172 -243 116 -103 254 -155 415 -155 77 0 102 6 281
64 108 36 988 320 1956 631 968 312 2051 661 2406 775 l646 209 214 16 c149
11 457 19 1009 25 794 9 927 12 1115 31 516 50 1019 224 1148 396 54 72 26
148 -80 219 -150 100 -477 203 -853 269 -85 15 -165 29 -178 31 -13 3 -22 10
-20 17 3 7 1005 684 2229 1504 1223 821 2238 1508 2255 1526 32 35 74 131 74
170 0 38 -64 211 -100 271 -41 67 -111 136 -139 136 -11 0 -27 -7 -35 -16z"/>
                </g>
            </svg>
            <p class="site-name">
                <span >RC-SlopeS</span></br>
                <span class="site-by">by Finesse+</span>
            </p>

        </a>

        <!-- Groupe droit mobile : loupe + burger, toujours ensemble -->
        <div class="d-flex align-items-center ms-auto rc-mobile-actions">

            <button class="btn btn-link text-light d-lg-none rc-search-btn"
                    type="button" data-bs-toggle="collapse" data-bs-target="#searchMobile"
                    aria-controls="searchMobile" aria-label="Rechercher">
                <i class="bi bi-search"></i>
            </button>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarContent" aria-controls="navbarContent"
                    aria-expanded="false" aria-label="Afficher/masquer le menu">
                <span class="navbar-toggler-icon"></span>
            </button>

        </div>

        <!-- Contenu repliable (desktop : lien + recherche / mobile : lien seul) -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <button type="button" class="btn btn-link nav-link" data-bs-toggle="modal" data-bs-target="#OpenWindMapModal">
                        Wind data
                    </button>
                </li>

                <li class="nav-item d-none d-lg-block ms-lg-3">
                    <form class="position-relative rc-slope-search" role="search" autocomplete="off">
                        <input type="search" class="form-control rc-search-input ps-3 pe-5"
                               placeholder="Rechercher" aria-label="Rechercher"
                               id="slopeSearchInput"
                               autocomplete="off"
                               aria-expanded="false"
                               aria-controls="slopeSearchResults"
                               role="combobox">
                        <i class="bi bi-search rc-search-icon"></i>

                        <ul class="dropdown-menu rc-search-dropdown w-100" id="slopeSearchResults" role="listbox"></ul>
                    </form>
                </li>

            </ul>
        </div>

    </div>
</nav>

<!-- Barre de recherche mobile : apparaît sous la navbar quand on clique sur la loupe -->
<div class="collapse d-lg-none bg-dark" id="searchMobile">
    <div class="container-fluid py-2">
        <form class="position-relative rc-slope-search" role="search" autocomplete="off">
            <input type="search" class="form-control rc-search-input ps-3 pe-5"
                   placeholder="Rechercher" aria-label="Rechercher"
                   id="slopeSearchInputMobile"
                   autocomplete="off"
                   aria-expanded="false"
                   aria-controls="slopeSearchResultsMobile"
                   role="combobox">
            <i class="bi bi-search rc-search-icon"></i>

            <ul class="dropdown-menu rc-search-dropdown w-100" id="slopeSearchResultsMobile" role="listbox"></ul>
        </form>
    </div>
</div>

<main class="rc-main">
    <div id="map"></div>
</main>

<!-- Marker Modal -->
<div class="modal fade" id="markerModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title" id="markerModalLabel">Titre du marker</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="markerModalBody">
                <p>Lorem ipsum dolor sit amet. Aut quae repellat aut sequi quaeratQui delectus id nihil consequatur aut iste impedit hic modi voluptate. Ut repellat praesentium sed alias aspernaturest asperiores id eligendi facere et eius autem. Eos nihil laboreQuo neque ea nisi ducimus sed libero omnis et perspiciatis inventore. Ad dicta doloribus <a href="https://www.loremipzum.com" target="_blank">Qui obcaecati</a> ut quae maiores! Est tempore galisum sed impedit fugitAut inventore nam facere ratione id laborum illo quo nesciunt placeat aut necessitatibus aliquid. Ut voluptatibus nemo ab deleniti nostrumet veritatis. Cum consequuntur delectus est labore nobisSed voluptatem vel aperiam quibusdam cum voluptatem velit qui labore quas ut laborum autem. Sit dignissimos rerum eos totam laboriosamut perspiciatis ut asperiores blanditiis. Id blanditiis autemEa quia qui dignissimos quod et voluptatem aspernatur in laborum voluptas sed sint distinctio. </p><ul><li>In architecto voluptas non ullam dolores. </li><li>Ea recusandae labore et odit ratione! </li></ul>
                <p>Lorem ipsum dolor sit amet. Aut quae repellat aut sequi quaeratQui delectus id nihil consequatur aut iste impedit hic modi voluptate. Ut repellat praesentium sed alias aspernaturest asperiores id eligendi facere et eius autem. Eos nihil laboreQuo neque ea nisi ducimus sed libero omnis et perspiciatis inventore. Ad dicta doloribus <a href="https://www.loremipzum.com" target="_blank">Qui obcaecati</a> ut quae maiores! Est tempore galisum sed impedit fugitAut inventore nam facere ratione id laborum illo quo nesciunt placeat aut necessitatibus aliquid. Ut voluptatibus nemo ab deleniti nostrumet veritatis. Cum consequuntur delectus est labore nobisSed voluptatem vel aperiam quibusdam cum voluptatem velit qui labore quas ut laborum autem. Sit dignissimos rerum eos totam laboriosamut perspiciatis ut asperiores blanditiis. Id blanditiis autemEa quia qui dignissimos quod et voluptatem aspernatur in laborum voluptas sed sint distinctio. </p><ul><li>In architecto voluptas non ullam dolores. </li><li>Ea recusandae labore et odit ratione! </li></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- OpenWindMap Modal -->
<div class="modal fade" id="OpenWindMapModal" tabindex="-1" aria-labelledby="OpenWindMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="OpenWindMapModalLabel">Légende OpenWindMap data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Pour visualiser les données vent en temps réel, activer la couche "Vent" sur la carte.</p>
                <p>Les données sont fournies par le Réseau Opendata Windbird</p>
                <p>Dernières données : <span id="lastupdate"></span></p>
                <p>Légende des vitesse de vent : </p>
                <div class="wind-legend" id="windLegend">
                    <div class="gauge-container" id="gaugeContainer"></div>
                    <div class="labels">
                        <span>0</span>
                        <span>15</span>
                        <span>30</span>
                        <span>45</span>
                        <span>60 km/h</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script src="js/leaflet-gps.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<script>

    function generateWindRoseSVG(sectors) {
        // Définition des 16 secteurs de la rose des vents dans l'ordre horaire (secteur 1 = N en haut)
        const allSectors = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];

        // Paramètres du cercle (basés sur votre code)
        const radius = 18;
        const circumference = 2 * Math.PI * radius; // ≈ 113.097
        const sectorLength = circumference / 16; // ≈ 7.069
        const initialOffset = circumference / 4 + sectorLength / 2; // 31.809 (pour centrer le secteur en haut)

        // Construire les cercles pour chaque secteur actif
        let sectorCircles = [];

        sectors.forEach(sector => {
            const index = allSectors.indexOf(sector);
            if (index === -1) {
                console.warn(`Secteur inconnu: ${sector}`);
                return;
            }

            // Calculer le dashoffset pour ce secteur
            // Formule: offset initial - (index × longueur d'un secteur)
            const offset = initialOffset - (index * sectorLength);

            sectorCircles.push(
                `            <circle cx="25" cy="25" r="18" fill="none" stroke-width= "12"
                    stroke="#0000FF"
                    stroke-dasharray="${sectorLength.toFixed(3)} ${(circumference - sectorLength).toFixed(3)}"
                    stroke-dashoffset="${offset.toFixed(3)}"/>`
            );
        });

        // Générer le SVG complet
        const svg = `        <svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">
            <circle cx="25" cy="25" r="13" fill="none" stroke="rgb(131, 220, 26)" stroke-width="12"/>

            <!-- Secteurs actifs en bleu -->
${sectorCircles.join('\n\n')}
        </svg>`;

        return svg;
    }

    function getWindSpeedColor(speed) {
        var color = 'white';
        if (speed > 5) color = '#5CF1FF';
        if (speed > 10) color = '#00FFC8';
        if (speed > 15) color = '#00FF7B';
        if (speed > 20) color = '#44FF00';
        if (speed > 25) color = '#C3FF00';
        if (speed > 30) color = '#FFF200';
        if (speed > 35) color = '#FFBB00';
        if (speed > 40) color = '#FF8000';
        if (speed > 45) color = '#FF3300';
        if (speed > 50) color = '#FF0080';
        if (speed > 55) color = '#D000FF';
        if (speed > 60) color = '#5500FF';
        return color;
    }

    function generateWindDirectionSVG(heading,speed) {

        var svg = '<svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">' +
            '<polyline points="30,25 20,25 25,0 30,25" fill="none" stroke="black" ' + ' transform="rotate(' + (heading + 180) + ',25,25)"/>' +
            '<polygon points="30,25 20,25 25,0" style="fill:' + getWindSpeedColor(speed) + '" transform="rotate(' + (heading + 180) + ',25,25)"/>' +
            '<circle r="5" cx="25" cy="25" fill="black" />' +
            '</svg>';
        return svg;
    }

    // Génère la jauge avec les couleurs
    function generateWinSpeedColorLegend() {
        const container = document.getElementById('gaugeContainer');
        const steps = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60];

        for (let i = 0; i < steps.length - 1; i++) {
            const segment = document.createElement('div');
            segment.className = 'gauge-segment';
            segment.style.backgroundColor = getWindSpeedColor(steps[i] + 1);
            container.appendChild(segment);
        }
    }

    function feedModalBySlope(slopeId) {
        fetch('/api/slopes/desc/'+slopeId)
            .then(r => r.ok ? r.json() : null)
            .then(json => {
                if (!json?.data?.title) return;
                document.getElementById("markerModalLabel").innerHTML= "<span class='label'>Dénomination du site : </span>" + json.data.title;
                document.getElementById("markerModalBody").innerHTML= json.data.html;
            })
            .catch(() => {});

    }

    // Instance Bootstrap réutilisable
    const markerModalEl = document.getElementById('markerModal');
    const markerModal = new bootstrap.Modal(markerModalEl);


    // DEFINITION DES FONDS DE CARTE
    var osmFrTile = L.tileLayer('//{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        attribution: 'donn&eacute;es &copy; <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
        minZoom: 1,
        maxZoom: 20
    });

    var openTopoTile = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://www.osm.org/copyright">OpenStreetMap</a> contributors',
    });

    var ESRI_carto = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community</a>'
    });

    // DEFINITION DES COUCHES DE DONNEES

    var slopeMarkers = [];
    var parkingMarkers = [];
    var windMarkers = [];
    var parkingLayerGroup = L.layerGroup(parkingMarkers);    // Couche Parking
    var slopesLayerGroup = L.layerGroup(slopeMarkers);    // Couche Parking
    var windLayerGroup = L.layerGroup(windMarkers);    // Couche Vent

    // CREATION CARTE
    var map = L.map('map', {
        center: [46, 2],
        zoom: 7,
        layers: [openTopoTile, slopesLayerGroup]
    });

    // CHOIX DES CARTES ET COUCHES
    var cartoLayer = {
        "Vue Topographique": openTopoTile,
        "Vue Satellite": ESRI_carto,
        "Vue OSM Fr": osmFrTile,
    };

    var infoLayer = {
        "Pentes": slopesLayerGroup,
        "Parkings": parkingLayerGroup,
        "Vent": windLayerGroup
    };

    // ajout des groupe Cartes et couche en tant que control menu
    L.control.layers(cartoLayer,infoLayer).addTo(map);

    // create the sidebar instance and add it to the map
    //var sidebar = L.control.sidebar({ container: 'sidebar' })
    //    .addTo(map);


    // CHARGEMENT DES PENTES VIA API
    fetch('/api/slopes')
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            if (!json?.data?.length) return;

            var meteo_pnt = L.icon({
                iconUrl: 'assets/wind-turbine.png',
                iconSize:     [50,50],
                iconAnchor:   [25, 25],
                popupAnchor:  [0, -25]
            });

            var interdit_pnt = L.icon({
                iconUrl: 'assets/no-flight.png',
                iconSize:     [50,50],
                iconAnchor:   [25, 25],
                popupAnchor:  [0, -25]
            });

            var parking_pnt = L.icon({
                iconUrl: 'assets/parking.png',
                iconSize:     [20,20],
                iconAnchor:   [10, 10],
                popupAnchor:  [0, -10]
            });

            json.data.forEach(site =>
            {
                var slopeId = site.slopeId;
                var name = site.name;
                var lat = site.lat;
                var lng = site.lng;
                var type = site.type;

                if (type == "pente") {
                    //var sectors = ['N', 'NNE', 'NE'];
                    var svgIcon =generateWindRoseSVG(site.orient);
                    // Encoder le SVG en Data URI
                    var svgUrl = 'data:image/svg+xml;base64,' + btoa(svgIcon);

                    // Créer l'icône Leaflet avec le SVG
                    var slope_pnt = L.icon({
                        iconUrl: svgUrl,
                        iconSize: [50, 50],      // Taille de l'icône
                        iconAnchor: [25, 25],    // Point d'ancrage (centre)
                        popupAnchor: [0, -20]    // Position du popup par rapport à l'icône
                    });
                    marker = L.marker([lat, lng], {icon: slope_pnt}).addTo(slopesLayerGroup);

                    marker.on('click', function () {
                        feedModalBySlope(slopeId);
                        markerModal.show();
                    });

                }
                if (type == "interdit") {
                    marker = L.marker([lat, lng], {icon: interdit_pnt})
                        .addTo(slopesLayerGroup);

                }

                if (type == "parking") {
                    marker = L.marker([lat, lng], {icon: parking_pnt})   //,  zIndexOffset: -500
                        .addTo(parkingLayerGroup);

                }

                if (type == "meteo") {
                    marker = L.marker([lat, lng], {icon: meteo_pnt})   //,  zIndexOffset: -500
                        .addTo(windLayerGroup);
                    marker.bindPopup(name +"("+slopeId+")<div class='slopeDet'>|"+slopeId+"|"+lat+"|"+lng+"|</div>");

                    marker.on('click', function () {
                        // Ouvre la barre latérale
                        //sidebar.open("home");
                        // récupére les éléments contenu dans le popup du marker
                        var pops = this.getPopup().getContent().split("|");
                        var slope_id = pops[1];
                        var slope_lat = pops[2];
                        var slope_lng =  pops[3];
                        // Ferme le popup pour ne pas afficher son contenu
                        this.closePopup();
                    });

                    marker.on('mouseover',function(ev){
                        ev.target.openPopup();
                    })
                }


            }
            );

        })
        .catch(() => {}); // pas de sites : carte seule

    // CHARGEMENT DES OBSERVATIONS VENT VIA API
    var lastUpdate = 'nc.';
    var lastUpdateTs = 0;
    fetch('/api/stations')
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            if (!json?.data?.length) return;
            var currentTimestamp  = new Date().getTime();  // Current UTC Timestamp
            json.data.forEach(station =>
            {
                // On récupère le temps UTC de la mesure, et on affichera le temps local
                // on élimine de l'affichage toutes les mesures de plus de 2h
                var station_id = station.station_id;
                var latitude = station.latitude;
                var longitude = station.longitude;
                var wind_heading = station.wind_heading;
                var wind_speed_avg = station.wind_speed_avg;
                var wind_speed_max = station.wind_speed_max;
                var wind_speed_min = station.wind_speed_min;
                var utc_wind_measurement_date_str = station.measurement_date;  // UTC Data date
                var offset = 10000;
                try {
                    var utc_wind_measurement_date = new Date(utc_wind_measurement_date_str);
                    //console.log(utc_wind_measurement_date);

                    var wind_measurement_date = new Date(Date.UTC(
                        utc_wind_measurement_date.getFullYear(),
                        utc_wind_measurement_date.getMonth(),
                        utc_wind_measurement_date.getDate(),
                        utc_wind_measurement_date.getHours(),
                        utc_wind_measurement_date.getMinutes(),
                        utc_wind_measurement_date.getSeconds()
                    ));

                    var wind_measurement_date_ts = wind_measurement_date.getTime();
                    offset = (currentTimestamp - wind_measurement_date_ts)/1000;
                    // console.log(offset);

                    //console.log(wind_measurement_date,wind_measurement_date_ts, lastUpdateTs, lastUpdate);
                    if (wind_measurement_date_ts > lastUpdateTs)
                    {
                        lastUpdateTs = wind_measurement_date_ts;
                        var dateString =
                            ("0" + wind_measurement_date.getDate()).slice(-2) + "/" +
                            ("0" + (wind_measurement_date.getMonth()+1)).slice(-2) + "/" +
                            wind_measurement_date.getFullYear() + " " +
                            ("0" + wind_measurement_date.getHours()).slice(-2) + ":" +
                            ("0" + wind_measurement_date.getMinutes()).slice(-2) + ":" +
                            ("0" + wind_measurement_date.getSeconds()).slice(-2);

                        document.getElementById('lastupdate').innerHTML = dateString;
                    }

                }
                catch (e) {
                    
                }

                if (offset < 7000)
                {
                    // on n'affiche le vent que si la mesure date de moins de 2h
                    var svgIcon =generateWindDirectionSVG(wind_heading,wind_speed_avg);
                    // Encoder le SVG en Data URI
                    var svgUrl = 'data:image/svg+xml;base64,' + btoa(svgIcon);

                    // Créer l'icône Leaflet avec le SVG
                    var wind_pnt = L.icon({
                        iconUrl: svgUrl,
                        iconSize: [60, 60],      // Taille de l'icône
                        iconAnchor: [30, 30],    // Point d'ancrage (centre)
                        popupAnchor: [0, -30]    // Position du popup par rapport à l'icône
                    });

                    const popup = `
                <strong style="color:#7ecfff">Station Windbird #${station_id}</strong><br>
                <small>Vent (km/h)  (min)-Moyen-(max) : (${wind_speed_min})-${wind_speed_avg}-(${wind_speed_max})</small><br>
                <small>Date : ${wind_measurement_date}</small>
                `;

                    marker = L.marker([latitude, longitude], {icon: wind_pnt, zIndexOffset: 1000})
                        .addTo(windLayerGroup)
                        .bindPopup(popup);
                }



            })
        })
        .catch(() => {}); // pas de sites : carte seule

    // IDENITICATION DE LA POSITION DU USER
    var myLocationIcon = L.icon({
        iconUrl: '../assets/location.png',
        iconSize:     [50, 50], // size of the icon
        iconAnchor:   [10, 25], // point of the icon which will correspond to marker's location
        popupAnchor:  [-3, -25] // point from which the popup should open relative to the iconAnchor
    });

    var gps = new L.Control.Gps({
        //autoActive:true,
        autoCenter:true,
        maxZoom:12,
        marker: new L.Marker([0,0], {icon: myLocationIcon})
    });//inizialize control

    gps
        .on('gps:located', function(e) {
            //	e.marker.bindPopup(e.latlng.toString()).openPopup()
            console.log(e.latlng, map.getCenter())
        })
        .on('gps:disabled', function(e) {
            e.marker.closePopup()
        });

    gps.addTo(map);

    generateWinSpeedColorLegend();

    (function () {
        const DEBOUNCE_MS = 300;
        const MIN_CHARS = 2;
        const API_URL_BASE = '/api/slopes/search/';



        // déterminer si on travaille avec le Input mobile ou screen
        const inputScreen = document.getElementById('slopeSearchInput');
        let isVisible = inputScreen.checkVisibility({
            opacityProperty: true,   // Check CSS opacity property too
            visibilityProperty: true // Check CSS visibility property too
        });
        var input = document.getElementById('slopeSearchInput');
        var dropdown = document.getElementById('slopeSearchResults');
        if (!isVisible)
        {
            input = document.getElementById('slopeSearchInputMobile');
            dropdown = document.getElementById('slopeSearchResultsMobile');
        }

        if (!input || !dropdown) return;

        let debounceTimer = null;
        let currentRequestId = 0; // ignore les réponses obsolètes (requêtes parties dans le désordre)
        let activeIndex = -1;     // pour la navigation clavier
        let currentResults = [];

        input.addEventListener('input', function () {
            const query = input.value.trim();
            clearTimeout(debounceTimer);

            if (query.length < MIN_CHARS) {
                closeDropdown();
                return;
            }

            debounceTimer = setTimeout(function () {
                fetchResults(query);
            }, DEBOUNCE_MS);
        });

        input.addEventListener('keydown', function (e) {
            if (!isDropdownOpen()) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                moveActive(1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                moveActive(-1);
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && currentResults[activeIndex]) {
                    e.preventDefault();
                    selectSlope(currentResults[activeIndex]);
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        // Ferme le dropdown si on clique ailleurs sur la page
        document.addEventListener('click', function (e) {
            if (!input.closest('.rc-slope-search').contains(e.target)) {
                closeDropdown();
            }
        });

        function fetchResults(query) {
            const requestId = ++currentRequestId;
            showLoading();

            fetch(API_URL_BASE + encodeURIComponent(query), {
                headers: { 'Accept': 'application/json' },
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Erreur serveur (' + response.status + ')');
                    return response.json();
                })
                .then(function (data) {
                    // Si une requête plus récente est partie depuis, on jette ce résultat obsolète
                    if (requestId !== currentRequestId) return;
                    renderResults(Array.isArray(data.data) ? data.data : (data.results || []));
                })
                .catch(function () {
                    if (requestId !== currentRequestId) return;
                    showError();
                });

        }

        function renderResults(results) {
            currentResults = results;
            activeIndex = -1;
            dropdown.innerHTML = '';

            if (results.length === 0) {
                dropdown.innerHTML = '<li class="rc-search-empty">Aucun site trouvé</li>';
                openDropdown();
                return;
            }
            results.forEach(function (slope, index) {
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.className = 'dropdown-item';
                a.href = '#';
                a.textContent = slope.name;
                a.dataset.index = index;

                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    selectSlope(slope);
                });
                a.addEventListener('mouseenter', function () {
                    setActiveIndex(index);
                });

                li.appendChild(a);
                dropdown.appendChild(li);
            });

            openDropdown();
        }

        function selectSlope(slope) {
            input.value = slope.name;
            closeDropdown();

            // Redirige vers la fiche du site. Adapte cette URL à ta structure réelle.
            //window.location.href = '/slopes/' + slope.slopeId;

            map.flyTo([slope.lat, slope.lng],14);
        }

        function moveActive(direction) {
            if (currentResults.length === 0) return;
            let newIndex = activeIndex + direction;
            if (newIndex < 0) newIndex = currentResults.length - 1;
            if (newIndex >= currentResults.length) newIndex = 0;
            setActiveIndex(newIndex);
        }

        function setActiveIndex(index) {
            const items = dropdown.querySelectorAll('.dropdown-item');
            items.forEach(function (item) { item.classList.remove('active'); });
            if (items[index]) {
                items[index].classList.add('active');
                items[index].scrollIntoView({ block: 'nearest' });
            }
            activeIndex = index;
        }

        function showLoading() {
            dropdown.innerHTML = '<li class="rc-search-loading">Recherche…</li>';
            openDropdown();
        }

        function showError() {
            dropdown.innerHTML = '<li class="rc-search-empty">Erreur lors de la recherche</li>';
            openDropdown();
        }

        function openDropdown() {
            dropdown.classList.add('show');
            input.setAttribute('aria-expanded', 'true');
        }

        function closeDropdown() {
            dropdown.classList.remove('show');
            dropdown.innerHTML = '';
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
            currentResults = [];
        }

        function isDropdownOpen() {
            return dropdown.classList.contains('show');
        }
    })();
</script>
</body>
</html>
<?php
exit;
}

// ── Routes API ────────────────────────────────────────────────
// En-têtes JSON + CORS (uniquement pour les routes /api/*)

use controllers\SlopeController;
use controllers\WindStationController;

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');          // ← restreindre en production
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Réponse aux pre-flight OPTIONS (AJAX cross-origin)
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}
require_once __DIR__ . '/controllers/SlopeController.php';
require_once __DIR__ . '/controllers/WindStationController.php';
require_once __DIR__ . '/helpers/response.php';

$slopeController = new SlopeController();
$windStationController = new WindStationController();

// ── GET /slopes ──────────────────────────────────────────────
if ($method === 'GET' && $uri === '/slopes') {
    $slopeController->index();

// ── GET /stations ──────────────────────────────────────────────
} elseif ($method === 'GET' && $uri === '/stations') {
    $windStationController->index();

    // ── GET /slopes/desc/{id} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/slopes/desc/(\d+)$#', $uri, $m)) {
    $slopeController->showHtml((int) $m[1]);

    // ── GET /slopes/search/{txt} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/slopes/search/([A-Za-z0-9_]{2,30})$#', $uri, $m)) {
    $slopeController->search($m[1]);

// ── GET /slopes/{id} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/slopes/(\d+)$#', $uri, $m)) {
    $slopeController->show((int) $m[1]);


// ── GET /stations/{id} ─────────────────────────────────────────
} elseif ($method === 'GET' && preg_match('#^/stations/(\d+)$#', $uri, $m)) {
    $windStationController->show((int) $m[1]);

// ── POST /slopes ─────────────────────────────────────────────
} elseif ($method === 'POST' && $uri === '/slopes') {
    $slopeController->store();

// ── POST /stations ─────────────────────────────────────────────
} elseif ($method === 'POST' && $uri === '/stations') {
    $windStationController->store();

// ── PUT /slopes/{id} ─────────────────────────────────────────
} elseif ($method === 'PUT' && preg_match('#^/slopes/(\d+)$#', $uri, $m)) {
    $slopeController->update((int) $m[1]);

// ── PUT /stations/{id} ─────────────────────────────────────────
} elseif ($method === 'PUT' && preg_match('#^/stations/(\d+)$#', $uri, $m)) {
    $windStationController->update((int) $m[1]);

// ── Route inconnue ───────────────────────────────────────────
} else {
    jsonResponse(['success' => false, 'error' => 'Route introuvable.'], 404);
}
