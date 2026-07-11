<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class NewsletterSubscriber extends Model
{
    protected static string $table = 'newsletter_subscribers';

    /** Idempotent subscribe — safe to call repeatedly for the same email. */
    public static function subscribe(string $name, string $email): void
    {
        $email = mb_strtolower(trim($email));
        $existing = Database::first('SELECT id FROM newsletter_subscribers WHERE email = ? LIMIT 1', [$email]);
        if ($existing) {
            Database::run('UPDATE newsletter_subscribers SET name = ? WHERE id = ?', [$name, (int) $existing['id']]);
            return;
        }
        Database::insert('newsletter_subscribers', ['name' => $name, 'email' => $email, 'confirmed' => 0]);
    }
}
