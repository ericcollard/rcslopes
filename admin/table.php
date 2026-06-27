<?php
require_once __DIR__ . '/includes/bootstrap.php';
Auth::requireLogin();

$tableKey = $_GET['t'] ?? '';
if (!TableRegistry::exists($tableKey)) {
    http_response_code(404);
    die('Table inconnue.');
}

$schema = TableRegistry::get($tableKey);
$engine = new CrudEngine($tableKey);
$pk = $schema['primary_key'];

$mode = $_GET['mode'] ?? 'list'; // list | create | edit
$pkValue = $_GET['pk'] ?? null;

// ---------------------------------------------------------------------------
// Traitement des actions POST (create / update / delete)
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireValidCsrf($_POST['csrf_token'] ?? null);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $deletePk = $_POST['pk'] ?? null;
        if ($deletePk !== null) {
            $engine->delete($deletePk);
            flash_set('success', 'Élément supprimé avec succès.');
        }
        header('Location: ' . admin_url('table.php?t=' . urlencode($tableKey)));
        exit;
    }

    if ($action === 'save') {
        $isCreate = ($_POST['form_mode'] ?? '') === 'create';
        $result = FormProcessor::process($schema, $_POST, $isCreate);

        if (!empty($result['errors'])) {
            $formErrors = $result['errors'];
            $formData = $_POST;
            $mode = $isCreate ? 'create' : 'edit';
            $pkValue = $_POST[$pk] ?? $pkValue;
        } else {
            if ($isCreate) {
                $newPk = $engine->insert($result['data']);
                flash_set('success', 'Élément créé avec succès.');
            } else {
                $editPk = $_POST['pk'] ?? null;
                $engine->update($editPk, $result['data']);
                flash_set('success', 'Élément mis à jour avec succès.');
            }
            header('Location: ' . admin_url('table.php?t=' . urlencode($tableKey)));
            exit;
        }
    }
}

// ---------------------------------------------------------------------------
// Préparation des données pour l'affichage selon le mode
// ---------------------------------------------------------------------------
$pageTitle = $schema['label'];
$activePage = 'table:' . $tableKey;

if ($mode === 'edit' && empty($formData)) {
    $record = $engine->find($pkValue);
    if ($record === null) {
        flash_set('error', 'Élément introuvable.');
        header('Location: ' . admin_url('table.php?t=' . urlencode($tableKey)));
        exit;
    }
    $formData = $record;
}

if ($mode === 'list') {
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $search = trim((string) ($_GET['q'] ?? ''));
    $result = $engine->paginate($page, 20, $search);
}

// Pré-charge les options des champs "lookup" et "select" pour le formulaire
$lookupOptionsCache = [];
if ($mode === 'create' || $mode === 'edit') {
    foreach ($schema['columns'] as $colName => $colDef) {
        if (($colDef['type'] ?? '') === 'lookup') {
            $lookupOptionsCache[$colName] = $engine->lookupOptions(
                $colDef['lookup_table'],
                $colDef['lookup_pk'],
                $colDef['lookup_label']
            );
        }
    }
}

require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/sidebar.php';
?>

<main class="rcs-content">

  <?php require __DIR__ . '/includes/views/flash_messages.php'; ?>

  <?php if ($mode === 'list'): ?>
    <?php require __DIR__ . '/includes/views/table_list.php'; ?>
  <?php else: ?>
    <?php require __DIR__ . '/includes/views/table_form.php'; ?>
  <?php endif; ?>

</main>

<?php require __DIR__ . '/includes/views/footer.php'; ?>
