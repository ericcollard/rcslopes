<!-- Dialogue modal pour les markers -->
<div class="modal fade" id="markerModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title" id="markerModalLabel">Nom de la pente</h1>
                <div class="modal-title-right" id="markerModalShare"></div>
            </div>
            <div class="modal-body" id="markerModalBody">
                <img src="./assets/loading.gif">
            </div>
            <div class="modal-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-6" id="footer-links">
                            <p>Identifiant du site : </p>
                            <p>Lien direct : ####</p>
                        </div>
                        <div class="col-6 buttons-wrapper align-bottom">
                            <a class="btn btn-warning btn-sm mb-2" id="modal-edit-button"
                               href="#" target="_blank" role="button"><i class="bi bi-lock-fill"></i> Editer</a>
                            <button class="btn btn-info btn-sm mb-2" data-bs-target="#commentModal" data-bs-toggle="modal">Ajout commentaire</button>
                            <a class="btn btn-info btn-sm mb-2" id="modal-picture-button"
                               href="#" target="_blank" role="button"><i class="bi bi-images"></i> Ajout photos</a>
                            <button type="button" class="btn btn-primary  btn-sm mb-2" data-bs-dismiss="modal">Fermer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>