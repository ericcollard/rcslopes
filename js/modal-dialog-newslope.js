document.addEventListener('DOMContentLoaded', function () {


    // GESTION DU FORMULAIRE ADD SLOPE
    // ***************************

    const newslopeModalEl      = document.getElementById('newslopeModal');
    const newslopeForm         = document.getElementById('newslopeForm');
    const newslopeSubmitBtn    = document.getElementById('newslope_submitBtn');
    const newslopeSubmitSpinner= document.getElementById('newslope_submitSpinner');
    const newslopeFormErrorsBox= document.getElementById('newslopeformErrors');


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
            newslope_slopeGap: formData.get('newslope_slopeGap'),
            newslope_slopePark: formData.get('newslope_slopePark'),
            newslope_slopeAccess: formData.get('newslope_slopeAccess'),
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
            newslope_website: formData.get('newslope_website') // honeypot : doit rester vide côté humain
        };

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
                showFlash('Votre pente a bien été ajouté, un email vous a été envoyé.', 'success');
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
