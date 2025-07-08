<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$conn = new mysqli('localhost', 'root', '', 'dailycollect');

// Fetch user data including profile image, first name, last name, and phone
$res = $conn->query("SELECT profile_image, first_name, last_name, phone_number FROM users WHERE user_id = $user_id");
if ($user_data = $res->fetch_assoc()) {
    $_SESSION['profile_image'] = $user_data['profile_image'] ?? 'default.png';
    $_SESSION['first_name'] = $user_data['first_name'] ?? '';
    $_SESSION['last_name'] = $user_data['last_name'] ?? '';
    $_SESSION['phone_number'] = $user_data['phone_number'] ?? '';
}

// --- Fetch ALL data for dashboard ---
// Quick Stats & Recent Activity
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
$res = $conn->query("SELECT username, amount, Date FROM transaction WHERE user_id = $user_id ORDER BY Date DESC LIMIT 50");
while ($row = $res->fetch_assoc()) {
    $recentActivity[] = $row;
}
// Notifications
$notifications = [];
$stmt = $conn->prepare("SELECT message, created_at FROM notification WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($message, $created_at);
while ($stmt->fetch()) {
    $notifications[] = ['message' => $message, 'created_at' => $created_at];
}
$stmt->close();
// Chart Data
$chartData = [];
$res = $conn->query("SELECT DATE(Date) as day, SUM(amount) as total FROM transaction WHERE user_id = $user_id GROUP BY day ORDER BY day ASC");
if ($res) while ($row = $res->fetch_assoc()) $chartData[] = $row;
$pieData = [];
$res = $conn->query("SELECT username, SUM(amount) as total FROM transaction WHERE user_id = $user_id GROUP BY username");
if ($res) while ($row = $res->fetch_assoc()) $pieData[] = $row;
// Advanced Analytics
$res = $conn->query("SELECT COUNT(*) as cnt FROM transaction WHERE user_id = $user_id");
$totalTransactions = ($row = $res->fetch_assoc()) ? $row['cnt'] : 0;
$res = $conn->query("SELECT MAX(amount) as max_amt FROM transaction WHERE user_id = $user_id");
$largestContribution = ($row = $res->fetch_assoc()) ? ($row['max_amt'] ?? 0) : 0;
$topContributor = 'N/A';
$avgContribution = 0;
$contribSums = [];
$totalSum = 0;
$totalCount = 0;
foreach ($recentActivity as $act) {
    $totalSum += $act['amount'];
    $totalCount++;
    $user = $act['username'];
    if (!isset($contribSums[$user])) $contribSums[$user] = 0;
    $contribSums[$user] += $act['amount'];
}
if ($contribSums) {
    $topContributor = array_keys($contribSums, max($contribSums))[0];
}
$avgContribution = $totalCount ? ($totalSum / $totalCount) : 0;
?>
<!-- RESTORED FULL DASHBOARD LAYOUT -->
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold" style="color:#174ea6;">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
            <p class="lead" style="color:#2563eb;">Here's your collection dashboard overview</p>
        </div>
        <div class="col-md-4 d-flex align-items-center justify-content-end">
            <div class="card shadow-sm" style="min-width:220px;">
                <div class="card-body text-center">
                    <img src="../uploads/profile_images/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile Picture" class="rounded-circle mb-2" style="width: 60px; height: 60px; object-fit: cover;">
                    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($username); ?></h5>
                    <div class="text-muted small">Collector</div>
                    <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#profileModal"><i class="fas fa-cog"></i> Profile/Settings</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-content" type="button" role="tab">
                                <i class="fas fa-edit"></i> Register Contribution
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics-content" type="button" role="tab">
                                <i class="fas fa-chart-bar"></i> Analytics & Reports
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="dashboardTabContent">
        <!-- Register Contribution Tab -->
        <div class="tab-pane fade show active" id="register-content" role="tabpanel">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <?php include 'register_contribution_form.php'; ?>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title" style="color:#174ea6;"><i class="fas fa-bell"></i> Notifications</h5>
                            <ul class="list-group list-group-flush">
                                 <?php if (!empty($notifications)): ?>
                                    <?php foreach ($notifications as $noti): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($noti['message']); ?></span>
                                            <span class="badge bg-light text-dark small"><?php echo date('d M Y H:i', strtotime($noti['created_at'])); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="list-group-item text-muted">No notifications yet.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div class="tab-pane fade" id="analytics-content" role="tabpanel">
            <div class="row mb-2">
                <div class="col-12 text-end">
                    <a href="export_contributions_excel.php" class="btn btn-success"><i class="fas fa-file-excel"></i> Export Recent</a>
                    <a href="export_all_contributions_excel.php" class="btn btn-primary ms-2"><i class="fas fa-file-excel"></i> Export All Data</a>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title" style="color:#174ea6;"><i class="fas fa-chart-line"></i> Contributions Over Time</h5>
                            <canvas id="contribChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title" style="color:#174ea6;"><i class="fas fa-chart-pie"></i> By Contributor</h5>
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Total Transactions</h5>
                            <div class="display-6 fw-bold text-primary"><?php echo $totalTransactions; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Largest Contribution</h5>
                            <div class="display-6 fw-bold text-success">CFA <?php echo number_format($largestContribution,2); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title" style="color:#174ea6;">Advanced Analytics</h5>
                            <ul class="list-unstyled mb-0">
                                <li><strong>Top Contributor:</strong> <span class="fw-bold"><?php echo htmlspecialchars($topContributor); ?></span></li>
                                <li><strong>Average Contribution:</strong> <span class="fw-bold"><?php echo number_format($avgContribution,2); ?> CFA</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Profile/Settings Modal -->
<div class="modal fade" id="profileModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Profile/Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="profileForm" enctype="multipart/form-data">
          <div class="text-center mb-3">
              <img src="../uploads/profile_images/<?php echo htmlspecialchars($_SESSION['profile_image']); ?>" alt="Profile Picture" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
              <input type="file" class="form-control" name="profile_image" id="profileImageInput" accept="image/*">
          </div>
          <div class="mb-3">
            <label for="profileFirstName" class="form-label">First Name</label>
            <input type="text" class="form-control" id="profileFirstName" name="first_name" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label for="profileLastName" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="profileLastName" name="last_name" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label for="profilePhone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="profilePhone" name="phone_number" value="<?php echo htmlspecialchars($_SESSION['phone_number'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label for="profileEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="profileEmail" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
          </div>
          <div class="mb-3">
            <label for="profilePassword" class="form-label">New Password</label>
            <input type="password" class="form-control" id="profilePassword" name="password" placeholder="Leave blank to keep current">
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
        <div id="profileMsg" class="mt-2"></div>
      </div>
    </div>
  </div>
</div>
<!-- Add Show QR Code Button -->
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#qrModal">Show QR Code</button>
<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Your QR Code</h5></div>
      <div class="modal-body text-center">
        <div id="collector-qrcode"></div>
      </div>
    </div>
  </div>
</div>
<!-- SCRIPTS at the end of the body -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
<script>
// Tab switching functions for navigation
function showRegisterTab() {
    const registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
    registerTab.show();
}

function showAnalyticsTab() {
    const analyticsTab = new bootstrap.Tab(document.getElementById('analytics-tab'));
    analyticsTab.show();
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        // --- Line Chart ---
        const chartData = <?php echo json_encode($chartData); ?>;
        const lineCtx = document.getElementById('contribChart').getContext('2d');
        if (chartData && chartData.length > 0) {
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: chartData.map(row => row.day),
                    datasets: [{ label: 'CFA Collected', data: chartData.map(row => row.total), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.3 }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        } else {
            lineCtx.font = "16px Arial";
            lineCtx.fillText("No contribution data available", lineCtx.canvas.width / 4, lineCtx.canvas.height / 2);
        }
        // --- Pie Chart ---
        const pieData = <?php echo json_encode($pieData); ?>;
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        if (pieData && pieData.length > 0) {
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: pieData.map(row => row.username),
                    datasets: [{ data: pieData.map(row => row.total), backgroundColor: ['#2563eb', '#174ea6', '#4caf50', '#ff9800', '#e91e63', '#00bcd4'] }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom', labels:{padding:15} } } }
            });
        } else {
            pieCtx.font = "16px Arial";
            pieCtx.fillText("No contributor data", pieCtx.canvas.width / 3, pieCtx.canvas.height / 2);
        }
        // --- Profile Form AJAX ---
        const profileForm = document.getElementById('profileForm');
        profileForm.onsubmit = function(e) {
            e.preventDefault();
            const msgDiv = document.getElementById('profileMsg');
            msgDiv.className = 'alert alert-info mt-2';
            msgDiv.textContent = 'Saving...';
            
            const formData = new FormData(profileForm);

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                msgDiv.className = data.success ? 'alert alert-success mt-2' : 'alert alert-danger mt-2';
                msgDiv.textContent = data.message;
            })
            .catch(() => {
                msgDiv.className = 'alert alert-danger mt-2';
                msgDiv.textContent = 'An error occurred.';
            });
        };
        // --- QR Code Generation ---
        var collectorId = "<?php echo $_SESSION['user_id']; ?>";
        var qrDiv = document.getElementById("collector-qrcode");
        if (qrDiv && qrDiv.childNodes.length === 0) {
            new QRCode(qrDiv, collectorId);
        }
    } catch(e) {
        console.error("An error occurred in the dashboard script:", e);
    }
});
</script>
<?php
$conn->close();
include 'footer.php';
?>