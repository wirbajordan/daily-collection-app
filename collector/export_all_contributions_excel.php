<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'dailycollect');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=all_contributions.csv');
$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Contributor', 'Amount', 'Type']);
$res = $conn->query("SELECT Date, username, amount, transaction_type FROM transaction WHERE user_id = $user_id ORDER BY Date DESC");
while ($row = $res->fetch_assoc()) {
    fputcsv($output, [$row['Date'], $row['username'], $row['amount'], $row['transaction_type']]);
}
fclose($output);
$conn->close();
exit; 