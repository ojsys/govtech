<?php
/**
 * Global view/utility helpers. Loaded by bootstrap.
 */
declare(strict_types=1);

if (!function_exists('e')) {
    /** Escape for HTML output. Treat ALL db/user content as untrusted in views. */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/** Build an absolute URL from a path using config base_url. */
function url(string $path = '/'): string
{
    $base = rtrim((string) Config::get('app.base_url', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

/** Asset URL with a cache-busting version stamp. */
function asset(string $path): string
{
    $path = '/assets/' . ltrim($path, '/');
    $full = BASE_PATH . '/public_html' . $path;
    $v = is_file($full) ? substr((string) filemtime($full), -6) : '1';
    return $path . '?v=' . $v;
}

/** Format kobo (integer) as Naira for display. ₦7,500,000 kobo -> "75,000". */
function naira(int|string|null $kobo, bool $withSymbol = false): string
{
    $kobo = (int) $kobo;
    $naira = intdiv($kobo, 100);
    $out = number_format($naira);
    return $withSymbol ? '₦' . $out : $out;
}

/** Old form input after a failed POST (flash). */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

/** One-shot flash message helpers. */
function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }
    $val = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $val;
}

/** Redirect and stop. */
function redirect(string $path): never
{
    $location = preg_match('#^https?://#', $path) ? $path : url($path);
    header('Location: ' . $location, true, 302);
    exit;
}

/** JSON response and stop. */
function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/** Public URL a ticket QR encodes — scanning it affirms the ticket's authenticity. */
function ticket_verify_url(string $code): string
{
    return url('/verify?code=' . rawurlencode($code));
}

/** Editable site content (with schema default fallback). */
function content(string $key, ?string $fallback = null): string
{
    return \App\Models\Content::get($key, $fallback);
}

/** URL for an uploaded branding image (logo/favicon), or '' if unset. */
function content_image(string $key): string
{
    return \App\Models\Content::image($key);
}

/** Parse an admin Naira input ("75,000" or "75000") into integer kobo. */
function kobo_from_naira(string $value): int
{
    $clean = preg_replace('/[^0-9.]/', '', $value) ?? '0';
    return (int) round(((float) $clean) * 100);
}

/** Kobo -> plain Naira number for a form input value (no commas). */
function naira_input(int|string|null $kobo): string
{
    return (string) intdiv((int) $kobo, 100);
}

/** JSON array column -> newline-separated text for a textarea. */
function perks_to_lines(mixed $json): string
{
    return implode("\n", json_col($json));
}

/** Newline-separated textarea -> JSON array string (trimmed, no blanks). */
function perks_from_lines(string $text): string
{
    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: [])));
    return json_encode($lines, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

/** Make a URL-safe slug from a string. */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/** Decode a JSON column safely to an array. */
function json_col(mixed $raw): array
{
    if (is_array($raw)) {
        return $raw;
    }
    if (!is_string($raw) || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}
