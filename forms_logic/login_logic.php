<?php
session_start();
include_once '../config/config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $mysqli->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $mysqli->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashedPassword, $role);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        
        // Verify the password
        if (password_verify($password, $hashedPassword)) {
             // Store user ID in the session
            $_SESSION['user_id'] = $user_id;
            // Store user role in session
            $_SESSION['role'] = $role;
            $_SESSION['success_message'] = "Login successful!";

            // Redirect based on user role
            switch ($role) {
                case 'administrator':
                    header('Location: ../dashboards/admin_dashboard.php');
                    break;
                case 'collector':
                    header('Location: ../dashboards/collector_dashboard.php');
                    break;
                case 'contributor':
                    header('Location: ../dashboards/contributor_dashboard.php');
                    break;
                default:
                    $_SESSION['error_message'] = "Unknown role.";
                    header('Location: login_register.php');
                    break;
            }
            exit();
        } else {
            // Invalid password
            $_SESSION['error_message'] = "Invalid password.";
            header('Location: login_register.php');
            exit();
        }
    } else {
        // User not found
        $_SESSION['error_message'] = "User not found.";
        header('Location: login_register.php');
        exit();
    }

    $stmt->close();
}

$mysqli->close();
?>