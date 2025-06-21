<?php
session_start();
$page_title = "Dashboard - Vision Finance";
include 'header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<main class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Collector'); ?></h1>
        <p>Manage your daily collections and activities</p>
    </div>

    <div class="dashboard-grid">
        <!-- Daily Summary Card -->
        <div class="dashboard-card summary-card">
            <h2>Today's Summary</h2>
            <div class="summary-stats">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3>Customers to Visit</h3>
                        <p class="stat-number">12</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-money-bill-wave"></i>
                    <div>
                        <h3>Expected Collection</h3>
                        <p class="stat-number">₣ 250,000</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h3>Completed Collections</h3>
                        <p class="stat-number">5</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Collection Form Card -->
        <div class="dashboard-card">
            <h2>Record Collection</h2>
            <form action="process_collection.php" method="POST" class="collection-form">
                <div class="form-group">
                    <label for="customer_id">Customer ID</label>
                    <input type="text" id="customer_id" name="customer_id" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount Collected</label>
                    <input type="number" id="amount" name="amount" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" class="submit-btn">Record Collection</button>
            </form>
        </div>

        <!-- Recent Collections Card -->
        <div class="dashboard-card">
            <h2>Recent Collections</h2>
            <div class="recent-collections">
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>10:30 AM</td>
                            <td>John Doe</td>
                            <td>₣ 25,000</td>
                            <td><span class="status completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td>11:15 AM</td>
                            <td>Jane Smith</td>
                            <td>₣ 30,000</td>
                            <td><span class="status completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td>12:00 PM</td>
                            <td>Mike Johnson</td>
                            <td>₣ 20,000</td>
                            <td><span class="status pending">Pending</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Upcoming Collections Card -->
        <div class="dashboard-card">
            <h2>Upcoming Collections</h2>
            <div class="upcoming-collections">
                <div class="collection-item">
                    <div class="collection-info">
                        <h3>Sarah Williams</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Central Business District</p>
                        <p><i class="fas fa-money-bill-wave"></i> Expected: ₣ 35,000</p>
                    </div>
                    <button class="action-btn">Start Collection</button>
                </div>
                <div class="collection-item">
                    <div class="collection-info">
                        <h3>Robert Brown</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Commercial Avenue</p>
                        <p><i class="fas fa-money-bill-wave"></i> Expected: ₣ 28,000</p>
                    </div>
                    <button class="action-btn">Start Collection</button>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 100px auto 40px;
    padding: 0 20px;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 40px;
}

.dashboard-header h1 {
    color: #2c3e50;
    font-size: 2.5em;
    margin-bottom: 10px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.dashboard-card {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.dashboard-card h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5em;
}

.summary-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-item i {
    font-size: 2em;
    color: #3498db;
}

.stat-item h3 {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 5px;
}

.stat-number {
    color: #2c3e50;
    font-size: 1.2em;
    font-weight: bold;
}

.collection-form .form-group {
    margin-bottom: 20px;
}

.collection-form label {
    display: block;
    color: #2c3e50;
    margin-bottom: 5px;
    font-weight: 500;
}

.collection-form input,
.collection-form select,
.collection-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1em;
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

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    color: #2c3e50;
    font-weight: 600;
}

.status {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.9em;
}

.status.completed {
    background: #d4edda;
    color: #155724;
}

.status.pending {
    background: #fff3cd;
    color: #856404;
}

.collection-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.collection-info h3 {
    color: #2c3e50;
    margin-bottom: 5px;
}

.collection-info p {
    color: #666;
    margin: 5px 0;
}

.collection-info i {
    color: #3498db;
    margin-right: 5px;
}

.action-btn {
    background: #27ae60;
    color: #fff;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.action-btn:hover {
    background: #219a52;
}

@media (max-width: 768px) {
    .dashboard-container {
        margin-top: 80px;
    }
    
    .collection-item {
        flex-direction: column;
        text-align: center;
    }
    
    .action-btn {
        margin-top: 10px;
    }
}
</style>

<?php include 'footer.php'; ?> 