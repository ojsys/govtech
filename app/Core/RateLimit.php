<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Simple fixed-window rate limiter backed by the session. Good enough for
 * public forms (register, newsletter, nominate). For cross-session limits
 * (e.g. by IP across the whole app) move this to a DB table later.
 */
final class RateLimit
{
    /** Returns true if the action is allowed; false if the limit is exceeded. */
    public static function allow(string $key, int $maxAttempts = 5, int $windowSeconds = 60): bool
    {
        $now = time();
        $bucket = $_SESSION['_rl'][$key] ?? ['count' => 0, 'reset' => $now + $windowSeconds];

        if ($now > $bucket['reset']) {
            $bucket = ['count' => 0, 'reset' => $now + $windowSeconds];
        }
        $bucket['count']++;
        $_SESSION['_rl'][$key] = $bucket;

        return $bucket['count'] <= $maxAttempts;
    }

    /** Honeypot check: the named hidden field must be empty (bots fill it). */
    public static function honeypotPassed(Request $request, string $field = 'website'): bool
    {
        return trim((string) $request->input($field, '')) === '';
    }
}
