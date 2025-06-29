<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$cardDetails = null;
$error = null;
$cardExpired = false;
$requestPending = false;

// Always fetch card details for the logged-in user
$stmt = $conn->prepare("SELECT id, userid, cardno, valid_till FROM cards WHERE userid=?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['id']); // Use 'i' for integer
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $userid, $cardno, $valid_till);
if ($stmt->fetch()) {
    // valid_till is in 'YYYY-MM-DD' format
    $today = date('Y-m-d');
    if ($valid_till < $today) {
        $cardExpired = true;
        // Check if a card request is already pending
        $req = $conn->prepare("SELECT id FROM cardrequests WHERE userid=?");
        $req->bind_param("i", $_SESSION['id']);
        $req->execute();
        $req->store_result();
        if ($req->num_rows > 0) {
            $requestPending = true;
        }
        $req->close();
    } else {
        $cardDetails = [
            'cardno' => $cardno,
            'valid_till' => date('m/y', strtotime($valid_till)),
            'cardholder' => $_SESSION['username']
        ];
    }
} else {
    $error = "No card found.";
}
$stmt->close();

// Handle new card request
if (isset($_POST['request_card']) && $cardExpired && !$requestPending) {
    $ins = $conn->prepare("INSERT INTO cardrequests (userid, cardno) VALUES (?, ?)");
    $ins->bind_param("is", $_SESSION['id'], $cardno);
    if ($ins->execute()) {
        $requestPending = true;
    } else {
        $error = "Failed to request new card.";
    }
    $ins->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cards</title>
    <link rel="stylesheet" href="assets/cards.css">
</head>

<body>
    <div class="container">
        <h2>Manage Cards</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($cardDetails): ?>
            <div class="card">
                <img src="assets/visa.jpg" alt="">
                <p id="card-number"><?= htmlspecialchars($cardDetails['cardno']) ?></p>
                <div id="card-details">
                    <p class="cardholder"><?= htmlspecialchars($cardDetails['cardholder']) ?></p>
                    <p id="date"><?= htmlspecialchars($cardDetails['valid_till']) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($cardExpired): ?>
            <?php if ($requestPending): ?>
                <p style="color:orange;">Request pending for new card.</p>
            <?php else: ?>
                <form method="post">
                    <button type="submit" name="request_card" class="btn">Request New Card</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <a class="btn" href="index.php" class="back-btn">Go Back</a>
</body>

</html>