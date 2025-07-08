<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once '../config/config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'administrator') {
    header('Location: ../login.php');
    exit();
}

// Get filter parameters
$filter_collector = isset($_GET['collector']) ? $_GET['collector'] : '';
$filter_rating = isset($_GET['rating']) ? $_GET['rating'] : '';

// Build WHERE clause for filtering
$where_clause = "WHERE 1=1";
$params = [];
$param_types = "";

if ($filter_collector) {
    $where_clause .= " AND u1.username LIKE ?";
    $params[] = "%$filter_collector%";
    $param_types .= "s";
}

if ($filter_rating) {
    $where_clause .= " AND r.rating = ?";
    $params[] = $filter_rating;
    $param_types .= "i";
}

// Fetch collector ratings statistics with proper collector names
$stats = [];
$stats_query = "SELECT 
    u.username as collector_name,
    u.user_id as collector_id,
    AVG(r.rating) as avg_rating,
    COUNT(r.id) as num_ratings,
    MIN(r.rating) as min_rating,
    MAX(r.rating) as max_rating
FROM users u 
LEFT JOIN collector_ratings r ON u.user_id = r.collector_id 
WHERE u.role = 'collector'
GROUP BY u.user_id, u.username 
ORDER BY avg_rating DESC, num_ratings DESC";

$stmt = $mysqli->prepare($stats_query);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $stats[] = $row;
}
$stmt->close();

// Fetch recent ratings with proper names
$recent = [];
$recent_query = "SELECT 
    r.*,
    u1.username as collector_name,
    u2.username as contributor_name,
    t.transaction_type,
    t.amount
FROM collector_ratings r 
JOIN users u1 ON r.collector_id = u1.user_id 
JOIN users u2 ON r.contributor_id = u2.user_id
LEFT JOIN transaction t ON r.transaction_id = t.transaction_id
$where_clause
ORDER BY r.created_at DESC 
LIMIT 50";

$stmt = $mysqli->prepare($recent_query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent[] = $row;
}
$stmt->close();

// Get unique collector names for filter dropdown
$collectors = [];
$collector_query = "SELECT DISTINCT u.username FROM users u JOIN collector_ratings r ON u.user_id = r.collector_id WHERE u.role = 'collector' ORDER BY u.username";
$result = $mysqli->query($collector_query);
while ($row = $result->fetch_assoc()) {
    $collectors[] = $row['username'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Ratings - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }
        .rating-stars {
            color: #ffd700;
            font-size: 1.2rem;
            letter-spacing: 2px;
        }
        .rating-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        .table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
            text-align: center;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        .filter-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .collector-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        .collector-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .avg-rating {
            font-size: 2.2rem;
            font-weight: bold;
            color: #667eea;
            text-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
        }
        .comment-cell {
            max-width: 300px;
            word-wrap: break-word;
        }
        .performance-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .performance-excellent {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }
        .performance-good {
            background: linear-gradient(45deg, #17a2b8, #6f42c1);
            color: white;
        }
        .performance-average {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
            color: white;
        }
        .performance-poor {
            background: linear-gradient(45deg, #dc3545, #e83e8c);
            color: white;
        }
        .section-title {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #667eea;
            display: inline-block;
        }
        .stats-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-outline-secondary {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #667eea;
            opacity: 0.5;
        }
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
            .comment-cell {
                max-width: 150px;
            }
            .stats-card {
                padding: 1rem;
            }
            .page-header {
                padding: 1.5rem 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h5><i class="fas fa-filter"></i> Filter Ratings</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="collector" class="form-label">Collector</label>
                    <select class="form-select" name="collector" id="collector">
                        <option value="">All Collectors</option>
                        <?php foreach ($collectors as $collector): ?>
                            <option value="<?php echo htmlspecialchars($collector); ?>" 
                                    <?php echo $filter_collector === $collector ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($collector); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="rating" class="form-label">Rating</label>
                    <select class="form-select" name="rating" id="rating">
                        <option value="">All Ratings</option>
                        <option value="5" <?php echo $filter_rating === '5' ? 'selected' : ''; ?>>5 Stars</option>
                        <option value="4" <?php echo $filter_rating === '4' ? 'selected' : ''; ?>>4 Stars</option>
                        <option value="3" <?php echo $filter_rating === '3' ? 'selected' : ''; ?>>3 Stars</option>
                        <option value="2" <?php echo $filter_rating === '2' ? 'selected' : ''; ?>>2 Stars</option>
                        <option value="1" <?php echo $filter_rating === '1' ? 'selected' : ''; ?>>1 Star</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="admin_collector_ratings.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="stats-card">
                    <h4><i class="fas fa-chart-bar"></i> Collector Performance Overview</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Collector</th>
                                    <th>Average Rating</th>
                                    <th>Total Ratings</th>
                                    <th>Rating Range</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats as $row): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['collector_name']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avg-rating me-2">
                                                    <?php echo $row['avg_rating'] ? number_format($row['avg_rating'], 1) : 'N/A'; ?>
                                                </span>
                                                <div class="rating-stars">
                                                    <?php 
                                                    $avg = $row['avg_rating'] ? round($row['avg_rating']) : 0;
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        echo $i <= $avg ? '★' : '☆';
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $row['num_ratings']; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($row['min_rating'] && $row['max_rating']): ?>
                                                <span class="rating-badge">
                                                    <?php echo $row['min_rating']; ?> - <?php echo $row['max_rating']; ?> stars
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No ratings</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $avg = $row['avg_rating'];
                                            if ($avg >= 4.5) {
                                                echo '<span class="badge bg-success">Excellent</span>';
                                            } elseif ($avg >= 4.0) {
                                                echo '<span class="badge bg-info">Good</span>';
                                            } elseif ($avg >= 3.0) {
                                                echo '<span class="badge bg-warning">Average</span>';
                                            } elseif ($avg > 0) {
                                                echo '<span class="badge bg-danger">Needs Improvement</span>';
                                            } else {
                                                echo '<span class="text-muted">No ratings</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Ratings -->
        <div class="row">
            <div class="col-12">
                <div class="stats-card">
                    <h4><i class="fas fa-clock"></i> Recent Ratings</h4>
                    <?php if (empty($recent)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No ratings found matching your criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Collector</th>
                                        <th>Contributor</th>
                                        <th>Transaction</th>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent as $row): ?>
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                                    <br>
                                                    <?php echo date('g:i A', strtotime($row['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['collector_name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?php echo htmlspecialchars($row['contributor_name']); ?></span>
                                            </td>
                                            <td>
                                                <?php if ($row['transaction_type'] && $row['amount']): ?>
                                                    <span class="badge bg-secondary">
                                                        <?php echo ucfirst($row['transaction_type']); ?>
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">₦<?php echo number_format($row['amount']); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rating-stars me-2">
                                                        <?php 
                                                        for ($i = 1; $i <= 5; $i++) {
                                                            echo $i <= $row['rating'] ? '★' : '☆';
                                                        }
                                                        ?>
                                                    </div>
                                                    <span class="badge bg-primary"><?php echo $row['rating']; ?>/5</span>
                                                </div>
                                            </td>
                                            <td class="comment-cell">
                                                <?php if ($row['comment']): ?>
                                                    <div class="text-truncate" title="<?php echo htmlspecialchars($row['comment']); ?>">
                                                        <?php echo htmlspecialchars($row['comment']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">No comment</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 