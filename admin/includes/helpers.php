<?php
/**
 * Fonctions utilitaires partagées par les vues.
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function admin_url(string $path = ''): string
{
    return '/admin/' . ltrim($path, '/');
}

function format_datetime(?string $value): string
{
    if (empty($value)) {
        return '—';
    }
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $value) ?: date_create($value);
    return $dt ? $dt->format('d/m/Y H:i') : $value;
}

function truncate_text(?string $text, int $length = 60): string
{
    $text = trim(strip_tags($text ?? ''));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '…';
}

function role_label(string $role): string
{
    return match ($role) {
        'admin'  => 'Administrateur',
        'editor' => 'Éditeur',
        default  => $role,
    };
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * @return array<int, array{type:string,message:string}>
 */
function flash_get_all(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}
