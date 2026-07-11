<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Read-only aggregate queries for the admin reports dashboard + exports.
 */
final class Report extends Model
{
    /** Paid revenue + quantity grouped by ticket type. */
    public static function revenueByTicketType(): array
    {
        return Database::all(
            "SELECT tt.name,
                    SUM(oi.quantity) AS qty,
                    SUM(oi.subtotal_kobo) AS revenue_kobo
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             JOIN ticket_types tt ON tt.id = oi.ticket_type_id
             WHERE o.status = 'paid'
             GROUP BY tt.id, tt.name
             ORDER BY revenue_kobo DESC"
        );
    }

    /** Paid registrations grouped by sector. */
    public static function registrationsBySector(): array
    {
        return Database::all(
            "SELECT COALESCE(NULLIF(a.sector,''),'other') AS sector, COUNT(*) AS c
             FROM attendees a
             JOIN orders o ON o.attendee_id = a.id
             WHERE o.status = 'paid'
             GROUP BY sector ORDER BY c DESC"
        );
    }

    /** Issued vs checked-in, grouped by ticket type (incl. comp passes). */
    public static function checkinByType(): array
    {
        return Database::all(
            "SELECT COALESCE(tt.name,'Complimentary') AS name,
                    COUNT(*) AS issued,
                    SUM(CASE WHEN t.status = 'checked_in' THEN 1 ELSE 0 END) AS checked_in
             FROM tickets t
             LEFT JOIN ticket_types tt ON tt.id = t.ticket_type_id
             WHERE t.status <> 'void'
             GROUP BY name ORDER BY issued DESC"
        );
    }

    /** Verified votes per award category. */
    public static function votesByCategory(): array
    {
        return Database::all(
            "SELECT c.title, COALESCE(SUM(n.votes_count),0) AS votes,
                    COUNT(CASE WHEN n.status='shortlisted' THEN 1 END) AS shortlisted
             FROM award_categories c
             LEFT JOIN nominations n ON n.category_id = c.id
             WHERE c.event_id = ?
             GROUP BY c.id, c.title ORDER BY votes DESC",
            [self::eventId()]
        );
    }

    /** Sponsor pipeline counts by status + comp passes issued. */
    public static function sponsorSummary(): array
    {
        $byStatus = Database::all(
            'SELECT status, COUNT(*) AS c FROM sponsor_applications GROUP BY status'
        );
        $comp = (int) Database::scalar("SELECT COUNT(*) FROM tickets WHERE source = 'comp' AND status <> 'void'");
        return ['byStatus' => $byStatus, 'comp_passes' => $comp];
    }

    /** One row per issued pass — the master attendee/delegate list. */
    public static function passRows(): array
    {
        return Database::all(
            "SELECT t.ticket_code, COALESCE(tt.name,'Complimentary') AS pass_type,
                    t.holder_name, t.holder_email, t.source, t.status,
                    t.checked_in_at, o.reference AS order_reference,
                    a.organization, a.phone
             FROM tickets t
             LEFT JOIN ticket_types tt ON tt.id = t.ticket_type_id
             LEFT JOIN order_items oi ON oi.id = t.order_item_id
             LEFT JOIN orders o ON o.id = oi.order_id
             LEFT JOIN attendees a ON a.id = t.attendee_id
             WHERE t.status <> 'void'
             ORDER BY t.id ASC"
        );
    }
}
