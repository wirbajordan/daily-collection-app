<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="main-header">
    <div class="header-container">
        <div class="logo-section">
            <a href="home.php">
                <img src="images/vision-finance-logo.png" alt="Vision Finance Logo" class="logo">
                <span class="company-name">Vision Finance</span>
            </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="home.php" class="<?php echo ($current_page == 'home.php') ? 'active' : ''; ?>">Home</a></li>
                <li><a href="about.php" class="<?php echo ($current_page == 'about.php') ? 'active' : ''; ?>">About Us</a></li>
                <li><a href="services.php" class="<?php echo ($current_page == 'services.php') ? 'active' : ''; ?>">Services</a></li>
                <li><a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">Contact</a></li>
                <?php if ($current_page != 'about.php'): ?>
                <li><a href="/practicals/contributor/call_customer_support.php" class="<?php echo ($current_page == 'call_customer_support.php') ? 'active' : ''; ?>">Call Customer Support</a></li>
                <?php endif; ?>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
                    <li><a href="../practicals/home.php">logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="<?php echo ($current_page == 'login.php') ? 'active' : ''; ?>">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<style>
.main-header {
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-section a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #2c3e50;
}

.logo {
    height: 40px;
    margin-right: 10px;
}

.company-name {
    font-size: 1.5em;
    font-weight: bold;
}

.main-nav ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 20px;
}

.main-nav a {
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.main-nav a:hover {
    background-color: #f8f9fa;
    color: #3498db;
}

.main-nav a.active {
    background-color: #3498db;
    color: #fff;
}

.main-nav a.active:hover {
    background-color: #2980b9;
}

@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        text-align: center;
    }
    
    .main-nav ul {
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }
}
</style> 