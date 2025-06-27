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
    $cardno = (float) $_POST['cardno'];

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
        $del->bind_param('id', $userid, $cardno);
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
$requests = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Bank - Card Requests</title>
    <link rel="stylesheet" href="assets/admin-card-requests.css">
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
                    <a href="adminDashboard.php" class="back-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="page-main">
            <div class="page-content">
                <div class="page-title-section">
                    <h2 class="page-title">Card Requests</h2>
                    <p class="page-subtitle">Review and manage pending card applications</p>
                </div>

                <?php if (!empty($actionMsg)): ?>
                    <div class="alert alert-success">
                        <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?= htmlspecialchars($actionMsg) ?>
                    </div>
                <?php endif; ?>

                <div class="requests-container">

                    <?php if (empty($requests)): ?>
                        <div class="empty-state">
                            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                            <h3>No Pending Requests</h3>
                            <p>There are no card requests waiting for approval at this time.</p>
                        </div>
                    <?php else: ?>
                        <div class="requests-grid">
                            <?php foreach ($requests as $request): ?>
                                <div class="request-card">
                                    <div class="request-header">
                                        <div class="user-info-section">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($request['username'], 0, 1)) ?>
                                            </div>
                                            <div class="user-details">
                                                <h3 class="username"><?= htmlspecialchars($request['username']) ?></h3>
                                                <p class="user-email"><?= htmlspecialchars($request['email']) ?></p>
                                            </div>
                                        </div>
                                        <div class="status-badge <?= $request['status'] ? 'active' : 'inactive' ?>">
                                            <?= $request['status'] ? 'Active' : 'Inactive' ?>
                                        </div>
                                    </div>

                                    <div class="request-details">
                                        <div class="detail-item">
                                            <span class="detail-label">User ID</span>
                                            <span class="detail-value">#<?= htmlspecialchars($request['userid']) ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Card Number</span>
                                            <span class="detail-value card-number"><?= htmlspecialchars($request['cardno']) ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Account Balance</span>
                                            <span class="detail-value balance">৳ <?= number_format($request['balance'], 2) ?></span>
                                        </div>
                                    </div>

                                    <div class="request-actions">
                                        <form method="post" style="display: inline-block;">
                                            <input type="hidden" name="userid" value="<?= $request['userid'] ?>">
                                            <input type="hidden" name="cardno" value="<?= $request['cardno'] ?>">
                                            <button type="submit" name="action" value="accept" class="btn btn-accept" onclick="return confirm('Are you sure you want to accept this card request?')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Accept
                                            </button>
                                        </form>
                                        <form method="post" style="display: inline-block;">
                                            <input type="hidden" name="userid" value="<?= $request['userid'] ?>">
                                            <input type="hidden" name="cardno" value="<?= $request['cardno'] ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-reject" onclick="return confirm('Are you sure you want to reject this card request?')">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <line x1="15" y1="9" x2="9" y2="15"/>
                                                    <line x1="9" y1="9" x2="15" y2="15"/>
                                                </svg>
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
