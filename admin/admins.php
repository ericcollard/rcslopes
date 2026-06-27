<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireAdminRole();

$pageTitle = 'Administrateurs';
$activePage = 'admins';

$manager = new AdminManager();
$mode = $_GET['mode'] ?? 'list'; // list | create | edit
$editId = isset($_GET['id']) ? (int) $_GET['id'] : null;

$formErrors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? null);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $deleteId = (int) ($_POST['id'] ?? 0);
        $result = $manager->delete($deleteId, Auth::currentAdminId());
        flash_set($result['success'] ? 'success' : 'error', $result['message']);
        header('Location: ' . admin_url('admins.php'));
        exit;
    }

    if ($action === 'save') {
        $isCreate = ($_POST['form_mode'] ?? '') === 'create';
        $email    = trim((string) ($_POST['email'] ?? ''));
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $role     = (string) ($_POST['role'] ?? ROLE_EDITOR);
        $isActive = isset($_POST['is_active']);
        $password = (string) ($_POST['password'] ?? '');

        if ($isCreate) {
            $result = $manager->create($email, $password, $fullName, $role, $isActive);
        } else {
            $targetId = (int) ($_POST['id'] ?? 0);
            $result = $manager->update($targetId, $email, $fullName, $role, $isActive, $password !== '' ? $password : null);
        }

        if ($result['success']) {
            flash_set('success', $result['message']);
            header('Location: ' . admin_url('admins.php'));
            exit;
        }

        $formErrors['general'] = $result['message'];
        $formData = $_POST;
        $mode = $isCreate ? 'create' : 'edit';
        $editId = (int) ($_POST['id'] ?? 0);
    }
}

if ($mode === 'edit' && empty($formData)) {
    $admin = $manager->find($editId);
    if ($admin === null) {
        flash_set('error', 'Administrateur introuvable.');
        header('Location: ' . admin_url('admins.php'));
        exit;
    }
    $formData = $admin;
}

if ($mode === 'list') {
    $admins = $manager->all();
}

require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/sidebar.php';
?>

<main class="rcs-content">

  <?php require __DIR__ . '/includes/views/flash_messages.php'; ?>

  <?php if ($mode === 'list'): ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h1 class="h3 mb-1">Administrateurs</h1>
        <p class="text-secondary mb-0 small"><?= count($admins) ?> compte<?= count($admins) > 1 ? 's' : '' ?></p>
      </div>
      <a href="<?= admin_url('admins.php?mode=create') ?>" class="btn btn-dark">
        <i class="bi bi-plus-lg me-1"></i> Ajouter un administrateur
      </a>
    </div>

    <!-- Tableau desktop -->
    <div class="table-responsive-wrapper d-none d-md-block">
      <table class="table rcs-table mb-0 align-middle">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Dernière connexion</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($admins as $admin): ?>
            <tr>
              <td><?= e($admin['full_name']) ?></td>
              <td><?= e($admin['email']) ?></td>
              <td>
                <span class="badge <?= $admin['role'] === 'admin' ? 'rcs-badge-role-admin' : 'rcs-badge-role-editor' ?>">
                  <?= e(role_label($admin['role'])) ?>
                </span>
              </td>
              <td>
                <?php if ($admin['is_active']): ?>
                  <span class="badge bg-success">Actif</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Désactivé</span>
                <?php endif; ?>
              </td>
              <td class="small text-secondary"><?= e(format_datetime($admin['last_login_at'])) ?></td>
              <td class="rcs-table-actions">
                <a href="<?= admin_url('admins.php?mode=edit&id=' . $admin['admin_id']) ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                  <i class="bi bi-pencil"></i>
                </a>
                <?php if ((int) $admin['admin_id'] !== Auth::currentAdminId()): ?>
                  <form method="post" action="<?= admin_url('admins.php') ?>" class="d-inline" data-confirm="Supprimer cet administrateur ?">
                    <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $admin['admin_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Cartes mobile -->
    <div class="d-md-none d-flex flex-column gap-2">
      <?php foreach ($admins as $admin): ?>
        <div class="rcs-card p-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-semibold"><?= e($admin['full_name']) ?></div>
              <div class="small text-secondary"><?= e($admin['email']) ?></div>
            </div>
            <span class="badge <?= $admin['role'] === 'admin' ? 'rcs-badge-role-admin' : 'rcs-badge-role-editor' ?>">
              <?= e(role_label($admin['role'])) ?>
            </span>
          </div>
          <div class="small text-secondary mt-2">
            Statut : <?= $admin['is_active'] ? 'Actif' : 'Désactivé' ?> ·
            Dernière connexion : <?= e(format_datetime($admin['last_login_at'])) ?>
          </div>
          <div class="d-flex gap-2 mt-3">
            <a href="<?= admin_url('admins.php?mode=edit&id=' . $admin['admin_id']) ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">
              <i class="bi bi-pencil me-1"></i> Modifier
            </a>
            <?php if ((int) $admin['admin_id'] !== Auth::currentAdminId()): ?>
              <form method="post" action="<?= admin_url('admins.php') ?>" class="flex-grow-1" data-confirm="Supprimer cet administrateur ?">
                <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $admin['admin_id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash me-1"></i> Supprimer</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php else: ?>

    <?php $isCreate = $mode === 'create'; ?>

    <div class="d-flex align-items-center gap-2 mb-4">
      <a href="<?= admin_url('admins.php') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i></a>
      <h1 class="h4 mb-0"><?= $isCreate ? 'Ajouter un administrateur' : 'Modifier l\'administrateur' ?></h1>
    </div>

    <?php if (!empty($formErrors['general'])): ?>
      <div class="alert alert-danger"><?= e($formErrors['general']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= admin_url('admins.php') ?>" class="rcs-card p-3 p-md-4">
      <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="form_mode" value="<?= $isCreate ? 'create' : 'edit' ?>">
      <?php if (!$isCreate): ?>
        <input type="hidden" name="id" value="<?= e($formData['admin_id']) ?>">
      <?php endif; ?>

      <div class="row g-3">
        <div class="col-12 col-md-6">
          <label class="form-label rcs-required">Nom complet</label>
          <input type="text" name="full_name" class="form-control" required value="<?= e($formData['full_name'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label rcs-required">Email</label>
          <input type="email" name="email" class="form-control" required value="<?= e($formData['email'] ?? '') ?>">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label <?= $isCreate ? 'rcs-required' : '' ?>">
            Mot de passe <?= $isCreate ? '' : '<span class="text-secondary small">(laisser vide pour ne pas changer)</span>' ?>
          </label>
          <input type="password" name="password" class="form-control" autocomplete="new-password"
                 <?= $isCreate ? 'required' : '' ?> minlength="10">
          <div class="form-text">Minimum 10 caractères.</div>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label rcs-required">Rôle / privilèges</label>
          <select name="role" class="form-select" required>
            <option value="editor" <?= ($formData['role'] ?? 'editor') === 'editor' ? 'selected' : '' ?>>
              Éditeur — créer / modifier / supprimer les données
            </option>
            <option value="admin" <?= ($formData['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
              Administrateur — éditeur + gestion des administrateurs
            </option>
          </select>
        </div>
        <div class="col-12">
          <div class="form-check form-switch">
            <input type="checkbox" class="form-check-input" role="switch" name="is_active" id="is_active"
                   <?= ($formData['is_active'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Compte actif (autorisé à se connecter)</label>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg me-1"></i> Enregistrer</button>
        <a href="<?= admin_url('admins.php') ?>" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>

  <?php endif; ?>

</main>

<?php require __DIR__ . '/includes/views/footer.php'; ?>
