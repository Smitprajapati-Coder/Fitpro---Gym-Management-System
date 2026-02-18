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

if ($action === 'apply') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = $_POST['gender'] ?? 'other';
    $age = intval($_POST['age'] ?? 0);
    $address = trim($_POST['address'] ?? '');
    $program_id = intval($_POST['program_id'] ?? 0);
    $trainer_id = intval($_POST['trainer_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (!$full_name || !$phone || !$program_id || !$start_date) {
        echo json_encode(['status'=>'error','message'=>'Please fill all required fields']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO admissions (user_id,full_name,email,phone,gender,age,address,program_id,trainer_id,start_date,notes,payment_status)
                               VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$userId,$full_name,$email,$phone,$gender,$age,$address,$program_id,$trainer_id,$start_date,$notes,'pending']);
        echo json_encode(['status'=>'success','message'=>'Admission form submitted successfully!']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['status'=>'error','message'=>'Server error']);
    }
}

elseif ($action === 'myadmissions') {
    $stmt = $pdo->prepare("
        SELECT a.*, p.name AS program_name, t.name AS trainer_name
        FROM admissions a
        LEFT JOIN programs p ON a.program_id = p.id
        LEFT JOIN users t ON a.trainer_id = t.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$userId]);
    echo json_encode(['status'=>'success','admissions'=>$stmt->fetchAll()]);
}

elseif ($action === 'list') {
    // Admin can view all admissions
    if ($_SESSION['user']['role'] !== 'admin') {
        echo json_encode(['status'=>'error','message'=>'Unauthorized']);
        exit;
    }
    $stmt = $pdo->query("
        SELECT a.*, u.name AS member_name, p.name AS program_name, t.name AS trainer_name
        FROM admissions a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN programs p ON a.program_id = p.id
        LEFT JOIN users t ON a.trainer_id = t.id
        ORDER BY a.created_at DESC
    ");
    echo json_encode(['status'=>'success','admissions'=>$stmt->fetchAll()]);
}

else {
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
}
