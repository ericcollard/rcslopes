<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$pageTitle = 'Tableau de bord';
$activePage = '';

$counts = [];
foreach (TableRegistry::all() as $tableKey => $schema) {
    $engine = new CrudEngine($tableKey);
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT COUNT(*) AS nb FROM `{$tableKey}`");
    $counts[$tableKey] = (int) $stmt->fetch()['nb'];
}
$imageCount = count(ImageManager::list());

require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/sidebar.php';
?>

<main class="rcs-content">

  <?php require __DIR__ . '/includes/views/flash_messages.php'; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Bonjour, <?= e(Auth::currentAdminName()) ?> 👋</h1>
      <p class="text-secondary mb-0">Vue d'ensemble de la base RC Slopes.</p>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <?php foreach (TableRegistry::all() as $tableKey => $schema): ?>
      <div class="col-12 col-sm-6 col-lg-3">
        <a href="<?= admin_url('table.php?t=' . urlencode($tableKey)) ?>" class="text-decoration-none">
          <div class="rcs-card p-3 h-100">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                <i class="bi <?= e($schema['icon'] ?? 'bi-table') ?> fs-4 text-dark"></i>
              </div>
              <div>
                <div class="fs-4 fw-semibold text-dark"><?= $counts[$tableKey] ?></div>
                <div class="text-secondary small"><?= e($schema['label']) ?></div>
              </div>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>

    <div class="col-12 col-sm-6 col-lg-3">
      <a href="<?= admin_url('images.php') ?>" class="text-decoration-none">
        <div class="rcs-card p-3 h-100">
          <div class="d-flex align-items-center gap-3">
            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
              <i class="bi bi-images fs-4 text-dark"></i>
            </div>
            <div>
              <div class="fs-4 fw-semibold text-dark"><?= $imageCount ?></div>
              <div class="text-secondary small">Images stockées</div>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>

  <div class="rcs-card p-3 p-md-4">
    <h2 class="h6 mb-3"><i class="bi bi-info-circle me-2"></i>Accès rapide</h2>
    <ul class="mb-0 small text-secondary">
      <li>Utilisez le menu latéral pour gérer les <strong>sites de vol</strong>, <strong>prévisions météo</strong> et <strong>stations de vent</strong>.</li>
      <li>La <strong>bibliothèque d'images</strong> centralise tous les visuels utilisables dans les descriptions HTML.</li>
      <?php if (Auth::isAdminRole()): ?>
        <li>En tant qu'<strong>administrateur</strong>, vous pouvez gérer les comptes dans la section <em>Administrateurs</em>.</li>
      <?php endif; ?>
    </ul>
  </div>

</main>

<?php require __DIR__ . '/includes/views/footer.php'; ?>
