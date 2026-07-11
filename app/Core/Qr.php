<?php
declare(strict_types=1);

namespace App\Core;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * QR code generation for tickets. Prefers chillerlan/php-qrcode (pure PHP, SVG
 * output — no GD needed). Degrades to a labelled placeholder SVG when the
 * library isn't installed yet (e.g. before `composer install` on the host),
 * so pages/emails still render. Files are cached under storage/qr.
 */
final class Qr
{
    /** Generate (or reuse cached) an SVG QR for $data; returns the file path. */
    public static function toFile(string $data, string $cacheName): string
    {
        $dir = STORAGE_PATH . '/qr';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $path = $dir . '/' . preg_replace('/[^A-Za-z0-9_\-]/', '', $cacheName) . '.svg';
        if (is_file($path)) {
            return $path;
        }
        file_put_contents($path, self::svg($data));
        return $path;
    }

    /** Return the QR as an inline SVG string. */
    public static function svg(string $data): string
    {
        if (class_exists(QRCode::class)) {
            $options = new QROptions([
                'version'      => QRCode::VERSION_AUTO,
                'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel'     => QRCode::ECC_M,
                'svgViewBoxSize' => 0,
                'imageBase64'  => false,
                'addQuietzone' => true,
            ]);
            return (new QRCode($options))->render($data);
        }
        return self::placeholder($data);
    }

    /**
     * Return the QR as raw PNG bytes. Used for email (clients don't render
     * inline SVG). chillerlan + GD on the host; a GD placeholder otherwise.
     */
    public static function png(string $data, int $scale = 6): string
    {
        return self::pngReal($data, $scale) ?? self::placeholderPng();
    }

    /**
     * Real scannable PNG, or null if we can't make one. chillerlan (preferred,
     * fully local) → external QR service (fallback when the lib isn't installed)
     * → null. Callers cache the result so the service is hit at most once.
     */
    public static function pngReal(string $data, int $scale = 6): ?string
    {
        if (class_exists(QRCode::class)) {
            try {
                $options = new QROptions([
                    'outputType'  => QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel'    => QRCode::ECC_M,
                    'scale'       => $scale,
                    'imageBase64' => false,
                    'addQuietzone'=> true,
                ]);
                return (new QRCode($options))->render($data);
            } catch (\Throwable $e) {
                error_log('QR PNG render failed: ' . $e->getMessage());
            }
        }
        return self::fetchRemote($data);
    }

    /** Fetch a real QR PNG from a public QR service (only used without chillerlan). */
    private static function fetchRemote(string $data): ?string
    {
        $url = 'https://api.qrserver.com/v1/create-qr-code/?'
            . http_build_query(['size' => '600x600', 'margin' => '12', 'ecc' => 'M', 'format' => 'png', 'data' => $data]);
        $png = null;
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $png = ($res !== false && $code === 200) ? $res : null;
        } else {
            $res = @file_get_contents($url);
            $png = $res !== false ? $res : null;
        }
        // Validate it's actually a PNG.
        return (is_string($png) && strncmp($png, "\x89PNG", 4) === 0) ? $png : null;
    }

    /** A simple GD-drawn placeholder PNG for when no QR can be generated. */
    public static function placeholderPng(): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            // No GD — return a 1x1 transparent PNG so <img> doesn't break.
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M8AAAMBAQDJ/pLvAAAAAElFTkSuQmCC');
        }
        $size = 180;
        $img = imagecreatetruecolor($size, $size);
        $bg = imagecolorallocate($img, 245, 242, 233);
        $green = imagecolorallocate($img, 12, 122, 77);
        $ink = imagecolorallocate($img, 7, 20, 14);
        imagefill($img, 0, 0, $bg);
        imagerectangle($img, 8, 8, $size - 9, $size - 9, $green);
        imagestring($img, 5, 40, 78, 'QR PENDING', $ink);
        ob_start();
        imagepng($img);
        $png = (string) ob_get_clean();
        imagedestroy($img);
        return $png;
    }

    /** Return the QR as a data: URI (handy for embedding directly in <img>). */
    public static function dataUri(string $data): string
    {
        if (class_exists(QRCode::class)) {
            $options = new QROptions([
                'outputType'  => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel'    => QRCode::ECC_M,
                'imageBase64' => true,
            ]);
            return (new QRCode($options))->render($data);
        }
        return 'data:image/svg+xml;base64,' . base64_encode(self::placeholder($data));
    }

    /** Minimal placeholder so the UI isn't broken before the QR lib is installed. */
    private static function placeholder(string $data): string
    {
        $label = htmlspecialchars(substr($data, 0, 24), ENT_QUOTES);
        return '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160">'
            . '<rect width="160" height="160" fill="#F5F2E9"/>'
            . '<rect x="8" y="8" width="144" height="144" fill="none" stroke="#0C7A4D" stroke-width="2"/>'
            . '<text x="80" y="76" font-family="monospace" font-size="9" fill="#07140E" text-anchor="middle">QR PENDING</text>'
            . '<text x="80" y="92" font-family="monospace" font-size="8" fill="#5a6a60" text-anchor="middle">' . $label . '</text>'
            . '</svg>';
    }
}
