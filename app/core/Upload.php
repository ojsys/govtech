<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Safe image upload handling: validates real MIME + extension + size, writes a
 * random filename, never trusts the client name, and stores under
 * public_html/uploads (which is set no-exec by its own .htaccess).
 */
final class Upload
{
    private const MAX_BYTES = 4_194_304; // 4 MB
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * Handle a single $_FILES entry. Returns the stored relative filename
     * (e.g. "spk_ab12cd34.jpg") or null if no file was uploaded.
     * @throws \RuntimeException on a rejected upload.
     */
    public static function image(array $file, string $prefix = 'img'): ?string
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed (code ' . $file['error'] . ').');
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new \RuntimeException('Image is too large (max 4 MB).');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime])) {
            throw new \RuntimeException('Only JPG, PNG, WEBP or GIF images are allowed.');
        }
        $ext = self::ALLOWED[$mime];

        $dir = (string) \Config::get('app.uploads_dir', BASE_PATH . '/public_html/uploads');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = rtrim($dir, '/') . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Could not save the uploaded image.');
        }
        @chmod($dest, 0644);
        return $name;
    }

    /**
     * Handle a document/asset upload (sponsor brochure ads etc.): images + PDF.
     * Same hardening as image(): real-MIME check, size cap, random filename.
     */
    public static function document(array $file, string $prefix = 'doc'): ?string
    {
        $allowed = self::ALLOWED + ['application/pdf' => 'pdf'];
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload failed (code ' . $file['error'] . ').');
        }
        if (($file['size'] ?? 0) > 8_388_608) { // 8 MB for documents
            throw new \RuntimeException('File is too large (max 8 MB).');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload.');
        }
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            throw new \RuntimeException('Allowed file types: JPG, PNG, WEBP, GIF or PDF.');
        }
        $ext = $allowed[$mime];
        $dir = (string) \Config::get('app.uploads_dir', BASE_PATH . '/public_html/uploads');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $name = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], rtrim($dir, '/') . '/' . $name)) {
            throw new \RuntimeException('Could not save the uploaded file.');
        }
        @chmod(rtrim($dir, '/') . '/' . $name, 0644);
        return $name;
    }

    /** Delete a previously uploaded file by its stored name (best effort). */
    public static function delete(?string $name): void
    {
        if (!$name) {
            return;
        }
        $name = basename($name); // never traverse out of uploads
        $dir = (string) \Config::get('app.uploads_dir', BASE_PATH . '/public_html/uploads');
        $path = rtrim($dir, '/') . '/' . $name;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
