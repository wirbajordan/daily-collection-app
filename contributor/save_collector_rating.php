<?php
session_start();
include '../config/config.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$contributor_id = $_SESSION['user_id'] ?? 0;
$transaction_id = intval($data['transaction_id'] ?? 0);
$collector_id = intval($data['collector_id'] ?? 0);
$rating = intval($data['rating'] ?? 0);
$comment = trim($data['comment'] ?? '');
if (!$contributor_id || !$transaction_id || !$collector_id || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
    exit;
}
// Prevent duplicate rating
$stmt = $mysqli->prepare("SELECT id FROM collector_ratings WHERE transaction_id = ? AND contributor_id = ?");
$stmt->bind_param('ii', $transaction_id, $contributor_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'You have already rated this transaction.']);
    exit;
}
$stmt->close();
// Insert rating
$stmt = $mysqli->prepare("INSERT INTO collector_ratings (collector_id, contributor_id, transaction_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('iiiis', $collector_id, $contributor_id, $transaction_id, $rating, $comment);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save rating.']);
}
$stmt->close(); 