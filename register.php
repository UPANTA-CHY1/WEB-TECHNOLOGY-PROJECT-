<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $securityanswer = trim($_POST["securityanswer"]);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, securityanswer) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $securityanswer);
    
    if ($stmt->execute()) {
        // Get the new user's ID
        $userid = $stmt->insert_id;

        // Generate a unique 16-digit card number
        do {
            $cardno = '';
            for ($i = 0; $i < 16; $i++) {
                $cardno .= mt_rand($i === 0 ? 1 : 0, 9); // First digit not zero
            }
            $check = $conn->prepare("SELECT id FROM cards WHERE cardno = ?");
            $check->bind_param("s", $cardno);
            $check->execute();
            $check->store_result();
            $isUnique = $check->num_rows === 0;
            $check->close();
        } while (!$isUnique);

        // Set valid_till to 3 years from today
        $valid_till = date('Y-m-d', strtotime('+3 years'));
        $insertCard = $conn->prepare("INSERT INTO cards (userid, cardno, valid_till) VALUES (?, ?, ?)");
        $insertCard->bind_param("iss", $userid, $cardno, $valid_till);
        $insertCard->execute();
        $insertCard->close();

        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['id'] = $userid;
        header("Location: index.php");
        exit;
    } else {
        $error = "User already exists or error occurred.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Bank - Create Account</title>
    <link rel="stylesheet" href="assets/register.css">
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
                <h2 class="login-title">Create Your Account</h2>
                <p class="login-subtitle">Join Lucky Bank and start your secure banking journey</p>

                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form class="login-form" method="post" action="register.php">
                    <div class="form-group">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" id="username" name="username" class="form-input" required placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" required placeholder="Enter your email address">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-input" required placeholder="Create a secure password">
                        <div class="password-requirements">
                            <small>Password should be at least 8 characters long</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="securityanswer" class="form-label">What is your great grandmother's name?</label>
                        <input type="text" id="securityanswer" name="securityanswer" class="form-input" required placeholder="Enter the name">
                    </div>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" class="checkbox" required>
                            I agree to the <a href="#" class="terms-link">Terms & Conditions</a>
                        </label>
                    </div>

                    <button type="submit" class="login-button">Create Account</button>
                </form>

                <div class="signup-link">
                    <p>Already have an account? <a href="login.php" class="signup-text">Sign in here</a></p>
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
