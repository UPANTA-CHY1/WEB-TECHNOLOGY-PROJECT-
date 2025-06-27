<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    
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
    } else {
        $error = "User already exists or error occurred.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <h2>Register</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="post" action="register.php">
    <label>Username:</label><br>
    <input type="text" name="username" required><br>
    <label>Email:</label><br>
    <input type="email" name="email" required><br>
    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    <input type="submit" value="Register">
  </form>
  <p>Already registered? <a href="login.php">Login here</a></p>
</body>
</html>
