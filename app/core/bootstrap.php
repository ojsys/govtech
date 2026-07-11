<?php
/**
 * Application bootstrap: paths, autoloader, config, error handling, session.
 * Included once by public_html/index.php (the front controller).
 */
declare(strict_types=1);

define('APP_START', microtime(true));
define('BASE_PATH', dirname(__DIR__, 2));          // project root
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', APP_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEW_PATH', APP_PATH . '/views');

/* ---- Composer autoload (PHPMailer, chillerlan/php-qrcode) if present ---- */
$composer = BASE_PATH . '/vendor/autoload.php';
if (is_file($composer)) {
    require $composer;
}

/* ---- PSR-4-ish autoloader for App\ classes ---- */
spl_autoload_register(static function (string $class): void {
    if (strncmp($class, 'App\\', 4) !== 0) {
        return;
    }
    $rel = str_replace('\\', '/', substr($class, 4));   // App\Core\Foo -> Core/Foo
    $file = APP_PATH . '/' . $rel . '.php';
    if (is_file($file)) {
        require $file;
    }
});

/* ---- Config loader ---- */
final class Config
{
    private static array $cache = [];

    /** dot access: Config::get('paystack.secret_key') or Config::get('app.env') */
    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);
        // 'app' is an alias for config.php so callers read app.env, app.base_url, etc.
        $fileName = $file === 'app' ? 'config' : $file;

        if (!array_key_exists($fileName, self::$cache)) {
            $full = CONFIG_PATH . "/{$fileName}.php";
            self::$cache[$fileName] = is_file($full) ? require $full : [];
        }
        $data = self::$cache[$fileName];
        if ($path === null) {
            return $data;
        }
        foreach (explode('.', $path) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }
        return $data;
    }
}

/* ---- Error & exception handling ---- */
$isDev = Config::get('app.env') === 'development';
error_reporting(E_ALL);
ini_set('display_errors', $isDev ? '1' : '0');
ini_set('log_errors', '1');
@ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

set_exception_handler(static function (Throwable $e) use ($isDev): void {
    error_log('[' . date('c') . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if ($isDev) {
        echo '<pre style="padding:20px;font:14px monospace;background:#111;color:#f66;white-space:pre-wrap">';
        echo htmlspecialchars((string) $e);
        echo '</pre>';
    } else {
        // Minimal, brand-neutral fallback so we never leak internals in production.
        echo '<!doctype html><meta charset="utf-8"><title>Something went wrong</title>'
           . '<body style="font:16px system-ui;background:#07140E;color:#E8EFE9;'
           . 'display:grid;place-items:center;height:100vh;margin:0;text-align:center">'
           . '<div><h1 style="font-weight:600">We hit a snag</h1>'
           . '<p>Please try again shortly.</p></div>';
    }
    exit;
});

/* ---- Session (HttpOnly, SameSite=Lax; Secure when on HTTPS) ---- */
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name((string) Config::get('app.session_name', 'govtech_sess'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---- Helpers ---- */
require APP_PATH . '/core/helpers.php';
