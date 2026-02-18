<?php
header('Content-Type: application/json');
session_start();

$phone = $_POST['mobile'] ?? '';

if (!$phone) {
    echo json_encode(['status' => 'error', 'message' => 'Mobile number required']);
    exit;
}

// Generate 6-digit OTP
$otp = rand(100000, 999999);

// Store in session
$_SESSION['otp'] = $otp;
$_SESSION['otp_phone'] = $phone;

// Since we are not using SMS, return OTP for testing
echo json_encode(['status' => 'success', 'message' => "OTP generated!", 'otp' => $otp]);
