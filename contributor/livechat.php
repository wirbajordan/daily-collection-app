<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'contributor') {
    header('Location: ../login.php');
    exit();
}
// Simple chat storage (for demo: use a text file)
$chatFile = __DIR__ . '/chat_demo.txt';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = strip_tags($_POST['message']);
    $user = $_SESSION['user_id'];
    $line = date('H:i') . " | User $user: $msg\n";
    file_put_contents($chatFile, $line, FILE_APPEND);
}
$messages = file_exists($chatFile) ? file($chatFile) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat - Support Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; }
        .chat-container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px; }
        .chat-messages { height: 300px; overflow-y: auto; background: #f8f9fa; border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: 0.98rem; }
        .chat-input { display: flex; gap: 8px; }
        .chat-input input { flex: 1; }
    </style>
</head>
<body>
<div class="chat-container">
    <h3 class="mb-3"><i class="fa-solid fa-comments text-primary"></i> Live Chat</h3>
    <div class="chat-messages" id="chatMessages">
        <?php foreach ($messages as $msg): ?>
            <div><?php echo htmlspecialchars($msg); ?></div>
        <?php endforeach; ?>
    </div>
    <form method="POST" class="chat-input" autocomplete="off">
        <input type="text" name="message" class="form-control" placeholder="Type your message..." required>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
    <a href="call_customer_support.php" class="btn btn-secondary mt-3">Back to Support Center</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-scroll chat to bottom
    var chatDiv = document.getElementById('chatMessages');
    chatDiv.scrollTop = chatDiv.scrollHeight;
</script>
</body>
</html> 