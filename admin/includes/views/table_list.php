<?php
/**
 * Vue "liste" du CRUD générique.
 * Variables attendues : $tableKey, $schema, $result, $search, $pk
 */
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h1 class="h3 mb-1"><?= e($schema['label']) ?></h1>
    <p class="text-secondary mb-0 small"><?= $result['total'] ?> élément<?= $result['total'] > 1 ? 's' : '' ?> au total</p>
  </div>
  <a href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&mode=create') ?>" class="btn btn-dark">
    <i class="bi bi-plus-lg me-1"></i> Ajouter
  </a>
</div>

<?php if (!empty($schema['search_columns'])): ?>
<form method="get" action="<?= admin_url('table.php') ?>" class="rcs-card p-3 mb-3 d-flex flex-wrap gap-2">
  <input type="hidden" name="t" value="<?= e($tableKey) ?>">
  <div class="flex-grow-1" style="min-width: 200px;">
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" name="q" class="form-control" placeholder="Rechercher..." value="<?= e($search) ?>">
    </div>
  </div>
  <button type="submit" class="btn btn-outline-secondary">Filtrer</button>
  <?php if ($search !== ''): ?>
    <a href="<?= admin_url('table.php?t=' . urlencode($tableKey)) ?>" class="btn btn-outline-secondary">
      <i class="bi bi-x-lg"></i>
    </a>
  <?php endif; ?>
</form>
<?php endif; ?>

<?php if (empty($result['rows'])): ?>

  <div class="rcs-card rcs-empty-state">
    <i class="bi bi-inbox"></i>
    <p class="mb-0">Aucun élément trouvé<?= $search !== '' ? ' pour cette recherche' : '' ?>.</p>
  </div>

<?php else: ?>

  <!-- Vue tableau (desktop / tablette) -->
  <div class="table-responsive-wrapper d-none d-md-block">
    <table class="table rcs-table mb-0 align-middle">
      <thead>
        <tr>
          <?php foreach ($schema['list_columns'] as $col): ?>
            <th><?= e($schema['columns'][$col]['label'] ?? $col) ?></th>
          <?php endforeach; ?>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result['rows'] as $row): ?>
          <tr>
            <?php foreach ($schema['list_columns'] as $col): ?>
              <td>
                <?php
                  $colDef = $schema['columns'][$col] ?? ['type' => 'text'];
                  $value = $row[$col] ?? null;
                  switch ($colDef['type']) {
                      case 'checkbox':
                          echo $value ? '<i class="bi bi-check-lg text-success"></i>' : '<i class="bi bi-dash text-secondary"></i>';
                          break;
                      case 'datetime':
                      case 'date':
                          echo e(format_datetime($value));
                          break;
                      case 'wysiwyg':
                          echo e(truncate_text($value, 50));
                          break;
                      default:
                          echo e(truncate_text((string) $value, 50));
                  }
                ?>
              </td>
            <?php endforeach; ?>
            <td class="rcs-table-actions">
              <a href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&mode=edit&pk=' . urlencode($row[$pk])) ?>"
                 class="btn btn-sm btn-outline-secondary" title="Modifier">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="post" action="<?= admin_url('table.php?t=' . urlencode($tableKey)) ?>"
                    class="d-inline" data-confirm="Supprimer définitivement cet élément ?">
                <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="pk" value="<?= e($row[$pk]) ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Vue cartes (mobile) -->
  <div class="d-md-none d-flex flex-column gap-2">
    <?php foreach ($result['rows'] as $row): ?>
      <div class="rcs-card p-3">
        <?php foreach (array_slice($schema['list_columns'], 0, 4) as $i => $col): ?>
          <?php
            $colDef = $schema['columns'][$col] ?? ['type' => 'text'];
            $value = $row[$col] ?? null;
            $displayValue = match ($colDef['type']) {
                'checkbox' => $value ? 'Oui' : 'Non',
                'datetime', 'date' => format_datetime($value),
                default => truncate_text((string) $value, 40),
            };
          ?>
          <?php if ($i === 0): ?>
            <div class="fw-semibold mb-1"><?= e($displayValue) ?></div>
          <?php else: ?>
            <div class="small text-secondary">
              <span class="fw-medium"><?= e($colDef['label']) ?> :</span> <?= e($displayValue) ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>

        <div class="d-flex gap-2 mt-3">
          <a href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&mode=edit&pk=' . urlencode($row[$pk])) ?>"
             class="btn btn-sm btn-outline-secondary flex-grow-1">
            <i class="bi bi-pencil me-1"></i> Modifier
          </a>
          <form method="post" action="<?= admin_url('table.php?t=' . urlencode($tableKey)) ?>"
                class="flex-grow-1" data-confirm="Supprimer définitivement cet élément ?">
            <input type="hidden" name="csrf_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="pk" value="<?= e($row[$pk]) ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
              <i class="bi bi-trash me-1"></i> Supprimer
            </button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($result['total_pages'] > 1): ?>
    <?php

        $paginationTable = [];
        if ($result['page'] == 1)  $paginationTable[] = 1;

        for ($j = -2; $j <= 2; $j++) {
            if (($j + $result['page']) > 1 and ($j + $result['page']) < $result['total_pages']) {
                $paginationTable[] = $j + $result['page'];
            }
        }
        if ($result['page'] == $result['total_pages'])  $paginationTable[] = $result['total_pages'];
        $paginationCnt = sizeof($paginationTable);
    ?>

    <nav class="mt-4 d-flex justify-content-center">
      <ul class="pagination mb-0">
          <li class="page-item">
              <a class="page-link" href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&page=1&q=' . urlencode($search)) ?>"><<</a>
          </li>
          <li class="page-item">
              <a class="page-link" href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&page='.($result['page']-1>0?$result['page']-1:1).'&q=' . urlencode($search)) ?>"><</a>
          </li>


        <?php for ($p = 0; $p < $paginationCnt; $p++): ?>
          <li class="page-item <?= $paginationTable[$p] === $result['page'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&page=' . $paginationTable[$p] . '&q=' . urlencode($search)) ?>">
              <?= $paginationTable[$p] ?>
            </a>
          </li>
        <?php endfor; ?>

          <li class="page-item">
              <a class="page-link" href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&page='.($result['page']+1<=$result['total_pages']?$result['page']+1:$result['total_pages']).'&q=' . urlencode($search)) ?>"> > </a>
          </li>
          <li class="page-item">
              <a class="page-link" href="<?= admin_url('table.php?t=' . urlencode($tableKey) . '&page='.$result['total_pages'].'&q=' . urlencode($search)) ?>"> >> </a>
          </li>



      </ul>
    </nav>
  <?php endif; ?>

<?php endif; ?>
