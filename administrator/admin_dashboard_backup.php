<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if this file is being included or accessed directly
$is_included = defined('INCLUDED_FROM_ADMIN') || (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], 'admin.php') !== false);

if ($is_included) {
    // If included from admin.php, use the existing connection
    global $mysqli;
    $conn = $mysqli;
} else {
    // If accessed directly, include the config
    include_once('../config/config.php');
    $conn = $mysqli;
}

// Check if assignments table exists and create it if it doesn't
$table_check = $conn->query("SHOW TABLES LIKE 'assignments'");
if ($table_check->num_rows == 0) {
    // Create assignments table if it doesn't exist
    $create_table_sql = "
    CREATE TABLE assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        collector_id INT NOT NULL,
        contributor_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_assignment (collector_id, contributor_id),
        FOREIGN KEY (collector_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (contributor_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    
    if ($conn->query($create_table_sql)) {
        $_SESSION['message'] = 'Assignments table created successfully.';
    } else {
        $_SESSION['error'] = 'Error creating assignments table: ' . $conn->error;
    }
}

$message = '';
$error = '';

// Show messages from session if available
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch all assignments for the view
$assignments_query = "
    SELECT u_collector.username AS collector_name, u_contributor.username AS contributor_name, a.contributor_id
    FROM assignments a
    JOIN users u_collector ON a.collector_id = u_collector.user_id
    JOIN users u_contributor ON a.contributor_id = u_contributor.user_id
    ORDER BY u_collector.username, u_contributor.username";
$assignments_result = $conn->query($assignments_query);

// Debug: Check if query was successful
if (!$assignments_result) {
    error_log("Assignments query failed: " . $conn->error);
    $_SESSION['error'] = 'Error fetching assignments: ' . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Collector</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-main {
            margin-left: 20px;
            margin-right: 20px;
            padding-top: 2rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #6f42c1, #4a148c);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            text-align: center;
            padding: 1.5rem;
        }
        .card-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .form-label {
            font-weight: 600;
        }
        .select2-container .select2-selection--single {
            height: calc(2.25rem + 10px);
            padding: 0.5rem 1rem;
            border: 1px solid #ced4da;
            border-radius: 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 10px);
            right: 10px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(2.25rem + 2px);
        }
        .btn-primary {
            background-color: #6f42c1;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #5a2a9e;
        }
        .table-hover tbody tr:hover {
            background-color: #f1eaff;
            cursor: pointer;
        }
        .table th {
            font-weight: 600;
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="container-main">
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Debug Information (remove in production) -->
    <!-- (Removed) -->
    <!-- Test Section (remove in production) -->
    <!-- (Removed) -->

    <div class="row">
        <!-- Assignment Form -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-user-plus"></i> Assign Collector</h2>
                </div>
                <div class="card-body p-4">
                    <form id="assignmentForm" method="POST" action="">
                        <div class="mb-4">
                            <label for="collector" class="form-label">Select Collector</label>
                            <select id="collector" name="collector_id" class="form-control" required>
                                <option value="">- Search for a Collector -</option>
                                <?php
                                $collectors = $conn->query("SELECT user_id, username FROM users WHERE role = 'collector'");
                                while ($row = $collectors->fetch_assoc()) {
                                    echo "<option value='{$row['user_id']}'>{$row['username']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="contributor" class="form-label">Select Contributor</label>
                            <select id="contributor" name="contributor_id" class="form-control" required>
                                <option value="">- Search for an Unassigned Contributor -</option>
                                <?php
                                $contributors = $conn->query("
                                    SELECT u.user_id, u.username 
                                    FROM users u
                                    LEFT JOIN assignments a ON u.user_id = a.contributor_id
                                    WHERE u.role = 'contributor' AND a.contributor_id IS NULL
                                ");
                                while ($row = $contributors->fetch_assoc()) {
                                    echo "<option value='{$row['user_id']}'>{$row['username']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-link"></i> Assign Collector</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Assignments Overview Table -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list-alt"></i> Assignments Overview</h2>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Collector</th>
                                    <th>Contributor</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($assignments_result && $assignments_result->num_rows > 0): ?>
                                <?php while ($row = $assignments_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><i class="fas fa-user-shield text-primary"></i> <?php echo htmlspecialchars($row['collector_name']); ?></td>
                                        <td><i class="fas fa-user text-success"></i> <?php echo htmlspecialchars($row['contributor_name']); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to unassign this contributor?');">
                                                <input type="hidden" name="unassign_contributor_id" value="<?php echo $row['contributor_id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash-alt"></i> Unassign
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No assignments found.</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Unassign Form -->
<form id="unassignForm" method="POST" style="display:none;">
    <input type="hidden" name="unassign_contributor_id" id="unassign_contributor_id" value="">
</form>

<!-- Unassign Confirmation Modal -->
<div class="modal fade" id="unassignModal" tabindex="-1" aria-labelledby="unassignModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="unassignModalLabel">Confirm Un-assignment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to unassign this collector?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="modalUnassignBtn">Unassign</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#collector, #contributor').select2({
            theme: "default"
        });
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    });

    let unassignContributorId = null;
    function confirmUnassign(contributorId) {
        unassignContributorId = contributorId;
        var myModal = new bootstrap.Modal(document.getElementById('unassignModal'));
        myModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalUnassignBtn').onclick = function() {
            if (unassignContributorId) {
                // Hide the modal
                let modalEl = document.getElementById('unassignModal');
                let modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                // Remove any lingering backdrops
                setTimeout(function() {
                    document.querySelectorAll('.modal-backdrop').forEach(bd => bd.remove());
                }, 200);

                // Submit the form
                document.getElementById('unassign_contributor_id').value = unassignContributorId;
                document.getElementById('unassignForm').submit();

                // Fallback: If form doesn't submit, reload after 1s
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            }
        };
    });

    function debugUnassign(contributorId) {
        console.log('=== UNASSIGN DEBUG ===');
        console.log('Contributor ID:', contributorId);
        console.log('Type of ID:', typeof contributorId);
        console.log('Modal element exists:', document.getElementById('unassignModal') !== null);
        console.log('Form element exists:', document.getElementById('unassignForm') !== null);
        console.log('Hidden input exists:', document.getElementById('unassign_contributor_id') !== null);
        if (contributorId && contributorId > 0) {
            console.log('ID is valid, proceeding with unassign...');
            confirmUnassign(contributorId);
        } else {
            console.error('Invalid contributor ID:', contributorId);
            alert('Invalid contributor ID: ' + contributorId);
        }
    }
</script>

</body>
</html>