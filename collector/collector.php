<?php
session_start();
include ('../config/config.php');


// Check user role
if ($_SESSION['role'] != 'collector') {
    header('Location: ../login.php');//redirect to logged in if role not valide
    exit();
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) { 
    header('Location: ../login.php'); // Redirect to login if not logged in
    exit();
}    
 // Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch quick stats
$conn = new mysqli('localhost', 'root', '', 'dailycollect');
$totalContributions = 0;
$totalContributors = 0;
$recentActivity = [];
$res = $conn->query("SELECT COUNT(*) as cnt, COALESCE(SUM(amount),0) as total FROM transaction WHERE user_id = $user_id");
if ($row = $res->fetch_assoc()) {
    $totalContributions = $row['total'];
}
$res = $conn->query("SELECT COUNT(DISTINCT username) as cnt FROM transaction WHERE user_id = $user_id");
if ($row = $res->fetch_assoc()) {
    $totalContributors = $row['cnt'];
}
$res = $conn->query("SELECT Date, username, amount, transaction_type FROM transaction WHERE user_id = $user_id ORDER BY Date DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $recentActivity[] = $row;
}

include 'header.php';
?>
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="fw-bold" style="font-size:2.2rem; margin-top:2rem;">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p class="text-muted" style="font-size:1.1rem; font-weight:bold;">Here's your collector dashboard overview.</p>
    </div>
</div>
<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Total Contributions</h5>
                <p class="display-6 fw-bold text-primary"><?php echo number_format($totalContributions, 2); ?> CFA</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Contributors Assigned</h5>
                <p class="display-6 fw-bold text-success"><?php echo $totalContributors; ?></p>
            </div>
        </div>
    </div>
    <!-- Add more stat cards as needed -->
</div>
<!-- Register Contribution Form (hidden by default, shown when button is clicked) -->
<div id="register-contribution-section" style="max-width: 480px; margin: 2rem auto; background: #fff; border-radius: 18px; box-shadow: 0 6px 24px rgba(37,99,235,0.08); padding: 2rem; display: none;">
    <?php include 'register_contribution_form.php'; ?>
</div>
<!-- Recent Activity Table -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-primary text-white fw-bold">
        Recent Contributions
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Contributor</th>
                    <th>Amount</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentActivity as $activity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['Date']); ?></td>
                        <td><?php echo htmlspecialchars($activity['username']); ?></td>
                        <td><?php echo htmlspecialchars($activity['amount']); ?></td>
                        <td><?php echo htmlspecialchars($activity['transaction_type']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
// Show/hide the form inline
function showRegisterContributionForm() {
    document.getElementById('register-contribution-section').style.display = 'block';
    window.scrollTo({ top: document.getElementById('register-contribution-section').offsetTop - 60, behavior: 'smooth' });
}
</script>
<?php
include 'footer.php';






