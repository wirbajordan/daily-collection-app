<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'dailycollect');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);
$phone_number = trim($_POST['phone_number']);
$password = $_POST['password'];

if (empty($first_name) || empty($last_name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Check if email already exists for another user
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This email is already taken.']);
    $stmt->close();
    exit;
}
$stmt->close();

$update_sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ?";
$params = ["ssss", $first_name, $last_name, $email, $phone_number];

// Handle profile image upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $upload_dir = '../uploads/profile_images/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['profile_image']['type'];

    if (in_array($file_type, $allowed_types)) {
        // Delete old image if it's not the default
        if (!empty($_SESSION['profile_image']) && $_SESSION['profile_image'] != 'default.png' && file_exists($upload_dir . $_SESSION['profile_image'])) {
            unlink($upload_dir . $_SESSION['profile_image']);
        }

        $file_name = uniqid() . '-' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $update_sql .= ", profile_image = ?";
            $params[0] .= "s";
            $params[] = $file_name;
            $_SESSION['profile_image'] = $file_name; // Update session
        }
    }
}

if (!empty($password)) {
    $update_sql .= ", password = ?";
    $params[0] .= "s";
    $params[] = password_hash($password, PASSWORD_DEFAULT);
}

$update_sql .= " WHERE user_id = ?";
$params[0] .= "i";
$params[] = $user_id;

$stmt = $conn->prepare($update_sql);
// Use call_user_func_array to bind params dynamically
call_user_func_array([$stmt, 'bind_param'], array_merge([$params[0]], array_slice($params, 1)));

if ($stmt->execute()) {
    // Update session variables
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['email'] = $email;
    $_SESSION['phone_number'] = $phone_number;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
}

$stmt->close();
$conn->close();
?> 