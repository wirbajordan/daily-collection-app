<?php
session_start();
include_once '../config/config.php';

// Logic to display success or error messages
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

unset($_SESSION['error_message']);
unset($_SESSION['success_message']);

// Fetch contributors and collectors from the database
$contributors = [];
$collectors = [];
$username = [];

// Fetch contributors
$result = $mysqli->query("SELECT user_id, username FROM users WHERE role = 'contributor'");
while ($row = $result->fetch_assoc()) {
    $contributors[] = $row;
}

// Fetch collectors
$result = $mysqli->query("SELECT user_id, username FROM users WHERE role = 'collector'");
while ($row = $result->fetch_assoc()) {
    $collectors[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contributor_id = $_POST['contributor'];
    $collector_id = $_POST['collector'];
    

    // Check if the assignment already exists
    $stmt = $mysqli->prepare("SELECT COUNT(*) FROM assignments WHERE contributor_id = ? AND collector_id = ? AND username = ?");
    $stmt->bind_param("iii", $contributor_id, $collector_id, $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close(); // Close the statement to free resources

    if ($count > 0) {
        $_SESSION['error_message'] = "The collector has already been assigned to this contributor.";
        header('Location: admin_assign_collect_con.php');
        exit();
    }

    // Assigning the collector to the contributor
    $stmt = $mysqli->prepare("INSERT INTO assignments (contributor_id, collector_id, username) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $contributor_id, $collector_id, $username);

    if ($stmt->execute()) {
        // Fetch usernames for notifications
        //$stmt->close(); // Close the previous statement

         // Fetch contributor username
         $stmt->bind_param("i", $contributor_id);
         $stmt->execute();
         $stmt->bind_result($contributor_username);
         $stmt->fetch();
         
         // Fetch collector username
         $stmt->bind_param("i", $collector_id);
         $stmt->execute();
         $stmt->bind_result($collector_username);
         $stmt->fetch();
 
         $_SESSION['success_message'] = "Successfully assigned $collector_username to $contributor_username.";

         // Notify both users
            $stmt->close();
         $stmt = $mysqli->prepare("INSERT INTO notification (user_id, message, username) VALUES (?, ?, ?)");
 
         // Notify contributor
         $message = "You have been assigned to collector: " . $collector_id . $username;
         $stmt->bind_param("isi", $contributor_id, $message, $username);
         $stmt->execute();
 
         // Notify collector
         $message = "You have been assigned to contributor: " . $contributor_id . $username;
         $stmt->bind_param("is", $collector_id, $message, $username);
         $stmt->execute();
 
         $_SESSION['success_message'] = "Successfully assigned collector . $username to contributor . $username";
         header('Location: admin_assign_collect_con.php');
         exit(); 
     } else {
         $_SESSION['error_message'] = "Assignment failed: " . $stmt->error;
         header('Location: admin_assign_collect_con.php');
        $stmt = $mysqli->prepare("SELECT username FROM users WHERE user_id = ?");
        
       
        
        // Redirect back to the dashboard
       // header('Location: admin_assign_collect_con.php');
        //exit(); 
    //} else {
      //  $_SESSION['error_message'] = "Assignment failed: " . $stmt->error;
        ///header('Location: admin_assign_collect_con.php');
        //exit();
    }

    // $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .assign-form {
            display: none; /* Initially hidden */
            margin-top: 20px;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
        .success-message {
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
<h1>Assign a Collector to a Contributor for Proper Follow-up</h1>

    <!-- Display success or error messages -->
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <button id="assignButton">Assign Collector to Contributor</button>

    <div class="assign-form" id="assignForm">
        <form method="POST" action="admin_assign_collect_con.php">
            <select name="contributor" required>
                <option value="">Select Contributor</option>
                <!-- Populate contributors dynamically -->
                <?php foreach ($contributors as $contributor): ?>
                    <option value="<?php echo $contributor['user_id']; ?>"><?php echo $contributor['username']; ?></option>
                <?php endforeach; ?>
            </select>

            <select name="collector" required>
                <option value="">Select Collector</option>
                <!-- Populate collectors dynamically -->
                <?php foreach ($collectors as $collector): ?>
                    <option value="<?php echo $collector['user_id']; ?>"><?php echo $collector['username']; ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Assign</button>
        </form>
    </div>
</div>

<script>
    // JavaScript to toggle the visibility of the assignment form
    document.getElementById('assignButton').addEventListener('click', function() {
        var form = document.getElementById('assignForm');
        form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
    });
</script>
</body>
</html>