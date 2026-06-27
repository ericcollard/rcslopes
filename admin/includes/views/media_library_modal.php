<?php
/**
 * Modale "Médiathèque" : liste les images existantes pour insertion rapide
 * dans un champ WYSIWYG (sans re-uploader). Incluse sur les pages formulaire.
 */
$mediaLibraryImages = ImageManager::list();
?>
<div class="modal fade" id="rcsMediaLibraryModal" tabindex="-1" aria-labelledby="rcsMediaLibraryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rcsMediaLibraryModalLabel"><i class="bi bi-images me-2"></i>Bibliothèque d'images</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <?php if (empty($mediaLibraryImages)): ?>
          <p class="text-secondary text-center mb-0">Aucune image disponible. Téléversez-en une depuis la bibliothèque d'images.</p>
        <?php else: ?>
          <div class="rcs-image-grid">
            <?php foreach ($mediaLibraryImages as $img): ?>
              <div class="rcs-image-card cursor-pointer rcs-media-pick" data-url="<?= e($img['url']) ?>">
                <img src="<?= e($img['url']) ?>" alt="<?= e($img['filename']) ?>" class="rcs-image-thumb" loading="lazy">
                <div class="rcs-image-meta text-truncate" title="<?= e($img['filename']) ?>"><?= e($img['filename']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="<?= admin_url('images.php') ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
          <i class="bi bi-upload me-1"></i> Gérer / téléverser des images
        </a>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.rcs-media-pick').forEach(function (card) {
    card.addEventListener('click', function () {
      const url = card.getAttribute('data-url');
      const modalEl = document.getElementById('rcsMediaLibraryModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl);
      if (typeof window._rcsMediaLibraryCallback === 'function') {
        window._rcsMediaLibraryCallback(url);
        window._rcsMediaLibraryCallback = null;
      }
      if (modalInstance) modalInstance.hide();
    });
  });
});
</script>
