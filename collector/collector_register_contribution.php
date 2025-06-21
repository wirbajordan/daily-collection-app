<?php
include 'header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
// Only show the register contribution form
include 'register_contribution_form.php';
include 'footer.php';