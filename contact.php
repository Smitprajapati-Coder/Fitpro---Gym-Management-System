<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'send') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $user_id = $_SESSION['user']['id'] ?? null;

    if (!$name || !$email || !$message) {
        echo json_encode(['status'=>'error','message'=>'Please fill in all required fields.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status'=>'error','message'=>'Invalid email address.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name,email,phone,subject,message,user_id)
                               VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name,$email,$phone,$subject,$message,$user_id]);
        echo json_encode(['status'=>'success','message'=>'Message sent successfully!']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['status'=>'error','message'=>'Server error. Try again later.']);
    }
}
elseif ($action === 'list') {
    // Admin-only: view all messages
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        echo json_encode(['status'=>'error','message'=>'Unauthorized']);
        exit;
    }
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    echo json_encode(['status'=>'success','messages'=>$stmt->fetchAll()]);
}
else {
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
}
@mail("admin@yourdomain.com", "New Contact Message from $name", $message, "From: $email");
