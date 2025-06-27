<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || !isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in.']);
    exit;
}

require '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$amount = isset($data['amount']) ? floatval($data['amount']) : 0;
$receiver_email = isset($data['beneficiary']) ? trim($data['beneficiary']) : '';

if ($amount <= 0 || empty($receiver_email)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Get sender info
$stmt = $conn->prepare("SELECT id, balance FROM users WHERE email=?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$stmt->bind_result($sender_id, $sender_balance);
$stmt->fetch();
$stmt->close();

if (empty($sender_id)) {
    echo json_encode(['success' => false, 'message' => 'Sender not found.']);
    die();
}

if ($sender_balance < $amount) {
    echo json_encode(['success' => false, 'message' => 'Insufficient balance.']);
    die();
}

// Get receiver info
$stmt = $conn->prepare("SELECT id, balance FROM users WHERE email=?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    die();
}
$stmt->bind_param("s", $receiver_email);
$stmt->execute();
$stmt->bind_result($receiver_id, $receiver_balance);
$stmt->fetch();
$stmt->close();

if (empty($receiver_id)) {
    echo json_encode(['success' => false, 'message' => 'Receiver not found.']);
    die();
}
if($receiver_id == $sender_id) {
    echo json_encode(['success'=> false, 'message'=> 'forbidden']);
    die();
}

// Start transaction
$conn->begin_transaction();
try {
    // Deduct from sender
    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    if (!$stmt) throw new Exception('DB error: ' . $conn->error);
    $stmt->bind_param("di", $amount, $sender_id);
    $stmt->execute();
    $stmt->close();
    
    // Add to receiver
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    if (!$stmt) throw new Exception('DB error: ' . $conn->error);
    $stmt->bind_param("di", $amount, $receiver_id);
    $stmt->execute();
    $stmt->close();
    
    // Insert transaction
    $stmt = $conn->prepare("INSERT INTO transactions (sender, receiver, amount) VALUES (?, ?, ?)");
    if (!$stmt) throw new Exception('DB error: ' . $conn->error);
    $stmt->bind_param("iid", $sender_id, $receiver_id, $amount);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Transfer successful.']);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Transfer completed!']);
    die();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Transfer failed: ' . $e->getMessage()]);
    die();
}