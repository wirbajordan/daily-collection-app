<?php
// Simple test script to verify assignments table
include_once('../config/config.php');

echo "<h2>Assignments Table Test</h2>";

// Check if table exists
$table_check = $mysqli->query("SHOW TABLES LIKE 'assignments'");
if ($table_check->num_rows > 0) {
    echo "<p style='color: green;'>✓ Assignments table exists</p>";
    
    // Check table structure
    $structure = $mysqli->query("DESCRIBE assignments");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for data
    $data_check = $mysqli->query("SELECT COUNT(*) as count FROM assignments");
    $count = $data_check->fetch_assoc()['count'];
    echo "<p><strong>Number of assignments:</strong> " . $count . "</p>";
    
    if ($count > 0) {
        echo "<h3>Current Assignments:</h3>";
        $assignments = $mysqli->query("
            SELECT a.*, 
                   u1.username as collector_name, 
                   u2.username as contributor_name
            FROM assignments a
            JOIN users u1 ON a.collector_id = u1.user_id
            JOIN users u2 ON a.contributor_id = u2.user_id
        ");
        
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Collector</th><th>Contributor</th><th>Created</th></tr>";
        while ($row = $assignments->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['collector_name'] . "</td>";
            echo "<td>" . $row['contributor_name'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Assignments table does not exist</p>";
}

// Check users table for collectors and contributors
echo "<h3>Users Check:</h3>";
$collectors = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE role = 'collector'");
$collector_count = $collectors->fetch_assoc()['count'];
echo "<p><strong>Collectors:</strong> " . $collector_count . "</p>";

$contributors = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE role = 'contributor'");
$contributor_count = $contributors->fetch_assoc()['count'];
echo "<p><strong>Contributors:</strong> " . $contributor_count . "</p>";

$mysqli->close();
?> 