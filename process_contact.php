<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    
    // Email recipient
    $to = "info@visionfinance.com";
    
    // Email subject
    $email_subject = "New Contact Form Submission: $subject";
    
    // Email body
    $email_body = "You have received a new message from your website contact form.\n\n" .
                  "Here are the details:\n\n" .
                  "Name: $name\n" .
                  "Email: $email\n" .
                  "Phone: $phone\n" .
                  "Subject: $subject\n\n" .
                  "Message:\n$message";
    
    // Email headers
    $headers = "From: $email\n";
    $headers .= "Reply-To: $email\n";
    
    // Send email
    if(mail($to, $email_subject, $email_body, $headers)) {
        $_SESSION['contact_success'] = true;
        header("Location: contact.php?status=success");
    } else {
        $_SESSION['contact_error'] = true;
        header("Location: contact.php?status=error");
    }
} else {
    // If not a POST request, redirect to contact page
    header("Location: contact.php");
}
exit();
?> 