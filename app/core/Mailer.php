<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Transactional email via Brevo SMTP (PHPMailer), with a PHP mail() fallback
 * and a "log" no-op for local dev when no SMTP is configured.
 */
final class Mailer
{
    private array $cfg;

    public function __construct()
    {
        $this->cfg = (array) \Config::get('mail', []);
    }

    /**
     * @param array $attachments  list of ['path'=>..., 'name'=>...] or ['data'=>..., 'name'=>..., 'type'=>...]
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody, array $attachments = []): bool
    {
        $driver = $this->cfg['driver'] ?? 'log';

        // Use PHPMailer when available and SMTP is configured.
        if ($driver === 'smtp' && class_exists(PHPMailer::class)) {
            return $this->sendSmtp($toEmail, $toName, $subject, $htmlBody, $attachments);
        }
        if ($driver === 'mail') {
            return $this->sendNative($toEmail, $subject, $htmlBody);
        }
        // Dev fallback: log instead of sending so the flow never blocks locally.
        error_log("[mail:log] To: {$toEmail} | Subject: {$subject}");
        return true;
    }

    private function sendSmtp(string $toEmail, string $toName, string $subject, string $htmlBody, array $attachments): bool
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->cfg['host'] ?? 'smtp-relay.brevo.com';
            $mail->Port = (int) ($this->cfg['port'] ?? 587);
            $mail->SMTPAuth = true;
            $mail->Username = $this->cfg['username'] ?? '';
            $mail->Password = $this->cfg['password'] ?? '';
            $enc = $this->cfg['encryption'] ?? 'tls';
            $mail->SMTPSecure = $enc === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->cfg['from_email'] ?? 'no-reply@localhost', $this->cfg['from_name'] ?? 'GovTech Conference');
            if (!empty($this->cfg['reply_to'])) {
                $mail->addReplyTo($this->cfg['reply_to']);
            }
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = trim(strip_tags(preg_replace('/<(br|\/p|\/div)\s*\/?>/i', "\n", $htmlBody) ?? $htmlBody));

            foreach ($attachments as $att) {
                if (!empty($att['path']) && is_file($att['path'])) {
                    $mail->addAttachment($att['path'], $att['name'] ?? '');
                } elseif (!empty($att['data'])) {
                    $mail->addStringAttachment($att['data'], $att['name'] ?? 'attachment', PHPMailer::ENCODING_BASE64, $att['type'] ?? 'application/octet-stream');
                }
            }
            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer SMTP error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function sendNative(string $toEmail, string $subject, string $htmlBody): bool
    {
        $from = $this->cfg['from_email'] ?? 'no-reply@localhost';
        $name = $this->cfg['from_name'] ?? 'GovTech Conference';
        $headers = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . sprintf('%s <%s>', $name, $from),
        ]);
        return @mail($toEmail, $subject, $htmlBody, $headers);
    }
}
