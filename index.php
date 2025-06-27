<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lucky Bank - Dashboard</title>
  <link rel="stylesheet" href="assets/index.css">
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
          <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </div>
    </header>

    <!-- Main Dashboard Content -->
    <main class="dashboard-main">
      <div class="dashboard-content">
        <!-- Balance Section -->
        <div class="balance-section">
          <div class="balance-card">
            <h2 class="balance-title">Account Balance</h2>
            <div class="balance-amount" id="balanceValue">Loading...</div>
            <button class="refresh-btn" onclick="updateBalance()">
              <svg class="refresh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12c0 5-4 9-9 9s-9-4-9-9 4-9 9-9c2.5 0 4.7 1 6.4 2.6L21 3v6h-6"/>
              </svg>
              Refresh
            </button>
          </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="actions-grid">
          <div class="action-card" onclick="quickTransfer()">
            <div class="action-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
            </div>
            <h3 class="action-title">Quick Transfer</h3>
            <p class="action-description">Send money instantly</p>
          </div>

          <div class="action-card" onclick="goToTransactions()">
            <div class="action-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10,9 9,9 8,9"/>
              </svg>
            </div>
            <h3 class="action-title">Transaction History</h3>
            <p class="action-description">View your transactions</p>
          </div>

          <div class="action-card" onclick="manageCards()">
            <div class="action-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
              </svg>
            </div>
            <h3 class="action-title">Manage Cards</h3>
            <p class="action-description">View and manage your cards</p>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="dashboard-footer">
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

  <!-- Transfer Modal -->
  <div id="transferModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title">Transfer Funds</h2>
        <button class="modal-close" onclick="closeTransferModal()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      
      <form class="transfer-form" onsubmit="return validateTransferForm()">
        <div class="form-group">
          <label for="amount" class="form-label">Amount (৳)</label>
          <input type="number" id="amount" class="form-input" required placeholder="Enter amount" min="1" step="0.01">
        </div>

        <div class="form-group">
          <label for="beneficiary" class="form-label">Beneficiary Account</label>
          <input type="text" id="beneficiary" class="form-input" required placeholder="Enter account number">
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-secondary" onclick="closeTransferModal()">Cancel</button>
          <button type="submit" class="btn-primary">Transfer Funds</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function quickTransfer() {
      document.getElementById("transferModal").style.display = "flex";
      document.body.style.overflow = "hidden";
    }

    function closeTransferModal() {
      document.getElementById("transferModal").style.display = "none";
      document.body.style.overflow = "auto";
    }

    function goToTransactions() {
      window.location.href = 'history.php';
    }

    function manageCards() {
      window.location.href = 'cards.php';
    }

    function updateBalance() {
      const balanceElement = document.getElementById("balanceValue");
      const refreshBtn = document.querySelector(".refresh-btn");
      
      balanceElement.textContent = "Loading...";
      refreshBtn.style.opacity = "0.5";
      refreshBtn.disabled = true;
      
      fetch('api/get_balance.php')
        .then(response => response.json())
        .then(data => {
          if (data.balance !== undefined) {
            balanceElement.textContent = "৳ " + Number(data.balance).toLocaleString('en-BD', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            });
          } else {
            balanceElement.textContent = "Balance unavailable";
          }
        })
        .catch(error => {
          balanceElement.textContent = "Error loading balance";
        })
        .finally(() => {
          refreshBtn.style.opacity = "1";
          refreshBtn.disabled = false;
        });
    }

    function validateTransferForm() {
      let amount = document.getElementById("amount").value;
      let beneficiary = document.getElementById("beneficiary").value;

      if (amount <= 0) {
        alert("Amount should be greater than zero!");
        return false;
      }

      if (!beneficiary) {
        alert("Beneficiary account is required!");
        return false;
      }

      // Show loading state
      const submitBtn = document.querySelector(".btn-primary");
      const originalText = submitBtn.textContent;
      submitBtn.textContent = "Processing...";
      submitBtn.disabled = true;

      fetch('api/transfer.php', {
        method: 'POST',
        body: JSON.stringify({ amount, beneficiary }),
        headers: { 'Content-Type': 'application/json' }
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert(data.message || "Transfer Successful!");
            updateBalance();
            closeTransferModal();
            document.getElementById("amount").value = "";
            document.getElementById("beneficiary").value = "";
          } else {
            alert("Error: " + (data.message || "Transfer failed."));
          }
        })
        .catch(error => {
          alert('Network error: ' + error);
        })
        .finally(() => {
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
        });

      return false; // prevent form submission
    }

    // Load balance on page load
    window.onload = function() {
      updateBalance();
    };

    // Close modal when clicking outside
    document.getElementById("transferModal").addEventListener("click", function(e) {
      if (e.target === this) {
        closeTransferModal();
      }
    });
  </script>
</body>
</html>