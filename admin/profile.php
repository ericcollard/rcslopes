<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$pageTitle = 'Mon profil';
$activePage = '';

$manager = new AdminManager();
$currentId = Auth::currentAdminId();
$admin = $manager->find($currentId);

$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? null);

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if ($password !== '' && $password !== $passwordConfirm) {
        $formError = 'Les deux mots de passe ne correspondent pas.';
    } else {
        // Un éditeur/admin ne peut pas changer son propre rôle ni se désactiver lui-même ici :
        // on réutilise update() en conservant son rôle et son statut actuels.
        $result = $manager->update(
            $currentId,
            $email,
            $fullName,
            $admin['role'],
            (bool) $admin['is_active'],
            $password !== '' ? $password : null
        );

        if ($result['success']) {
            $_SESSION['admin_name'] = $fullName;
            flash_set('success', 'Profil mis à jour avec succès.');
            header('Location: ' . admin_url('profile.php'));
            exit;
        }
        $formError = $result['message'];
    }

    $admin['full_name'] = $fullName;
    $admin['email'] = $email;
}

require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/sidebar.php';
?>

<main class="rcs-content">

  <?php require __DIR__ . '/includes/views/flash_messages.php'; ?>

  <h1 class="h4 mb-4">Mon profil</h1>

  <?php if ($formError): ?>
    <div class="alert alert-danger"><?= e($formError) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= admin_url('profile.php') ?>" class="rcs-card p-3 p-md-4" style="max-width: 520px;">
    <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">

    <div class="mb-3">
      <label class="form-label rcs-required">Nom complet</label>
      <input type="text" name="full_name" class="form-control" required value="<?= e($admin['full_name']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label rcs-required">Email</label>
      <input type="email" name="email" class="form-control" required value="<?= e($admin['email']) ?>">
    </div>

    <div class="mb-3">
      <span class="badge <?= $admin['role'] === 'admin' ? 'rcs-badge-role-admin' : 'rcs-badge-role-editor' ?>">
        <?= e(role_label($admin['role'])) ?>
      </span>
    </div>

    <hr>

    <div class="mb-3">
      <label class="form-label">Nouveau mot de passe</label>
      <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="10">
      <div class="form-text">Laisser vide pour conserver le mot de passe actuel. Minimum 10 caractères.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Confirmer le nouveau mot de passe</label>
      <input type="password" name="password_confirm" class="form-control" autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg me-1"></i> Enregistrer</button>
  </form>

</main>

<?php require __DIR__ . '/includes/views/footer.php'; ?>
