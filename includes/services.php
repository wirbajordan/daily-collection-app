<?php
$page_title = "Our Services - Vision Finance";
include 'header.php';
?>

<main class="services-container">
    <div class="services-header">
        <h1>Our Services</h1>
        <p>Comprehensive Financial Solutions for Your Needs</p>
    </div>

    <div class="services-grid">
        <div class="service-card">
            <h2>Microfinance Services</h2>
            <p>Empowering individuals and small businesses with accessible financial solutions.</p>
            <ul class="service-features">
                <li>Personal Loans</li>
                <li>Business Loans</li>
                <li>Savings Accounts</li>
                <li>Investment Opportunities</li>
                <li>Financial Advisory Services</li>
            </ul>
        </div>

        <div class="service-card">
            <h2>Daily Collection Services</h2>
            <p>Convenient and secure money collection services for our valued customers.</p>
            <ul class="service-features">
                <li>Door-to-Door Collection</li>
                <li>Flexible Payment Schedules</li>
                <li>Real-time Transaction Updates</li>
                <li>Secure Payment Processing</li>
                <li>24/7 Customer Support</li>
            </ul>
        </div>

        <div class="service-card">
            <h2>Financial Education</h2>
            <p>Empowering our customers with financial knowledge and skills.</p>
            <ul class="service-features">
                <li>Financial Literacy Workshops</li>
                <li>Business Management Training</li>
                <li>Investment Planning</li>
                <li>Budget Management</li>
                <li>Risk Management</li>
            </ul>
        </div>
    </div>

    <div class="dailycollect-section">
        <h2>DailyCollect Application Features</h2>
        <div class="dailycollect-features">
            <div class="feature-item">
                <h3>Secure Transactions</h3>
                <p>End-to-end encryption and secure authentication for all financial transactions.</p>
            </div>

            <div class="feature-item">
                <h3>Real-time Tracking</h3>
                <p>Monitor collection progress and transaction history in real-time.</p>
            </div>

            <div class="feature-item">
                <h3>Mobile Accessibility</h3>
                <p>Access your account and make transactions from anywhere, anytime.</p>
            </div>

            <div class="feature-item">
                <h3>Automated Reports</h3>
                <p>Generate detailed reports and analytics for better financial management.</p>
            </div>

            <div class="feature-item">
                <h3>Customer Management</h3>
                <p>Efficiently manage customer information and payment schedules.</p>
            </div>

            <div class="feature-item">
                <h3>Payment Reminders</h3>
                <p>Automated notifications for upcoming payments and due dates.</p>
            </div>
        </div>
    </div>
</main>

<style>
.services-container {
    max-width: 1200px;
    margin: 100px auto 40px;
    padding: 0 20px;
}

.services-header {
    text-align: center;
    margin-bottom: 50px;
}

.services-header h1 {
    color: #2c3e50;
    font-size: 2.5em;
    margin-bottom: 15px;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 50px;
}

.service-card {
    background: #fff;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
}

.service-card h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5em;
}

.service-card p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
}

.service-features {
    list-style: none;
    padding: 0;
}

.service-features li {
    margin: 10px 0;
    padding-left: 25px;
    position: relative;
    color: #666;
}

.service-features li:before {
    content: "✓";
    color: #27ae60;
    position: absolute;
    left: 0;
    font-weight: bold;
}

.dailycollect-section {
    background: #f8f9fa;
    padding: 40px;
    border-radius: 10px;
    margin-top: 50px;
}

.dailycollect-section h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
}

.dailycollect-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.feature-item {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.feature-item h3 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 1.2em;
}

.feature-item p {
    color: #666;
    font-size: 0.9em;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .services-container {
        margin-top: 80px;
    }
    
    .dailycollect-section {
        padding: 20px;
    }
}
</style>

<?php include 'footer.php'; ?> 