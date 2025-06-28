<?php
session_start();

// Initialize step if not set
if (!isset($_SESSION['reset_step'])) {
    $_SESSION['reset_step'] = 1;
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_email'])) {
        $email = trim($_POST['email']);
        
        if (empty($email)) {
            $error = 'Please enter your email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Make POST request to send_otp.php
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'api/send_otp.php');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['email' => $email]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_step'] = 2;
                $message = 'OTP has been sent to your email address.';
            } else {
                $error = $result['message'] ?? 'Failed to send OTP.';
            }
        }
    } elseif (isset($_POST['submit_otp'])) {
        $otp = trim($_POST['otp']);
        
        if (empty($otp)) {
            $error = 'Please enter the OTP.';
        } elseif (!isset($_SESSION['otp']) || $otp !== strval($_SESSION['otp']) || time() > ($_SESSION['otp_expires'] ?? 0)) {
            $error = 'Invalid or expired OTP. Please try again.';
        } else {
            $_SESSION['reset_step'] = 3;
            $message = 'OTP verified successfully. Please set your new password.';
        }
    } elseif (isset($_POST['submit_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please fill in both password fields.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            // Update password in database
            require 'db.php';
            $email = $_SESSION['reset_email'] ?? '';
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET password=? WHERE email=?');
            $stmt->bind_param('ss', $hash, $email);
            $stmt->execute();
            $stmt->close();
            unset($_SESSION['reset_step'], $_SESSION['reset_email'], $_SESSION['otp'], $_SESSION['otp_expires']);
            $message = 'Password reset successfully! You can now login with your new password.';
            $_SESSION['reset_complete'] = true;
        }
    }
}

// Handle reset request
if (isset($_GET['reset'])) {
    unset($_SESSION['reset_step'], $_SESSION['reset_email'], $_SESSION['otp'], $_SESSION['otp_expires'], $_SESSION['reset_complete']);
    header('Location: forgotPassword.php');
    exit;
}

$current_step = $_SESSION['reset_step'] ?? 1;
$reset_complete = isset($_SESSION['reset_complete']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Bank - Forgot Password</title>
    <link rel="stylesheet" href="assets/forgot-password.css">
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

        <!-- Main Content -->
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
                        <h2 class="reset-title">Reset Your Password</h2>
                        <p class="reset-subtitle">Follow the steps below to reset your account password</p>
                    </div>

                    <!-- Progress Steps -->
                    <div class="progress-steps">
                        <div class="step <?= $current_step >= 1 ? 'active' : '' ?> <?= $current_step > 1 ? 'completed' : '' ?>">
                            <div class="step-number">1</div>
                            <div class="step-label">Email</div>
                        </div>
                        <div class="step-line <?= $current_step > 1 ? 'completed' : '' ?>"></div>
                        <div class="step <?= $current_step >= 2 ? 'active' : '' ?> <?= $current_step > 2 ? 'completed' : '' ?>">
                            <div class="step-number">2</div>
                            <div class="step-label">Verify OTP</div>
                        </div>
                        <div class="step-line <?= $current_step > 2 ? 'completed' : '' ?>"></div>
                        <div class="step <?= $current_step >= 3 ? 'active' : '' ?> <?= $reset_complete ? 'completed' : '' ?>">
                            <div class="step-number">3</div>
                            <div class="step-label">New Password</div>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-success">
                            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error">
                            <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Reset Complete -->
                    <?php if ($reset_complete): ?>
                        <div class="reset-complete">
                            <div class="success-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h3>Password Reset Complete!</h3>
                            <p>Your password has been successfully reset. You can now login with your new password.</p>
                            <div class="complete-actions">
                                <a href="index.php" class="btn btn-primary">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4m-5-4l5-5-5-5m5 5H3"/>
                                    </svg>
                                    Go to Login
                                </a>
                                <a href="?reset=1" class="btn btn-secondary">Reset Another Account</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Step 1: Email -->
                        <?php if ($current_step === 1): ?>
                            <div class="reset-step active" id="step1">
                                <div class="step-content">
                                    <h3 class="step-title">Enter Your Email Address</h3>
                                    <p class="step-description">We'll send a verification code to your registered email address.</p>
                                    
                                    <form method="POST" class="reset-form">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" id="email" name="email" class="form-input" 
                                                   placeholder="Enter your email address" required 
                                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                        </div>
                                        
                                        <button type="submit" name="submit_email" class="btn btn-primary btn-full">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                                <polyline points="22,6 12,13 2,6"/>
                                            </svg>
                                            Send Verification Code
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Step 2: OTP Verification -->
                        <?php if ($current_step === 2): ?>
                            <div class="reset-step active" id="step2">
                                <div class="step-content">
                                    <h3 class="step-title">Enter Verification Code</h3>
                                    <p class="step-description">
                                        We've sent a 6-digit verification code to 
                                        <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>
                                    </p>
                                    
                                    <form method="POST" class="reset-form">
                                        <div class="form-group">
                                            <label for="otp" class="form-label">Verification Code</label>
                                            <input type="text" id="otp" name="otp" class="form-input otp-input" 
                                                   placeholder="Enter 6-digit code" maxlength="6" required
                                                   value="<?= htmlspecialchars($_POST['otp'] ?? '') ?>">
                                            <div class="otp-hint">
                                                <small>Didn't receive the code? <a href="#" onclick="resendOTP()">Resend OTP</a></small>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" name="submit_otp" class="btn btn-primary btn-full">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Verify Code
                                        </button>
                                        
                                        <button type="button" onclick="goToStep(1)" class="btn btn-secondary btn-full">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                                            </svg>
                                            Back to Email
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Step 3: New Password -->
                        <?php if ($current_step === 3): ?>
                            <div class="reset-step active" id="step3">
                                <div class="step-content">
                                    <h3 class="step-title">Set New Password</h3>
                                    <p class="step-description">Choose a strong password for your account security.</p>
                                    
                                    <form method="POST" class="reset-form">
                                        <div class="form-group">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" id="new_password" name="new_password" class="form-input" 
                                                   placeholder="Enter new password" required minlength="6">
                                            <div class="password-requirements">
                                                <small>Password must be at least 6 characters long</small>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                                                   placeholder="Confirm new password" required minlength="6">
                                        </div>
                                        
                                        <button type="submit" name="submit_password" class="btn btn-primary btn-full">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 01 2-2h11l5 5v11a2 2 0 01-2 2z"/>
                                                <polyline points="17,21 17,13 7,13 7,21"/>
                                                <polyline points="7,3 7,8 15,8"/>
                                            </svg>
                                            Reset Password
                                        </button>
                                        
                                        <button type="button" onclick="goToStep(2)" class="btn btn-secondary btn-full">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                                            </svg>
                                            Back to OTP
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
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

    <script>
        function goToStep(step) {
            // In a real application, you might want to make an AJAX call
            // For now, we'll just redirect with a parameter
            window.location.href = `forgotPassword.php?step=${step}`;
        }

        function resendOTP() {
            alert('OTP resent successfully! (Demo: Use 123456)');
        }

        // Auto-format OTP input
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.addEventListener('input', function(e) {
                    // Remove any non-numeric characters
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }

            // Password confirmation validation
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword && confirmPassword) {
                function validatePasswords() {
                    if (confirmPassword.value && newPassword.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                }

                newPassword.addEventListener('input', validatePasswords);
                confirmPassword.addEventListener('input', validatePasswords);
            }
        });
    </script>
</body>
</html>
