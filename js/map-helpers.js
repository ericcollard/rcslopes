function getEditUrl(datatable,id) {

    let currentLocation = window.location;
    if (currentLocation.origin.length > 1)
    {
        return currentLocation.origin + "/admin/table.php?t=" + datatable + "&mode=edit&pk=" + id;
    }
    else
    {
        return "#";
    }
}

function feedModalBySlope(slopeId) {
    fetch('/api/slopes/desc/'+slopeId)
        .then(r => r.ok ? r.json() : null)
        .then(json => {
            if (!json?.data?.title) return;

            let currentLocation = window.location;
            footerLinkHtml = '';
            footerLinkHtml+= '<p>Identifiant du site : '+slopeId+'</p>';
            footerLinkHtml+= '<p>Lien direct : <a href="'+currentLocation.origin+'/'+slopeId+'">'+currentLocation.origin+'/'+slopeId+'</a></p>';

            document.getElementById("markerModalLabel").innerHTML= json.data.title;
            document.getElementById("markerModalBody").innerHTML= json.data.html;
            document.getElementById("footer-links").innerHTML= footerLinkHtml;
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

            // Ajout du lien d'édition
            var linkElement = document.getElementById("modal-edit-button");
            linkElement.href = getEditUrl('slopes',slopeId);

            // Ajout du lien ajout photos
            var pictureElement = document.getElementById("modal-picture-button");
            pictureElement.href = "mailto:rcslopes@finesseplus.org?subject=Photos%20pente%20ref%20"+slopeId+"&body=Bonjour%0AVoici%20les%20photos%20de%20la%20pente%20ref%20%3A%20"+slopeId+"%0A5%20photos%20maxi%20en%201000x600%20pixels.";


            var SlopeIdFormElem = document.getElementById("slopeId");
            SlopeIdFormElem.value = slopeId;

            // Gestion du rating
            const starRating = document.querySelector('.star-rating');
            //const ratingValue = document.getElementById('rating-value');
            const ratingForm = document.getElementById('form_rating');

            starRating.addEventListener('change', async function(e) {
                //ratingValue.textContent = e.target.value;
                //console.log(e.target.value);
                const formData = new FormData(ratingForm);
                //console.log(formData.get('slope_rendered_at'),);

                const payload = {
                    slope_slopeId: slopeId,
                    slope_rating: e.target.value,
                    slope_rendered_at: formData.get('slope_rendered_at'),
                };
                //console.log(payload);


                try {
                    const response = await fetch('/api/rate', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        //console.log("Succès");
                        showFlash('Notation enregistrée, merci.','success')
                    } else {
                        //console.log(data.errors);
                        showFlash('Erreur : ' + data.errors, 'danger')
                    }

                } catch (err) {
                    //console.log(err);
                    showFlash('Erreur : ' + err, 'danger')
                }


            });

        })
        .catch(() => {});
}

function generateWindRoseSVG(sectors,pictures) {
    // Définition des 16 secteurs de la rose des vents dans l'ordre horaire (secteur 1 = N en haut)
    // pictures = true si la pente a des photos
    const allSectors = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
        'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];

    // Paramètres du cercle
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
    var svg ="<svg width='50' height='50' xmlns='http://www.w3.org/2000/svg'>";
    //svg +=   "<circle cx='25' cy='25' r='13' fill='none' stroke='rgb(131, 220, 26)' stroke-width='12'/>";
    if (pictures) {
        svg +=   "<g fill='red' transform=' translate(19 19) scale(20 20)'>";
        svg += "<path d='M0.475 0.163h-0.032l-0.008 -0.025a0.075 0.075 0 0 0 -0.071 -0.05H0.236A0.075 0.075 0 0 0 0.165 0.139l-0.008 0.025H0.125a0.075 0.075 0 0 0 -0.075 0.075v0.2a0.075 0.075 0 0 0 0.075 0.075h0.35a0.075 0.075 0 0 0 0.075 -0.075v-0.2a0.075 0.075 0 0 0 -0.075 -0.076m0.025 0.275a0.025 0.025 0 0 1 -0.025 0.025H0.125a0.025 0.025 0 0 1 -0.025 -0.025v-0.2a0.025 0.025 0 0 1 0.025 -0.025h0.05a0.025 0.025 0 0 0 0.025 -0.017l0.014 -0.041a0.025 0.025 0 0 1 0.024 -0.017h0.128a0.025 0.025 0 0 1 0.024 0.017l0.014 0.041a0.025 0.025 0 0 0 0.023 0.017h0.05a0.025 0.025 0 0 1 0.025 0.025Zm-0.2 -0.225a0.1 0.1 0 1 0 0.1 0.1 0.1 0.1 0 0 0 -0.1 -0.1m0 0.15a0.05 0.05 0 1 1 0.05 -0.05 0.05 0.05 0 0 1 -0.05 0.05'/>";
        svg += "</g>";
        svg +=  "<circle cx='25' cy='25' r='13' fill='none' stroke='red' stroke-width='12' stroke-opacity = '0.6' />";
    }
    else
    {
        svg +=   "<circle cx='25' cy='25' r='13' fill='none' stroke='rgb(131, 220, 26)' stroke-width='12' stroke-opacity = '0.8' />";
    }
    svg += sectorCircles.join('\n\n');
    svg += "</svg>";

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