<?php
session_start();

// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: .../login.php');//redirect to logged in if role not valide
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: .../login.php'); // Redirect to login if not logged in
    exit();
}    


$host = 'localhost'; // Your database host
$dbname = 'dailycollect'; // Your database name
$user = 'root'; // Your database user
$pass = ''; // Your database password

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch collectors and contributors with valid coordinates
$sql = "SELECT username, role, latitude, longitude FROM users WHERE role IN ('collector', 'contributor') AND latitude IS NOT NULL AND longitude IS NOT NULL";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($users);
?>