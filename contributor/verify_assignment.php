<?php
session_start();
include '../config/config.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$collector_id = intval($data['collector_id'] ?? 0);
$contributor_id = $_SESSION['user_id'] ?? 0;

if (!$collector_id || !$contributor_id) {
    echo json_encode(['success' => false]);
    exit;
}
$stmt = $mysqli->prepare("SELECT * FROM assignments WHERE collector_id = ? AND contributor_id = ?");
$stmt->bind_param("ii", $collector_id, $contributor_id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode(['success' => $result->num_rows > 0]); 