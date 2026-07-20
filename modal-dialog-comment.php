<!-- Dialogue modal pour les commentaires -->
<div class="modal fade" id="commentModal" aria-hidden="true" aria-labelledby="commentModalLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">
            <form id="commentForm" novalidate>
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="commentModalLabel">Ajouter un commentaire</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Zone d'erreurs générales (renvoyées par le POST) -->
                    <div id="formErrors" class="alert alert-danger d-none"></div>
                    <input type="hidden" id="slopeId" name="slopeId">


                    <!-- Token CSRF -->
                    <input type="hidden" id="csrf_token" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">

                    <!-- Horodatage d'affichage du formulaire (détection de soumission trop rapide) -->
                    <input type="hidden" id="form_rendered_at" name="form_rendered_at" value="<?= $formRenderedAt ?>">

                    <!-- Honeypot : champ piège, doit rester vide. Les bots le remplissent souvent. -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Ne pas remplir ce champ</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>



                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <div class="invalid-feedback" id="email-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Commentaire</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                        <div class="invalid-feedback" id="comment-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>