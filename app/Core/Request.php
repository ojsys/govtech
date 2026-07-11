<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Read-only view of the incoming HTTP request.
 */
final class Request
{
    public function method(): string
    {
        $m = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Support method override for HTML forms (_method=PUT/DELETE).
        if ($m === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }
        return $m;
    }

    /** Path without query string, normalised (no trailing slash except root). */
    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = rawurldecode($uri);
        $uri = '/' . trim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    public function isPost(): bool { return $this->method() === 'POST'; }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /** Trimmed string input. */
    public function str(string $key, string $default = ''): string
    {
        $v = $this->input($key, $default);
        return is_string($v) ? trim($v) : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    public function all(): array
    {
        return $_POST + $_GET;
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function rawBody(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }

    public function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json')
            || ($this->header('X-Requested-With') === 'XMLHttpRequest');
    }
}
