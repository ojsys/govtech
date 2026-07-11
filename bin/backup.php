<?php
/**
 * Database backup — cron-friendly. Dumps the configured MySQL database to
 * storage/backups/ (gzipped) and keeps the most recent N files.
 *
 * Manual:   php bin/backup.php
 * Cron:     0 3 * * *  php /home/USER/bin/backup.php >> /home/USER/storage/logs/backup.log 2>&1
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

require dirname(__DIR__) . '/app/Core/bootstrap.php';

const KEEP = 14; // retain this many backups

$db = Config::get('database');
if (!$db || ($db['driver'] ?? 'mysql') !== 'mysql') {
    fwrite(STDERR, "backup: only MySQL is supported (driver is '" . ($db['driver'] ?? 'mysql') . "').\n");
    exit(1);
}

$dir = STORAGE_PATH . '/backups';
if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
    fwrite(STDERR, "backup: cannot create {$dir}\n");
    exit(1);
}

$stamp = date('Ymd-His');
$file = "{$dir}/govtech-{$stamp}.sql.gz";

// Pass the password via env to keep it off the process list.
$cmd = sprintf(
    'MYSQL_PWD=%s mysqldump --single-transaction --quick --default-character-set=utf8mb4 -h%s -P%d -u%s %s 2>/dev/null | gzip > %s',
    escapeshellarg((string) $db['pass']),
    escapeshellarg((string) $db['host']),
    (int) ($db['port'] ?? 3306),
    escapeshellarg((string) $db['user']),
    escapeshellarg((string) $db['name']),
    escapeshellarg($file)
);

passthru($cmd, $code);

if ($code !== 0 || !is_file($file) || filesize($file) < 64) {
    fwrite(STDERR, "[" . date('c') . "] backup FAILED (exit {$code}). Is mysqldump available on this host?\n");
    @unlink($file);
    exit(1);
}

// Rotate: keep the newest KEEP files.
$backups = glob("{$dir}/govtech-*.sql.gz") ?: [];
rsort($backups);
foreach (array_slice($backups, KEEP) as $old) {
    @unlink($old);
}

echo "[" . date('c') . "] backup OK: " . basename($file) . ' (' . number_format(filesize($file) / 1024, 1) . " KB), " . min(count($backups), KEEP) . " kept\n";
exit(0);
