<?php
session_start(); // Start the session if needed

// Check for success or error messages in the session
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear messages after displaying
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link rel="stylesheet" href="styles/login_register.css">
</head>
<body>
    <div class="container">
        <h1>Hello Welcome To DailyCollect</h1>

        <!-- Display success or error messages -->
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="tab">  
            <button class="tablinks" onclick="openTab(event, 'Login')">Login</button>
            <button class="tablinks" onclick="openTab(event, 'Register')">Register</button>
        </div>

        <div id="Login" class="tabcontent">
            <h2>Login</h2>
            <form method="POST" action="login_logic.php">
                <input type="text" name="email" placeholder="email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="hidden" name="role" value="<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : ''; ?>">
                <button type="submit">Login</button>
            </form>   
        </div>

        <div id="Register" class="tabcontent" style="display:none;">
            <h2>Register</h2>
            <form method="POST" action="registration_logic.php">
                <input type="text" name="reg_username" placeholder="Username" required>
                <input type="text" name="reg_email" placeholder="email" required>
                <input type="number" name="reg_number" placeholder="phone_number" required>
                <input type="password" name="reg_password" placeholder="Password" required>
                <select name="role" required>
                    <option value="contributor">Contributor</option>
                    <option value="collector">Collector</option>   
                </select>
                <button type="submit">Register</button>
            </form>
        </div>
    </div>

    <script src="scripts/login_register.js"></script>
    
    <!-- this is the css for the login and register form -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        .tab {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .tab button {
            padding: 10px;
            cursor: pointer;
            border: none;
            background-color: #5cb85c;
            color: white;
            border-radius: 70px;
        }
        .tab button:hover { 
            background-color: #4cae4c;
        }
        .tabcontent {
            display: none;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
        .success-message {
            color: green;
            margin-bottom: 15px;
        }
    </style><!-- the end of login and register form -->
     
     <!-- the JS for the login and register button and form -->
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;

            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";  
            }

            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            document.getElementById(tabName).style.display = "block";  
            evt.currentTarget.className += " active";
        }
    </script><!-- the end of JS for login and register button and form -->
</body>
</html>