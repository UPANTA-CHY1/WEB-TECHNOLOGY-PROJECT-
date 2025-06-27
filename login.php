<?php
session_start();

// this is a change 

if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT username, password, role, status, id FROM users WHERE email=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $hashed_password, $role, $status, $id);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
        if($status ==0){
            header("Location: inactiveAccount.php");
            $stmt->close();
            $conn->close();
            exit;
        }
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['id'] = $id;
        if ($role === 'admin') {
            $_SESSION['admin'] = true;
            header("Location: adminDashboard.php");
            exit;
        }
        elseif ($role === "user") {
          $_SESSION['admin'] = false;
          header("Location: index.php");
          exit;
        }
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Bank - Secure Login</title>
    <link rel="stylesheet" href="assets/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="bank-header">
                <div class="bank-logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <h1 class="bank-name">Lucky Bank</h1>
                </div>
                <p class="bank-tagline">Secure Online Banking</p>
            </div>

            <div class="login-form-container">
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Please sign in to your account</p>

                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form class="login-form" method="post" action="login.php">
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" required placeholder="Enter your email">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required placeholder="Enter your password">
                    </div>

                    <div class="form-options">
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="login-button">Sign In</button>
                </form>

                <div class="signup-link">
                    <p>Don't have an account? <a href="register.php" class="signup-text">Register here</a></p>
                </div>
            </div>
        </div>

        <footer class="login-footer">
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