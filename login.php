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
<html>

<head>
  <title>Login</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <h2>Login</h2>
  <?php if (!empty($error))
    echo "<p style='color:red;'>$error</p>"; ?>
  <form method="post" action="login.php">
    <label>Email:</label><br>
    <input type="email" name="email" required><br>
    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    <input type="submit" value="Login">
  </form>
  <p>Don’t have an account? <a href="register.php">Register here</a></p>
</body>

</html>