<?php
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