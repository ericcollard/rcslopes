<?php
/** Affiche et vide les messages flash de la session. */
$flashes = flash_get_all();
foreach ($flashes as $flash):
    $alertClass = match ($flash['type']) {
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };
    $icon = match ($flash['type']) {
        'success' => 'bi-check-circle-fill',
        'error'   => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default   => 'bi-info-circle-fill',
    };
?>
<div class="alert <?= $alertClass ?> d-flex align-items-start gap-2 alert-dismissible fade show" role="alert">
  <i class="bi <?= $icon ?> mt-1"></i>
  <div class="flex-grow-1"><?= e($flash['message']) ?></div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
</div>
<?php endforeach; ?>
