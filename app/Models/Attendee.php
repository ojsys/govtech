<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Attendee extends Model
{
    protected static string $table = 'attendees';

    /** Create an attendee (the buyer/registrant) and return the new id. */
    public static function create(array $d): int
    {
        return Database::insert('attendees', [
            'first_name'   => $d['first_name'] ?? '',
            'last_name'    => $d['last_name'] ?? '',
            'email'        => $d['email'] ?? '',
            'phone'        => $d['phone'] ?? '',
            'organization' => $d['organization'] ?? '',
            'job_title'    => $d['job_title'] ?? '',
            'sector'       => $d['sector'] ?? 'other',
            'state'        => $d['state'] ?? '',
        ]);
    }

    public static function fullName(array $a): string
    {
        return trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
    }
}
