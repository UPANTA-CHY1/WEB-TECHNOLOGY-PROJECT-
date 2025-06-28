<?php
session_start();
header('Content-Type: application/json');
require '../db.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Get current user's ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// Get transactions where user is sender or receiver
$stmt = $conn->prepare("SELECT id, sender, receiver, amount, created_at FROM transactions WHERE sender=? OR receiver=? ORDER BY id DESC");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    // Get sender email
    $sender_stmt = $conn->prepare("SELECT email FROM users WHERE id=?");
    $sender_stmt->bind_param("i", $row['sender']);
    $sender_stmt->execute();
    $sender_stmt->bind_result($sender_email);
    $sender_stmt->fetch();
    $sender_stmt->close();

    // Get receiver email
    $receiver_stmt = $conn->prepare("SELECT email FROM users WHERE id=?");
    $receiver_stmt->bind_param("i", $row['receiver']);
    $receiver_stmt->execute();
    $receiver_stmt->bind_result($receiver_email);
    $receiver_stmt->fetch();
    $receiver_stmt->close();

    $transactions[] = [
        'id' => $row['id'],
        'sender_email' => $sender_email,
        'receiver_email' => $receiver_email,
        'amount' => $row['amount'],
        'created_at' => $row['created_at']
    ];
}
$stmt->close();

echo json_encode(['status' => 'success', 'transactions' => $transactions]);
exit;
