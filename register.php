<?php
session_start(); // Start the session if needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyCollect Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 300px; /* Fixed width for the form */
        }
        h1 {
            color: #333;
        }
        .registration-form {
            margin-top: 20px;
        }
        input[type="text"], input[type="number"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 400px) {
            .container {
                width: 90%; /* Responsive width */
            }
        }
    </style>
</head>  
<body>
<div class="container">
    <h1>Hello welcome to DailyCollect</h1>
    <div class="registration-form">
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <h2>Register</h2>
        <form action="registration_logic.php" method="POST">
            <input type="text" name="reg_username" placeholder="Username" required>
            <input type="text" name="reg_email" placeholder="Email" required>
            <input type="number" name="reg_number" placeholder="Phone Number" required>
            <input type="password" name="reg_password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
    </div>
</div>
<script>
    // Basic JavaScript for future enhancements
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Registration page loaded.');
    });
</script>
</body>
</html>