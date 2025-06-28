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
    <title>Lucky Bank - Admin Dashboard</title>
    <link rel="stylesheet" href="assets/admin-dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <header class="dashboard-header">
            <div class="header-content">
                <div class="bank-logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <h1 class="bank-name">Lucky Bank</h1>
                </div>
                <div class="user-info">
                    <span class="welcome-text">Admin Panel</span>
                    <span class="admin-badge">Administrator</span>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </header>

        <!-- Main Dashboard Content -->
        <main class="dashboard-main">
            <div class="dashboard-content">
                <div class="page-title-section">
                    <h2 class="page-title">Admin Dashboard</h2>
                    <p class="page-subtitle">Manage users, card requests, and system settings</p>
                </div>

                <!-- Admin Actions Grid -->
                <div class="actions-grid">
                    <div class="action-card" onclick="window.location.href='adminAllUsers.php'">
                        <div class="action-icon users-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                        </div>
                        <h3 class="action-title">Manage Users</h3>
                        <p class="action-description">View and manage all registered users</p>
                        
                    </div>

                    <div class="action-card" onclick="window.location.href='adminManageCardRequests.php'">
                        <div class="action-icon cards-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                <line x1="1" y1="10" x2="23" y2="10"/>
                            </svg>
                        </div>
                        <h3 class="action-title">Card Requests</h3>
                        <p class="action-description">Review and approve card applications</p>
                        
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="dashboard-footer">
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

    <script>
        function showSystemStats() {
            alert('System Statistics feature coming soon!');
        }

        function showSettings() {
            alert('System Settings feature coming soon!');
        }

        window.onload = function() {
            loadDashboardStats();
        };
    </script>
</body>
</html>
