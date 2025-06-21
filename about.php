<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Vision Finance DailyCollect</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .about-section {
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .about-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 15px;
        }
        
        .about-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .about-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .about-card h2 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .about-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .locations {
            text-align: center;
            margin-top: 40px;
        }
        
        .locations h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .location-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        
        .location-item {
            background: #f8f9fa;
            padding: 15px 25px;
            border-radius: 25px;
            color: #2c3e50;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="about-section">
        <div class="about-header">
            <h1>About Vision Finance</h1>
            <p>Empowering Communities Through Financial Inclusion</p>
        </div>

        <div class="about-content">
            <div class="about-card">
                <h2>Our Mission</h2>
                <p>At Vision Finance, we are committed to providing accessible financial services to communities across Cameroon. Through our DailyCollect application, we ensure secure and efficient money collection services, bringing financial solutions closer to our valued customers.</p>
            </div>

            <div class="about-card">
                <h2>Our Services</h2>
                <p>We specialize in microfinance services with a focus on daily collection operations. Our dedicated collectors work tirelessly to provide convenient and secure financial services to our customers, ensuring their money is handled with the utmost care and professionalism.</p>
            </div>

            <div class="about-card">
                <h2>Our Commitment</h2>
                <p>We are dedicated to maintaining the highest standards of security and efficiency in our operations. Our DailyCollect application ensures that every transaction is recorded and processed with precision, providing peace of mind to both our collectors and customers.</p>
            </div>
        </div>

        <div class="locations">
            <h2>Our Presence</h2>
            <p>We are proud to serve communities across Cameroon with branches in major cities:</p>
            <div class="location-list">
                <span class="location-item">Yaoundé</span>
                <span class="location-item">Douala</span>
                <span class="location-item">Baffoussam</span>
                <span class="location-item">Bamenda</span>
                <span class="location-item">And More</span>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
</body>
</html>
