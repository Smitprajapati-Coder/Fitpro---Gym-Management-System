<?php
header('Content-Type: application/json');
session_start();

$enteredOTP = $_POST['otp'] ?? '';
$phone = $_POST['mobile'] ?? '';

if (!$enteredOTP || !$phone) {
    echo json_encode(['status' => 'error', 'message' => 'Mobile and OTP required']);
    exit;
}

// Check session OTP
if (isset($_SESSION['otp'], $_SESSION['otp_phone']) && $_SESSION['otp'] == $enteredOTP && $_SESSION['otp_phone'] == $phone) {
    // Simulate user login
    $user = [
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'gymid' => 'GYM-1234',
        'role' => 'member'
    ];

    // Clear OTP
    unset($_SESSION['otp'], $_SESSION['otp_phone']);

    echo json_encode(['status' => 'success', 'user' => $user]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid OTP']);
}
