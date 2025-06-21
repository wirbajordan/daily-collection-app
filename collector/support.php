<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header('Location: ../login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'dailycollect');
$successMsg = $errorMsg = '';

// Handle support ticket submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ticket'])) {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $priority = $_POST['priority'];
    $category = $_POST['category'];
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    if ($subject && $message) {
        $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, username, subject, message, priority, category, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'open', NOW())");
        $stmt->bind_param("isssss", $user_id, $username, $subject, $message, $priority, $category);
        
        if ($stmt->execute()) {
            $successMsg = "Support ticket submitted successfully! Ticket ID: " . $conn->insert_id;
        } else {
            $errorMsg = "Failed to submit ticket. Please try again.";
        }
        $stmt->close();
    } else {
        $errorMsg = "Please fill in all required fields.";
    }
}

// Fetch user's existing tickets
$user_tickets = [];
$stmt = $conn->prepare("SELECT * FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_tickets[] = $row;
}
$stmt->close();

include 'header.php';
?>

<div class="container-fluid mt-4">
    <!-- Support Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h1 class="display-5 fw-bold" style="color:#174ea6;">
                        <i class="fas fa-headset"></i> Support Center
                    </h1>
                    <p class="lead text-muted">We're here to help you succeed with your collection activities</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Support Options -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 text-center support-card">
                <div class="card-body">
                    <i class="fas fa-question-circle fa-3x mb-3" style="color:#174ea6;"></i>
                    <h5 class="card-title">FAQ</h5>
                    <p class="card-text">Find answers to common questions</p>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#faqModal">
                        Browse FAQ
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 text-center support-card">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-3x mb-3" style="color:#174ea6;"></i>
                    <h5 class="card-title">Submit Ticket</h5>
                    <p class="card-text">Create a support ticket for specific issues</p>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
                        New Ticket
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 text-center support-card">
                <div class="card-body">
                    <i class="fas fa-comments fa-3x mb-3" style="color:#174ea6;"></i>
                    <h5 class="card-title">Live Chat</h5>
                    <p class="card-text">Chat with our support team</p>
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#chatModal">
                        Start Chat
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 text-center support-card">
                <div class="card-body">
                    <i class="fas fa-phone fa-3x mb-3" style="color:#174ea6;"></i>
                    <h5 class="card-title">Call Support</h5>
                    <p class="card-text">Speak directly with our team</p>
                    <a href="tel:+237123456789" class="btn btn-outline-primary">
                        Call Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="supportTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                                <i class="fas fa-home"></i> Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button" role="tab">
                                <i class="fas fa-ticket-alt"></i> My Tickets
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab">
                                <i class="fas fa-book"></i> Resources
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">
                                <i class="fas fa-envelope"></i> Contact
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="supportTabContent">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4>Welcome to Support Center</h4>
                                    <p>We're committed to providing you with the best support experience. Here's what we offer:</p>
                                    
                                    <div class="row mt-4">
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-clock text-primary me-3 mt-1"></i>
                                                <div>
                                                    <h6>24/7 Support</h6>
                                                    <p class="text-muted small">Round-the-clock assistance for urgent issues</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-users text-primary me-3 mt-1"></i>
                                                <div>
                                                    <h6>Expert Team</h6>
                                                    <p class="text-muted small">Experienced professionals ready to help</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-shield-alt text-primary me-3 mt-1"></i>
                                                <div>
                                                    <h6>Secure Communication</h6>
                                                    <p class="text-muted small">All communications are encrypted and secure</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="fas fa-history text-primary me-3 mt-1"></i>
                                                <div>
                                                    <h6>Ticket History</h6>
                                                    <p class="text-muted small">Track all your previous support requests</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title">Quick Stats</h5>
                                            <div class="row text-center">
                                                <div class="col-6">
                                                    <h4 class="text-primary"><?php echo count($user_tickets); ?></h4>
                                                    <small class="text-muted">Total Tickets</small>
                                                </div>
                                                <div class="col-6">
                                                    <h4 class="text-success"><?php echo count(array_filter($user_tickets, function($t) { return $t['status'] === 'resolved'; })); ?></h4>
                                                    <small class="text-muted">Resolved</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tickets Tab -->
                        <div class="tab-pane fade" id="tickets" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>My Support Tickets</h4>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
                                    <i class="fas fa-plus"></i> New Ticket
                                </button>
                            </div>
                            
                            <?php if (!empty($user_tickets)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Ticket ID</th>
                                                <th>Subject</th>
                                                <th>Category</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user_tickets as $ticket): ?>
                                                <tr>
                                                    <td>#<?php echo $ticket['ticket_id']; ?></td>
                                                    <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($ticket['category']); ?></span></td>
                                                    <td>
                                                        <?php 
                                                        $priorityClass = $ticket['priority'] === 'high' ? 'bg-danger' : ($ticket['priority'] === 'medium' ? 'bg-warning' : 'bg-success');
                                                        echo '<span class="badge ' . $priorityClass . '">' . ucfirst($ticket['priority']) . '</span>';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $statusClass = $ticket['status'] === 'open' ? 'bg-primary' : ($ticket['status'] === 'in_progress' ? 'bg-warning' : 'bg-success');
                                                        echo '<span class="badge ' . $statusClass . '">' . ucfirst(str_replace('_', ' ', $ticket['status'])) . '</span>';
                                                        ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="viewTicket(<?php echo $ticket['ticket_id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                    <h5>No tickets yet</h5>
                                    <p class="text-muted">You haven't submitted any support tickets yet.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ticketModal">
                                        Submit Your First Ticket
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Resources Tab -->
                        <div class="tab-pane fade" id="resources" role="tabpanel">
                            <h4>Helpful Resources</h4>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-play-circle text-primary"></i> Video Tutorials
                                            </h5>
                                            <ul class="list-unstyled">
                                                <li><a href="#" class="text-decoration-none">Getting Started Guide</a></li>
                                                <li><a href="#" class="text-decoration-none">How to Register Contributions</a></li>
                                                <li><a href="#" class="text-decoration-none">Understanding Analytics</a></li>
                                                <li><a href="#" class="text-decoration-none">Exporting Reports</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-file-pdf text-primary"></i> Documentation
                                            </h5>
                                            <ul class="list-unstyled">
                                                <li><a href="#" class="text-decoration-none">User Manual (PDF)</a></li>
                                                <li><a href="#" class="text-decoration-none">API Documentation</a></li>
                                                <li><a href="#" class="text-decoration-none">Security Guidelines</a></li>
                                                <li><a href="#" class="text-decoration-none">Best Practices</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Tab -->
                        <div class="tab-pane fade" id="contact" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Contact Information</h4>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-phone text-primary me-3"></i>
                                                <div>
                                                    <h6>Phone Support</h6>
                                                    <p class="mb-0">+237 123 456 789</p>
                                                    <small class="text-muted">Available 24/7</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-envelope text-primary me-3"></i>
                                                <div>
                                                    <h6>Email Support</h6>
                                                    <p class="mb-0">support@visionfinance.com</p>
                                                    <small class="text-muted">Response within 2 hours</small>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-map-marker-alt text-primary me-3"></i>
                                                <div>
                                                    <h6>Office Address</h6>
                                                    <p class="mb-0">123 Finance Street, Douala, Cameroon</p>
                                                    <small class="text-muted">Main Office</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4>Send us a Message</h4>
                                    <form>
                                        <div class="mb-3">
                                            <label for="contactName" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="contactName" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contactEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="contactEmail" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="contactSubject" class="form-label">Subject</label>
                                            <input type="text" class="form-control" id="contactSubject" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="contactMessage" class="form-label">Message</label>
                                            <textarea class="form-control" id="contactMessage" rows="4" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Send Message
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'support_modals.php'; ?>
<?php include 'support_scripts.php'; ?>

<?php
$conn->close();
include 'footer.php';
?> 