<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Si déjà connecté, redirige directement vers le tableau de bord.
if (Auth::isLoggedIn()) {
    header('Location: ' . admin_url('index.php'));
    exit;
}

$error = '';
$redirectTarget = $_GET['redirect'] ?? admin_url('index.php');
// Sécurise la redirection : n'accepte que des chemins internes commençant par /admin
if (!is_string($redirectTarget) || !preg_match('#^/admin(/|$)#', $redirectTarget)) {
    $redirectTarget = admin_url('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? null);

    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Merci de renseigner votre email et votre mot de passe.';
    } else {
        $result = Auth::attemptLogin($email, $password);
        if ($result['success']) {
            header('Location: ' . $redirectTarget);
            exit;
        }
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — RC Slopes Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>

<div class="rcs-login-wrapper">
  <div class="rcs-login-card">

    <i class="bi bi-triangle-fill text-dark d-block text-center" style="font-size: 2.5rem;"></i>
    <h1 class="h4 text-center mt-2 mb-1">RC Slopes</h1>
    <p class="text-center text-secondary small mb-4">Interface d'administration</p>

    <?php if ($error): ?>
      <div class="alert alert-danger d-flex align-items-start gap-2 py-2">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div><?= e($error) ?></div>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= admin_url('login.php?redirect=' . urlencode($redirectTarget)) ?>" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">

      <div class="mb-3">
        <label for="email" class="form-label">Adresse email</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus
               value="<?= e($_POST['email'] ?? '') ?>" autocomplete="username">
      </div>

      <div class="mb-4">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" id="password" name="password" required
               autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-dark w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
      </button>
    </form>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
