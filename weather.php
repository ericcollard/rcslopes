<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>RC Slopes – Sites de vol de pente</title>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body, #map {
            height: 100%;
            width: 100vw;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f1923;
            color: #e8edf2;
            /*height: 100dvh;*/
            display: flex;
            flex-direction: column;
        }


    </style>
</head>
<body>


<div id="map"></div>

<script>

    let stations = [
        {latitude : 52.52, longitude :13.41 },
        {latitude : 50.12, longitude :8.68 },
        {latitude : 53.55, longitude :9.99 },
    ];

    var url = 'https://api.open-meteo.com/v1/forecast?';
    url = url + 'latitude=';
    stations.forEach(station => {
        url = url + station.latitude + ',';
    })
    url = url.slice(0, -1);
    url = url + '&longitude=';
    stations.forEach(station => {
        url = url + station.longitude + ',';
    })
    url = url.slice(0, -1);
    url = url + '&hourly=wind_speed_10m,wind_direction_10m&forecast_days=3';
    console.log(url);

    fetch(url)
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            json.forEach(station=>
            {
                console.log(station.latitude, station.longitude);

                let hourlyData = station.hourly;
                let dataArray = [];
                for (let i = 0; i < hourlyData.time.length; i++) {
                    let dataItem = { timeStr : hourlyData.time[i], windSpeed : hourlyData.wind_speed_10m[i], windDirection : hourlyData.wind_direction_10m[i] };
                    dataArray.push(dataItem);

                }
                console.log(dataArray);

            })
        }
        )
        .catch(() => {}); // pas de sites : carte seule



</script>
</body>
</html>
