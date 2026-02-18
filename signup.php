<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

// We'll accept POST fields via normal form POST or fetch FormData
$name = trim($_POST['name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';
$role = $_POST['role'] ?? 'member'; // expected: admin|member|trainer|student|other

$allowedRoles = ['admin','member','trainer','student','other'];
if (!in_array($role, $allowedRoles)) $role = 'member';

if (!$name || !$email || !$password || !$confirm) {
    echo json_encode(['status'=>'error','message'=>'Please fill all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status'=>'error','message'=>'Invalid email.']);
    exit;
}
if ($password !== $confirm) {
    echo json_encode(['status'=>'error','message'=>"Passwords don't match."]);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['status'=>'error','message'=>'Password must be at least 6 characters.']);
    exit;
}

try {
    // check existing email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['status'=>'error','message'=>'Email already registered.']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO users (name,email,phone,password_hash,role) VALUES (?,?,?,?,?)");
    $insert->execute([$name,$email,$phone,$passwordHash,$role]);
    $userid = $pdo->lastInsertId();

    // set session
    $_SESSION['user'] = [
        'id' => $userid,
        'name' => $name,
        'email' => $email,
        'role' => $role
    ];

    echo json_encode(['status'=>'success','message'=>'Account created successfully','user'=>$_SESSION['user']]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error']);
}
