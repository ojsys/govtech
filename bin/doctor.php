<?php
/**
 * Deployment health check. Run on the host after uploading:
 *   php bin/doctor.php
 * Reports OK / WARN / FAIL for environment, config, DB, permissions and secrets.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require dirname(__DIR__) . '/app/core/bootstrap.php';

use App\Core\Database;

$pass = 0; $warn = 0; $fail = 0;
function line(string $status, string $msg): void
{
    global $pass, $warn, $fail;
    $icon = match ($status) { 'OK' => "\033[32m  OK \033[0m", 'WARN' => "\033[33mWARN \033[0m", default => "\033[31mFAIL \033[0m" };
    $status === 'OK' ? $pass++ : ($status === 'WARN' ? $warn++ : $fail++);
    echo $icon . ' ' . $msg . "\n";
}

echo "\nNigeria GovTech — deployment doctor\n===================================\n\n";

/* ---- PHP + extensions ---- */
echo "Environment\n";
version_compare(PHP_VERSION, '8.2.0', '>=')
    ? line('OK', 'PHP ' . PHP_VERSION)
    : line('WARN', 'PHP ' . PHP_VERSION . ' (8.2+ recommended for production)');
foreach (['pdo_mysql', 'curl', 'mbstring', 'gd', 'openssl', 'json', 'fileinfo'] as $ext) {
    extension_loaded($ext) ? line('OK', "ext: {$ext}") : line('FAIL', "ext: {$ext} missing");
}

/* ---- Config files ---- */
echo "\nConfiguration\n";
foreach (['config', 'database', 'paystack', 'mail'] as $cfg) {
    is_file(CONFIG_PATH . "/{$cfg}.php") ? line('OK', "config: {$cfg}.php") : line('FAIL', "config: {$cfg}.php missing (copy {$cfg}.php.example)");
}
$env = Config::get('app.env');
$base = (string) Config::get('app.base_url', '');
$key = (string) Config::get('app.app_key', '');
$env === 'production' ? line('OK', "env = production") : line('WARN', "env = {$env} (set to 'production' for go-live)");
str_starts_with($base, 'https://') ? line('OK', "base_url is HTTPS ({$base})") : line('WARN', "base_url is not HTTPS ({$base})");
($key !== '' && !str_contains($key, 'CHANGE_ME')) ? line('OK', 'app_key is set') : line('FAIL', 'app_key is unset/default — generate one');

/* ---- Paystack + mail secrets ---- */
echo "\nSecrets\n";
$psk = (string) Config::get('paystack.secret_key', '');
if ($psk === '' || str_contains($psk, 'xxxxxx')) {
    line('FAIL', 'Paystack secret key is a placeholder');
} else {
    line(str_starts_with($psk, 'sk_live_') ? 'OK' : 'WARN', 'Paystack key ' . (str_starts_with($psk, 'sk_live_') ? '(LIVE)' : '(TEST — switch to live for go-live)'));
}
$mailDriver = Config::get('mail.driver');
$mailDriver === 'smtp' && !str_contains((string) Config::get('mail.password', ''), 'CHANGE_ME')
    ? line('OK', 'Mail: SMTP configured')
    : line('WARN', "Mail driver = {$mailDriver} (configure Brevo SMTP for transactional email)");

/* ---- Composer deps ---- */
echo "\nDependencies\n";
is_file(BASE_PATH . '/vendor/autoload.php') ? line('OK', 'vendor/ present') : line('WARN', 'vendor/ missing — run composer install (needed for real QR + PHPMailer)');
class_exists(\chillerlan\QRCode\QRCode::class) ? line('OK', 'QR library available') : line('WARN', 'chillerlan/php-qrcode not loaded (QR placeholder will be used)');
class_exists(\PHPMailer\PHPMailer\PHPMailer::class) ? line('OK', 'PHPMailer available') : line('WARN', 'PHPMailer not loaded (email will fall back to mail()/log)');

/* ---- Writable paths ---- */
echo "\nPermissions\n";
foreach ([STORAGE_PATH . '/logs', STORAGE_PATH . '/qr', BASE_PATH . '/public_html/uploads'] as $dir) {
    is_dir($dir) && is_writable($dir) ? line('OK', "writable: " . str_replace(BASE_PATH . '/', '', $dir)) : line('FAIL', "not writable: " . str_replace(BASE_PATH . '/', '', $dir));
}

/* ---- Database ---- */
echo "\nDatabase\n";
try {
    Database::pdo();
    line('OK', 'DB connection');
    foreach (['events', 'ticket_types', 'orders', 'tickets', 'users', 'sponsor_applications'] as $t) {
        try {
            Database::scalar("SELECT COUNT(*) FROM {$t}");
            line('OK', "table: {$t}");
        } catch (\Throwable $e) {
            line('FAIL', "table: {$t} — did you import schema.sql?");
        }
    }
    // Default admin password still in use?
    $admin = Database::first("SELECT password_hash FROM users WHERE role='superadmin' ORDER BY id ASC LIMIT 1");
    if ($admin && password_verify('admin1234', $admin['password_hash'])) {
        line('WARN', "Default admin password ('admin1234') is still active — change it now");
    } elseif ($admin) {
        line('OK', 'Default admin password changed');
    }
} catch (\Throwable $e) {
    line('FAIL', 'DB connection failed: ' . $e->getMessage());
}

echo "\n-----------------------------------\n";
echo "\033[32m{$pass} OK\033[0m  \033[33m{$warn} WARN\033[0m  \033[31m{$fail} FAIL\033[0m\n\n";
exit($fail > 0 ? 1 : 0);
