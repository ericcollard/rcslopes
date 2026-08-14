document.addEventListener('DOMContentLoaded', function () {


    // GESTION DU FORMULAIRE ADD SLOPE
    // ***************************

    const newslopeModalEl      = document.getElementById('newslopeModal');
    const newslopeForm         = document.getElementById('newslopeForm');
    const newslopeSubmitBtn    = document.getElementById('newslope_submitBtn');
    const newslopeSubmitSpinner= document.getElementById('newslope_submitSpinner');
    const newslopeFormErrorsBox= document.getElementById('newslopeformErrors');

    const slopEditButton = document.getElementById('slope-edit-button');
    slopEditButton.addEventListener("click", EditSlope);


    function EditSlope() {
        slopeId = document.getElementById('slopeId').innerHTML;
        //console.log('click : ', slopeId);

        // Ferme de modal Slope
        markerModal.hide();

        fetch('/api/slopes/'+slopeId)
            .then(r => r.ok ? r.json() : null)
            .then(json => {
                console.log(json.data);
                if (json?.data?.lat) document.getElementById("newslope_lat").value= json.data.lat;
                if (json?.data?.lng) document.getElementById("newslope_lng").value= json.data.lng;
                if (json?.data?.country) document.getElementById("newslope_country").value= json.data.country;
                if (json?.data?.dpt) document.getElementById("newslope_dept").value= json.data.dpt;
                if (json?.data?.name) document.getElementById("newslope_name").value= json.data.name;
                if (json?.data?.type) document.getElementsByName("newslope_type").forEach(el => el.checked = el.value == json.data.type )
                if (json?.data?.slope_size) document.getElementById("newslope_slopeSize").value= json.data.slope_size;
                if (json?.data?.slope_surface) document.getElementById("newslope_slopeSurface").value= json.data.slope_surface;
                if (json?.data?.slope_compatibility) document.getElementById("newslope_slopeCompatibility").value= json.data.slope_compatibility;
                if (json?.data?.slope_motor) document.getElementById("newslope_moulinette").value= json.data.slope_motor;
                if (json?.data?.slope_paragliding) document.getElementById("newslope_paragliding").checked= json.data.slope_paragliding;
                if (json?.data?.slope_gap) document.getElementById("newslope_slopeGap").value= json.data.slope_gap;
                if (json?.data?.slope_park) document.getElementById("newslope_slopePark").value= json.data.slope_park;
                if (json?.data?.slope_car_access) document.getElementById("newslope_slopeAccess").value= json.data.slope_car_access;
                if (json?.data?.slope_season) document.getElementById("newslope_season").value= json.data.slope_season;
                if (json?.data?.slope_plafond) document.getElementById("newslope_plafond").value= json.data.slope_plafond;

                if (json?.data?.aip) document.getElementById("newslope_slopeAIP").value= json.data.aip;
                if (json?.data?.club) document.getElementById("newslope_slopeClub").checked= json.data.club;
                if (json?.data?.cotisation) document.getElementById("newslope_slopeCotisation").checked= json.data.cotisation;
                if (json?.data?.licence) document.getElementById("newslope_slopeLicence").checked= json.data.licence;
                if (json?.data?.url) document.getElementById("newslope_slopeURL").value= json.data.url;
                if (json?.data?.club_name) document.getElementById("newslope_clubName").value= json.data.club_name;

                if (json?.data?.desc_fr_access) document.getElementById("newslope_access").value= strip(json.data.desc_fr_access);
                if (json?.data?.desc_fr_flight) document.getElementById("newslope_flight").value= strip(json.data.desc_fr_flight);
                if (json?.data?.desc_fr_landing) document.getElementById("newslope_landing").value= strip(json.data.desc_fr_landing);
                if (json?.data?.desc_fr) document.getElementById("newslope_slopeInfo").value= strip(json.data.desc_fr);
                if (json?.data?.desc_en) document.getElementById("newslope_slopeInfoEn").value= strip(json.data.desc_en);

                if (json?.data?.orient) {
                    console.log(json.data.orient);
                    json.data.orient.forEach(sec => addSector(sec))
                }
            })
            .catch(() => {});

        // feed modal with current data
        document.getElementById('newslopeModalLabel').innerHTML = "Proposer une modification pour la pente Ref " + slopeId;
        document.getElementById('edit_slopeId').value = slopeId;
        // change the submit behaviour

        // Affiche le modal Newslope
        //var newslopeModalEl = document.getElementById('newslopeModal');
        //var newslopeModal = new bootstrap.Modal(newslopeModalEl);
        newslopeModal.show();
    };


    function strip(html){
        let doc = new DOMParser().parseFromString(html, 'text/html');
        return doc.body.textContent || "";
    }

    // Nettoie les erreurs affichées à chaque nouvelle tentative / ouverture
    function resetErrorsNewslope() {
        newslopeFormErrorsBox.classList.add('d-none');
        newslopeFormErrorsBox.innerHTML = '';
        newslopeForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        newslopeForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    newslopeModalEl.addEventListener('show.bs.modal', resetErrorsNewslope);


    // Affiche les erreurs de validation renvoyées par le serveur
    // errors attendu au format : { email: "message", comment: "message" }
    // ou un tableau de messages génériques : ["message1", "message2"]
    function displayServerErrorsNewslope(errors) {
        if (Array.isArray(errors)) {
            newslopeFormErrorsBox.innerHTML = errors.map(e => `<div>${e}</div>`).join('');
            newslopeFormErrorsBox.classList.remove('d-none');
            return;
        }

        // Objet clé/valeur -> on cible le champ concerné si possible
        let generalMessages = [];
        Object.entries(errors).forEach(([field, message]) => {
            const input      = document.getElementById(field);
            const feedbackEl = document.getElementById(field + '-error');
            if (input && feedbackEl) {
                input.classList.add('is-invalid');
                feedbackEl.textContent = message;
            } else {
                generalMessages.push(message);
            }
        });

        if (generalMessages.length) {
            formErrorsBox.innerHTML = generalMessages.map(e => `<div>${e}</div>`).join('');
            formErrorsBox.classList.remove('d-none');
        }
    }


    newslopeForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        resetErrorsNewslope();

        newslopeSubmitBtn.disabled = true;
        newslopeSubmitSpinner.classList.remove('d-none');

        const formData = new FormData(newslopeForm);


        const payload = {
            newslope_lat: formData.get('newslope_lat'),
            newslope_lng: formData.get('newslope_lng'),
            newslope_type: formData.get('newslope_type'),
            newslope_name: formData.get('newslope_name'),
            newslope_orient: formData.get('newslope_slopeOrientation'),
            newslope_country: formData.get('newslope_country'),
            newslope_dept: formData.get('newslope_dept'),
            newslope_slopeSize: formData.get('newslope_slopeSize'),
            newslope_slopeSurface: formData.get('newslope_slopeSurface'),
            newslope_slopeCompatibility: formData.get('newslope_slopeCompatibility'),
            newslope_moulinette: formData.get('newslope_moulinette'),
            newslope_slopeGap: formData.get('newslope_slopeGap'),
            newslope_slopePark: formData.get('newslope_slopePark'),
            newslope_slopeAccess: formData.get('newslope_slopeAccess'),
            newslope_paragliding: formData.get('newslope_paragliding'),
            newslope_plafond: formData.get('newslope_plafond'),
            newslope_season: formData.get('newslope_season'),
            newslope_access: formData.get('newslope_access'),
            newslope_flight: formData.get('newslope_flight'),
            newslope_landing: formData.get('newslope_landing'),
            newslope_slopeAIP: formData.get('newslope_slopeAIP'),
            newslope_slopeClub: formData.get('newslope_slopeClub'),
            newslope_slopeCotisation: formData.get('newslope_slopeCotisation'),
            newslope_slopeLicence: formData.get('newslope_slopeLicence'),
            newslope_slopeURL: formData.get('newslope_slopeURL'),
            newslope_clubName: formData.get('newslope_clubName'),
            newslope_slopeInfo: formData.get('newslope_slopeInfo'),
            newslope_slopeInfoEn: formData.get('newslope_slopeInfoEn'),
            newslope_email: formData.get('newslope_email'),
            newslope_csrf_token: formData.get('newslope_csrf_token'),
            newslope_form_rendered_at: formData.get('newslope_form_rendered_at'),
            newslope_website: formData.get('newslope_website'), // honeypot : doit rester vide côté humain
            edit_slopeId: formData.get('edit_slopeId') // Rempli si edit d'une pente existante
        };

        // Soumission nouvelle pente
        try {
            console.log(payload);

            const response = await fetch('/api/newslope', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            console.log(data);

            if (response.ok && data.success) {
                // Succès : on ferme le modal, on vide le formulaire, on affiche le flash
                bootstrap.Modal.getInstance(document.getElementById('newslopeModal')).hide();
                newslopeForm.reset();
                showFlash('Votre demande a bien été traitée, un email vous a été envoyé.', 'success');
            } else {
                // Erreur(s) : le modal reste ouvert, on affiche les erreurs
                displayServerErrorsNewslope(data.errors || ['Une erreur est survenue, veuillez réessayer.']);
                showFlash('Le formulaire contient des erreurs.', 'danger');
            }

        } catch (err) {
            displayServerErrorsNewslope(['Erreur réseau, veuillez réessayer.']);
            showFlash('Impossible de contacter le serveur.', 'danger');
        } finally {
            newslopeSubmitBtn.disabled = false;
            newslopeSubmitSpinner.classList.add('d-none');
        }


    });

});



function addSector(sector){
    var orient = document.getElementById('newslope_slopeOrientation').value
    if(document.getElementById(sector).style.display=="none"){
        document.getElementById(sector).style.display = "block"
        orient += " "+sector+" "
        document.getElementById('newslope_slopeOrientation').value = orient
    } else {
        document.getElementById(sector).style.display = "none"
        orient = orient.replace(" "+sector+" ", "");
        document.getElementById('newslope_slopeOrientation').value = orient
    }
}  // ====================== END of addSector =====================================
