<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>RC Slopes – Mise à jour pente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/main.css" />
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark rc-navbar">
    <div class="container-fluid rc-navbar-inner">

        <!-- Logo + titre -->
        <a class="navbar-brand d-flex align-items-center" href="https://www.finesseplus.org">
            <img src="/assets/logo.svg" alt="Logo RcSlopes"/>
            <p class="site-name">
                RC-SlopeS <span class="site-version">v2.2</span></br>
                <span class="site-by">by Finesse+</span>
            </p>
        </a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-3">Mise à jour de la pente "{NAME}" ref #{SLOPEID}</h2>
            <p>Accès direct à la <a href="{SLOPEURL}" target="_blank">pente > </a></p>
            <p>Accès direct à l'admin de la <a href="{SLOPEEDITURL}" target="_blank">pente > </a></p>
            <div class="table-responsive-sm">
                {TABLE}
            </div>
        </div>
    </div>
</div>




<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>