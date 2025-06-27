<?php
session_start();
// API to get balance
header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}

require '../db.php';

function getBalance()
{
    $balance = 0.00;
    global $conn;
    $stmt = $conn->prepare("SELECT balance FROM users WHERE username=?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'User not found.']);
        exit;
    } else {
        $row = $result->fetch_assoc();
        $balance = $row['balance'];
    }
    return $balance;
}

$balance = getBalance();
echo json_encode(['balance' => $balance]);
?>