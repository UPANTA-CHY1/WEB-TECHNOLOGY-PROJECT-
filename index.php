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
  <title>Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
  <a href="logout.php" style="float:right;">Logout</a>

  <div class="dashboard">
    <div class="tile" id="balanceTile">
      <span id="balanceValue">balance unavailable</span>
    </div>

    <div class="tile" onclick="quickTransfer()">
      💸 Quick Transfer
    </div>

    <div class="tile" onclick="goToTransactions()">
      📜 View Transactions
    </div>

    <div class="tile" onclick="manageCards()">
      💳 Manage Cards
    </div>
  </div>

  <button class="refresh-btn" onclick="updateBalance()">Refresh Balance</button>

  <!-- Modal for Fund Transfer -->
  <div id="transferModal" style="display:none;">
    <h2>Transfer Funds</h2>
    <form onsubmit="return validateTransferForm()">
      <label for="amount">Amount:</label><br>
      <input type="number" id="amount" required><br><br>

      <label for="beneficiary">Beneficiary Account:</label><br>
      <input type="text" id="beneficiary" required><br><br>

      <button type="submit">Submit Transfer</button>
    </form>
    <button onclick="closeTransferModal()">Close</button>
  </div>


</body>

<script>
  function quickTransfer() {
    document.getElementById("transferModal").style.display = "block";
  }

  function closeTransferModal() {
    document.getElementById("transferModal").style.display = "none";
  }

  function goToTransactions() {
    window.location.href = 'history.php';
  }

  function manageCards() {
    window.location.href = 'cards.html';
  }

  function updateBalance() {
    fetch('api/get_balance.php')
      .then(response => response.json())
      .then(data => {
        if (data.balance !== undefined) {
          document.getElementById("balanceValue").innerText = "Balance: ৳ " + data.balance;
        } else if (data.error) {
          // alert('Error: ' + data.error);
        } else {
          // alert('Unknown error occurred while fetching balance.' + data);
        }
      })
      .catch(error => {
        // alert('Network error: ' + error);
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
        } else {
          // alert("Error: " + (data.message || "Transfer failed."));
        }
      })
      // .catch(error => alert('Network error: ' + error));
      getBalance(); // Update balance after transfer
    closeTransferModal();
    return false; // prevent form submission
  }

  window.onload = function() {
    updateBalance();
  };
</script>

</html>
