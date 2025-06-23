<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Connect to the database
$host = 'localhost'; // Change if necessary
$user = 'root'; // Change to your database username
$password = ''; // Change to your database password
$dbname = 'dailycollect'; // Change to your database name

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$error = '';

// Handle un-assignment (now via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_contributor_id'])) {
    $contributor_id_to_unassign = intval($_POST['unassign_contributor_id']);
    $unassign_stmt = $conn->prepare("DELETE FROM assignments WHERE contributor_id = ?");
    $unassign_stmt->bind_param("i", $contributor_id_to_unassign);
    if ($unassign_stmt->execute()) {
        $_SESSION['message'] = 'Assignment successfully removed.';
    } else {
        $_SESSION['error'] = 'Failed to remove assignment.';
    }
    $unassign_stmt->close();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Handle form submission for assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['collector_id']) && isset($_POST['contributor_id'])) {
    $collector_id = intval($_POST['collector_id']);
    $contributor_id = intval($_POST['contributor_id']);

    if ($collector_id > 0 && $contributor_id > 0) {
        // Check if contributor is already assigned
        $check_stmt = $conn->prepare("SELECT contributor_id FROM assignments WHERE contributor_id = ?");
        $check_stmt->bind_param("i", $contributor_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $_SESSION['error'] = 'This contributor is already assigned.';
        } else {
            // Assign collector to contributor
            $assign_stmt = $conn->prepare("INSERT INTO assignments (collector_id, contributor_id) VALUES (?, ?)");
            $assign_stmt->bind_param("ii", $collector_id, $contributor_id);
            if ($assign_stmt->execute()) {
                $_SESSION['message'] = 'Collector successfully assigned.';
            } else {
                $_SESSION['error'] = 'Failed to assign collector.';
            }
            $assign_stmt->close();
        }
        $check_stmt->close();
    } else {
        $_SESSION['error'] = 'Invalid collector or contributor selected.';
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

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
                                            <button class="btn btn-danger btn-sm" onclick="confirmUnassign(<?php echo $row['contributor_id']; ?>)">
                                                <i class="fas fa-trash-alt"></i> Unassign
                                            </button>
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
    });

    var unassignContributorId = null;
    function confirmUnassign(contributorId) {
        unassignContributorId = contributorId;
        var myModal = new bootstrap.Modal(document.getElementById('unassignModal'), {});
        myModal.show();
    }

    // When modal Unassign button is clicked, submit the hidden form
    document.getElementById('modalUnassignBtn').onclick = function() {
        if (unassignContributorId) {
            document.getElementById('unassign_contributor_id').value = unassignContributorId;
            document.getElementById('unassignForm').submit();
        }
    };

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
</script>

</body>
</html>