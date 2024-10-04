
<?php
include_once '../config/config.php';        


// Check user role
//if ($_SESSION['role'] != 'contributor') {
   // header('Location: .../login.php' );
  //  exit();
//}

// Check if user is logged in
//if (!isset($_SESSION['user_id'])) {
  //  header('Location: .../login.php'); // Redirect to login if not logged in
    //exit();
//}    
     
          
// Assuming user ID is stored in session after login
//$user_id = $_SESSION['user_id'];

 
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