<?php
/**
 * En-tête HTML commun à toutes les pages authentifiées.
 * Attend une variable optionnelle $pageTitle définie avant inclusion.
 */
$pageTitle = $pageTitle ?? 'Administration';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — RC Slopes Admin</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>

<script>window.RCS_CSRF_TOKEN = <?= json_encode(Auth::csrfToken()) ?>;</script>

<nav class="navbar navbar-dark rcs-topbar px-2 px-md-3">
  <div class="container-fluid d-flex align-items-center px-0">

    <button id="rcsSidebarToggle" class="btn btn-link text-light d-lg-none p-1 me-2" type="button" aria-label="Afficher le menu">
      <i class="bi bi-list fs-3"></i>
    </button>

    <a class="navbar-brand d-flex align-items-center mb-0" href="<?= admin_url('index.php') ?>">
      <i class="bi bi-triangle-fill me-2 fs-5"></i>
      <span class="fw-semibold">RC Slopes <span class="text-secondary">Admin</span></span>
    </a>

    <div class="ms-auto d-flex align-items-center gap-2">
      <div class="dropdown">
        <button class="btn btn-dark btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-circle fs-5"></i>
          <span class="d-none d-sm-inline"><?= e(Auth::currentAdminName()) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text small text-secondary"><?= e(role_label(Auth::currentAdminRole())) ?></span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= admin_url('profile.php') ?>"><i class="bi bi-person me-2"></i>Mon profil</a></li>
          <li><a class="dropdown-item text-danger" href="<?= admin_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
        </ul>
      </div>
    </div>

  </div>
</nav>

<div class="rcs-layout">

<div id="rcsSidebarBackdrop" class="rcs-sidebar-backdrop"></div>

<?php
/**
 * ORDRE D'INCLUSION ATTENDU DANS CHAQUE PAGE :
 *   1. require includes/bootstrap.php
 *   2. Auth::requireLogin() (+ requireAdminRole() si besoin)
 *   3. $pageTitle = '...'; $activePage = '...';
 *   4. require includes/views/header.php   (ce fichier)
 *   5. require includes/views/sidebar.php
 *   6. <main class="rcs-content"> ... contenu de la page ... </main>
 *   7. require includes/views/footer.php
 */
?>

