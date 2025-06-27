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
    <title>Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <h1>Transactions</h1>
        <table class="table table-striped" id="historyTable">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Transaction Type</th>
                    <th>Sender/Receiver</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <!-- Transactions will be inserted here -->
            </tbody>
    </table>

    <script>
    window.onload = function() {
        fetch('api/transactionHistory.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const tbody = document.getElementById('historyTable').querySelector('tbody');
                    tbody.innerHTML = '';
                    // Get my email from PHP session (rendered into JS)
                    const myEmail = <?php echo json_encode($_SESSION['email']); ?>;
                    data.transactions.forEach(tx => {
                        let type, otherParty;
                        if (tx.sender_email === myEmail) {
                            type = 'cash out';
                            otherParty = tx.receiver_email;
                        } else {
                            type = 'cash in';
                            otherParty = tx.sender_email;
                        }
                        const row = `<tr>
                            <td>${tx.id}</td>
                            <td>${type}</td>
                            <td>${otherParty}</td>
                            <td>${tx.amount}</td>
                        </tr>`;
                        tbody.innerHTML += row;
                    });
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('Network error: ' + error);
            });
    };
    </script>
</body>
</html>