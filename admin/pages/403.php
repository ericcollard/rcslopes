<?php
/**
 * Page d'erreur 403. Peut être incluse directement (depuis Auth::requireAdminRole())
 * ou appelée seule. $pageTitle / $activePage sont définis ici si absents.
 */
if (!isset($pageTitle)) {
    require_once __DIR__ . '/../includes/bootstrap.php';
    Auth::requireLogin();
    $pageTitle = 'Accès refusé';
    $activePage = '';
    require __DIR__ . '/../includes/views/header.php';
    require __DIR__ . '/../includes/views/sidebar.php';
    $standalone = true;
}
?>

<main class="rcs-content">
  <div class="rcs-card rcs-empty-state">
    <i class="bi bi-shield-lock"></i>
    <h2 class="h5">Accès refusé</h2>
    <p class="mb-3">Vous n'avez pas les privilèges nécessaires pour accéder à cette page.</p>
    <a href="<?= admin_url('index.php') ?>" class="btn btn-dark btn-sm">Retour au tableau de bord</a>
  </div>
</main>

<?php if (!empty($standalone)): ?>
  <?php require __DIR__ . '/../includes/views/footer.php'; ?>
<?php endif; ?>
