<?php
/**
 * install.php — À EXÉCUTER UNE SEULE FOIS après le déploiement, puis À SUPPRIMER.
 *
 * Ce script :
 *  1. Vérifie la connexion à la base.
 *  2. Régénère un hash bcrypt valide pour le compte admin par défaut
 *     (le hash inséré par sql/00_admin_schema.sql est un placeholder).
 *  3. Affiche les identifiants à utiliser pour la première connexion.
 *
 * Sécurité : le script refuse de s'exécuter une seconde fois une fois le
 * mot de passe par défaut changé, et se désactive si appelé en HTTP sans
 * confirmation explicite.
 */

require_once __DIR__ . '/includes/bootstrap.php';

$defaultEmail = 'admin@rcslopes.local';
$defaultPassword = 'ChangeMoi123!';

$pdo = Database::getConnection();

$stmt = $pdo->prepare('SELECT admin_id, password_hash FROM administrators WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $defaultEmail]);
$admin = $stmt->fetch();

$messages = [];
$success = false;

if ($admin === null) {
    $messages[] = "Aucun compte trouvé avec l'email {$defaultEmail}. Vérifiez que sql/00_admin_schema.sql a bien été importé.";
} elseif (password_verify($defaultPassword, $admin['password_hash'])) {
    $messages[] = "Le compte par défaut est déjà correctement initialisé. Ce script peut être supprimé.";
    $success = true;
} else {
    $confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

    if (!$confirmed) {
        $messages[] = "Cliquez sur le bouton ci-dessous pour générer le mot de passe initial.";
    } else {
        $newHash = password_hash($defaultPassword, PASSWORD_BCRYPT);
        $update = $pdo->prepare('UPDATE administrators SET password_hash = :hash WHERE admin_id = :id');
        $update->execute(['hash' => $newHash, 'id' => $admin['admin_id']]);
        $success = true;
        $messages[] = "Mot de passe initial configuré avec succès.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installation — RC Slopes Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-dark">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-7">
      <div class="card shadow-lg">
        <div class="card-body p-4">
          <h1 class="h4 mb-3"><i class="bi bi-gear-fill me-2"></i>Installation RC Slopes Admin</h1>

          <?php foreach ($messages as $m): ?>
            <div class="alert alert-<?= $success ? 'success' : 'info' ?>"><?= e($m) ?></div>
          <?php endforeach; ?>

          <?php if ($success): ?>
            <div class="alert alert-warning">
              <strong>Identifiants de première connexion :</strong><br>
              Email : <code><?= e($defaultEmail) ?></code><br>
              Mot de passe : <code><?= e($defaultPassword) ?></code>
            </div>
            <p class="text-danger fw-semibold">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              Connectez-vous immédiatement et changez ce mot de passe depuis "Mon profil",
              puis supprimez ce fichier <code>install.php</code> du serveur.
            </p>
            <a href="<?= admin_url('login.php') ?>" class="btn btn-dark">Aller à la page de connexion</a>
          <?php elseif ($admin !== null): ?>
            <a href="?confirm=yes" class="btn btn-dark">Initialiser le mot de passe par défaut</a>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
