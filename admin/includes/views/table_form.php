<?php
/**
 * Vue "formulaire" (création/édition) du CRUD générique.
 * Variables attendues : $tableKey, $schema, $mode, $formData, $formErrors, $pk, $lookupOptionsCache
 */
$isCreate = $mode === 'create';
$formData = $formData ?? [];
$formErrors = $formErrors ?? [];
?>

<div class="d-flex align-items-center gap-2 mb-4">
  <a href="<?= admin_url('table.php?t=' . urlencode($tableKey)) ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h1 class="h4 mb-0"><?= $isCreate ? 'Ajouter — ' : 'Modifier — ' ?><?= e($schema['label']) ?></h1>
</div>

<?php if (!empty($formErrors)): ?>
  <div class="alert alert-danger">
    <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Merci de corriger les erreurs suivantes :</strong>
    <ul class="mb-0 small">
      <?php foreach ($formErrors as $err): ?>
        <li><?= e($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="<?= admin_url('table.php?t=' . urlencode($tableKey)) ?>" class="rcs-card p-3 p-md-4">
  <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="form_mode" value="<?= $isCreate ? 'create' : 'edit' ?>">
  <?php if (!$isCreate): ?>
    <input type="hidden" name="pk" value="<?= e($formData[$pk]) ?>">
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($schema['columns'] as $colName => $colDef): ?>
      <?php
        $type = $colDef['type'] ?? 'text';
        if ($type === 'hidden' || !($colDef['editable'] ?? true)) {
            continue;
        }
        $value = $formData[$colName] ?? '';
        $hasError = isset($formErrors[$colName]);
        $isWide = in_array($type, ['wysiwyg', 'textarea'], true);
      ?>
      <div class="col-12 <?= $isWide ? '' : 'col-md-6' ?>">
        <label for="field_<?= e($colName) ?>" class="form-label <?= !empty($colDef['required']) ? 'rcs-required' : '' ?>">
          <?= e($colDef['label']) ?>
        </label>

        <?php switch ($type):
          case 'text': ?>
            <input type="text" class="form-control <?= $hasError ? 'is-invalid' : '' ?>"
                   id="field_<?= e($colName) ?>" name="<?= e($colName) ?>"
                   value="<?= e((string) $value) ?>"
                   <?= !empty($colDef['maxlength']) ? 'maxlength="' . (int) $colDef['maxlength'] . '"' : '' ?>
                   <?= !empty($colDef['required']) ? 'required' : '' ?>>
            <?php break;

          case 'textarea': ?>
            <textarea class="form-control <?= $hasError ? 'is-invalid' : '' ?>"
                      id="field_<?= e($colName) ?>" name="<?= e($colName) ?>" rows="4"
                      <?= !empty($colDef['required']) ? 'required' : '' ?>><?= e((string) $value) ?></textarea>
            <?php break;

          case 'wysiwyg': ?>
            <textarea class="form-control rcs-wysiwyg <?= $hasError ? 'is-invalid' : '' ?>"
                      id="field_<?= e($colName) ?>" name="<?= e($colName) ?>" rows="10"><?= /* HTML non échappé : c'est le contenu HTML lui-même destiné à TinyMCE */ ($value ?? '') ?></textarea>
            <?php break;

          case 'number': ?>
            <input type="number" step="1" class="form-control <?= $hasError ? 'is-invalid' : '' ?>"
                   id="field_<?= e($colName) ?>" name="<?= e($colName) ?>"
                   value="<?= e((string) $value) ?>"
                   <?= !empty($colDef['required']) ? 'required' : '' ?>>
            <?php break;

          case 'decimal': ?>
            <input type="number" step="<?= e($colDef['step'] ?? '0.01') ?>" class="form-control <?= $hasError ? 'is-invalid' : '' ?>"
                   id="field_<?= e($colName) ?>" name="<?= e($colName) ?>"
                   value="<?= e((string) $value) ?>"
                   <?= !empty($colDef['required']) ? 'required' : '' ?>>
            <?php break;

          case 'checkbox': ?>
            <div class="form-check form-switch mt-2">
              <input type="checkbox" class="form-check-input" role="switch"
                     id="field_<?= e($colName) ?>" name="<?= e($colName) ?>" value="1"
                     <?= !empty($value) ? 'checked' : '' ?>>
            </div>
            <?php break;

          case 'select': ?>
            <select class="form-select <?= $hasError ? 'is-invalid' : '' ?>"
                    id="field_<?= e($colName) ?>" name="<?= e($colName) ?>"
                    <?= !empty($colDef['required']) ? 'required' : '' ?>>
              <option value="">— Choisir —</option>
              <?php foreach ($colDef['options'] as $optValue => $optLabel):
                  // Supporte à la fois les options indexées (liste simple) et les options clé => libellé
                  if (is_int($optValue)) { $optValue = $optLabel; }
              ?>
                <option value="<?= e($optValue) ?>" <?= (string) $value === (string) $optValue ? 'selected' : '' ?>>
                  <?= e($optLabel) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php break;

          case 'select_multiple': ?>
            <?php $selectedValues = is_string($value) ? explode(',', $value) : (array) $value; ?>
            <div class="d-flex flex-wrap gap-2 border rounded p-2">
              <?php foreach ($colDef['options'] as $opt): ?>
                <div class="form-check form-check-inline m-0">
                  <input class="form-check-input" type="checkbox"
                         id="field_<?= e($colName) ?>_<?= e($opt) ?>"
                         name="<?= e($colName) ?>[]" value="<?= e($opt) ?>"
                         <?= in_array($opt, $selectedValues, true) ? 'checked' : '' ?>>
                  <label class="form-check-label small" for="field_<?= e($colName) ?>_<?= e($opt) ?>"><?= e($opt) ?></label>
                </div>
              <?php endforeach; ?>
            </div>
            <?php break;

          case 'lookup': ?>
            <select class="form-select <?= $hasError ? 'is-invalid' : '' ?>"
                    id="field_<?= e($colName) ?>" name="<?= e($colName) ?>"
                    <?= !empty($colDef['required']) ? 'required' : '' ?>>
              <option value="">— Choisir —</option>
              <?php foreach ($lookupOptionsCache[$colName] ?? [] as $optPk => $optLabel): ?>
                <option value="<?= e($optPk) ?>" <?= (string) $value === (string) $optPk ? 'selected' : '' ?>>
                  <?= e($optLabel) ?> (#<?= e($optPk) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <?php break;

          case 'datetime': ?>
            <?php
              $dtValue = '';
              if (!empty($value)) {
                  $dt = DateTime::createFromFormat('Y-m-d H:i:s', (string) $value) ?: date_create((string) $value);
                  $dtValue = $dt ? $dt->format('Y-m-d\TH:i') : '';
              }
            ?>
            <input type="datetime-local" class="form-control <?= $hasError ? 'is-invalid' : '' ?>"
                   id="field_<?= e($colName) ?>" name="<?= e($colName) ?>"
                   value="<?= e($dtValue) ?>"
                   <?= !empty($colDef['required']) ? 'required' : '' ?>>
            <?php break;

          case 'date': ?>
            <input type="date" class="form-control <?= $hasError ? 'is-invalid' : '' ?>"
                   id="field_<?= e($colName) ?>" name="<?= e($colName) ?>"
                   value="<?= e((string) $value) ?>"
                   <?= !empty($colDef['required']) ? 'required' : '' ?>>
            <?php break;

          default: ?>
            <input type="text" class="form-control" id="field_<?= e($colName) ?>" name="<?= e($colName) ?>" value="<?= e((string) $value) ?>">
        <?php endswitch; ?>

        <?php if ($hasError): ?>
          <div class="invalid-feedback d-block"><?= e($formErrors[$colName]) ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="d-flex gap-2 mt-4 pt-3 border-top">
    <button type="submit" class="btn btn-dark">
      <i class="bi bi-check-lg me-1"></i> Enregistrer
    </button>
    <a href="<?= admin_url('table.php?t=' . urlencode($tableKey)) ?>" class="btn btn-outline-secondary">
      Annuler
    </a>
  </div>
</form>

<?php require __DIR__ . '/media_library_modal.php'; ?>
