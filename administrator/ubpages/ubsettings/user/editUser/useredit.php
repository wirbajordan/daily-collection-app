<?php
// Include config file
include_once ('../config/config.php');

// Define variables and initialize with empty values
$username = $email = $phone_number = $first_name = $last_name = "";
$username_err = $email_err = $phone_number_err = "";
$user_id = $_GET["user_id"];
$referrer = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["user_id"];
    $referrer = $_POST["referrer"];
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);

    // Validate username
    $input_username = trim($_POST["username"]);
    if (empty($input_username)) {
        $username_err = "Please enter a username.";
    } else {
        $username = $input_username;
    }

    // Validate email
    $input_email = trim($_POST["email"]);
    if (empty($input_email)) {
        $email_err = "Please enter an email address.";
    } else {
        $email = $input_email;
    }

    // Validate phone number
    $input_phone = trim($_POST["phone_number"]);
    if (empty($input_phone)) {
        $phone_number_err = "Please enter a phone number.";
    } else {
        $phone_number = $input_phone;
    }

    // Check input errors before updating in database
    if (empty($username_err) && empty($email_err) && empty($phone_number_err)) {
        // Prepare an update statement
        $sql = "UPDATE users SET username=?, email=?, phone_number=?, first_name=?, last_name=? WHERE user_id=?";

        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssi", $param_username, $param_email, $param_phone, $param_first_name, $param_last_name, $param_id);

            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $param_phone = $phone_number;
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_id = $user_id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Records updated successfully. Redirect to landing page.
                header("location: " . $referrer);
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }
        // Close statement
        $stmt->close();
    }
    // Close connection
    $mysqli->close();
} else {
    // Store the referrer to redirect back to the list page
    if(isset($_SERVER['HTTP_REFERER'])) {
        $referrer = $_SERVER['HTTP_REFERER'];
    } else {
        $referrer = "admin.php?q=1"; // Fallback
    }

    // Check existence of id parameter before processing further
    if (isset($_GET["user_id"]) && !empty(trim($_GET["user_id"]))) {
        // Get URL parameter
        $user_id = trim($_GET["user_id"]);

        // Prepare a select statement
        $sql = "SELECT * FROM users WHERE user_id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("i", $param_id);

            // Set parameters
            $param_id = $user_id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = $result->fetch_array(MYSQLI_ASSOC);

                    // Retrieve individual field value
                    $username = $row["username"];
                    $email = $row["email"];
                    $phone_number = $row["phone_number"];
                    $first_name = $row["first_name"];
                    $last_name = $row["last_name"];
                } else {
                    // URL doesn't contain valid id. Redirect to error page
                    // header("location: error.php");
                    exit();
                }

            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close statement
        $stmt->close();

        // Close connection
       // $mysqli->close();
    } else {
        // URL doesn't contain id parameter. Redirect to error page
        // header("location: error.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Shared styles from addContributor.php */
        body.update-user-form {
            background-color: #f8f9fa !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }
        .update-user-form .wrapper{
            width: 850px !important;
            margin: 40px auto !important;
        }
        .update-user-form .card {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
        }
        .update-user-form .card-header {
            background: linear-gradient(135deg, #007bff, #0056b3) !important;
            color: white !important;
            border-bottom: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 25px 30px !important;
        }
        .update-user-form .card-header h2 {
            margin: 0 !important;
            font-weight: 600 !important;
            font-size: 1.5rem !important;
        }
        .update-user-form .card-body {
            padding: 35px !important;
        }
        .update-user-form .form-group {
            margin-bottom: 22px !important;
        }
        .update-user-form .form-group label {
            font-weight: 600 !important;
            color: #495057 !important;
            margin-bottom: 8px !important;
            display: block !important;
            font-size: 0.9rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
        }
        .update-user-form .form-control {
            border: 2px solid #e9ecef !important;
            border-radius: 8px !important;
            padding: 12px 15px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            height: auto !important;
        }
        .update-user-form .form-control:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15) !important;
        }
        .update-user-form .form-control.is-invalid {
            border-color: #dc3545 !important;
        }
        .update-user-form .invalid-feedback {
            font-size: 12px !important;
            margin-top: 5px !important;
            color: #dc3545 !important;
        }
        .update-user-form .btn {
            padding: 12px 25px !important;
            border-radius: 6px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            transition: all 0.3s ease !important;
            font-size: 14px !important;
        }
        .update-user-form .btn-primary {
            background: linear-gradient(135deg, #007bff, #0056b3) !important;
            border: none !important;
        }
        .update-user-form .btn-primary:hover {
            background: linear-gradient(135deg, #0069d9, #004085) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.25) !important;
        }
        .update-user-form .btn-secondary {
            background: #6c757d !important;
            border: none !important;
        }
        .update-user-form .btn-secondary:hover {
            background: #5a6268 !important;
            transform: translateY(-1px) !important;
        }
        .update-user-form .row {
            margin-left: -8px !important;
            margin-right: -8px !important;
        }
        .update-user-form .col-md-6 {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
    </style>
</head>
<body class="update-user-form">
    <div class="wrapper">
        <div class="card">
            <div class="card-header">
                <h2 class="text-center"><i class="fas fa-user-edit"></i> Update User Record</h2>
            </div>
            <div class="card-body">
                <p class="text-center mb-4">Please edit the input values and submit to update the user record.</p>
                <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo $first_name; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo $last_name; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                                <span class="invalid-feedback"><?php echo $username_err;?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                <span class="invalid-feedback"><?php echo $email_err;?></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" class="form-control <?php echo (!empty($phone_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone_number; ?>">
                        <span class="invalid-feedback"><?php echo $phone_number_err;?></span>
                    </div>
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>"/>
                    <input type="hidden" name="referrer" value="<?php echo htmlspecialchars($referrer); ?>"/>
                    <div class="text-center mt-4">
                        <input type="submit" class="btn btn-primary" value="Update Record">
                        <a href="<?php echo htmlspecialchars($referrer); ?>" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>