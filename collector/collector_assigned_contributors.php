<?php
include 'header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header('Location: ../login.php');
    exit();
}

$collector_id = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'dailycollect');

// Fetch assigned contributors
$assigned_contributors = [];
$stmt = $conn->prepare("
    SELECT u.user_id, u.username, u.email, u.phone_number, u.first_name, u.last_name
    FROM users u
    JOIN assignments a ON u.user_id = a.contributor_id
    WHERE a.collector_id = ? AND u.role = 'contributor'
    ORDER BY u.username ASC
");
$stmt->bind_param("i", $collector_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $assigned_contributors[] = $row;
}
$stmt->close();
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 fw-bold mb-0" style="color:#174ea6;">
                            <i class="fas fa-users"></i> Assigned Contributors
                        </h1>
                        <p class="mb-0 text-muted">A list of all contributors assigned to you.</p>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                             <span class="input-group-text bg-light border-0"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control border-0 bg-light" id="contributorSearch" placeholder="Search by name or username...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contributors Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (!empty($assigned_contributors)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="contributorsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assigned_contributors as $contributor): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                    $fullName = trim($contributor['first_name'] . ' ' . $contributor['last_name']);
                                                    echo htmlspecialchars($fullName ?: $contributor['username']); 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($contributor['username']); ?></td>
                                            <td><a href="mailto:<?php echo htmlspecialchars($contributor['email']); ?>"><?php echo htmlspecialchars($contributor['email']); ?></a></td>
                                            <td><a href="tel:<?php echo htmlspecialchars($contributor['phone_number']); ?>"><?php echo htmlspecialchars($contributor['phone_number']); ?></a></td>
                                            <td class="text-center">
                                                <a href="collector_view_registered_contribution.php?contributor_id=<?php echo $contributor['user_id']; ?>" class="btn btn-sm btn-outline-primary" title="View Contributions">
                                                    <i class="fas fa-eye"></i> View Contributions
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Contributors Assigned</h5>
                            <p>You have not been assigned any contributors yet. Please contact your administrator.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('contributorSearch');
    const tableRows = document.querySelectorAll('#contributorsTable tbody tr');

    searchInput.addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        
        tableRows.forEach(row => {
            const fullName = row.cells[0].textContent.toLowerCase();
            const username = row.cells[1].textContent.toLowerCase();
            
            if (fullName.includes(searchTerm) || username.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>

<?php
$conn->close();
include 'footer.php';
?> 