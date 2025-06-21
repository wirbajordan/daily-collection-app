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

// Assuming user ID is stored in session after login
$user_id = $_SESSION['user_id'];

// Initialize variables
$userName = '';
$totalSum = 0;
$transactions = [];
$notifications = [];

// Fetch user details
$stmt = $mysqli->prepare("SELECT username FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($userName);
$stmt->fetch();
$stmt->close();

// Fetch total balance
$stmt = $mysqli->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transaction WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($totalSum);
$stmt->fetch();
$stmt->close();

// Fetch transactions with more details
$stmt = $mysqli->prepare("SELECT t.amount, t.transaction_type, t.Date, t.username, t.transaction_id 
                         FROM transaction t 
                         WHERE t.user_id = ? 
                         ORDER BY t.Date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Fetch notifications for the logged-in user
$stmt = $mysqli->prepare("SELECT notification_id, message, created_at FROM notification WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Handle notification deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    $notification_id = intval($_POST['notification_id']);
    
    // Prepare and execute delete statement
    $deleteStmt = $mysqli->prepare("DELETE FROM notification WHERE notification_id = ? AND user_id = ?");
    $deleteStmt->bind_param('ii', $notification_id, $user_id);
    
    if ($deleteStmt->execute()) {
        $successMessage = 'Notification deleted successfully!';
    } else {
        $errorMessage = 'Failed to delete notification.';
    }
    
    $deleteStmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contributor Dashboard - Vision Finance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --background-color: #f8f9fa;
        }

        /* Add modal-specific styles */
        /*
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            overflow-y: auto;
        }
        
        .modal.show {
            display: block;
        }

        .modal-dialog {
            position: relative;
            width: 95%;
            max-width: 800px;
            margin: 50px auto;
            transform: translate(0, 0);
        }

        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            background-color: #fff;
            border: 1px solid rgba(0,0,0,.2);
            border-radius: 0.3rem;
            outline: 0;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .modal-header .close {
            padding: 1rem;
            margin: -1rem -1rem -1rem auto;
            background: transparent;
            border: 0;
            font-size: 1.5rem;
            cursor: pointer;
            color: #000;
            opacity: 0.5;
            line-height: 1;
        }

        .modal-header .close:hover {
            opacity: 0.75;
        }

        .modal-body {
            padding: 1rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 1rem;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 800px;
                margin: 50px auto;
            }
        }
        */
        /
        .table-responsive {
            width: 100%;
            margin: 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table th,
        .table td {
            text-align: center;
            padding: 0.75rem;
            vertical-align: middle;
            white-space: normal;
            word-wrap: break-word;
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .table td {
            border-bottom: 1px solid #dee2e6;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.875em;
            display: inline-block;
            min-width: 80px;
            white-space: nowrap;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-color);
        }

        .dashboard-container {
            padding: 2rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }

        .notification {
            background-color: white;
            border-left: 4px solid var(--secondary-color);
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .notification .date {
            color: #666;
            font-size: 0.9rem;
        }

        .delete-button {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-button {
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .action-button:hover {
            background-color: #2980b9;
            color: white;
        }

        .balance-card {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .balance-amount {
            font-size: 2rem;
            font-weight: bold;
        }

        .currency {
            font-size: 1.2rem;
            font-weight: normal;
        }

        @media (max-width: 768px) {
            .modal-dialog {
                width: 95%;
                margin: 0.5rem auto;
            }
            
            .table th,
            .table td {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .badge {
                min-width: 60px;
                padding: 0.4em 0.6em;
                font-size: 0.8em;
            }
        }

        @media (max-width: 576px) {
            .modal-dialog {
                width: 100%;
                margin: 0;
                max-height: 100vh;
            }

            .modal-content {
                border-radius: 0;
            }

            .table th,
            .table td {
                padding: 0.4rem;
                font-size: 0.85rem;
            }
        }

        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 800px;
                margin: 50px auto;
            }
        }

        .modal.show {
            display: block !important;
            opacity: 1 !important;
        }
        .modal-backdrop.show {
            opacity: 0.5;
        }*/
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2 style="margin-top: 2rem; font-size: 2.5rem; font-weight: bold;">Welcome, <?php echo htmlspecialchars($userName); ?>!</h2>
                    <p class="text-muted" style="font-size: 1.3rem; font-weight: bold;">Here's your contribution overview</p>
                </div>
            </div>

            <!-- Balance and Quick Stats -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Your Total Balance</h5>
                            <div class="total-balance">
                                <span class="currency">CFA</span> <?php echo number_format($totalSum, 2); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Contributions</h5>
                            <div class="balance-amount">
                                <?php echo count($transactions); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Last Contribution</h5>
                            <div class="balance-amount">
                                <?php 
                                if (!empty($transactions)) {
                                    echo date('d M Y', strtotime($transactions[0]['Date']));
                                } else {
                                    echo 'No contributions yet';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Notification Center -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card notification-panel">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                            <h5 class="mb-0">
                                    <i class="fas fa-bell"></i> Notification Center
                                <?php if (count($notifications) > 0): ?>
                                        <span class="badge bg-danger notification-counter"><?php echo count($notifications); ?></span>
                                <?php endif; ?>
                            </h5>
                                <div class="notification-refresh ms-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshNotifications()">
                                        <i class="fas fa-sync-alt"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="notification-filters">
                                <button class="btn btn-sm btn-outline-light active" data-filter="all">
                                    <i class="fas fa-list"></i> All
                                </button>
                                <button class="btn btn-sm btn-outline-light" data-filter="unread">
                                    <i class="fas fa-envelope"></i> Unread
                                </button>
                                <button class="btn btn-sm btn-outline-light" data-filter="important">
                                    <i class="fas fa-star"></i> Important
                                </button>
                                <button class="btn btn-sm btn-outline-light" data-filter="archived">
                                    <i class="fas fa-archive"></i> Archived
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="notification-search">
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent">
                                                <i class="fas fa-search"></i>
                                            </span>
                                            <input type="text" class="form-control" id="notificationSearch" placeholder="Search notifications...">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Search by
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-search="content">Content</a></li>
                                                <li><a class="dropdown-item" href="#" data-search="date">Date</a></li>
                                                <li><a class="dropdown-item" href="#" data-search="type">Type</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="notification-bulk-actions text-end">
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="markAllAsRead()">
                                            <i class="fas fa-check-double"></i> Mark All as Read
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="clearAllNotifications()">
                                            <i class="fas fa-trash-alt"></i> Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Unified Notification Categories -->
                            <div class="notification-categories mb-4">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-main bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-info-circle text-main"></i>
                                            <span>System Updates</span>
                                            <span class="badge bg-main">3</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-main bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-clock text-main"></i>
                                            <span>Payment Reminders</span>
                                            <span class="badge bg-main">5</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-main bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-money-bill text-main"></i>
                                            <span>Contribution Updates</span>
                                            <span class="badge bg-main">2</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-main bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-headset text-main"></i>
                                            <span>Support Messages</span>
                                            <span class="badge bg-main">1</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-accent bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-exclamation-triangle text-accent"></i>
                                            <span>Security Alerts</span>
                                            <span class="badge bg-accent">2</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-main bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-gift text-main"></i>
                                            <span>Promotions</span>
                                            <span class="badge bg-main">4</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-main bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-chart-line text-main"></i>
                                            <span>Analytics Updates</span>
                                            <span class="badge bg-main">3</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="category-card bg-main bg-opacity-10 p-2 rounded">
                                            <i class="fas fa-calendar-alt text-main"></i>
                                            <span>Event Reminders</span>
                                            <span class="badge bg-main">2</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="notifications-container custom-scrollbar">
                            <?php if (!empty($notifications)): ?>
                                <?php foreach ($notifications as $notification): ?>
                                        <div class="notification-card mb-3" data-notification-id="<?php echo $notification['notification_id']; ?>">
                                            <div class="notification-header d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <span class="notification-priority me-2">
                                                        <i class="fas fa-circle text-warning"></i>
                                                    </span>
                                                    <span class="notification-type">
                                                        <i class="fas fa-info-circle text-primary"></i>
                                                        System Update
                                                    </span>
                                                </div>
                                                <div class="notification-meta">
                                                    <span class="notification-time me-3">
                                                        <i class="far fa-clock"></i>
                                                        <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                                    </span>
                                                    <div class="dropdown d-inline">
                                                        <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="#" onclick="markAsRead(<?php echo $notification['notification_id']; ?>)">
                                                                <i class="fas fa-check"></i> Mark as Read
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="#" onclick="archiveNotification(<?php echo $notification['notification_id']; ?>)">
                                                                <i class="fas fa-archive"></i> Archive
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteNotification(<?php echo $notification['notification_id']; ?>)">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="notification-body mt-2">
                                                <p class="mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <div class="notification-attachments">
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-paperclip"></i> attachment.pdf
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="notification-footer mt-2 pt-2 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                                    <div class="notification-tags">
                                                        <span class="badge bg-light text-dark me-1">Important</span>
                                                        <span class="badge bg-light text-dark">Update</span>
                                            </div>
                                                    <div class="notification-actions">
                                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="replyToNotification(<?php echo $notification['notification_id']; ?>)">
                                                            <i class="fas fa-reply"></i> Reply
                                                </button>
                                                        <button class="btn btn-sm btn-outline-info" onclick="showNotificationDetails(<?php echo $notification['notification_id']; ?>)">
                                                            <i class="fas fa-eye"></i> View Details
                                                        </button>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-bell-slash fa-3x mb-3"></i>
                                        <h5>No New Notifications</h5>
                                        <p class="text-muted">You're all caught up! Check back later for updates.</p>
                                    </div>
                            <?php endif; ?>
                        </div>

                            <!-- Notification Settings Panel -->
                            <div class="notification-settings mt-4">
                                <div class="settings-header d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0"><i class="fas fa-cog"></i> Notification Preferences</h6>
                                    <button class="btn btn-sm btn-outline-primary" onclick="saveNotificationSettings()">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                    </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="settings-section">
                                            <h6 class="text-muted mb-3">Notification Channels</h6>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                                <label class="form-check-label" for="emailNotifications">
                                                    <i class="fas fa-envelope text-primary"></i> Email Notifications
                                                </label>
                </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                                                <label class="form-check-label" for="smsNotifications">
                                                    <i class="fas fa-sms text-success"></i> SMS Notifications
                                                </label>
            </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="pushNotifications">
                                                <label class="form-check-label" for="pushNotifications">
                                                    <i class="fas fa-mobile-alt text-info"></i> Push Notifications
                                                </label>
        </div>
    </div>
                </div>
                                    <div class="col-md-6">
                                        <div class="settings-section">
                                            <h6 class="text-muted mb-3">Notification Types</h6>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="systemUpdates" checked>
                                                <label class="form-check-label" for="systemUpdates">
                                                    <i class="fas fa-info-circle text-primary"></i> System Updates
                                                </label>
                    </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="paymentReminders" checked>
                                                <label class="form-check-label" for="paymentReminders">
                                                    <i class="fas fa-clock text-warning"></i> Payment Reminders
                                                </label>
                </div>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="supportMessages" checked>
                                                <label class="form-check-label" for="supportMessages">
                                                    <i class="fas fa-headset text-success"></i> Support Messages
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                </div>
            </div>
        </div>
    </div>

            <style>
                .notification-panel {
                    border: none;
                    box-shadow: 0 0 20px rgba(0,0,0,0.1);
                }

                .notification-counter {
                    font-size: 0.7em;
                    padding: 0.3em 0.6em;
                    margin-left: 5px;
                    vertical-align: top;
                }

                .notification-filters .btn {
                    margin-left: 5px;
                    padding: 0.375rem 0.75rem;
                    border-color: rgba(255,255,255,0.2);
                }

                .notification-filters .btn.active {
                    background-color: white;
                    color: var(--primary-color);
                    border-color: white;
                }

                .notification-search .input-group {
                    border-radius: 20px;
                    overflow: hidden;
                }

                .notification-search .input-group-text {
                    border: none;
                }

                .notification-search .form-control {
                    border: none;
                    padding-left: 0;
                }

                .notification-search .form-control:focus {
                    box-shadow: none;
                }

                .category-card {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    cursor: pointer;
                    position: relative;
                    overflow: hidden;
                }

                .category-card::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: currentColor;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                .category-card:hover {
                    transform: translateY(-2px) scale(1.02);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                }

                .category-card:hover::before {
                    opacity: 0.03;
                }

                .category-card:active {
                    transform: translateY(1px) scale(0.98);
                }

                .category-card i {
                    font-size: 1.2rem;
                    margin-right: 8px;
                    transition: transform 0.3s ease;
                }

                .category-card:hover i {
                    transform: scale(1.1);
                }

                .category-card .badge {
                    transition: all 0.3s ease;
                }

                .category-card:hover .badge {
                    transform: scale(1.1);
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }

                .notifications-container {
                    max-height: 600px;
                    overflow-y: auto;
                    padding-right: 10px;
                }

                .custom-scrollbar::-webkit-scrollbar {
                    width: 6px;
                    height: 6px;
                }

                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 3px;
                }

                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #888;
                    border-radius: 3px;
                    transition: background 0.3s ease;
                }

                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #555;
                }

                .notification-card {
                    background: white;
                    border-radius: 10px;
                    padding: 15px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                    border-left: 4px solid var(--primary-color);
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    position: relative;
                    overflow: hidden;
                }

                .notification-card::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1));
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                .notification-card:hover::after {
                    opacity: 1;
                }

                .notification-card:hover {
                    transform: translateX(5px);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                }

                .notification-priority i {
                    font-size: 0.5rem;
                }

                .notification-type {
                    font-weight: 500;
                }

                .notification-time {
                    color: #6c757d;
                    font-size: 0.875rem;
                }

                .notification-meta .btn-link {
                    padding: 0;
                    color: #6c757d;
                }

                .notification-body {
                    color: #2c3e50;
                    font-size: 0.95rem;
                }

                .notification-attachments .badge {
                    padding: 0.5em 0.8em;
                }

                .notification-footer {
                    font-size: 0.9rem;
                }

                .settings-section {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 10px;
                }

                .form-check-input:checked {
                    background-color: var(--primary-color);
                    border-color: var(--primary-color);
                }

                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                .notification-card {
                    animation: fadeIn 0.3s ease-out;
                }

                /* New Color Variables */
                :root {
                    --main: #2563eb; /* Calm blue */
                    --main-light: #e7f0fa;
                    --main-dark: #174ea6;
                    --accent: #e74c3c; /* For urgent/important */
                    --gray: #f4f6fa;
                    --gray-dark: #6c757d;
                }

                /* New Color Classes */
                .bg-main { background-color: var(--main) !important; color: #fff !important; }
                .bg-main.bg-opacity-10 { background-color: rgba(37,99,235,0.08) !important; color: var(--main) !important; }
                .text-main { color: var(--main) !important; }
                .bg-accent { background-color: var(--accent) !important; color: #fff !important; }
                .bg-accent.bg-opacity-10 { background-color: rgba(231,76,60,0.08) !important; color: var(--accent) !important; }
                .text-accent { color: var(--accent) !important; }
                .category-card {
                    background: var(--gray);
                    border: 1px solid #e3e6ed;
                    color: var(--main);
                }
                .category-card .badge {
                    background: var(--main);
                    color: #fff;
                }
                .category-card.bg-accent .badge {
                    background: var(--accent);
                }
                .category-card.bg-accent {
                    color: var(--accent);
                }
                .notification-card {
                    border-left: 4px solid var(--main);
                    background: #fff;
                }
                .notification-card.urgent {
                    border-left: 4px solid var(--accent);
                }
                .notification-type, .notification-tags .badge {
                    color: var(--main-dark);
                }
                .notification-tags .badge.urgent {
                    background: var(--accent);
                    color: #fff;
                }
                .notification-footer {
                    background: var(--gray);
                }
                /* Remove old color classes */
                .bg-purple, .bg-teal, .bg-orange, .text-purple, .text-teal, .text-orange { background: none !important; color: inherit !important; }
            </style>

    <script>
                // Refresh notifications
                function refreshNotifications() {
                    // Implement refresh logic
                    console.log('Refreshing notifications...');
                    // Add loading spinner
                    // Make AJAX call to fetch new notifications
                    // Update UI with new notifications
                }

                // Mark all as read
                function markAllAsRead() {
                    if (confirm('Mark all notifications as read?')) {
                        console.log('Marking all notifications as read...');
                        // Implement mark all as read logic
                    }
                }

                // Clear all notifications
                function clearAllNotifications() {
                    if (confirm('Are you sure you want to clear all notifications? This cannot be undone.')) {
                        console.log('Clearing all notifications...');
                        // Implement clear all logic
                    }
                }

                // Archive notification
                function archiveNotification(notificationId) {
                    console.log('Archiving notification:', notificationId);
                    // Implement archive logic
                }

                // Reply to notification
                function replyToNotification(notificationId) {
                    console.log('Replying to notification:', notificationId);
                    // Implement reply logic
                }

                // Save notification settings
                function saveNotificationSettings() {
                    const settings = {
                        email: document.getElementById('emailNotifications').checked,
                        sms: document.getElementById('smsNotifications').checked,
                        push: document.getElementById('pushNotifications').checked,
                        systemUpdates: document.getElementById('systemUpdates').checked,
                        paymentReminders: document.getElementById('paymentReminders').checked,
                        supportMessages: document.getElementById('supportMessages').checked
                    };
                    console.log('Saving notification settings:', settings);
                    // Implement save settings logic
                }

                // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
                    // Initialize Bootstrap tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                    });

                    // Add click handlers for notification categories
                    document.querySelectorAll('.category-card').forEach(card => {
                        card.addEventListener('click', function() {
                            const category = this.querySelector('span').textContent;
                            filterNotificationsByCategory(category);
                        });
                    });
                });

                // Filter notifications by category
                function filterNotificationsByCategory(category) {
                    console.log('Filtering notifications by category:', category);
                    // Implement category filter logic
                }

                // Enhanced Search with Debounce and Highlighting
                let searchTimeout;
                document.getElementById('notificationSearch').addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    const searchTerm = e.target.value.toLowerCase();
                    searchTimeout = setTimeout(() => {
                        const notifications = document.querySelectorAll('.notification-card');
                        notifications.forEach(notification => {
                            const text = notification.textContent.toLowerCase();
                            const display = text.includes(searchTerm) ? 'block' : 'none';
                            notification.style.display = display;
                            if (display === 'block' && searchTerm) {
                                // Highlight matching text
                                const content = notification.querySelector('.notification-body');
                                const originalText = content.textContent;
                                content.innerHTML = highlightSearchTerm(originalText, searchTerm);
                            } else if (content) {
                                // Remove highlight if not searching
                                content.innerHTML = content.textContent;
                            }
                        });
                    }, 300);
                });

                // Delete notification with animation
                function deleteNotification(notificationId) {
                    if (confirm('Are you sure you want to delete this notification?')) {
                        const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
                        notification.style.transform = 'translateX(100%)';
                        notification.style.opacity = '0';
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    }
                }

                // AJAX for Real-time Updates
                function fetchNotifications() {
                    return new Promise((resolve, reject) => {
                        fetch('get_notifications.php')
                            .then(response => response.json())
                            .then(data => {
                                updateNotificationUI(data);
                                resolve(data);
                            })
                            .catch(error => {
                                console.error('Error fetching notifications:', error);
                                reject(error);
                            });
                    });
                }

                function updateNotificationUI(notifications) {
                    const container = document.querySelector('.notifications-container');
                    container.innerHTML = ''; // Clear existing notifications

                    notifications.forEach(notification => {
                        const card = createNotificationCard(notification);
                        container.appendChild(card);
                        // Trigger entrance animation
                        setTimeout(() => {
                            card.style.opacity = '1';
                            card.style.transform = 'translateX(0)';
                        }, 50);
                    });

                    updateCategoryCounters(notifications);
                }

                function createNotificationCard(notification) {
                    // Create notification card HTML structure
                    const card = document.createElement('div');
                    card.className = 'notification-card mb-3';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-20px)';
                    card.style.transition = 'all 0.3s ease';
                    // Add card content...
                    return card;
                }

                // Auto-refresh notifications
                let refreshInterval;
                function startAutoRefresh() {
                    refreshInterval = setInterval(fetchNotifications, 30000); // Every 30 seconds
                }

                function stopAutoRefresh() {
                    clearInterval(refreshInterval);
                }

                // Interactive Features
                document.addEventListener('DOMContentLoaded', function() {
                    // Initialize tooltips
                    const tooltips = document.querySelectorAll('[data-tooltip]');
                    tooltips.forEach(tooltip => {
                        new bootstrap.Tooltip(tooltip);
                    });

                    // Category click handlers with ripple effect
                    document.querySelectorAll('.category-card').forEach(card => {
                        card.addEventListener('click', function(e) {
                            // Create ripple effect
                            const ripple = document.createElement('div');
                            ripple.className = 'ripple';
                            this.appendChild(ripple);

                            const rect = this.getBoundingClientRect();
                            const x = e.clientX - rect.left;
                            const y = e.clientY - rect.top;

                            ripple.style.left = x + 'px';
                            ripple.style.top = y + 'px';

                            setTimeout(() => ripple.remove(), 1000);

                            // Filter notifications
                            const category = this.querySelector('span').textContent;
                            filterNotificationsByCategory(category);
                        });
                    });

                    // Start auto-refresh when page is visible
                    document.addEventListener('visibilitychange', function() {
                        if (document.hidden) {
                            stopAutoRefresh();
                        } else {
                            startAutoRefresh();
                        }
                    });

                    // Initialize auto-refresh
                    startAutoRefresh();
                });

                // Enhanced Search with Debounce and Highlighting
                function highlightSearchTerm(text, searchTerm) {
                    if (!searchTerm) return text;
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    return text.replace(regex, '<mark>$1</mark>');
                }

                // Interactive Animations
                function addInteractiveAnimations() {
                    // Add hover effects
                    document.querySelectorAll('.notification-card').forEach(card => {
                        card.addEventListener('mouseenter', function() {
                            this.style.transform = 'translateX(5px) scale(1.01)';
                        });

                        card.addEventListener('mouseleave', function() {
                            this.style.transform = 'translateX(0) scale(1)';
                        });
                    });

                    // Add click effects
                    document.querySelectorAll('.btn').forEach(button => {
                        button.addEventListener('click', function(e) {
                            const ripple = document.createElement('div');
                            ripple.className = 'ripple';
                            this.appendChild(ripple);

                            const rect = this.getBoundingClientRect();
                            ripple.style.left = e.clientX - rect.left + 'px';
                            ripple.style.top = e.clientY - rect.top + 'px';

                            setTimeout(() => ripple.remove(), 1000);
                        });
                    });
                }

                // Call this function after loading notifications
                addInteractiveAnimations();
    </script>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>