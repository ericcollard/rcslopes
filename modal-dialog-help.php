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

                <div class="divider-help">
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

                <div class="divider-help">
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
                        <h3>Partager une pente</h3>
                    </div>
                    <div class="col-9 mb-3">
                        Vous pouvez partager une pente sur Facebook ou Whatsapp via les boutons situés dans l'entête de chaque fiche de pente.
                        <br/>Pour tout autre partage, ou pour arriver directement sur une fiche de pente précise, vous pouvez copier le lien
                        direct en bas à droite de chaque fiche ("Lien direct") juste avant le pied de page de chaque fiche.
                    </div>
                </div>

                <div class="divider-help">
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

                            if ($slopes and count($slopes) > 0) {
                                foreach ((array) $slopes as $slope ) {
                                    echo "<tr>";
                                    echo "<td>".$slope['slopeId']."</td>";
                                    echo "<td>".$slope['name']."</td>";
                                    echo "<td>".substr($slope['addBy'],0,10)."xxxx</td>";
                                    $created = new \DateTime($slope['created_at']);
                                    echo "<td>".date_format($created,'d/m/Y')."</td>";
                                    echo "</tr>";
                                }
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
                                    <th>Commentaire</th>
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
                                    echo "<td>".substr(strip_tags($comment['comment']),0,15)."...</td>";
                                    echo "<td>".substr($comment['email'],0,10)."xxx</td>";
                                    $created = new \DateTime($comment['created_at']);
                                    echo "<td>".date_format($created,'d/m/Y')."</td>";
                                    echo "</tr>";
                                }

                                ?>
                            </table>
                        </div>
                    </div>
                    <div class="col-12">
                        <h4>Dernières modifications</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th>Id Pente</th>
                                    <th>Nom</th>
                                    <th>Mise à jour</th>
                                    <th>Date</th>
                                </tr>
                                <?php
                                use models\Slopes;
                                require_once __DIR__ . '/models/Slope.php';
                                $updates = Slope::getlastUpdate();

                                $serverName = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']
                                    === 'on' ? "https" : "http") .
                                    "://" . $_SERVER['HTTP_HOST'];

                                foreach ((array) $updates as $update ) {
                                    echo "<tr>";
                                    echo "<td>".$update['slopeId']."</td>";
                                    echo "<td><a href='".$serverName."/".$update['slopeId']."'>".$update['name']."</a></td>";
                                    echo "<td>".$update['source']."</td>";
                                    $created = new \DateTime($update['updated_at']);
                                    echo "<td>".date_format($created,'d/m/Y')."</td>";
                                    echo "</tr>";
                                }

                                ?>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="divider-help">
                    <div class="divider-fade"></div>
                </div>

                <div class="row">
                    <h2>Nouveautés (v2.0)</h2>
                    <div class="col-12">
                        <ul>
                            <li>Ouverture par défaut sur fond de carte Carto (et non GPS … pour alléger le chargement en zone de faible couverture 4G)</li>
                            <li>Parkings ont été mis dans une couche à part (non sélectionnée par défaut) pour ne pas surcharger l'affichage</li>
                            <li>Nouvelles icônes de pente indiquant les orientations de vent fonctionnelles quelque soit le niveau de zoom</li>
                            <li>Nouveau bouton (en dessous du zoom) qui permet de voir où se situe l'utilisateur et de centrer la carte autour de lui</li>
                            <li>Ajout d'une couche « Vent » présentant le vent en temps réel sur tous les anémomètres du réseau Windbird (cache 5mn)</li>
                            <li>Il est possible (administrateur) d'ajouter manuellement des sites d'observation vent temps réel hors windbird (holfuy etc.)</li>
                            <li>Résolution des bugs d'affichages en zoom max</li>
                            <li>Accès direct à une pente donnée, via un lien URL. Exemple : <a href="https://rcslopes.windfoilfan.com/59">https://rcslopes.windfoilfan.com/59</a> pour la pente de corps</li>
                            <li>Refonte de la fiche pente avec
                                <ul>
                                    <li>Le lien d'accès direct</li>
                                    <li>Le département affiché à côté du nom</li>
                                    <li>Possibilité d'illustrer avec un carrousel de photos</li>
                                    <li>Nouvelle affichage horaire de la météo à 3 jours</li>
                                    <li>Enrichissement des données (Nom du club gestionnaire, typologie de planeurs, Accès au trou, type de route d'accès etc.)</li>
                                    <li>Boutons de partage Facebook et WhatsApp (balises OG renseignées automatiquement)</li>
                                    <li>Accès direct à l'interface d'administration depuis la fiche de pente</li>
                                </ul>
                            </li>
                            <li>Refonte de la recherche de pente qui se fait par une partie du nom, ou par le département</li>
                            <li>Refonte du système de commentaire
                                <ul>
                                    <li>Tout commentaire apparaît immédiatement statut « new », avec envoi d'une confirmation à l'utilisateur, et d'une copie à l'administrateur (modérateur), qui pourra l'approuver, le rejeter, ou l'intégrer à la description générale du site</li>
                                    <li>Pour éviter les attaques, un utilisateur ne peut pas publier plus de 3 commentaires non approuvés</li>
                                    <li>Les commentaires approuvés apparaissent sous forme de liste horodatée sur la page de la pente</li>
                                </ul>
                            </li>
                            <li>Refonte de l'ajout de nouvelle pente
                                <ul>
                                    <li>Formulaire plus complet, avec les nouveaux champs (Nom du club gestionnaire, typologie de planeurs, Accès au trou, type de route d'accès etc.)</li>
                                    <li>Envoi d'une confirmation à l'utilisateur, et d'une copie à l'administrateur (modérateur), qui pourra l'approuver (la pente n'apparaît pas tant qu'elle n'est pas approuvée, mais le « soumetteur » peut la visualiser avec sa référence directe)</li>
                                    <li>Pour éviter les attaques, un utilisateur ne peut pas soumettre plus de 3 pentes avant acceptation par un administrateur</li>
                                </ul>
                            </li>
                            <li>Nouvelle interface d'administration permettant
                                <ul>
                                    <li>De modérer les ajouts de pente / commentaires</li>
                                    <li>De modifier les descriptions avec un contenu enrichi (html)</li>
                                    <li>De gérer la bibliothèque de photos</li>
                                    <li>De gérer les droits des administrateurs / modérateurs</li>
                                </ul>
                            </li>
                            <li>Visualisation publique des commentaires et pentes en attente de modération à la fin de la page d'aide</li>
                            <li>V2.1 > Ajout d'une échelle sur la carte</li>
                            <li>V2.1 > Blocage du zoom max pour éviter un affichage dégradé</li>
                            <li>V2.1 > Ajout de l'option "Planeur motorisé" dans la description d'une pente</li>
                            <li>V2.1 > Fond de carte OpenTopoMap backup par défaut (suite pb du server OpenTopoMap)</li>
                            <li>V2.1 > Nouveau menu spécifique pour l'aide aux ajouts / commentaires</li>
                            <li>V2.1 > Pré-remplissage du mail pour l'ajout de photos</li>
                            <li>V2.1 > Comptage des vues sues sur 1 an glissant</li>
                            <li>V2.1 > Possibilité de noter les pentes (rating)</li>
                            <li>V2.1 > Identification en rouge des pentes avec photos</li>
                        </ul>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>