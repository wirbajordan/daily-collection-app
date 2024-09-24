<?php
// Database connection parameters
$host = 'localhost'; // Database host
$dbname = 'dailycollect'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password

// Create a new mysqli instance
$mysqli = new mysqli($host, $username, $password, $dbname);


// Check connection
if ($mysqli->connect_error) {
    // You can log the error or handle it without outputting it to the user
    error_log("Connection failed: " . $mysqli->connect_error); // Log the error
    die("Database connection failed."); // Optional: generic error message without details
}

// No message on successful connection
 //echo "connection successful";
?>