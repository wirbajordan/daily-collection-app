<?php


// Check user role
if ($_SESSION['role'] != 'administrator') {
    header('Location: .../login.php');//redirect to logged in if role not valide
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: .../login.php'); // Redirect to login if not logged in
    exit();
}    

// Determine the type of list to retrieve
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($type == 'collectors') {
    // Fetch collectors from the database
    $result = $mysqli->query("SELECT user_id, username, email  FROM users WHERE role = 'Collector'");
    
    if ($result->num_rows > 0) {
        echo '<table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        
                 </tr>
                </thead>
                <tbody>';
        while ($collector = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . htmlspecialchars($collector['user_id']) . '</td>
                    <td>' . htmlspecialchars($collector['username']) . '</td>
                    <td>' . htmlspecialchars($collector['email']) . '</td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo 'No collectors found.';
    }
} elseif ($type == 'contributors') {
    // Fetch contributors from the database
    $result = $mysqli->query("SELECT user_id, username, email FROM users WHERE role = 'Contributor'");
    
    if ($result->num_rows > 0) {
        echo '<table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                       
                    </tr>
                </thead>
                <tbody>';
        while ($contributor = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . htmlspecialchars($contributor['user_id']) . '</td>
                    <td>' . htmlspecialchars($contributor['username']) . '</td>
                    <td>' . htmlspecialchars($contributor['email']) . '</td>
                   
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo 'No contributors found.';
    }
} else {
    echo 'Invalid request.';
}
?>