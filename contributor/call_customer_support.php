<?php
include_once '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit();
}

// Check user role
if ($_SESSION['role'] != 'contributor') {
    header('Location: ../login.php');
    exit();
}

// Get the logged-in user's ID from session
$userId = $_SESSION['user_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "dailycollect";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle support request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = array();
    
    // Validate required fields
    if (empty($_POST['message'])) {
        $errors[] = "Message is required";
    }
    if (empty($_POST['category'])) {
        $errors[] = "Category is required";
    }
    if (empty($_POST['priority'])) {
        $errors[] = "Priority is required";
    }

    // If no validation errors, proceed with submission
    if (empty($errors)) {
        $message = $_POST['message'];
        $category = $_POST['category'];
        $priority = $_POST['priority'];
        
        // Handle file upload
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $filename = $_FILES['attachment']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                $upload_path = 'uploads/';
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $new_filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path . $new_filename)) {
                    $attachment_path = $upload_path . $new_filename;
                }
            }
        }

        // Get assigned collector ID from assignments table
        $stmt = $conn->prepare("SELECT collector_id FROM assignments WHERE contributor_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($collectorId);
        $stmt->fetch();
        $stmt->close();

        if (!$collectorId) {
            $collectorId = 1; // Default collector
        }

        try {
            // Store support request in support_requests table
            $stmt = $conn->prepare("INSERT INTO support_requests (user_id, collector_id, message, category, priority, attachment, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("iissss", $userId, $collectorId, $message, $category, $priority, $attachment_path);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Support request submitted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error submitting support request.";
                $_SESSION['message_type'] = "error";
            }
            $stmt->close();
        } catch (Exception $e) {
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Please fix the following errors: " . implode(", ", $errors);
        $_SESSION['message_type'] = "error";
    }
}

// Fetch support request history
$history = array();
try {
    $stmt = $conn->prepare("SELECT sr.*, c.username as collector_name 
                           FROM support_requests sr 
                           LEFT JOIN users c ON sr.collector_id = c.user_id 
                           WHERE sr.user_id = ? 
                           ORDER BY sr.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['message'] = "Error fetching history: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Customer Support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { 
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container { 
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .support-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        textarea { 
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .history-item {
            border-left: 4px solid #007bff;
            padding: 10px;
            margin: 10px 0;
            background: white;
        }
        .priority-high { border-left-color: #dc3545; }
        .priority-medium { border-left-color: #ffc107; }
        .priority-low { border-left-color: #28a745; }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background-color: #ffc107; color: black; }
        .status-in-progress { background-color: #17a2b8; color: white; }
        .status-resolved { background-color: #28a745; color: white; }
        .attachment-preview {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="support-card">
            <h2><i class="fas fa-headset"></i> Customer Support</h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert <?php echo ($_SESSION['message_type'] === 'error') ? 'alert-danger' : 'alert-success'; ?>">
                    <?php 
                        echo htmlspecialchars($_SESSION['message']);
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>
            
            <form id="supportForm" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="mb-3">
                    <label for="category" class="form-label">Category *</label>
                    <select name="category" id="category" class="form-select" required>
                        <option value="">Select a category</option>
                        <option value="technical">Technical Issue</option>
                        <option value="account">Account Related</option>
                        <option value="payment">Payment Issue</option>
                        <option value="general">General Inquiry</option>
                    </select>
                    <div class="invalid-feedback">Please select a category</div>
                </div>

                <div class="mb-3">
                    <label for="priority" class="form-label">Priority Level *</label>
                    <select name="priority" id="priority" class="form-select" required>
                        <option value="">Select priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    <div class="invalid-feedback">Please select a priority level</div>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Describe your issue *</label>
                    <textarea name="message" id="message" rows="5" class="form-control" placeholder="Please provide details about your issue..." required></textarea>
                    <div class="invalid-feedback">Please describe your issue</div>
                </div>

                <div class="mb-3">
                    <label for="attachment" class="form-label">Attachment (optional)</label>
                    <input type="file" name="attachment" id="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <small class="text-muted">Supported formats: JPG, PNG, PDF, DOC, DOCX</small>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Support Request
                </button>
            </form>
        </div>

        <!-- Support Request History -->
        <div class="support-card">
            <h3><i class="fas fa-history"></i> Support History</h3>
            <?php if (empty($history)): ?>
                <p class="text-muted">No support requests yet.</p>
            <?php else: ?>
                <?php foreach ($history as $request): ?>
                    <div class="history-item priority-<?php echo htmlspecialchars($request['priority']); ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                            </span>
                            <small class="text-muted">
                                <?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?>
                            </small>
                        </div>
                        <div class="mt-2">
                            <strong>Category:</strong> <?php echo ucfirst(htmlspecialchars($request['category'])); ?>
                        </div>
                        <p class="mt-2"><?php echo htmlspecialchars($request['message']); ?></p>
                        <?php if ($request['attachment']): ?>
                            <div class="mt-2">
                                <i class="fas fa-paperclip"></i> 
                                <a href="<?php echo htmlspecialchars($request['attachment']); ?>" target="_blank">
                                    View Attachment
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if ($request['collector_name']): ?>
                            <div class="mt-2 text-muted">
                                <small>Assigned to: <?php echo htmlspecialchars($request['collector_name']); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function validateForm() {
            const form = document.getElementById('supportForm');
            const category = document.getElementById('category');
            const priority = document.getElementById('priority');
            const message = document.getElementById('message');
            let isValid = true;

            // Remove previous validation classes
            form.classList.remove('was-validated');

            // Check category
            if (!category.value) {
                category.classList.add('is-invalid');
                isValid = false;
            } else {
                category.classList.remove('is-invalid');
                category.classList.add('is-valid');
            }

            // Check priority
            if (!priority.value) {
                priority.classList.add('is-invalid');
                isValid = false;
            } else {
                priority.classList.remove('is-invalid');
                priority.classList.add('is-valid');
            }

            // Check message
            if (!message.value.trim()) {
                message.classList.add('is-invalid');
                isValid = false;
            } else {
                message.classList.remove('is-invalid');
                message.classList.add('is-valid');
            }

            if (!isValid) {
                form.classList.add('was-validated');
            }

            return isValid;
        }

        // Preview attachment if it's an image
        document.querySelector('input[name="attachment"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'attachment-preview';
                    const container = document.querySelector('.mb-3:last-of-type');
                    const existingPreview = container.querySelector('.attachment-preview');
                    if (existingPreview) {
                        container.removeChild(existingPreview);
                    }
                    container.appendChild(preview);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>