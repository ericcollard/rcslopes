document.addEventListener('DOMContentLoaded', function () {

    // GESTION DU FORMULAIRE COMMENT
    // ***************************
    var modalEl      = document.getElementById('commentModal');
    const form         = document.getElementById('commentForm');
    const submitBtn    = document.getElementById('submitBtn');
    const submitSpinner= document.getElementById('submitSpinner');
    const formErrorsBox= document.getElementById('formErrors');

    // Nettoie les erreurs affichées à chaque nouvelle tentative / ouverture
    function resetErrors() {
        formErrorsBox.classList.add('d-none');
        formErrorsBox.innerHTML = '';
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    modalEl.addEventListener('show.bs.modal', resetErrors);

    // Affiche les erreurs de validation renvoyées par le serveur
    // errors attendu au format : { email: "message", comment: "message" }
    // ou un tableau de messages génériques : ["message1", "message2"]
    function displayServerErrors(errors) {
        if (Array.isArray(errors)) {
            formErrorsBox.innerHTML = errors.map(e => `<div>${e}</div>`).join('');
            formErrorsBox.classList.remove('d-none');
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

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        resetErrors();

        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');

        const formData = new FormData(form);
        const payload = {
            email: formData.get('email'),
            comment: formData.get('comment'),
            slopeId: formData.get('slopeId'),
            csrf_token: formData.get('csrf_token'),
            form_rendered_at: formData.get('form_rendered_at'),
            website: formData.get('website') // honeypot : doit rester vide côté humain
        };

        try {
            const response = await fetch('/api/comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Succès : on ferme le modal, on vide le formulaire, on affiche le flash
                bootstrap.Modal.getInstance(document.getElementById('commentModal')).hide();
                form.reset();
                showFlash('Votre commentaire a bien été ajouté.', 'success');
            } else {
                // Erreur(s) : le modal reste ouvert, on affiche les erreurs
                displayServerErrors(data.errors || ['Une erreur est survenue, veuillez réessayer.']);
                showFlash('Le formulaire contient des erreurs.', 'danger');
            }

        } catch (err) {
            displayServerErrors(['Erreur réseau, veuillez réessayer.']);
            showFlash('Impossible de contacter le serveur.', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitSpinner.classList.add('d-none');
        }
    });

});