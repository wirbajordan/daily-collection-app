
<?php
//session_start();
include_once '../config/config.php';        


// Check user role
if ($_SESSION['role'] != 'contributor') {
    header('Location: ../login.php' );
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redirect to login if not logged in
    exit();
}    
     
          
// Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];

 
// Database connection parameters
$servername = "localhost"; // Change if necessary
$username = "root"; // Change to your DB username
$password = ""; // Change to your DB password
$dbname = "dailycollect"; // Change to your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$userId = $_SESSION['user_id'];
$userQuery = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userQuery->bind_result($userName);
$userQuery->fetch();
$userQuery->close();

// Initialize the success message flag
$successMessage = false;

// Handle form submission for deposits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
    // Get the form data
    $amount = $_POST['amount'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO transaction (user_id, username, amount, transaction_type) VALUES (?, ?, ?, 'deposite')");
    if ($stmt) {
        $stmt->bind_param("isd", $userId, $userName, $amount);

        // Execute the statement
        if ($stmt->execute()) {
            // Set success message flag and session variable
            $successMessage = true;
            $_SESSION['transaction_success'] = true; // Set session variable
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Statement preparation failed: " . $conn->error;
    }
}

// Fetch all transactions for the user
$transactions = [];
$totalSum = 0;

$transactionQuery = $conn->prepare("SELECT amount, transaction_type, Date FROM transaction WHERE user_id = ?");
$transactionQuery->bind_param("i", $userId);
$transactionQuery->execute();
$transactionQuery->bind_result($amount, $transactionType, $date);

while ($transactionQuery->fetch()) {
    $transactions[] = ['amount' => $amount, 'transaction_type' => $transactionType, 'date' => $date];
    $totalSum += $amount; // Calculate total sum
}

$transactionQuery->close();
$conn->close();

// Check if the transaction was successful for display
if (isset($_SESSION['transaction_success']) && $_SESSION['transaction_success']) {
    $successMessage = true;
    unset($_SESSION['transaction_success']); // Clear the session variable
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="contributor_dashboard_css/style.css">
    <style>
        /* Here is the CSS styles for deposite contribution form and it buttons */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"] {
            width: 50%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 30%;
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
        .success-message {
            display: <?php echo $successMessage ? 'block' : 'none'; ?>;
            margin-top: 20px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
        }
        .dismiss-button {
            margin-top: 10px;
            padding: 5px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: <?php echo $successMessage ? 'inline-block' : 'none'; ?>;
        }
        .dismiss-button:hover {
            background-color: #c82333;
        }

        /* here begins the css for the transaction table */
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .transactions-table th, .transactions-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .transactions-table th {
            background-color: #f2f2f2;
        }
        .total-sum {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
        }
        .view-transactions-button {
            margin-top: 20px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            width: 30%;
        }
        .view-transactions-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

<div class="container">
    <h2>Deposit Contribution</h2>
    <form id="depositForm" method="POST" action="">
        <div class="form-group">
            <label for="userName">User Name</label>
            <input type="text" id="userName" name="username" value="<?php echo htmlspecialchars($userName); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="amount">Deposit Amount</label>
            <input type="number" id="amount" name="amount" required min="0" step="0.01">
        </div>
        <button type="submit">Submit</button>

        <button class="view-transactions-button" id="viewTransactionsButton">View My Transactions</button>
    </form>
    <div class="success-message" id="successMessage">
        Your contribution has been recorded successfully!
        <button class="dismiss-button" id="dismissButton">Dismiss</button>
    </div>

    

    <div id="transactionsContainer" style="display:none;">
        <h3>Transaction History</h3>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Username</th>
                    <th>Transaction Type</th>
                    <th>Deposit Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                        <td><?php echo htmlspecialchars($userName); ?></td>
                        <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total-sum">Total Sum: <?php echo htmlspecialchars($totalSum); ?></div>
    </div>
</div>

<script>
    // Function to hide the success message after 5 seconds
    window.onload = function() {
        const successMessage = document.getElementById('successMessage');
        const dismissButton = document.getElementById('dismissButton');
        const transactionsContainer = document.getElementById('transactionsContainer');

        if (successMessage.style.display === 'block') {
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000); // 5000 milliseconds = 5 seconds
        }

        // Dismiss button functionality
        dismissButton.onclick = function() {
            successMessage.style.display = 'none';
        };

        // View Transactions button functionality
        document.getElementById('viewTransactionsButton').onclick = function() {
            if (transactionsContainer.style.display === 'none') {
                transactionsContainer.style.display = 'block';
            } else {
                transactionsContainer.style.display = 'none';
            }
        };
    };
</script>

</body>
</html>