<?php
// Connect to the database
$host = 'localhost'; // Change if necessary
$user = 'root'; // Change to your database username
$password = ''; // Change to your database password
$dbname = 'dailycollect'; // Change to your database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $collector_id = $_POST['collector_id'];
    $contributor_id = $_POST['contributor_id'];

    // Check if contributor already has an assigned collector
    $checkAssignment = $conn->prepare("SELECT * FROM assignments WHERE contributor_id = ?");
    $checkAssignment->bind_param("i", $contributor_id);
    $checkAssignment->execute();
    $result = $checkAssignment->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['message' => 'Error: Contributor already has an assigned collector.']);
        exit;
    }

    // Assign collector to contributor
    $assign = $conn->prepare("INSERT INTO assignments (collector_id, contributor_id) VALUES (?, ?)");
    $assign->bind_param("ii", $collector_id, $contributor_id);
    if ($assign->execute()) {   
        // Get collector and contributor details for notifications
        $collectorQuery = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
        $collectorQuery->bind_param("i", $collector_id);
        $collectorQuery->execute();
        $collectorResult = $collectorQuery->get_result()->fetch_assoc();

        $contributorQuery = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
        $contributorQuery->bind_param("i", $contributor_id);
        $contributorQuery->execute();
        $contributorResult = $contributorQuery->get_result()->fetch_assoc();

        // Create notifications for both users
        $notificationForContributor = $conn->prepare("INSERT INTO notification (user_id, message) VALUES (?, ?)");
        $messageForContributor = "You have been assigned to collector {$collectorResult['username']} (ID: {$collector_id}).";
        $notificationForContributor->bind_param("is", $contributor_id, $messageForContributor);
        $notificationForContributor->execute();

        $notificationForCollector = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $messageForCollector = "You have been assigned to contributor {$contributorResult['username']} (ID: {$contributor_id}).";
        $notificationForCollector->bind_param("is", $collector_id, $messageForCollector);
        $notificationForCollector->execute();

        echo json_encode(['message' => 'Success: Collector assigned to contributor.']);
    } else {
        echo json_encode(['message' => 'Error: Could not assign collector.']);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Collector</title>
    
    <style>
      
    </style>
</head>
<body>

<h2>Assign Collector</h2>
<form id="assignmentForm">
    <select id="collector" name="collector_id" required>
        <option value="">Select Collector</option>
        <?php
        $collectors = $conn->query("SELECT user_id, username FROM users WHERE role = 'collector'");
        while ($row = $collectors->fetch_assoc()) {
            echo "<option value='{$row['user_id']}'>{$row['username']}</option>";
        }
        ?>
    </select>
    <select id="contributor" name="contributor_id" required>
        <option value="">Select Contributor</option>
        <?php
        $contributors = $conn->query("SELECT user_id, username FROM users WHERE role = 'contributor'");
        while ($row = $contributors->fetch_assoc()) {
            echo "<option value='{$row['user_id']}'>{$row['username']}</option>";
        }
        ?>
    </select>
    <button type="submit">Assign Collector</button>
</form>

<div class="message" id="message"></div>

<script>
    document.getElementById('assignmentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch('admin_dashboard.php', { // Submit to the same PHP file
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('message').innerText = data.message;
        })
        .catch(error => {
            document.getElementById('message').innerText = 'An error occurred: ' + error;
        });
    });
</script>

</body>
</html>