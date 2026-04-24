<?php
include '../config/koneksi.php';
include_once '../config/helpers.php';

// Midtrans akan mengirimkan data berupa JSON ke file ini
$json_result = file_get_contents('php://input');
$result = json_decode($json_result);

if ($result) {
    $order_id = $result->order_id;
    $status_code = $result->status_code;
    $gross_amount = $result->gross_amount;
    $signature_key = $result->signature_key ?? '';
    $transaction_status = $result->transaction_status;

    // Verify signature to prevent fraudulent notifications
    $serverKey = env('MIDTRANS_SERVER_KEY');
    $expected_signature = hash('sha512', $order_id . $status_code . $gross_amount . $serverKey);
    
    if ($signature_key !== $expected_signature) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }

    $new_status = '';
    if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
        $new_status = 'success';
    } else if ($transaction_status == 'pending') {
        $new_status = 'pending';
    } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
        $new_status = 'failed';
    }

    if ($new_status != '') {
        // Check if order_id is an order_ref (ORD-*) or a legacy single transaksi ID
        if (strpos($order_id, 'ORD-') === 0) {
            // Cart checkout: update all transaksi with this order_ref
            db_execute($conn, "UPDATE transaksi SET status = ? WHERE order_ref = ?", ["ss", $new_status, $order_id]);
        } else {
            // Legacy single-item purchase
            $id = (int) $order_id;
            db_execute($conn, "UPDATE transaksi SET status = ? WHERE id = ?", ["si", $new_status, $id]);
        }
    }
}
?>
