

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
                    marker.on('click', function () {
                        feedModalBySlope(slopeId);
                        markerModal.show();
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
                var svgIcon = generateWindDirectionSVG(wind_heading,wind_speed_avg);
                // Encoder le SVG en Data URI
                var svgUrl = 'data:image/svg+xml;base64,' + btoa(svgIcon);

                // Créer l'icône Leaflet avec le SVG
                var wind_pnt = L.icon({
                    iconUrl: svgUrl,
                    iconSize: [30, 30],      // Taille de l'icône
                    iconAnchor: [15, 15],    // Point d'ancrage (centre)
                    popupAnchor: [0, -15]    // Position du popup par rapport à l'icône
                });

                const popup = `
                <h2>Station Windbird #${station_id}</h2>
                <hr>
                <p class="leaflet-popup-meanwind">Vent moyen : ${wind_speed_avg} km/h</p>
                <p class="leaflet-popup-minmaxwind">( Mini: ${wind_speed_min}   -   Maxi : ${wind_speed_max} )</p>
                <p class="leaflet-popup-date">Date : ${wind_measurement_date}</p>
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


// va à la pente demandée si une demande est faite dans l'URL
let currentLocation = window.location;
var requestedSlopeId = 0;
if (currentLocation.pathname.length > 1)
{
    // le pathname contient plus que /
    requestedSlopeId = parseInt(currentLocation.pathname.substring(1));
}
if (requestedSlopeId > 0)
{
    //console.log('Déplacement vers la pente demandée');
    fetch('/api/slopes/'+requestedSlopeId)
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            //console.log(json.data);
            if (!json?.data?.lat) return;
            map.flyTo([json.data.lat, json.data.lng],14);
            feedModalBySlope(requestedSlopeId);
            markerModal.show();
            //document.getElementById("markerModalLabel").innerHTML= "<span class='label'>Dénomination du site : </span>" + json.data.title;
            //document.getElementById("markerModalBody").innerHTML= json.data.html;
        })
        .catch(() => {});
}

function shareOnFacebook(url, quote = '') {
    const shareUrl = new URL('https://www.facebook.com/sharer/sharer.php');
    shareUrl.searchParams.set('u', url);
    if (quote) shareUrl.searchParams.set('quote', quote);

    openSharePopup(shareUrl.toString());
}

function shareOnWhatsApp(url, text = '') {
    // Sur mobile, l'app s'ouvre directement ; sur desktop, WhatsApp Web s'ouvre
    const message = text ? `${text} ${url}` : url;
    const shareUrl = new URL('https://wa.me/');
    shareUrl.searchParams.set('text', message);

    openSharePopup(shareUrl.toString());
}

function openSharePopup(shareUrl) {
    const width = 600;
    const height = 500;
    const left = (window.innerWidth - width) / 2;
    const top = (window.innerHeight - height) / 2;

    window.open(
        shareUrl,
        'share-dialog',
        `width=${width},height=${height},top=${top},left=${left},toolbar=0,location=0,menubar=0`
    );
}