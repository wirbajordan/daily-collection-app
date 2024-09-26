<?php
session_start();
include_once '../config/config.php';

// Check user role
if ($_SESSION['role'] != 'collector') {
    header('Location: ../forms_logic/login_register.php');
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../forms_logic/login_register.php'); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'dailycollect';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $collector_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $password = $_POST['password'];

    // Validate collector's password
    $result = $conn->query("SELECT password, username FROM users WHERE user_id = '$collector_id'");
    $row = $result->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        // Get collector's username
        $collector_username = $row['username'];

        // Insert transaction
        $date = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO transaction (amount, date, user_id, username, transaction_type, register_contribution) VALUES ('$amount', '$date', '$collector_id', '$collector_username', 'register', 'Yes')");

        echo json_encode(['status' => 'success', 'message' => 'Transaction registered successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password!']);
    }
    exit(); // Ensure to exit after processing the request
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        /* Button Styles */
        button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 12px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="number"],
        input[type="password"],
        select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="number"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.5);
        }

        /* Message Styles */
        #message {
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h1>Register Contribution</h1>
    <button id="transactionBtn">Transactions</button>

    <div id="transactionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add Transaction</h2>
            <form id="transactionForm" method="POST">
                <label for="contributor">Choose Contributor:</label>
                <select name="contributor_id" required>
                    <?php
                    $result = $conn->query("SELECT user_id, username FROM users");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['user_id']}'>{$row['username']}</option>";
                    }
                    ?>
                </select>

                <label for="amount">Amount:</label>
                <input type="number" name="amount" required>

                <label for="password">Your Password:</label>
                <input type="password" name="password" required>

                <button type="submit">Validate Transaction</button>
            </form>
            <div id="message">
                <button id="clearMessage" style="display:none;">Clear Message</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('transactionBtn').onclick = function() {
            document.getElementById('transactionModal').style.display = 'block';
        }

        document.querySelector('.close').onclick = function() {
            document.getElementById('transactionModal').style.display = 'none';
        }

        document.getElementById('transactionForm').onsubmit = function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('collector_register_contribution.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('message');
                const clearMessageButton = document.getElementById('clearMessage');
                messageDiv.innerText = data.message;
                messageDiv.style.color = data.status === 'success' ? 'green' : 'red';
                clearMessageButton.style.display = 'inline'; // Show button
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        document.getElementById('clearMessage').onclick = function() {
            const messageDiv = document.getElementById('message');
            messageDiv.innerText = '';
            this.style.display = 'none'; // Hide button
        }
    </script>
</body>
</html>