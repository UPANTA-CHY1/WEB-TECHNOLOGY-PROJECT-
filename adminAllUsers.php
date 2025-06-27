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
    <title>Lucky Bank - All Users</title>
    <link rel="stylesheet" href="assets/admin-users.css">
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
                    <h2 class="page-title">All Users</h2>
                    <p class="page-subtitle">Manage and view all registered users in the system</p>
                </div>

                <div class="users-container">
                    <div class="users-header">
                    </div>

                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            <h3>No Users Found</h3>
                            <p>There are no registered users in the system yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="users-table-container">
                            <div class="users-table">
                                <div class="table-header">
                                    <div class="table-row">
                                        <div class="table-cell">ID</div>
                                        <div class="table-cell">Username</div>
                                        <div class="table-cell">Email</div>
                                        <div class="table-cell">Balance</div>
                                        <div class="table-cell">Role</div>
                                        <div class="table-cell">Status</div>
                                        <div class="table-cell">Actions</div>
                                    </div>
                                </div>
                                <div class="table-body">
                                    <?php foreach ($users as $user): ?>
                                        <div class="table-row user-row" onclick="window.location.href='userDetails.php?id=<?= $user['id'] ?>'">
                                            <div class="table-cell">
                                                <span class="user-id">#<?= htmlspecialchars($user['id']) ?></span>
                                            </div>
                                            <div class="table-cell">
                                                <div class="user-info-cell">
                                                    <div class="user-avatar">
                                                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                    </div>
                                                    <span class="username"><?= htmlspecialchars($user['username']) ?></span>
                                                </div>
                                            </div>
                                            <div class="table-cell">
                                                <span class="email"><?= htmlspecialchars($user['email']) ?></span>
                                            </div>
                                            <div class="table-cell">
                                                <span class="balance">৳ <?= number_format($user['balance'], 2) ?></span>
                                            </div>
                                            <div class="table-cell">
                                                <span class="role-badge <?= $user['role'] === 'admin' ? 'admin-role' : 'user-role' ?>">
                                                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                                </span>
                                            </div>
                                            <div class="table-cell">
                                                <span class="status-badge <?= $user['status'] ? 'active' : 'inactive' ?>">
                                                    <?= $user['status'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </div>
                                            <div class="table-cell">
                                                <button class="view-btn" onclick="event.stopPropagation(); window.location.href='userDetails.php?id=<?= $user['id'] ?>'">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                        <circle cx="12" cy="12" r="3"/>
                                                    </svg>
                                                    View
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
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
