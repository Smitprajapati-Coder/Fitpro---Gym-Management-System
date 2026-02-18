<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (empty($_SESSION['user'])) {
    echo json_encode(['status'=>'error','message'=>'Not logged in']);
    exit;
}

$userId = $_SESSION['user']['id'];

if ($action === 'pay') {
    $amount = floatval($_POST['amount'] ?? 0);
    $bookingId = intval($_POST['booking_id'] ?? 0);
    $programId = intval($_POST['program_id'] ?? 0);
    $mode = $_POST['payment_mode'] ?? 'online';
    $txn = uniqid('TXN');

    if ($amount <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid amount']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO payments (user_id, booking_id, program_id, amount, payment_mode, payment_status, transaction_id)
                           VALUES (?,?,?,?,?,'paid',?)");
    $stmt->execute([$userId, $bookingId ?: null, $programId ?: null, $amount, $mode, $txn]);

    echo json_encode(['status'=>'success','message'=>'Payment successful','transaction_id'=>$txn]);
}
elseif ($action === 'history') {
    $stmt = $pdo->prepare("
        SELECT p.*, b.booking_date, pr.name AS program_name
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN programs pr ON p.program_id = pr.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId]);
    echo json_encode(['status'=>'success','payments'=>$stmt->fetchAll()]);
}
else {
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
}
