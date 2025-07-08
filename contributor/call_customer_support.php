<?php
include_once '../config/config.php';

// Security: Only logged-in contributors
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'contributor') {
    header('Location: ../login.php');
    exit();
}
$userId = $_SESSION['user_id'];

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "dailycollect";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// After session and role checks

// Handle support request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = array();
    if (empty($_POST['message'])) $errors[] = "Message is required";
    if (empty($_POST['category'])) $errors[] = "Category is required";
    if (empty($_POST['priority'])) $errors[] = "Priority is required";
    if (empty($errors)) {
        $message = $_POST['message'];
        $category = $_POST['category'];
        $priority = $_POST['priority'];
        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx');
            $filename = $_FILES['attachment']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $allowed)) {
                $upload_path = 'uploads/';
                if (!file_exists($upload_path)) { mkdir($upload_path, 0777, true); }
                $new_filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path . $new_filename)) {
                    $attachment_path = $upload_path . $new_filename;
                }
            }
        }
        // Get assigned collector ID
        $stmt = $conn->prepare("SELECT collector_id FROM assignments WHERE contributor_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($collectorId);
        $stmt->fetch();
        $stmt->close();
        if (!$collectorId) $collectorId = 1;
        try {
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

// Fetch support request history and stats
$history = array();
$totalTickets = 0;
$resolvedTickets = 0;
try {
    $stmt = $conn->prepare("SELECT sr.*, c.username as collector_name FROM support_requests sr LEFT JOIN users c ON sr.collector_id = c.user_id WHERE sr.user_id = ? ORDER BY sr.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
        $totalTickets++;
        if ($row['status'] === 'resolved') $resolvedTickets++;
    }
    $stmt->close();
} catch (Exception $e) {
    $_SESSION['message'] = "Error fetching history: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
}
?>

<div class="container" style="margin-top: 30px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading" style="background: #495057; color: white;">
                    <h3 class="panel-title"><span class="glyphicon glyphicon-earphone"></span> Call Customer Support</h3>
                </div>
                <div class="panel-body">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['message_type'] === 'success' ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars($_SESSION['message']); ?>
                        </div>
                        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" class="form-horizontal">
                        <div class="form-group">
                            <label for="category" class="control-label">Category</label>
                            <select name="category" id="category" class="form-control" required>
                                <option value="">Select Category</option>
                                <option value="Technical">Technical</option>
                                <option value="Account">Account</option>
                                <option value="Payment">Payment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="priority" class="control-label">Priority</label>
                            <select name="priority" id="priority" class="form-control" required>
                                <option value="">Select Priority</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message" class="control-label">Message</label>
                            <textarea name="message" id="message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="attachment" class="control-label">Attachment (optional)</label>
                            <input type="file" name="attachment" id="attachment" class="form-control">
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="panel panel-default" style="margin-top: 30px;">
                <div class="panel-heading" style="background: #495057; color: white;">
                    <h3 class="panel-title"><span class="glyphicon glyphicon-list-alt"></span> Support Request History</h3>
                </div>
                <div class="panel-body">
                    <?php if (empty($history)): ?>
                        <div class="alert alert-info">No support requests found.</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Collector</th>
                                    <th>Attachment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $req): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($req['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($req['category']); ?></td>
                                        <td><?php echo htmlspecialchars($req['priority']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($req['message'])); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($req['status'])); ?></td>
                                        <td><?php echo htmlspecialchars($req['collector_name']); ?></td>
                                        <td>
                                            <?php if (!empty($req['attachment'])): ?>
                                                <a href="<?php echo htmlspecialchars($req['attachment']); ?>" target="_blank">View</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>