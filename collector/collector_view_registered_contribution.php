<?php
//session_start();
include_once '../config/config.php';

// Check user role    
if ($_SESSION['role'] != 'collector') {
    header('Location: .../login.php');
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
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

// Handle transaction registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
    $collector_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $password = $_POST['password'];

    // Validate collector's password
    $result = $conn->query("SELECT password, username FROM users WHERE user_id = '$collector_id'");
    $row = $result->fetch_assoc();

    if ($row && password_verify($password, $row['password'])) {
        $collector_username = $row['username'];
        $date = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO transaction (amount, date, user_id, username, transaction_type, register_contribution) VALUES ('$amount', '$date', '$collector_id', '$collector_username', 'register', 'Yes')");
        echo json_encode(['status' => 'success', 'message' => 'Transaction registered successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password!']);
    }
    exit();
}

// Handle fetching contributions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fetch_contributions'])) {
    $stmt = $conn->prepare("SELECT user_id, username, amount, date, register_contribution FROM transaction WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $contributions = [];
    while ($row = $result->fetch_assoc()) {
        $contributions[] = $row;
    }

    echo json_encode($contributions);
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard</title>
    <style>
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
        button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
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
        .close:hover {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Register Contribution</h1>
    <button id="contributionBtn">View Registered Contributions</button>

    <div id="contributionModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Your Registered Contributions</h2>
            <table id="contributionTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Registered Contribution</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Contribution data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('contributionBtn').onclick = function() {
            const modal = document.getElementById('contributionModal');
            modal.style.display = 'block';

            // Fetch registered contributions for the logged-in collector
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ fetch_contributions: true })     
            })
            .then(response => response.json())
            .then(data => {
                const tableBody = document.querySelector('#contributionTable tbody');
                tableBody.innerHTML = ''; // Clear existing rows

                data.forEach(contribution => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${contribution.user_id}</td>
                        <td>${contribution.username}</td>
                        <td>${contribution.amount}</td>
                        <td>${contribution.date}</td>
                        <td>${contribution.register_contribution}</td>
                    `;
                    tableBody.appendChild(row);
                });
            })
            .catch(error => console.error('Error fetching contributions:', error));
        }

        // Close modal functionality
        document.querySelector('.close').onclick = function() {
            document.getElementById('contributionModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of modal content
        window.onclick = function(event) {
            const modal = document.getElementById('contributionModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>