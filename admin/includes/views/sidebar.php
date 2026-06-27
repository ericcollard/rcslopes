<?php
/**
 * Menu latéral. Attend $activePage (ex: 'table:slopes', 'images', 'admins') défini avant inclusion.
 */
$activePage = $activePage ?? '';
$tables = TableRegistry::all();
?>
<aside id="rcsSidebar" class="rcs-sidebar py-3">
  <nav class="nav flex-column px-2">

    <div class="nav-section-title">Données</div>
    <?php foreach ($tables as $sidebarTableKey => $tableSchema): ?>
      <a class="nav-link <?= $activePage === 'table:' . $sidebarTableKey ? 'active' : '' ?>"
         href="<?= admin_url('table.php?t=' . urlencode($sidebarTableKey)) ?>">
        <i class="bi <?= e($tableSchema['icon'] ?? 'bi-table') ?>"></i>
        <span><?= e($tableSchema['label']) ?></span>
      </a>
    <?php endforeach; ?>

    <div class="nav-section-title">Médias</div>
    <a class="nav-link <?= $activePage === 'images' ? 'active' : '' ?>" href="<?= admin_url('images.php') ?>">
      <i class="bi bi-images"></i>
      <span>Bibliothèque d'images</span>
    </a>

    <?php if (Auth::isAdminRole()): ?>
      <div class="nav-section-title">Administration</div>
      <a class="nav-link <?= $activePage === 'admins' ? 'active' : '' ?>" href="<?= admin_url('admins.php') ?>">
        <i class="bi bi-people"></i>
        <span>Administrateurs</span>
      </a>
    <?php endif; ?>

  </nav>
</aside>
