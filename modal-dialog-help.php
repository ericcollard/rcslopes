<!-- Dialogue modal pour les légendes OpenWindMap -->
<div class="modal fade" id="OpenWindMapModal" tabindex="-1" aria-labelledby="OpenWindMapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content markerModal">
            <div class="modal-header">
                <h1 class="modal-title" id="OpenWindMapModalLabel">Utilisation de RcSlopes</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <h2>Légende vent</h2>
                    <div class="col-6">
                        <p>Pour visualiser les données vent en temps réel, activer la couche "Vent" sur la carte.</p>
                        <p>Les données sont fournies par le Réseau Opendata Windbird</p>
                        <p>Dernières données : <span id="lastupdate"></span></p>
                    </div>
                    <div class="col-6">
                        <p>Légende des vitesse de vent : </p>
                        <div class="wind-legend" id="windLegend">
                            <div class="gauge-container" id="gaugeContainer"></div>
                            <div class="labels">
                                <span>0</span>
                                <span>15</span>
                                <span>30</span>
                                <span>45</span>
                                <span>60 km/h</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider">
                    <div class="divider-fade"></div>
                </div>

                <div class="row">
                    <h2>Avertissement</h2>
                    <div class="col-3">
                        <p>En utilisant la carte des pentes vous acceptez les conditions d’utilisation de RC-Slopes énoncées ci-contre</p>
                    </div>
                    <div class="col-9 mb-3">
                        <ul>
                            <li>Le contenu RC Slopes est communiqué à titre informatif. Il est destiné aux aéromodélistes qui disposent ainsi d’une information sur les sites de vol de pente.</li>
                            <li>RC Slopes est un site d’intérêt pour la communauté aéromodéliste. Sa vocation est d’être participative. En cas de différences constatées dans le contenu de l’information proposée, merci de nous le faire savoir afin de mettre à jour le site.</li>
                            <li>L’utilisation des pentes décrites dans le module RC Slopes n’exonère pas leurs utilisateurs des règles de courtoisie minimales : contact avec le gestionnaire ou le propriétaire du site, club, particulier ou collectivité pour en demander les conditions d’accès avant utilisation. Pour la pérennité de notre loisir, il est indispensable que chacun se conforme aux règles et ai un comportement responsable et respectueux.</li>
                            <li>L’utilisation des pentes décrites dans le module RC Slopes n’exonère pas l’utilisateur de consulter l’information aéronautique en vigueur : carte OACI, carte « drones », zones très basse altitude actives, NOTAM….</li>
                            <li>Finesse + ne peut voir en aucun cas sa responsabilité engagée par suite de l’utilisation ou de l’interprétation des informations communiquées sur son site.</li>
                        </ul>
                    </div>
                </div>

                <div class="divider">
                    <div class="divider-fade"></div>
                </div>


                <div class="row">
                    <h2>Aide</h2>
                    <div class="col-3">
                        <h3>Chercher une pente</h3>

                    </div>
                    <div class="col-9 mb-3">
                        En haut à droite de la carte, vous trouverez une loupe. En cliquant sur celle-ci vous ferez apparaitre un champ
                        dans lequel vous pourrez taper votre requête. Vous n'êtes pas obligé de taper le nom en entier de la pente : les premières lettres
                        sont suffisantes. Si toutefois vous ne trouvez pas la pente, vous pouvez faire une recherche par n° de département,
                        toutes les pentes du département seront affichées.<br>
                        <i>Note: seul les 5 premières pentes sont affichées, vous pouvez utiliser le petit ascenseur pour voir les suivantes.</i><br>
                    </div>

                    <div class="col-3">
                        <h3>Centrer sur l'utilisateur</h3>

                    </div>
                    <div class="col-9 mb-3">
                        En cliquant sur la cible située sous les boutons Zoom + et Zoom -, la carte va zoomer et se centrer autour de votre
                        position. Votre position s'affichera avec une icône rouge.
                        <br/>Attention : cette fonctionnalité ne fonctionne que si la localisation est activée dans votre navigateur.
                    </div>

                    <div class="col-3">
                        <h3>Modifier une pente</h3>
                    </div>
                    <div class="col-9 mb-3">
                        En cliquant sur le bouton "Commenter" qui est affiché sur chaque fiche descriptive de pente, vous pouvez nous faire part
                        d'une modification que vous souhaitez apporter ou d'une précision que vous souhaitez nous apporter.
                        <br>Remplissez le champ commentaire, indiquez votre adresse email et cliquez sur 'envoyer'... nous nous occupons du reste.
                        N'hésitez donc pas !
                    </div>

                    <div class="col-3">
                        <h3>Photos</h3>
                    </div>
                    <div class="col-9 mb-3">
                        Il est désormais possible d'ajouter des photos de description pour chaque pente. Pour des questions de sécurité,
                        l'ajout est réservé aux administrateurs.
                        L'objectif est de proposer un maximum de 5 photos par pente, permettant d'illustrer la zone de vol,
                        la zone d'atterrissage, et l'accès.
                        <br>Pour soumettre des photos, envoyer un email à l'administrateur avec vos photos. Le titre du mail doit être :
                        "Photos pente ref #XXX" ou #XXX est le numéro identifiant la pente. Vos photos doivent être impérativement au format
                        jpeg, avec des dimensions exactes de 1000x600px. Les photos de 5Go ou de dimensions fantaisistes seront ignorées.
                    </div>

                    <div class="col-3">
                        <h3>Ajouter une pente</h3>
                    </div>
                    <div class="col-9 mb-3">
                        Si vous désirez soumettre une nouvelle pente, zoomez sur la carte pour donner un point GPS avec un maximum de précision.
                        Double-cliquez sur la position voulue, puis acceptez la position GPS proposée.
                        <br>Vous pouvez alors remplir tous les champs pour donner un maximum d'information sur la nouvelle pente.
                    </div>

                    <div class="col-3">
                        <h3>Partager une pente</h3>
                    </div>
                    <div class="col-9 mb-3">
                        Vous pouvez partager une pente sur Facebook ou Whatsapp via les boutons situés dans l'entête de chaque fiche de pente.
                        <br/>Pour tout autre partage, ou pour arriver directement sur une fiche de pente précise, vous pouvez copier le lien
                        direct en bas à droite de chaque fiche ("Lien direct") juste avant le pied de pachge de chaque fiche.
                    </div>
                </div>

                <div class="divider">
                    <div class="divider-fade"></div>
                </div>

                <div class="row">
                    <h2>Modération en attente</h2>
                    <div class="col-6">
                        <h4>Nouvelles pentes</h4>
                        <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>Id</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Date</th>
                            </tr>
                            <?php
                            use models\Slope;
                            require_once __DIR__ . '/models/Slope.php';
                            $slopes = Slope::getUnderReview();

                            foreach ((array) $slopes as $slope ) {
                                echo "<tr>";
                                echo "<td>".$slope['slopeId']."</td>";
                                echo "<td>".$slope['name']."</td>";
                                echo "<td>".$slope['addBy']."</td>";
                                $created = new \DateTime($slope['created_at']);
                                echo "<td>".date_format($created,'d/m/Y')."</td>";
                                echo "</tr>";
                            }

                            ?>
                        </table>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4>Commentaires</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th>Id Pente</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Date</th>
                                </tr>
                                <?php
                                use models\Comment;
                                require_once __DIR__ . '/models/Comment.php';
                                $comments = Comment::getUnderReview();

                                foreach ((array) $comments as $comment ) {
                                    echo "<tr>";
                                    echo "<td>".$comment['slopeId']."</td>";
                                    echo "<td>".substr(strip_tags($comment['comment']),10)."...</td>";
                                    echo "<td>".$comment['addBy']."</td>";
                                    $created = new \DateTime($comment['created_at']);
                                    echo "<td>".date_format($created,'d/m/Y')."</td>";
                                    echo "</tr>";
                                }

                                ?>
                            </table>
                        </div>
                    </div>

                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>