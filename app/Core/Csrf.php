<?php
declare(strict_types=1);

namespace App\Core;

/**
 * CSRF protection. One token per session; verified on every state-changing POST.
 */
final class Csrf
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    /** Hidden input for forms. */
    public static function field(): string
    {
        return '<input type="hidden" name="_token" value="' . self::token() . '">';
    }

    public static function check(?string $token): bool
    {
        $stored = $_SESSION[self::KEY] ?? '';
        return is_string($token) && $stored !== '' && hash_equals($stored, $token);
    }

    /** Verify or abort with 419. Call at the top of every POST handler. */
    public static function verify(Request $request): void
    {
        $token = $request->input('_token');
        if (!self::check(is_string($token) ? $token : null)) {
            http_response_code(419);
            if ($request->wantsJson()) {
                json_response(['error' => 'CSRF token mismatch. Please refresh and try again.'], 419);
            }
            echo '<h1>419</h1><p>Your session expired. Please go back, refresh, and try again.</p>';
            exit;
        }
    }
}
