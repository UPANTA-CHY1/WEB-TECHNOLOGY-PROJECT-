<?php
session_start();
require 'db.php';

// Only allow admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$actionMsg = '';

// Handle accept/reject actions
if (isset($_POST['action'], $_POST['userid'], $_POST['cardno'])) {
    $userid = intval($_POST['userid']);
    $cardno = (float) $_POST['cardno']; // use float for BIGINT cardno

    if ($_POST['action'] === 'accept') {
        // Extend expiry by 3 years
        $new_expiry = date('Y-m-d', strtotime('+3 years'));
        $update = $conn->prepare("UPDATE cards SET valid_till = ? WHERE userid = ? AND cardno = ?");
        $update->bind_param('sid', $new_expiry, $userid, $cardno);
        $update->execute();
        $update->close();

        // Delete all requests by this user
        $del = $conn->prepare("DELETE FROM cardrequests WHERE userid = ?");
        $del->bind_param('i', $userid);
        $del->execute();
        $del->close();
        $actionMsg = "Card accepted and requests cleared.";
    } elseif ($_POST['action'] === 'reject') {
        // Only delete specific request
        $del = $conn->prepare("DELETE FROM cardrequests WHERE userid = ? AND cardno = ?");
        $del->bind_param('id', $userid, $cardno); // fixed from 'is' to 'id'
        $del->execute();
        $del->close();
        $actionMsg = "Card request rejected.";
    }

    // Refresh to reflect changes
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all pending requests
$sql = "SELECT cardrequests.userid, cardrequests.cardno, users.username, users.email, users.balance, users.status 
        FROM cardrequests 
        JOIN users ON cardrequests.userid = users.id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Card Requests</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h2>Pending Card Requests</h2>

    <?php if (!empty($actionMsg)): ?>
        <p style="color: green;"><?= htmlspecialchars($actionMsg) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="8">
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Balance</th>
            <th>Status</th>
            <th>Card Number</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['userid']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['balance']) ?></td>
            <td><?= $row['status'] ? 'Active' : 'Inactive' ?></td>
            <td><?= htmlspecialchars($row['cardno']) ?></td>
            <td>
                <form method="post" style="display:inline-block;">
                    <input type="hidden" name="userid" value="<?= $row['userid'] ?>">
                    <input type="hidden" name="cardno" value="<?= $row['cardno'] ?>">
                    <button type="submit" name="action" value="accept">Accept</button>
                </form>
                <form method="post" style="display:inline-block;">
                    <input type="hidden" name="userid" value="<?= $row['userid'] ?>">
                    <input type="hidden" name="cardno" value="<?= $row['cardno'] ?>">
                    <button type="submit" name="action" value="reject">Reject</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="adminDashboard.php">Back to Dashboard</a>
</body>
</html>
