<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    // Only admins can add
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        echo json_encode(['status'=>'error','message'=>'Unauthorized']);
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $duration = intval($_POST['duration_days'] ?? 30);
    $price = floatval($_POST['price'] ?? 0);

    if (!$name || $price <= 0) {
        echo json_encode(['status'=>'error','message'=>'Invalid input']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO programs (name, description, duration_days, price, created_by) VALUES (?,?,?,?,?)");
    $stmt->execute([$name, $desc, $duration, $price, $_SESSION['user']['id']]);

    echo json_encode(['status'=>'success','message'=>'Program added']);
}
elseif ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY created_at DESC");
    echo json_encode(['status'=>'success','programs'=>$stmt->fetchAll()]);
}
else {
    echo json_encode(['status'=>'error','message'=>'Invalid action']);
}
