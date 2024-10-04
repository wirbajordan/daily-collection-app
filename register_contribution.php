<?php
if ($_SESSION['role'] != 'collector') {
    header('Location: .../login.php');
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redirect to login if not logged in
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