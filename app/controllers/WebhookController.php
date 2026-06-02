<?php
require_once __DIR__ . '/../core/BaseController.php';

class WebhookController extends BaseController
{
    private Transaksi $transaksi;
    private MidtransService $midtrans;

    public function __construct()
    {
        parent::__construct();
        require_once __DIR__ . '/../models/Transaksi.php';
        $this->transaksi = new Transaksi();
        $this->midtrans = new MidtransService();
    }

    public function handle(): void
    {
        // Read raw JSON input
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data) {
            $this->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
            return;
        }

        // Verify SHA-512 signature
        $order_id     = $data['order_id'] ?? '';
        $status_code  = $data['status_code'] ?? '';
        $gross_amount = $data['gross_amount'] ?? '';
        $server_key   = $this->midtrans->getServerKey();

        $signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);

        if (!hash_equals($signature, $data['signature_key'] ?? '')) {
            $this->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            return;
        }

        // Map transaction status using the shared service
        $status = $this->midtrans->mapStatus($data);
        if ($status === null) {
            // Unknown/irrelevant status — acknowledge without changing state
            $this->json(['status' => 'ok', 'message' => 'No update needed']);
            return;
        }

        // Strip retry suffix (e.g. -r1234567) to get the original order_ref or ID in database
        $db_order_ref = preg_replace('/-r\d+$/', '', $order_id);

        // Update by order_ref (ORD-*) or legacy single ID
        if (strpos($db_order_ref, 'ORD-') === 0) {
            $this->transaksi->updateStatusByRef($db_order_ref, $status);
        } else {
            $this->transaksi->updateStatusById((int) $db_order_ref, $status);
        }

        $this->json(['status' => 'ok']);
    }
}
