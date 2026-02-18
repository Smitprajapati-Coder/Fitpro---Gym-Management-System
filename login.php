<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

// Read input (works with form-data or JSON)
$input = $_POST;
if (empty($input)) {
    $json = json_decode(file_get_contents('php://input'), true);
    if ($json) $input = $json;
}

$loginType = $input['loginType'] ?? 'email'; // email | mobile | gymid
$role = $input['role'] ?? null; // optional role filter
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';
$mobile = trim($input['mobile'] ?? '');
$gymid = trim($input['gymid'] ?? '');

try {
    // Determine query based on login type
    if ($loginType === 'email') {
        if (!$email || !$password) {
            echo json_encode(['status'=>'error','message'=>'Email and password required']);
            exit;
        }
        $query = "SELECT id, name, email, password_hash, role FROM users WHERE email = ?";
        $params = [$email];
    } elseif ($loginType === 'mobile') {
        if (!$mobile) {
            echo json_encode(['status'=>'error','message'=>'Mobile required']);
            exit;
        }
        $query = "SELECT id, name, email, password_hash, role FROM users WHERE phone = ?";
        $params = [$mobile];
    } elseif ($loginType === 'gymid') {
        if (!$gymid || !$password) {
            echo json_encode(['status'=>'error','message'=>'Gym ID and password required']);
            exit;
        }
        $query = "SELECT id, name, email, password_hash, role, gym_id FROM users WHERE gym_id = ?";
        $params = [$gymid];
    } else {
        echo json_encode(['status'=>'error','message'=>'Unknown login type']);
        exit;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['status'=>'error','message'=>'User not found']);
        exit;
    }

    // Role check
    if ($role && $user['role'] !== $role) {
        echo json_encode(['status'=>'error','message'=>'Role mismatch']);
        exit;
    }

    // Password verification for email/gymid
    if ($loginType === 'email' || $loginType === 'gymid') {
        if (empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            echo json_encode(['status'=>'error','message'=>'Invalid credentials']);
            exit;
        }
    }

    // Mobile login: assume exists, optionally implement OTP later
    // Set session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    echo json_encode(['status'=>'success','message'=>'Login successful','user'=>$_SESSION['user']]);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['status'=>'error','message'=>'Server error']);
}
