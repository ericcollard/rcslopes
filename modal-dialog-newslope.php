<!-- Dialogue modal pour un ajout de nouvelle pente -->
<div class="modal fade" id="newslopeModal" aria-hidden="true" aria-labelledby="newslopeModalLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">

        <div class="modal-content">
            <form id="newslopeForm" novalidate>
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="newslopeModalLabel">Ajouter un élément</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Zone d'erreurs générales (renvoyées par le POST) -->
                    <div id="newslopeformErrors" class="alert alert-danger d-none"></div>

                    <!-- Token CSRF -->
                    <input type="hidden" id="newslope_csrf_token" name="newslope_csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">

                    <!-- Horodatage d'affichage du formulaire (détection de soumission trop rapide) -->
                    <input type="hidden" id="newslope_form_rendered_at" name="newslope_form_rendered_at" value="<?= $formRenderedAt ?>">

                    <!-- Honeypot : champ piège, doit rester vide. Les bots le remplissent souvent. -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Ne pas remplir ce champ</label>
                        <input type="text" id="newslope_website" name="newslope_website" tabindex="-1" autocomplete="off">
                    </div>


                    <div class="row">
                        <!-- Longitude et lattitude. -->
                        <div class="col-3 mb-3">
                            <label for="newslope_lat" class="form-label">Lattitude*</label>
                            <input type="text" class="form-control" id="newslope_lat" name="newslope_lat" required>
                            <div class="invalid-feedback" id="newslope_lat-error"></div>
                        </div>
                        <div class="col-3 mb-3">
                            <label for="newslope_lng" class="form-label">Longitude*</label>
                            <input type="text" class="form-control" id="newslope_lng" name="newslope_lng" required>
                            <div class="invalid-feedback" id="newslope_lng-error"></div>
                        </div>
                        <!-- pays et département -->
                        <div class="col-3 mb-3">
                            <label for="newslope_country" class="form-label">Pays*</label>
                            <input type="text" class="form-control" id="newslope_country" name="newslope_country" required>
                            <div class="invalid-feedback" id="newslope_country-error"></div>
                        </div>
                        <div class="col-3 mb-3">
                            <label for="newslope_dept" class="form-label">Départment*</label>
                            <input type="text" class="form-control" id="newslope_dept" name="newslope_dept" required>
                            <div class="invalid-feedback" id="newslope_dept-error"></div>
                        </div>
                    </div>

                    <div class="divider">
                        <div class="divider-fade"></div>
                    </div>


                    <!-- Type (pente, iterdit, parking) -->
                    <div class="row">
                        <div class="col-6">
                            <!-- Nom du site -->
                            <div class="mb-3">
                                <label for="newslope_name" class="form-label">Nom*</label>
                                <input type="text" class="form-control me-3" id="newslope_name" name="newslope_name" required>
                                <div class="invalid-feedback" id="newslope_name-error"></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="newslope_type">Type d'élément*</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="newslope_type"
                                           id="newslope_type_pente" value="pente" checked>
                                    <label class="form-check-label" for="newslope_type_pente">
                                        Pente
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="newslope_type"
                                           id="newslope_type_interdit" value="interdit" >
                                    <label class="form-check-label" for="newslope_type_interdit">
                                        Vol interdit
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="newslope_type"
                                           id="newslope_type_parking" value="parking" >
                                    <label class="form-check-label" for="newslope_type_parking">
                                        Parking
                                    </label>
                                </div>
                        </div>
                    </div>

                    <div class="divider">
                        <div class="divider-fade"></div>
                    </div>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <label>Orientation de la pente</label>
                                    <div class='sector'>
                                        <img class='sec_dir' src='/assets/bgrd.png?v=2' style='opacity:0.5'>
                                        <img class='sec_dir sec_cntr' src='/assets/center.png'>
                                        <img class='sec_dir sec_cntr' src='/assets/sectorsW4.png' usemap='#sectors'>
                                        <img id='N' class='sec_dir sec_child' src='/assets/N.png' style='display:none'>
                                        <img id ='NNE' class='sec_dir sec_child' src='/assets/NNE.png' style='display:none'>
                                        <img id ='NE' class='sec_dir sec_child' src='/assets/NE.png' style='display:none'>
                                        <img id='ENE' class='sec_dir sec_child' src='/assets/ENE.png' style='display:none'>
                                        <img id='E' class='sec_dir sec_child' src='/assets/E.png' style='display:none'>
                                        <img id='ESE' class='sec_dir sec_child' src='/assets/ESE.png' style='display:none'>
                                        <img id='SE' class='sec_dir sec_child' src='/assets/SE.png' style='display:none'>
                                        <img id='SSE' class='sec_dir sec_child' src='/assets/SSE.png' style='display:none'>
                                        <img id='S' class='sec_dir sec_child' src='/assets/S.png' style='display:none'>
                                        <img id='SSW' class='sec_dir sec_child' src='/assets/SSW.png' style='display:none'>
                                        <img id='SW' class='sec_dir sec_child' src='/assets/SW.png' style='display:none'>
                                        <img id='WSW' class='sec_dir sec_child' src='/assets/WSW.png' style='display:none'>
                                        <img id='W' class='sec_dir sec_child' src='/assets/W.png' style='display:none'>
                                        <img id='WNW' class='sec_dir sec_child' src='/assets/WNW.png' style='display:none'>
                                        <img id='NW' class='sec_dir sec_child' src='/assets/NW.png' style='display:none'>
                                        <img id='NNW' class='sec_dir sec_child' src='/assets/NNW.png' style='display:none'>
                                    </div>
                                    <map name="sectors">
                                        <area shape="poly" coords="150,150,122,14,177,11" alt="N" onclick="addSector('N')">
                                        <area shape="poly" coords="150,150,177,11,228,34" alt="NNE" onclick="addSector('NNE')">
                                        <area shape="poly" coords="150,150,228,34,267,72" alt="NE" onclick="addSector('NE')">
                                        <area shape="poly" coords="150,150,267,72,286,121" alt="ENE" onclick="addSector('ENE')">
                                        <area shape="poly" coords="150,150,286,121,286,177" alt="E" onclick="addSector('E')">
                                        <area shape="poly" coords="150,150,286,177,265,230" alt="ESE" onclick="addSector('ESE')">
                                        <area shape="poly" coords="150,150,265,230,228,268" alt="SE" onclick="addSector('SE')">
                                        <area shape="poly" coords="150,150,228,268,177,287" alt="SSE" onclick="addSector('SSE')">
                                        <area shape="poly" coords="150,150,177,287,123,287" alt="S" onclick="addSector('S')">
                                        <area shape="poly" coords="150,150,123,287,72,267" alt="SSW" onclick="addSector('SSW')">
                                        <area shape="poly" coords="150,150,72,267,34,229" alt="SW" onclick="addSector('SW')">
                                        <area shape="poly" coords="150,150,34,229,13,178" alt="WSW" onclick="addSector('WSW')">
                                        <area shape="poly" coords="150,150,13,178,13,124" alt="W" onclick="addSector('W')">
                                        <area shape="poly" coords="150,150,13,124,33,72" alt="WNW" onclick="addSector('WNW')">
                                        <area shape="poly" coords="150,150,33,72,72,34" alt="NW" onclick="addSector('NW')">
                                        <area shape="poly" coords="150,150,72,34,121,13" alt="NNW" onclick="addSector('NNW')">
                                    </map>
                                    <input class="addOrient" type="text" name="newslope_slopeOrientation" id="newslope_slopeOrientation" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                                <label for="newslope_slopeSize">Hauteur de la pente</label>
                                <select class="form-control mb-3" id="newslope_slopeSize" name="newslope_slopeSize">
                                    <option value="Petite pente">Petite pente</option>
                                    <option value="Moyenne pente">Moyenne pente</option>
                                    <option value="Grande pente">Grande pente</option>
                                </select>

                                <label for="newslope_slopeType">Surface posé</label>
                                <select class="form-control mb-3" id="newslope_slopeSurface" name="newslope_slopeSurface">
                                    <option value="Herbe">Herbe</option>
                                    <option value="Cailloux">Cailloux</option>
                                    <option value="Herbe et cailloux">Herbe et cailloux</option>
                                </select>

                                <label for="newslope_slopeType">Comptabilité modèles</label>
                                <select class="form-control mb-3" id="newslope_slopeCompatibility" name="newslope_slopeCompatibility">
                                    <option value="Mousse (posé peu accueillant)">Mousse (posé peu accueillant)</option>
                                    <option value="Petit & moyen modèle (1 et 2m)">Petit & moyen modèle (1 et 2m)</option>
                                    <option value="Grande plume (>3m)">Grande plume (>3m)</option>
                                </select>


                        </div>
                        <div class="col-4">
                            <label for="newslope_slopeGap">Accès au trou</label>
                            <select class="form-control mb-3" id="newslope_slopeGap" name="newslope_slopeGap">
                                <option value="Facile">Facile</option>
                                <option value="Sportif">Sportif</option>
                                <option value="Impossible">Impossible</option>
                            </select>

                            <label for="newslope_slopePark">Accès depuis stationnement</label>
                            <select class="form-control mb-3" id="newslope_slopePark" name="newslope_slopePark">
                                <option value="proximité immédiate">Proximité immédiate</option>
                                <option value="moins de 30 minutes de marche">- de 30mn de marche</option>
                                <option value="30 à 60 minutes de marche">30 à 60mn de marche</option>
                                <option value="plus de 60 minutes de marche">+ de 60mn de marche</option>
                            </select>

                            <label for="newslope_slopeAccess">Accès véhicule au stationnement</label>
                            <select class="form-control mb-3" id="newslope_slopeAccess" name="newslope_slopeAccess">
                                <option value="Route">Route</option>
                                <option value="Chemin court - tout véhicule">Chemin court tout véhicule</option>
                                <option value="Piste caillouteuse, moins de 1km">Piste caillouteuse - de 1km</option>
                                <option value="piste longue">Piste caillouteuse + de 1km</option>
                                <option value="4x4">Véhicule tout terrain recommandé</option>
                            </select>

                        </div>
                    </div>


                    <div class="divider">
                        <div class="divider-fade"></div>
                    </div>


                    <div class="row">
                        <div class="col-4">
                            <label for="newslope_slopeAIP">N° AIP (si connu/existant)</label>
                            <input type="text" name="newslope_slopeAIP" id="newslope_slopeAIP" class="form-control">
                            <div class="invalid-feedback" id="newslope_slopeAIP-error"></div>
                        </div>
                        <div class="col-4">
                            <div class="checkbox mb-2">
                                <label><input type="checkbox" id="newslope_slopeClub" name="newslope_slopeClub"> Pente gérée par un club</label>
                            </div>

                            <div class="checkbox mb-2">
                                <label><input type="checkbox" id="newslope_slopeCotisation" name="newslope_slopeCotisation"> Cotisation club nécessaire</label>
                            </div>

                            <div class="checkbox">
                                <label><input type="checkbox" id="newslope_slopeLicence" name="newslope_slopeLicence"> Licence FFAM nécessaire</label>
                            </div>
                        </div>
                        <div class="col-4">
                            <label for="slopeURL">URL du club / pente</label>
                            <input type="text" name="newslope_slopeURL" id="newslope_slopeURL" class="form-control mb-2">
                            <div class="invalid-feedback" id="newslope_slopeURL-error"></div>

                            <label for="newslope_clubName">Nom du club gestionnaire</label>
                            <input type="text" name="newslope_clubName" id="newslope_clubName" class="form-control">
                            <div class="invalid-feedback" id="newslope_clubName-error"></div>
                        </div>
                    </div>


                    <div class="divider">
                        <div class="divider-fade"></div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label for="newslope_slopeInfo">Description et photos</label>
                            <textarea class="form-control" rows="3" id="newslope_slopeInfo" name="newslope_slopeInfo"></textarea>
                            <div class="invalid-feedback" id="newslope_slopeInfo-error"></div>

                            <label for="newslope_slopeInfoEn">Description (anglais)</label>
                            <textarea class="form-control" rows="3" id="newslope_slopeInfoEn" name="newslope_slopeInfoEn"></textarea>
                            <div class="invalid-feedback" id="newslope_slopeInfoEn-error"></div>

                            <label for="newslope_email">Email*</label>
                            <textarea class="form-control" rows="3" id="newslope_email" name="newslope_email"  required></textarea>
                            <div class="invalid-feedback" id="newslope_email-error"></div>

                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="newslope_submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="newslope_submitSpinner"></span>
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>