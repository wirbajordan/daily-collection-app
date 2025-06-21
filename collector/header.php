<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vision Finance Collector</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="collector_dashboard_css/style.css">
    <style>
        body { background: #f8f9fa; }
        .navbar-brand img { height: 48px; margin-right: 12px; }
        .nav-link.active, .nav-link:focus, .nav-link:hover { color: #174ea6 !important; font-weight: 600; }
        .navbar { background: #fff; box-shadow: 0 2px 8px rgba(37,99,235,0.08); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="collector_dashboard.php">
      <img src="../images/vision-finance-logo.png" alt="Vision Finance Logo">
      <span class="fw-bold" style="color:#174ea6; font-size:1.5rem;">Vision Finance</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="collector_dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="showRegisterTab(); return false;">Register Contribution</a></li>
        <li class="nav-item"><a class="nav-link" href="collector_view_registered_contribution.php">View Contributions</a></li>
        <li class="nav-item"><a class="nav-link" href="collector_assigned_contributors.php">Assigned Contributors</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="showAnalyticsTab(); return false;">Analytics</a></li>
        <li class="nav-item"><a class="nav-link" href="support.php">Support</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="../home.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4"> 