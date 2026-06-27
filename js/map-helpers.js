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