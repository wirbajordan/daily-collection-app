<?php
session_start(); // Start the session if needed

include_once '../config/config.php';

// Create a new mysqli instance
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Check if the form is submitted  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input values and sanitize
    $user_name = trim($_POST['reg_username']);
    $user_email = trim($_POST['reg_email']);
    $user_phone_number = trim($_POST['reg_number']);
    $user_password = trim($_POST['reg_password']);

    // Basic validation
    if (empty($user_name) || empty($user_email) || empty($user_password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header('Location: register.php'); // Redirect back to the registration form
        exit();
    } else {
        // Hash the password
        $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

        // Prepare and execute the SQL statement
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, phone_number, password) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            // Bind parameters
            $stmt->bind_param("ssss", $user_name, $user_email, $user_phone_number, $hashed_password);

            // Execute and check for success
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Registration successful! You can now log in.";
                header('Location: login_register.php'); // Redirect to login page

             // In your registration_logic.php after successful registration
            $_SESSION['role'] = $user_role; // Assuming $user_role is set to the selected role
            header('Location: login_register.php'); // Redirect to login page
            exit();

            } else {
                $_SESSION['error_message'] = "Registration failed: " . $stmt->error;
                header('Location: register.php'); // Redirect back to the registration form
            }

            // Close the statement
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Preparation failed: " . $mysqli->error;
            header('Location: register.php'); // Redirect back to the registration form
        }
    }
}

// Close the database connection
$mysqli->close();
?>