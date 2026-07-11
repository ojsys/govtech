<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Thin PDO wrapper (singleton). Prepared statements only — never concatenate SQL.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $cfg = \Config::get('database');
        if (!$cfg) {
            throw new RuntimeException('Database config missing (app/config/database.php).');
        }
        // Production uses MySQL. 'sqlite' is supported only for local dev/preview.
        $driver = $cfg['driver'] ?? 'mysql';
        if ($driver === 'sqlite') {
            $dsn = 'sqlite:' . $cfg['path'];
            $user = null;
            $pass = null;
        } else {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'],
                (int) ($cfg['port'] ?? 3306),
                $cfg['name'],
                $cfg['charset'] ?? 'utf8mb4'
            );
            $user = $cfg['user'];
            $pass = $cfg['pass'];
        }
        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Don't leak credentials/DSN to the client.
            error_log('DB connect failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed.', 0, $e);
        }
        return self::$pdo;
    }

    /** Run a prepared statement and return the PDOStatement. */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** First row or null. */
    public static function first(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** All rows. */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** Single scalar value from the first column. */
    public static function scalar(string $sql, array $params = []): mixed
    {
        return self::run($sql, $params)->fetchColumn();
    }

    /** Insert helper; returns last insert id. */
    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $place = array_map(static fn($c) => ':' . $c, $cols);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $cols),
            implode(', ', $place)
        );
        self::run($sql, $data);
        return (int) self::pdo()->lastInsertId();
    }

    public static function beginTransaction(): void { self::pdo()->beginTransaction(); }
    public static function commit(): void { self::pdo()->commit(); }
    public static function rollBack(): void { if (self::pdo()->inTransaction()) self::pdo()->rollBack(); }
}
