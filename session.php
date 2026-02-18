<?php
header('Content-Type: application/json');
session_start();

if (!empty($_SESSION['user'])) {
    echo json_encode(['user' => $_SESSION['user']]);
} else {
    echo json_encode(['user' => null]);
}
