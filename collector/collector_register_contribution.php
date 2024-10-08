<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'dailycollect';

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

  
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redirect to login if not logged in
    exit();
}    
 // Assuming user ID is stored in session after login
$collector_id = $_SESSION['user_id'];

// Hardcoded collector ID for demonstration purposes
//$collector_id = ; // Replace with actual collector ID from session or other authentication method

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contributor_username = $_POST['contributor_username'];
    $amount = $_POST['amount'];

    // Record transaction
    $date = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO transaction (amount, Date, user_id, username, transaction_type) VALUES (?, ?, ?, ?, 'register')");
    $stmt->bind_param("dsis", $amount, $date, $collector_id, $contributor_username);
    
    if ($stmt->execute()) {
        echo '<script>alert("Transaction successful.");</script>';
        // Notify contributor (this is a placeholder for actual notification logic)
        echo '<script>console.log("Notification sent to ' . $contributor_username . '");</script>';
    } else {
        echo '<script>alert("Transaction failed.");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Contribution</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
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
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Register Contribution</h2>
        <button onclick="document.getElementById('transactionModal').style.display='block'">Add Transaction</button>
        
        <!-- Modal for Transaction -->
        <div id="transactionModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('transactionModal').style.display='none'">&times;</span>
                <h2>Transaction Details</h2>
                <form method="POST" id="transactionForm">
                    <input type="hidden" name="collector_id" value="<?php echo $collector_id; ?>"> <!-- Hidden collector ID -->
                    <select name="contributor_username" required>
                        <?php
                        // Fetch contributors from the database
                        $contributors = $conn->query("SELECT username FROM users WHERE role='contributor'");
                        while ($row = $contributors->fetch_assoc()) {
                            echo '<option value="' . $row['username'] . '">' . $row['username'] . '</option>';
                        }
                        ?>
                    </select>
                    <input type="number" name="amount" placeholder="Amount" required>
                    <button type="submit">Validate Transaction</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Close the modal when the user clicks outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('transactionModal');
            if (event.target === modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>