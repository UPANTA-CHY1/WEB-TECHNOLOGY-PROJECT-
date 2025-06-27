<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Bank - Transaction History</title>
    <link rel="stylesheet" href="assets/history.css">
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
                    <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                    <a href="index.php" class="back-btn">
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
                    <h2 class="page-title">Transaction History</h2>
                    <p class="page-subtitle">View all your recent transactions and account activity</p>
                </div>

                <div class="transactions-container">
                    <div class="transactions-table-container">
                        <div class="empty-state" id="emptyState" style="display: none;">
                            <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10,9 9,9 8,9"/>
                            </svg>
                            <h3>No Transactions Found</h3>
                            <p>You haven't made any transactions yet. Start using your account to see your transaction history here.</p>
                        </div>

                        <div class="transactions-table" id="transactionsTable" style="display: none;">
                            <div class="table-header">
                                <div class="table-row">
                                    <div class="table-cell">Transaction ID</div>
                                    <div class="table-cell">Type</div>
                                    <div class="table-cell">Sender/Receiver</div>
                                    <div class="table-cell">Amount</div>
                                    <div class="table-cell">Date</div>
                                </div>
                            </div>
                            <div class="table-body" id="transactionsBody">
                                <!-- Transactions will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="page-footer">
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

    <script>
        let allTransactions = [];

        function loadTransactions() {
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');
            const transactionsTable = document.getElementById('transactionsTable');
            const refreshBtn = document.querySelector('.refresh-btn');

            // Show loading state
            loadingState.style.display = 'flex';
            emptyState.style.display = 'none';
            transactionsTable.style.display = 'none';
            refreshBtn.disabled = true;

            fetch('api/transactionHistory.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.transactions && data.transactions.length > 0) {
                        allTransactions = data.transactions;
                        displayTransactions(allTransactions);
                        updateStats(allTransactions);
                        
                        loadingState.style.display = 'none';
                        transactionsTable.style.display = 'block';
                    } else {
                        loadingState.style.display = 'none';
                        emptyState.style.display = 'flex';
                    }
                })
                .catch(error => {
                    console.error('Error loading transactions:', error);
                    loadingState.style.display = 'none';
                    emptyState.style.display = 'flex';
                })
                .finally(() => {
                    refreshBtn.disabled = false;
                });
        }

        function displayTransactions(transactions) {
            const tbody = document.getElementById('transactionsBody');
            tbody.innerHTML = '';
            
            const myEmail = <?php echo json_encode($_SESSION['email']); ?>;
            
            transactions.forEach(tx => {
                let type, otherParty, typeClass;
                if (tx.sender_email === myEmail) {
                    type = 'Cash Out';
                    otherParty = tx.receiver_email;
                    typeClass = 'cash-out';
                } else {
                    type = 'Cash In';
                    otherParty = tx.sender_email;
                    typeClass = 'cash-in';
                }

                const transactionDate = tx.created_at ? new Date(tx.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'N/A';

                const row = document.createElement('div');
                row.className = 'table-row transaction-row';
                row.innerHTML = `
                    <div class="table-cell">
                        <span class="transaction-id">#${tx.id}</span>
                    </div>
                    <div class="table-cell">
                        <span class="transaction-type ${typeClass}">${type}</span>
                    </div>
                    <div class="table-cell">
                        <span class="other-party">${otherParty}</span>
                    </div>
                    <div class="table-cell">
                        <span class="amount ${typeClass}">৳ ${Number(tx.amount).toLocaleString('en-BD', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })}</span>
                    </div>
                    <div class="table-cell">
                        <span class="transaction-date">${transactionDate}</span>
                    </div>
                `;
                tbody.appendChild(row);
            });
        }

        function updateStats(transactions) {
            const totalTransactions = transactions.length;
            const currentMonth = new Date().getMonth();
            const currentYear = new Date().getFullYear();
            
            const monthlyTransactions = transactions.filter(tx => {
                if (!tx.created_at) return false;
                const txDate = new Date(tx.created_at);
                return txDate.getMonth() === currentMonth && txDate.getFullYear() === currentYear;
            }).length;

            document.getElementById('totalTransactions').textContent = totalTransactions;
            document.getElementById('monthlyTransactions').textContent = monthlyTransactions;
        }

        // Load transactions on page load
        window.onload = function() {
            loadTransactions();
        };
    </script>
</body>
</html>