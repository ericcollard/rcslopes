<?php
/**
 * Traitement et validation des données de formulaire selon le schéma
 * déclaré dans TableRegistry pour une table donnée.
 */

require_once __DIR__ . '/HtmlSanitizer.php';

final class FormProcessor
{
    /**
     * Construit le tableau de données prêt pour CrudEngine::insert()/update()
     * à partir de $_POST, en respectant les types déclarés dans le schéma.
     *
     * @return array{data: array, errors: array<string,string>}
     */
    public static function process(array $schema, array $post, bool $isCreate): array
    {
        $data   = [];
        $errors = [];

        foreach ($schema['columns'] as $colName => $colDef) {
            $type     = $colDef['type'] ?? 'text';
            $editable = $colDef['editable'] ?? true;

            // Colonnes non éditables (clé primaire, timestamps auto) : ignorées côté input.
            if (!$editable) {
                continue;
            }

            $raw = $post[$colName] ?? null;

            switch ($type) {
                case 'checkbox':
                    $data[$colName] = isset($post[$colName]) ? 1 : 0;
                    break;

                case 'number':
                    if ($raw === null || $raw === '') {
                        $data[$colName] = null;
                    } else {
                        if (!is_numeric($raw)) {
                            $errors[$colName] = $colDef['label'] . ' doit être un nombre entier.';
                            break;
                        }
                        $data[$colName] = (int) $raw;
                    }
                    break;

                case 'decimal':
                    if ($raw === null || $raw === '') {
                        $data[$colName] = null;
                    } else {
                        $normalized = str_replace(',', '.', (string) $raw);
                        if (!is_numeric($normalized)) {
                            $errors[$colName] = $colDef['label'] . ' doit être un nombre décimal.';
                            break;
                        }
                        $data[$colName] = (float) $normalized;
                    }
                    break;

                case 'select_multiple':
                    $values = is_array($raw) ? $raw : [];
                    $allowed = $colDef['options'] ?? [];
                    $values = array_values(array_intersect($values, $allowed));
                    $data[$colName] = $values === [] ? null : implode(',', $values);
                    break;

                case 'select':
                    if ($raw === null || $raw === '') {
                        $data[$colName] = null;
                    } else {
                        $data[$colName] = trim((string) $raw);
                    }
                    break;

                case 'lookup':
                    if ($raw === null || $raw === '') {
                        if (!empty($colDef['required'])) {
                            $errors[$colName] = $colDef['label'] . ' est obligatoire.';
                        }
                        $data[$colName] = null;
                    } else {
                        $data[$colName] = (int) $raw;
                    }
                    break;

                case 'datetime':
                case 'date':
                    if ($raw === null || $raw === '') {
                        $data[$colName] = null;
                    } else {
                        $data[$colName] = self::normalizeDatetime((string) $raw, $type);
                        if ($data[$colName] === null) {
                            $errors[$colName] = $colDef['label'] . ' : format de date invalide.';
                        }
                    }
                    break;

                case 'wysiwyg':
                    // Contenu HTML : nettoyé via HtmlSanitizer (balises/attributs autorisés uniquement)
                    $data[$colName] = HtmlSanitizer::clean((string) ($raw ?? ''));
                    break;

                case 'textarea':
                case 'text':
                default:
                    $value = trim((string) ($raw ?? ''));
                    if (isset($colDef['maxlength']) && mb_strlen($value) > $colDef['maxlength']) {
                        $errors[$colName] = sprintf(
                            '%s ne doit pas dépasser %d caractères.',
                            $colDef['label'],
                            $colDef['maxlength']
                        );
                    }
                    $data[$colName] = $value === '' ? null : $value;
                    break;
            }

            // Vérification "required" générique (sauf checkbox, déjà 0/1 par nature)
            if (!empty($colDef['required']) && $type !== 'checkbox') {
                if ($data[$colName] === null || $data[$colName] === '') {
                    $errors[$colName] = $colDef['label'] . ' est obligatoire.';
                }
            }
        }

        // Timestamps automatiques
        foreach ($schema['columns'] as $colName => $colDef) {
            if ($isCreate && !empty($colDef['auto_on_create'])) {
                $data[$colName] = date('Y-m-d H:i:s');
            }
            if (!empty($colDef['auto_always'])) {
                $data[$colName] = date('Y-m-d H:i:s');
            }
        }

        return ['data' => $data, 'errors' => $errors];
    }

    private static function normalizeDatetime(string $raw, string $type): ?string
    {
        $raw = trim($raw);
        // Les <input type="datetime-local"> envoient "YYYY-MM-DDTHH:MM"
        $raw = str_replace('T', ' ', $raw);

        $format = $type === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s';
        $fallbackFormat = $type === 'date' ? 'Y-m-d' : 'Y-m-d H:i';

        $dt = DateTime::createFromFormat($fallbackFormat, $raw) ?: DateTime::createFromFormat($format, $raw);

        if ($dt === false) {
            // Dernière tentative permissive
            $ts = strtotime($raw);
            if ($ts === false) {
                return null;
            }
            return date($format, $ts);
        }

        return $dt->format($format);
    }
}
