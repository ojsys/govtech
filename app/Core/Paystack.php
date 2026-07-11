<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Paystack API client. Server-trusted: we send amounts in kobo and verify
 * everything (verify-by-reference + webhook HMAC) before issuing tickets.
 *
 * @see https://paystack.com/docs/api/
 */
final class Paystack
{
    private const BASE = 'https://api.paystack.co';

    private string $secret;
    private string $public;
    private string $currency;

    public function __construct()
    {
        $this->secret = (string) \Config::get('paystack.secret_key', '');
        $this->public = (string) \Config::get('paystack.public_key', '');
        $this->currency = (string) \Config::get('paystack.currency', 'NGN');
        if ($this->secret === '' || str_contains($this->secret, 'xxxxxx')) {
            throw new RuntimeException('Paystack secret key is not configured (app/config/paystack.php).');
        }
    }

    public function publicKey(): string
    {
        return $this->public;
    }

    /**
     * Initialize a transaction. $amountKobo is the integer kobo total.
     * Returns ['authorization_url','access_code','reference'].
     */
    public function initialize(string $email, int $amountKobo, string $reference, string $callbackUrl, array $metadata = []): array
    {
        $res = $this->request('POST', '/transaction/initialize', [
            'email'        => $email,
            'amount'       => $amountKobo,        // Paystack NGN amounts are in kobo
            'currency'     => $this->currency,
            'reference'    => $reference,
            'callback_url' => $callbackUrl,
            'metadata'     => $metadata,
        ]);
        if (empty($res['status']) || empty($res['data']['authorization_url'])) {
            throw new RuntimeException('Paystack initialize failed: ' . ($res['message'] ?? 'unknown error'));
        }
        return $res['data'];
    }

    /**
     * Verify a transaction by reference. Returns the `data` payload.
     * Caller MUST check data.status === 'success' and amount matches.
     */
    public function verify(string $reference): array
    {
        $res = $this->request('GET', '/transaction/verify/' . rawurlencode($reference));
        if (empty($res['status'])) {
            throw new RuntimeException('Paystack verify failed: ' . ($res['message'] ?? 'unknown error'));
        }
        return $res['data'] ?? [];
    }

    /**
     * Validate a webhook payload: HMAC-SHA512 of the RAW body with the secret key
     * must equal the X-Paystack-Signature header. Timing-safe.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        if (!is_string($signature) || $signature === '') {
            return false;
        }
        $computed = hash_hmac('sha512', $rawBody, $this->secret);
        return hash_equals($computed, $signature);
    }

    private function request(string $method, string $path, ?array $body = null): array
    {
        $ch = curl_init(self::BASE . $path);
        $headers = [
            'Authorization: Bearer ' . $this->secret,
            'Cache-Control: no-cache',
        ];
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_SLASHES);
            $headers[] = 'Content-Type: application/json';
        }
        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $opts);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Paystack request error: ' . $err);
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Paystack returned an invalid response (HTTP ' . $status . ').');
        }
        return $decoded;
    }
}
