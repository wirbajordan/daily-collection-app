<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p><i class="fas fa-phone"></i> +237 674 419 495</p>
            <p><i class="fas fa-envelope"></i> info@visionfinance.com</p>
            <p><i class="fas fa-map-marker-alt"></i> Yaoundé, Cameroon</p>
        </div>

        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="about.php">About Us</a></li>
                <li><a href="services.php">Our Services</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-links">
                <a href="#" target="_blank"><i class="fab fa-facebook"></i></a>
                <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="#" target="_blank"><i class="fab fa-linkedin"></i></a>
                <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Vision Finance. All rights reserved.</p>
    </div>
</footer>

<style>
.main-footer {
    background-color: #2c3e50;
    color: #fff;
    padding: 40px 0 0 0;
    margin-top: 60px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.footer-section h3 {
    color: #fff;
    margin-bottom: 20px;
    font-size: 1.2em;
}

.footer-section p {
    margin: 10px 0;
    color: #ecf0f1;
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section ul li {
    margin: 10px 0;
}

.footer-section a {
    color: #ecf0f1;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-section a:hover {
    color: #3498db;
}

.social-links {
    display: flex;
    gap: 15px;
}

.social-links a {
    color: #fff;
    font-size: 1.5em;
    transition: color 0.3s;
}

.social-links a:hover {
    color: #3498db;
}

.footer-bottom {
    text-align: center;
    padding: 20px;
    margin-top: 40px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.footer-bottom p {
    margin: 0;
    color: #ecf0f1;
}

@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .social-links {
        justify-content: center;
    }
}
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 