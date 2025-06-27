<?php
require 'db.php';
$users = [];
$sql = "SELECT id, username, email, balance, role, status FROM users";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin All Users</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        tr:hover { background: #f0f0f0; cursor: pointer; }
        a { color: inherit; text-decoration: none; display: block; width: 100%; height: 100%; }
    </style>
</head>
<body>
    <h2>All Users</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><a href="userDetails.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['id']) ?></a></td>
                    <td><a href="userDetails.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></a></td>
                    <td><a href="userDetails.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['email']) ?></a></td>
                    <td><a href="userDetails.php?id=<?= $user['id'] ?>"><?= htmlspecialchars($user['balance']) ?></a></td>
                    <td><a href="userDetails.php?id=<?= $user['id'] ?>"><?= $user['status'] ? 'Active' : 'Inactive' ?></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="adminDashboard.php">go back</a>
</body>
</html>