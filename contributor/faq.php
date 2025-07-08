<?php
// Only allow logged-in contributors
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'contributor') {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Support Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; }
        .faq-header { text-align: center; margin: 40px 0 24px 0; }
        .faq-search { max-width: 500px; margin: 0 auto 32px auto; }
        .faq-category { margin-bottom: 32px; }
        .faq-question { cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <div class="faq-header">
        <h1 class="mb-2"><i class="fa-solid fa-circle-question text-primary"></i> Frequently Asked Questions</h1>
        <p class="text-muted">Find answers to common questions about your contributor account and support.</p>
    </div>
    <form class="faq-search mb-4">
        <input type="text" class="form-control" id="faqSearch" placeholder="Search for a question...">
    </form>
    <div id="faqList">
        <div class="faq-category">
            <h4>Account</h4>
            <div class="accordion" id="faqAccount">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q1"><button class="accordion-button collapsed faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#a1">How do I update my profile information?</button></h2>
                    <div id="a1" class="accordion-collapse collapse" data-bs-parent="#faqAccount"><div class="accordion-body">Go to your profile page and click "Edit Profile" to update your information.</div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q2"><button class="accordion-button collapsed faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#a2">How do I reset my password?</button></h2>
                    <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faqAccount"><div class="accordion-body">Click "Forgot Password" on the login page and follow the instructions sent to your email.</div></div>
                </div>
            </div>
        </div>
        <div class="faq-category">
            <h4>Support Tickets</h4>
            <div class="accordion" id="faqTickets">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q3"><button class="accordion-button collapsed faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#a3">How do I submit a support ticket?</button></h2>
                    <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faqTickets"><div class="accordion-body">Go to the Support Center and click "Submit Ticket". Fill out the form and submit your request.</div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q4"><button class="accordion-button collapsed faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#a4">How can I check the status of my ticket?</button></h2>
                    <div id="a4" class="accordion-collapse collapse" data-bs-parent="#faqTickets"><div class="accordion-body">Check the "My Tickets" tab in the Support Center to view the status of all your tickets.</div></div>
                </div>
            </div>
        </div>
        <div class="faq-category">
            <h4>General</h4>
            <div class="accordion" id="faqGeneral">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q5"><button class="accordion-button collapsed faq-question" type="button" data-bs-toggle="collapse" data-bs-target="#a5">Who do I contact for urgent issues?</button></h2>
                    <div id="a5" class="accordion-collapse collapse" data-bs-parent="#faqGeneral"><div class="accordion-body">Use the "Call Support" button in the Support Center for urgent matters.</div></div>
                </div>
            </div>
        </div>
    </div>
    <a href="call_customer_support.php" class="btn btn-secondary mt-4">Back to Support Center</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // FAQ search filter
    document.getElementById('faqSearch').addEventListener('input', function() {
        var search = this.value.toLowerCase();
        document.querySelectorAll('.accordion-item').forEach(function(item) {
            var question = item.querySelector('.accordion-button').textContent.toLowerCase();
            item.style.display = question.includes(search) ? '' : 'none';
        });
    });
</script>
</body>
</html> 