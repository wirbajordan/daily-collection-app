<?php
include 'header.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
// Database connection
$conn = new mysqli('localhost', 'root', '', 'dailycollect');
$transactions = [];
$total_amount = 0;
$query = $conn->prepare("SELECT transaction_id, username, Date, amount, transaction_type FROM transaction WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
    $total_amount += $row['amount'];
}
$conn->close();
?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm mt-4 mb-4">
            <div class="card-body text-center">
                <h4 class="fw-bold" style="color:#174ea6;">Registered Contributions Overview</h4>
                <p class="mb-0">Total Collected: <span class="fw-bold text-primary">CFA <?php echo number_format($total_amount,2); ?></span></p>
            </div>
        </div>
    </div>
</div>
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Transaction ID</th>
                                <th>Contributor Name</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Transaction Type</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo $transaction['transaction_id']; ?></td>
                                <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($transaction['Date'])); ?></td>
                                <td><?php echo number_format($transaction['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong><?php echo number_format($total_amount, 2); ?></strong></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>