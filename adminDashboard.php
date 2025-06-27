<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
if (isset($_SESSION['admin']) && $_SESSION['admin'] === false) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>admin dashboard</h1>
    <a href="logout.php" style="float:right;">Logout</a>
    
</body>
</html>