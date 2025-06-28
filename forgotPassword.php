<?php
require 'db.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $securityanswer = trim($_POST['securityanswer']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email and security answer match
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND securityanswer = ?");
        $stmt->bind_param("ss", $email, $securityanswer);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            // Email + answer match — update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->bind_result($userid);
            $stmt->fetch();
            $stmt->close();

            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $userid);

            if ($update->execute()) {
                $success = "Your password has been successfully reset.";
            } else {
                $error = "Something went wrong while updating the password.";
            }

            $update->close();
        } else {
            $error = "Invalid email or security answer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lucky Bank - Forgot Password</title>
    <link rel="stylesheet" href="assets/forgot-password.css">
</head>
<body>
    <div class="page-container">
        <header class="page-header">
            <div class="header-content">
                <div class="bank-logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <h1 class="bank-name">Lucky Bank</h1>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="back-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Back to Login
                    </a>
                </div>
            </div>
        </header>

        <main class="page-main">
            <div class="page-content">
                <div class="reset-container">
                    <div class="reset-header">
                        <div class="reset-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <circle cx="12" cy="16" r="1"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <h2 class="reset-title">Forgot Password</h2>
                        <p class="reset-subtitle">Reset your account using your email and security answer</p>
                    </div>

                    <!-- Display messages -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="reset-form">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input" required 
                                   placeholder="Enter your registered email">
                        </div>

                        <div class="form-group">
                            <label for="securityanswer" class="form-label">What is your great grandmother's name?</label>
                            <input type="text" id="securityanswer" name="securityanswer" class="form-input" required 
                                   placeholder="Enter the answer">
                        </div>

                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-input" required 
                                 placeholder="Create new password">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" required 
                                 placeholder="Re-type new password">
                        </div>

                        <button type="submit" class="btn btn-primary btn-full">Reset Password</button>
                    </form>
                </div>
            </div>
        </main>

        <footer class="page-footer">
            <p>&copy; 2025 Lucky Bank. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <span class="separator">|</span>
                <a href="#">Terms of Service</a>
                <span class="separator">|</span>
                <a href="#">Support</a>
            </div>
        </footer>
    </div>
</body>
</html>
