<?php

/**
 * Centralized Midtrans integration.
 * Removes duplicated Snap API logic from CheckoutController & CustomerBayarController
 * and provides server-to-server transaction status verification.
 */
class MidtransService
{
    private string $serverKey;
    private string $clientKey;
    private bool $isProduction;

    public function __construct()
    {
        $this->serverKey    = env('MIDTRANS_SERVER_KEY', '');
        $this->clientKey    = env('MIDTRANS_CLIENT_KEY', '');
        $this->isProduction = env('MIDTRANS_IS_PRODUCTION', 'false') === 'true';
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getServerKey(): string
    {
        return $this->serverKey;
    }

    /**
     * Snap JS URL for the <script> tag.
     */
    public function getSnapJsUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Base URL for the Snap REST API.
     */
    private function getSnapApiUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Base URL for the core/v2 API (status checks).
     */
    private function getApiUrl(): string
    {
        return $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Request a Snap token for a transaction.
     *
     * @param array $items    list of ['id','price','quantity','name']
     * @param array $customer ['first_name','email']
     * @return string|null    Snap token or null on failure
     */
    public function createSnapToken(string $orderRef, int $grossAmount, array $items, array $customer): ?string
    {
        $payload = [
            'transaction_details' => [
                'order_id'     => $orderRef,
                'gross_amount' => $grossAmount,
            ],
            'item_details'     => $items,
            'customer_details' => $customer,
        ];

        $response = $this->request('POST', $this->getSnapApiUrl() . '/transactions', $payload);

        if ($response === null) {
            return null;
        }

        return $response['token'] ?? null;
    }

    /**
     * Verify a transaction's real status directly with Midtrans (server-to-server).
     * Returns the decoded status payload, or null on failure.
     */
    public function getTransactionStatus(string $orderId): ?array
    {
        return $this->request('GET', $this->getApiUrl() . '/' . rawurlencode($orderId) . '/status');
    }

    /**
     * Map a Midtrans status payload to an internal transaksi status.
     * Returns 'success', 'pending', 'failed', or null when undeterminable.
     */
    public function mapStatus(array $statusPayload): ?string
    {
        $transactionStatus = $statusPayload['transaction_status'] ?? '';
        $fraudStatus       = $statusPayload['fraud_status'] ?? 'accept';

        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            return 'success';
        }
        if ($transactionStatus === 'pending') {
            return 'pending';
        }
        if (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true)) {
            return 'failed';
        }
        return null;
    }

    /**
     * Perform an authenticated HTTP request to Midtrans.
     */
    private function request(string $method, string $url, ?array $payload = null): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':'),
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            error_log('Midtrans request failed: ' . $curlError);
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return null;
        }

        // 2xx => OK. Status API returns 404 for unknown orders, etc.
        if ($httpCode >= 200 && $httpCode < 300) {
            return $decoded;
        }

        // Some Midtrans status responses carry useful data with non-2xx codes;
        // return decoded payload so callers can inspect status_code if needed.
        return $decoded;
    }
}
