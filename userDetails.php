<?php
require 'db.php';

// Get user id from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid user ID.');
}
$user_id = (int)$_GET['id'];

// Handle update
$update_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $status = isset($_POST['status']) ? 1 : 0;
    $stmt = $conn->prepare("UPDATE users SET username=?, email=?, status=? WHERE id=?");
    $stmt->bind_param('ssii', $username, $email, $status, $user_id);
    $stmt->execute();
    if ($stmt->affected_rows >= 0) {
        $update_success = true;
    }
    $stmt->close();
}

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, balance, role, status FROM users WHERE id=?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $balance, $role, $status);
if (!$stmt->fetch()) {
    die('User not found.');
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Details</title>
    <style>
        .user-details { max-width: 400px; margin: 30px auto; border: 1px solid #ccc; border-radius: 8px; padding: 24px; background: #fafafa; }
        .user-details h2 { margin-top: 0; }
        .user-details p { margin: 8px 0; }
        .back-btn { display: inline-block; margin-top: 20px; padding: 8px 16px; background: #007bff; color: #fff; border-radius: 4px; text-decoration: none; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="user-details">
        <h2>User Details</h2>
        <?php if ($update_success): ?>
            <p class="success">User details updated successfully.</p>
        <?php endif; ?>
        <form method="POST">
            <p><strong>Username:</strong> <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" required></p>
            <p><strong>Email:</strong> <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required></p>
            <p><strong>Balance:</strong> <?= htmlspecialchars($balance) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($role) ?></p>
            <p><strong>Status:</strong> <input type="checkbox" name="status" value="1" <?= $status ? 'checked' : '' ?>> Active</p>
            <button type="submit">Update Details</button>
        </form>
        <a href="adminAllUsers.php" class="back-btn">Back to All Users</a>
    </div>
</body>
</html>