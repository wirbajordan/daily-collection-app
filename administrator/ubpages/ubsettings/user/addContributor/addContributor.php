<?php
// Include config file
include_once ('../config/config.php');

// Define variables and initialize with empty values
$username = $email = $phone_number = $first_name = $last_name = $password = "";
$username_err = $email_err = $phone_number_err = $password_err = "";
$success_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    $input_username = trim($_POST["username"]);
    if (empty($input_username)) {
        $username_err = "Please enter a username.";
    } else {
        // Check if username already exists
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $input_username;
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $username_err = "This username is already taken.";
                } else {
                    $username = $input_username;
                }
            }
            $stmt->close();
        }
    }

    // Validate email
    $input_email = trim($_POST["email"]);
    if (empty($input_email)) {
        $email_err = "Please enter an email address.";
    } else {
        // Check if email already exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $input_email;
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $email_err = "This email is already registered.";
                } else {
                    $email = $input_email;
                }
            }
            $stmt->close();
        }
    }

    // Validate phone number
    $input_phone = trim($_POST["phone_number"]);
    if (empty($input_phone)) {
        $phone_number_err = "Please enter a phone number.";
    } else {
        $phone_number = $input_phone;
    }

    // Validate password
    $input_password = trim($_POST["password"]);
    if (empty($input_password)) {
        $password_err = "Please enter a password.";
    } elseif (strlen($input_password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = $input_password;
    }

    // Get other fields
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $role = "contributor"; // Default role for this form

    // Check input errors before inserting in database
    if (empty($username_err) && empty($email_err) && empty($phone_number_err) && empty($password_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, email, phone_number, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssss", $param_username, $param_email, $param_phone, $param_password, $param_role, $param_first_name, $param_last_name);

            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_phone = $phone_number;
            $param_password = sha1($password); // Hash the password
            $param_role = $role;
            $param_first_name = $first_name;
            $param_last_name = $last_name;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $success_message = "Contributor added successfully!";
                // Clear form data after successful insertion
                $username = $email = $phone_number = $first_name = $last_name = $password = "";
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }
        // Close statement
        $stmt->close();
    }
    // Close connection
    $mysqli->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Contributor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Override admin page styles for this form */
        .add-contributor-form {
            background-color: #f8f9fa !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
        .add-contributor-form .wrapper{
            width: 850px !important;
            margin: 40px auto !important;
        }
        .add-contributor-form .card {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
        }
        .add-contributor-form .card-header {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
            color: white !important;
            border-bottom: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 25px 30px !important;
        }
        .add-contributor-form .card-header h2 {
            margin: 0 !important;
            font-weight: 600 !important;
            font-size: 1.5rem !important;
        }
        .add-contributor-form .card-body {
            padding: 35px !important;
        }
        .add-contributor-form .form-group {
            margin-bottom: 22px !important;
        }
        .add-contributor-form .form-group label {
            font-weight: 600 !important;
            color: #495057 !important;
            margin-bottom: 8px !important;
            display: block !important;
            font-size: 0.9rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }
        .add-contributor-form .form-control {
            border: 2px solid #e9ecef !important;
            border-radius: 8px !important;
            padding: 12px 15px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            height: auto !important;
        }
        .add-contributor-form .form-control:focus {
            border-color: #17a2b8 !important;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.15) !important;
        }
        .add-contributor-form .form-control.is-invalid {
            border-color: #dc3545 !important;
        }
        .add-contributor-form .invalid-feedback {
            font-size: 12px !important;
            margin-top: 5px !important;
            color: #dc3545 !important;
        }
        .add-contributor-form .btn {
            padding: 12px 25px !important;
            border-radius: 6px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            transition: all 0.3s ease !important;
            font-size: 14px !important;
        }
        .add-contributor-form .btn-info {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
            border: none !important;
        }
        .add-contributor-form .btn-info:hover {
            background: linear-gradient(135deg, #138496, #1ea085) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.25) !important;
        }
        .add-contributor-form .btn-secondary {
            background: #6c757d !important;
            border: none !important;
        }
        .add-contributor-form .btn-secondary:hover {
            background: #5a6268 !important;
            transform: translateY(-1px) !important;
        }
        .add-contributor-form .alert-success {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724 !important;
            border-radius: 8px !important;
            padding: 15px 20px !important;
        }
        .add-contributor-form .form-text {
            font-size: 12px !important;
            color: #6c757d !important;
            margin-top: 5px !important;
        }
        .add-contributor-form .row {
            margin-left: -8px !important;
            margin-right: -8px !important;
        }
        .add-contributor-form .col-md-6 {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
        .add-contributor-form .form-section {
            background: #f8f9fa !important;
            padding: 25px !important;
            border-radius: 10px !important;
            margin-bottom: 25px !important;
            border: 1px solid #e9ecef !important;
        }
        .add-contributor-form .form-section h5 {
            color: #495057 !important;
            margin-bottom: 20px !important;
            font-weight: 600 !important;
            border-bottom: 2px solid #17a2b8 !important;
            padding-bottom: 10px !important;
            font-size: 1.1rem !important;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            color: #6c757d;
        }
        .input-group .form-control {
            border-left: none;
        }
        .input-group .form-control:focus {
            border-left: none;
        }
    </style>
</head>
<body class="add-contributor-form">
    <div class="wrapper">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-user-plus"></i> Add New Contributor</h2>
            </div>
            <div class="card-body">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <p class="text-muted mb-4">Please fill in the details below to add a new contributor to the system.</p>
                
                <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                    <div class="form-section">
                        <h5><i class="fas fa-user"></i> Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo $first_name; ?>" placeholder="Enter first name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo $last_name; ?>" placeholder="Enter last name" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5><i class="fas fa-key"></i> Account Information</h5>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Choose a unique username" required>
                            <span class="invalid-feedback"><?php echo $username_err;?></span>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" placeholder="Enter email address" required>
                            <span class="invalid-feedback"><?php echo $email_err;?></span>
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" id="phone_number" name="phone_number" class="form-control <?php echo (!empty($phone_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone_number; ?>" placeholder="Enter phone number" required>
                            <span class="invalid-feedback"><?php echo $phone_number_err;?></span>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Enter password" required>
                            <span class="invalid-feedback"><?php echo $password_err;?></span>
                            <small class="form-text"><i class="fas fa-info-circle"></i> Password must be at least 6 characters long.</small>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <input type="submit" class="btn btn-info" value="Add Contributor">
                        <a href="admin.php?q=2&page=<?php echo base64_encode('../contributor_list'); ?>" class="btn btn-secondary ml-3">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html> 