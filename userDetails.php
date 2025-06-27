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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Bank - User Details</title>
    <link rel="stylesheet" href="assets/user-details.css">
</head>
<body>
    <div class="page-container">
        <!-- Header Section -->
        <header class="page-header">
            <div class="header-content">
                <div class="bank-logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <h1 class="bank-name">Lucky Bank</h1>
                </div>
                <div class="user-info">
                    <span class="admin-badge">Administrator</span>
                    <a href="adminAllUsers.php" class="back-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        All Users
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="page-main">
            <div class="page-content">
                <div class="page-title-section">
                    <h2 class="page-title">User Details</h2>
                    <p class="page-subtitle">View and manage user account information</p>
                </div>

                <?php if ($update_success): ?>
                    <div class="alert alert-success">
                        <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        User details updated successfully.
                    </div>
                <?php endif; ?>

                <div class="user-details-container">
                    <div class="user-profile-section">
                        <div class="user-avatar-large">
                            <?= strtoupper(substr($username, 0, 1)) ?>
                        </div>
                        <div class="user-basic-info">
                            <h3 class="user-name"><?= htmlspecialchars($username) ?></h3>
                            <p class="user-email"><?= htmlspecialchars($email) ?></p>
                            <div class="user-badges">
                                <span class="role-badge <?= $role === 'admin' ? 'admin-role' : 'user-role' ?>">
                                    <?= ucfirst(htmlspecialchars($role)) ?>
                                </span>
                                <span class="status-badge <?= $status ? 'active' : 'inactive' ?>">
                                    <?= $status ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="user-details-form">
                        <form method="POST" class="details-form">
                            <div class="form-section">
                                <h4 class="section-title">Account Information</h4>
                                
                                <div class="form-group">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-input" 
                                           value="<?= htmlspecialchars($username) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-input" 
                                           value="<?= htmlspecialchars($email) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Account Balance</label>
                                    <div class="balance-display">
                                        <span class="balance-amount">৳ <?= number_format($balance, 2) ?></span>
                                        <span class="balance-note">Read-only</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">User Role</label>
                                    <div class="role-display">
                                        <span class="role-badge <?= $role === 'admin' ? 'admin-role' : 'user-role' ?>">
                                            <?= ucfirst(htmlspecialchars($role)) ?>
                                        </span>
                                        <span class="role-note">Contact system administrator to change role</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="status" name="status" value="1" 
                                               <?= $status ? 'checked' : '' ?> class="form-checkbox">
                                        <label for="status" class="checkbox-label">
                                            <span class="checkbox-text">Active Account</span>
                                            <span class="checkbox-description">User can access their account and perform transactions</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                                        <polyline points="17,21 17,13 7,13 7,21"/>
                                        <polyline points="7,3 7,8 15,8"/>
                                    </svg>
                                    Update Details
                                </button>
                                <a href="adminAllUsers.php" class="btn btn-secondary">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                                    </svg>
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="page-footer">
            <p>&copy; 2025 Lucky Bank Admin Panel. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Admin Guide</a>
                <span class="separator">|</span>
                <a href="#">System Logs</a>
                <span class="separator">|</span>
                <a href="#">Support</a>
            </div>
        </footer>
    </div>
</body>
</html>
