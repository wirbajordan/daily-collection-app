<?php
$page_title = "Contact Us - Vision Finance";
include 'header.php';
?>

<main class="contact-container">
    <div class="contact-header">
        <h1>Contact Us</h1>
        <p>Get in touch with our team for any inquiries or support</p>
    </div>

    <div class="contact-content">
        <div class="contact-info">
            <h2>Contact Information</h2>
            <div class="info-item">
                <i class="fas fa-phone"></i>
                <div>
                    <h3>Phone</h3>
                    <p>+237 674 419 495</p>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <h3>Email</h3>
                    <p>info@visionfinance.com</p>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <div>
                    <h3>Working Hours</h3>
                    <p>Monday - Friday: 8:00 AM - 5:00 PM</p>
                    <p>Saturday: 9:00 AM - 1:00 PM</p>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <h3>Head Office</h3>
                    <p>Yaoundé, Cameroon</p>
                </div>
            </div>
        </div>

        <div class="contact-form">
            <h2>Send us a Message</h2>
            <?php if(isset($_GET['status'])): ?>
                <?php if($_GET['status'] == 'success'): ?>
                    <div class="alert alert-success">
                        Thank you for your message. We will get back to you soon!
                    </div>
                <?php elseif($_GET['status'] == 'error'): ?>
                    <div class="alert alert-error">
                        Sorry, there was an error sending your message. Please try again.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <form action="../process_contact.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select a subject</option>
                        <option value="general">General Inquiry</option>
                        <option value="support">Technical Support</option>
                        <option value="billing">Billing Question</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </div>

    <div class="locations-section">
        <h2>Our Branch Locations</h2>
        <div class="locations-grid">
            <div class="location-card">
                <h3>Yaoundé Branch</h3>
                <p><i class="fas fa-map-marker-alt"></i> Central Business District</p>
                <p><i class="fas fa-phone"></i> +237 XXX XXX XXX</p>
            </div>
            <div class="location-card">
                <h3>Douala Branch</h3>
                <p><i class="fas fa-map-marker-alt"></i> Commercial Avenue</p>
                <p><i class="fas fa-phone"></i> +237 XXX XXX XXX</p>
            </div>
            <div class="location-card">
                <h3>Baffoussam Branch</h3>
                <p><i class="fas fa-map-marker-alt"></i> Main Street</p>
                <p><i class="fas fa-phone"></i> +237 XXX XXX XXX</p>
            </div>
            <div class="location-card">
                <h3>Bamenda Branch</h3>
                <p><i class="fas fa-map-marker-alt"></i> City Center</p>
                <p><i class="fas fa-phone"></i> +237 XXX XXX XXX</p>
            </div>
        </div>
    </div>
</main>

<style>
.contact-container {
    max-width: 1200px;
    margin: 100px auto 40px;
    padding: 0 20px;
}

.contact-header {
    text-align: center;
    margin-bottom: 50px;
}

.contact-header h1 {
    color: #2c3e50;
    font-size: 2.5em;
    margin-bottom: 15px;
}

.contact-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 50px;
}

.contact-info {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.contact-info h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5em;
}

.info-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
}

.info-item i {
    color: #3498db;
    font-size: 1.2em;
    margin-right: 15px;
    margin-top: 5px;
}

.info-item div h3 {
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 1.1em;
}

.info-item div p {
    color: #666;
    line-height: 1.5;
}

.contact-form {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.contact-form h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5em;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: #2c3e50;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1em;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    border-color: #3498db;
    outline: none;
}

.form-group textarea {
    height: 150px;
    resize: vertical;
}

.submit-btn {
    background: #3498db;
    color: #fff;
    padding: 12px 25px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    font-weight: 500;
    transition: background-color 0.3s;
}

.submit-btn:hover {
    background: #2980b9;
}

.locations-section {
    margin-top: 50px;
}

.locations-section h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 30px;
    font-size: 1.8em;
}

.locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.location-card {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.location-card h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.3em;
}

.location-card p {
    color: #666;
    margin-bottom: 10px;
    line-height: 1.5;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .contact-content {
        grid-template-columns: 1fr;
    }

    .contact-container {
        margin-top: 80px;
    }
}
</style>

<?php include 'footer.php'; ?> 