function feedModalBySlope(slopeId) {
    fetch('/api/slopes/desc/'+slopeId)
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            if (!json?.data?.title) return;
            document.getElementById("markerModalLabel").innerHTML= "<span class='label'>Dénomination du site : </span>" + json.data.title;
            document.getElementById("markerModalBody").innerHTML= json.data.html;

            document.getElementById('markerModalShare').innerHTML = "";


            var container = document.getElementById("markerModalShare");
            container.classList.add('share-buttons');

            //ajouter les 2 boutons de partages
            // Bouton Facebook
            const fbBtn = document.createElement('button');
            fbBtn.textContent = 'Partager sur Facebook';
            fbBtn.classList.add('share-btn', 'share-btn-facebook');
            fbBtn.addEventListener('click', () => {
                shareOnFacebook(window.location.href, document.title);
            });

            // Bouton WhatsApp
            const waBtn = document.createElement('button');
            waBtn.textContent = 'Partager sur WhatsApp';
            waBtn.classList.add('share-btn', 'share-btn-whatsapp');
            waBtn.addEventListener('click', () => {
                shareOnWhatsApp(window.location.href, document.title);
            });

            container.appendChild(fbBtn);
            container.appendChild(waBtn);

            console.log(container);


        })
        .catch(() => {});
}

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
    if (speed > 2) color = '#00b1c2';
    if (speed > 5) color = '#00bd92';
    if (speed > 7) color = '#00b95e';
    if (speed > 10) color = '#2fb600';
    if (speed > 12) color = '#96c000';
    if (speed > 15) color = '#b9b000';
    if (speed > 17) color = '#c99200';
    if (speed > 20) color = '#d26700';
    if (speed > 22) color = '#ff5900';
    if (speed > 25) color = '#FF3300';
    if (speed > 27) color = '#FF0080';
    if (speed > 30) color = '#ff00ae';
    if (speed > 32) color = '#ff00fb';
    if (speed > 35) color = '#d000ff';
    if (speed > 37) color = '#aa00ff';
    if (speed > 40) color = '#7b00ff';
    if (speed > 42) color = '#4800ff';
    if (speed > 45) color = '#1100ff';
    return color;
}

function generateWindDirectionSVG(heading,speed) {


    var svg = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">' +
        '<polygon fill="white" stroke="black" stroke-width="0.25" points="18.87,46.68 31.13,46.68 36.14,3.32 13.86,3.32"' + ' transform="rotate(' + (heading) + ',25,25)"'+ '/>' +
        '<polygon fill="' + getWindSpeedColor(speed) + '" stroke="black" stroke-width="0.25"  points="36.14,3.32 35.14,11.99 14.86,11.99 13.86,3.32"' + ' transform="rotate(' + (heading) + ',25,25)"'+ '/>' +
        '<polygon fill="' + getWindSpeedColor(speed) + '" stroke="black" stroke-width="0.25"   points="15.86,20.66 34.14,20.66 33.14,29.34 16.86,29.34"' + ' transform="rotate(' + (heading) + ',25,25)"'+ '/>' +
        '<polygon fill="' + getWindSpeedColor(speed) + '" stroke="black" stroke-width="0.25"   points="32.14,38.01 17.86,38.01 18.87,46.68 31.13,46.68"' + ' transform="rotate(' + (heading) + ',25,25)"'+ '/>' +
        '</svg>';

    return svg;

    /*
#E84723
    var svg = '<svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">' +
        '<polyline points="30,25 20,25 25,0 30,25" fill="none" stroke="black" ' + ' transform="rotate(' + (heading + 180) + ',25,25)"/>' +
        '<polygon points="30,25 20,25 25,0" style="fill:' + getWindSpeedColor(speed) + '" transform="rotate(' + (heading + 180) + ',25,25)"/>' +
        '<circle r="5" cx="25" cy="25" fill="black" />' +
        '</svg>';
    return svg;

     */
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