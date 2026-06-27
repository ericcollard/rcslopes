<?php
/**
 * Gestion des images stockées dans assets/images :
 * upload (avec validation stricte du type réel), listing, suppression.
 */

require_once __DIR__ . '/../config/config.php';

final class ImageManager
{
    /**
     * Upload sécurisé d'une image envoyée via $_FILES.
     *
     * @return array{success: bool, message: string, filename?: string, url?: string}
     */
    public static function upload(array $file): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Paramètres de fichier invalides.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'message' => 'Aucun fichier sélectionné.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'message' => 'Le fichier dépasse la taille maximale autorisée.'];
            default:
                return ['success' => false, 'message' => 'Erreur lors du téléversement (code ' . $file['error'] . ').'];
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return ['success' => false, 'message' => sprintf(
                'Fichier trop volumineux (max %d Mo).',
                UPLOAD_MAX_SIZE / 1024 / 1024
            )];
        }

        // Vérifie le VRAI type MIME (pas l'extension ni le Content-Type déclaré par le client)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if (!isset(UPLOAD_ALLOWED_MIME[$realMime])) {
            return ['success' => false, 'message' => 'Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP.'];
        }

        // Vérifie que c'est bien une image décodable (protège contre les polyglottes image/PHP)
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => 'Le fichier ne semble pas être une image valide.'];
        }

        $extension = UPLOAD_ALLOWED_MIME[$realMime];
        $filename  = self::generateSafeFilename($file['name'], $extension);
        $destPath  = IMAGES_DIR . '/' . $filename;

        if (!is_dir(IMAGES_DIR)) {
            mkdir(IMAGES_DIR, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'message' => 'Impossible d\'enregistrer le fichier sur le serveur.'];
        }

        chmod($destPath, 0644);

        return [
            'success'  => true,
            'message'  => 'Image téléversée avec succès.',
            'filename' => $filename,
            'url'      => IMAGES_URL . '/' . $filename,
        ];
    }

    private static function generateSafeFilename(string $originalName, string $extension): string
    {
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $base);
        $base = trim($base, '-');
        $base = $base === '' ? 'image' : mb_substr($base, 0, 60);

        $unique = $base . '-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);

        return $unique . '.' . $extension;
    }

    /**
     * Liste les images du répertoire, triées par date de modification décroissante.
     *
     * @return array<int, array{filename: string, url: string, size: int, mtime: int}>
     */
    public static function list(): array
    {
        if (!is_dir(IMAGES_DIR)) {
            return [];
        }

        $allowedExt = array_values(UPLOAD_ALLOWED_MIME);
        $files = [];

        foreach (scandir(IMAGES_DIR) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $fullPath = IMAGES_DIR . '/' . $entry;
            if (!is_file($fullPath)) {
                continue;
            }
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                continue;
            }

            $files[] = [
                'filename' => $entry,
                'url'      => IMAGES_URL . '/' . $entry,
                'size'     => filesize($fullPath),
                'mtime'    => filemtime($fullPath),
            ];
        }

        usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);

        return $files;
    }

    /**
     * Supprime une image par son nom de fichier (jamais par chemin arbitraire).
     */
    public static function delete(string $filename): array
    {
        $filename = basename($filename); // empêche toute traversée de répertoire (../)
        $fullPath = IMAGES_DIR . '/' . $filename;

        if (!is_file($fullPath)) {
            return ['success' => false, 'message' => 'Fichier introuvable.'];
        }

        if (!unlink($fullPath)) {
            return ['success' => false, 'message' => 'Impossible de supprimer le fichier.'];
        }

        return ['success' => true, 'message' => 'Image supprimée.'];
    }

    public static function formatSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' o';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' Ko';
        }
        return round($bytes / (1024 * 1024), 2) . ' Mo';
    }
}
