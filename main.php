<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta property="og:url" content="<?= $fullUrl ?>" />
    <meta property="og:type"          content="website" />
    <meta property="og:title"         content="<?= $og_title ?>" />
    <meta property="og:description"   content="<?= $og_description ?>" />
    <meta property="og:image"         content="<?= $og_image ?>" />
    <title>RC Slopes – Sites de vol de pente</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/leaflet-gps.css" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/weather.css" />
</head>
<body  class="rc-body">

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark rc-navbar">
    <div class="container-fluid rc-navbar-inner">

        <!-- Logo + titre -->
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="/assets/logo.svg" alt="Logo RcSlopes"/>
            <p class="site-name">
                <span >RC-SlopeS</span></br>
                <span class="site-by">by Finesse+</span>
            </p>

        </a>

        <!-- Groupe droit mobile : loupe + burger, toujours ensemble -->
        <div class="d-flex align-items-center ms-auto rc-mobile-actions">

            <button class="btn btn-link text-light d-lg-none rc-search-btn"
                    type="button" data-bs-toggle="collapse" data-bs-target="#searchMobile"
                    aria-controls="searchMobile" aria-label="Rechercher">
                <i class="bi bi-search"></i>
            </button>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarContent" aria-controls="navbarContent"
                    aria-expanded="false" aria-label="Afficher/masquer le menu">
                <span class="navbar-toggler-icon"></span>
            </button>

        </div>

        <!-- Contenu repliable (desktop : lien + recherche / mobile : lien seul) -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <button type="button" class="btn btn-link nav-link" data-bs-toggle="modal" data-bs-target="#OpenWindMapModal">
                        Aide
                    </button>
                </li>

                <li class="nav-item d-none d-lg-block ms-lg-3">
                    <form class="position-relative rc-slope-search" role="search" autocomplete="off">
                        <input type="search" class="form-control rc-search-input ps-3 pe-5"
                               placeholder="Rechercher" aria-label="Rechercher"
                               id="slopeSearchInput"
                               autocomplete="off"
                               aria-expanded="false"
                               aria-controls="slopeSearchResults"
                               role="combobox">
                        <i class="bi bi-search rc-search-icon"></i>

                        <ul class="dropdown-menu rc-search-dropdown w-100" id="slopeSearchResults" role="listbox"></ul>
                    </form>
                </li>

            </ul>
        </div>

    </div>
</nav>

<!-- Barre de recherche mobile : apparaît sous la navbar quand on clique sur la loupe -->
<div class="collapse d-lg-none bg-dark" id="searchMobile">
    <div class="container-fluid py-2">
        <form class="position-relative rc-slope-search" role="search" autocomplete="off">
            <input type="search" class="form-control rc-search-input ps-3 pe-5"
                   placeholder="Rechercher" aria-label="Rechercher"
                   id="slopeSearchInputMobile"
                   autocomplete="off"
                   aria-expanded="false"
                   aria-controls="slopeSearchResultsMobile"
                   role="combobox">
            <i class="bi bi-search rc-search-icon"></i>

            <ul class="dropdown-menu rc-search-dropdown w-100" id="slopeSearchResultsMobile" role="listbox"></ul>
        </form>
    </div>
</div>

<main class="rc-main">
    <div id="map"></div>
</main>

<!-- Conteneur des messages flash -->
<div id="flashContainer"></div>

<!-- Dialogue modal pour les markers -->
<?php include 'modal-dialog-slope.php';?>

<!-- Dialogue modal pour les commentaires -->
<?php include 'modal-dialog-comment.php';?>

<!-- Dialogue modal pour les légendes OpenWindMap -->
<?php include 'modal-dialog-help.php';?>

<!-- Dialogue modal pour un ajout de nouvelle pente -->
<?php include 'modal-dialog-newslope.php';?>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="js/leaflet-gps.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<!-- main JS -->
<script src="js/map-helpers.js"></script>
<script src="js/map.js"></script>
<script src="js/modal-dialog-comment.js"></script>
<script src="js/modal-dialog-newslope.js"></script>

<script src="js/slope-search.js"></script>

</body>
</html>