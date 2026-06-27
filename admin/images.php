<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$pageTitle = "Bibliothèque d'images";
$activePage = 'images';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? null);
    $action = $_POST['action'] ?? '';

    if ($action === 'upload' && isset($_FILES['image'])) {
        $result = ImageManager::upload($_FILES['image']);
        flash_set($result['success'] ? 'success' : 'error', $result['message']);
    }

    if ($action === 'delete') {
        $filename = (string) ($_POST['filename'] ?? '');
        $result = ImageManager::delete($filename);
        flash_set($result['success'] ? 'success' : 'error', $result['message']);
    }

    header('Location: ' . admin_url('images.php'));
    exit;
}

$images = ImageManager::list();

require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/sidebar.php';
?>

<main class="rcs-content">

  <?php require __DIR__ . '/includes/views/flash_messages.php'; ?>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h1 class="h3 mb-1">Bibliothèque d'images</h1>
      <p class="text-secondary mb-0 small"><?= count($images) ?> image<?= count($images) > 1 ? 's' : '' ?> — stockées dans <code>assets/images</code></p>
    </div>
  </div>

  <!-- Zone d'upload -->
  <form method="post" action="<?= admin_url('images.php') ?>" enctype="multipart/form-data" class="mb-4">
    <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
    <input type="hidden" name="action" value="upload">

    <label class="rcs-image-dropzone d-block cursor-pointer" tabindex="0">
      <i class="bi bi-cloud-arrow-up fs-1 d-block mb-2"></i>
      <span class="d-block fw-medium">Cliquez ou glissez-déposez une image ici</span>
      <span class="d-block small mt-1">JPG, PNG, GIF, WEBP — 5 Mo maximum</span>
      <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" class="d-none" required>
    </label>
  </form>

  <?php if (empty($images)): ?>

    <div class="rcs-card rcs-empty-state">
      <i class="bi bi-images"></i>
      <p class="mb-0">Aucune image dans la bibliothèque pour le moment.</p>
    </div>

  <?php else: ?>

    <div class="rcs-image-grid">
      <?php foreach ($images as $img): ?>
        <div class="rcs-image-card">
          <div class="rcs-image-actions">
            <button type="button" class="btn btn-light rcs-copy-url" data-url="<?= e($img['url']) ?>" title="Copier l'URL">
              <i class="bi bi-clipboard"></i>
            </button>
            <form method="post" action="<?= admin_url('images.php') ?>" data-confirm="Supprimer définitivement cette image ?">
              <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="filename" value="<?= e($img['filename']) ?>">
              <button type="submit" class="btn btn-light text-danger" title="Supprimer">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </div>
          <img src="<?= e($img['url']) ?>" alt="<?= e($img['filename']) ?>" class="rcs-image-thumb" loading="lazy">
          <div class="rcs-image-meta">
            <div class="text-truncate" title="<?= e($img['filename']) ?>"><?= e($img['filename']) ?></div>
            <div><?= e(ImageManager::formatSize($img['size'])) ?> — <?= e(date('d/m/Y H:i', $img['mtime'])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.rcs-copy-url').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const url = btn.getAttribute('data-url');
      const fullUrl = window.location.origin + url;
      navigator.clipboard.writeText(fullUrl).then(function () {
        const icon = btn.querySelector('i');
        icon.className = 'bi bi-check-lg text-success';
        setTimeout(function () { icon.className = 'bi bi-clipboard'; }, 1500);
      });
    });
  });
});
</script>

<?php require __DIR__ . '/includes/views/footer.php'; ?>
