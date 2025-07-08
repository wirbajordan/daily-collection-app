<?php
session_start();
include '../config/config.php';
header('Content-Type: application/json');
if (!isset($mysqli) || !$mysqli || $mysqli->connect_errno) {
    echo json_encode(['valid' => false, 'error' => 'DB connection failed']);
    exit;
}
$data = json_decode(file_get_contents('php://input'), true);
$password = $data['password'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id || !$password) {
    echo json_encode(['valid' => false, 'error' => 'Missing user or password']);
    exit;
}
$stmt = $mysqli->prepare("SELECT password FROM users WHERE user_id = ? AND role = 'collector'");
if (!$stmt) {
    echo json_encode(['valid' => false, 'error' => 'DB prepare failed']);
    exit;
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();
// If your passwords are hashed with password_hash, use password_verify. If sha1, use sha1.
$isValid = false;
if ($hash) {
    if (password_verify($password, $hash) || $hash === sha1($password)) {
        $isValid = true;
    }
}
echo json_encode(['valid' => $isValid]); 