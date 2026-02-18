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

if ($action === 'book') {
    $trainerId = intval($_POST['trainer_id'] ?? 0);
    $programId = intval($_POST['program_id'] ?? 0);
    $date = $_POST['booking_date'] ?? '';
    $notes = $_POST['notes'] ?? '';

    if (!$trainerId || !$date) {
        echo json_encode(['status'=>'error','message'=>'Trainer and date required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO bookings (member_id, trainer_id, program_id, booking_date, notes) VALUES (?,?,?,?,?)");
    $stmt->execute([$userId, $trainerId, $programId ?: null, $date, $notes]);

    echo json_encode(['status'=>'success','message'=>'Booking created']);
}
elseif ($action === 'mybookings') {
    // show logged-in user's bookings
    $stmt = $pdo->prepare("
        SELECT b.*, t.name AS trainer_name, p.name AS program_name
        FROM bookings b
        LEFT JOIN users t ON b.trainer_id = t.id
        LEFT JOIN programs p ON b.program_id = p.id
        WHERE b.member_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$userId]);
    echo json_encode(['status'=>'success','bookings'=>$stmt->fetchAll()]);
}
else {
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
}
