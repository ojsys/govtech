<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Payment extends Model
{
    protected static string $table = 'payments';

    /** Record a gateway event (verify result or webhook) for audit. */
    public static function record(int $orderId, string $reference, int $amountKobo, string $status, array $raw): int
    {
        return Database::insert('payments', [
            'order_id'     => $orderId,
            'gateway'      => 'paystack',
            'reference'    => $reference,
            'amount_kobo'  => $amountKobo,
            'status'       => $status,
            'raw_response' => json_encode($raw, JSON_UNESCAPED_SLASHES),
        ]);
    }
}
