<?php
require 'config.php';

// Enforce HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validate session and input parameters
if (!isset($_SESSION['user_email']) || !isset($_POST['recipient_id']) || !isset($_POST['csrf_token'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized or missing parameters']));
}

// Validate CSRF token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit(json_encode(['error' => 'Invalid CSRF token']));
}

// Sanitize and validate recipient_id
$recipient_id = filter_var($_POST['recipient_id'], FILTER_VALIDATE_INT);
if (!$recipient_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid recipient ID']));
}

$email = $_SESSION['user_email'];

// Get current user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$stmt->close();

if (!$current_user) {
    http_response_code(403);
    exit(json_encode(['error' => 'User not found']));
}

// Validate recipient_id exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(400);
    exit(json_encode(['error' => 'Recipient not found']));
}
$stmt->close();

// Mark messages as read
$stmt = $conn->prepare("UPDATE discussion SET is_read = 1 WHERE sender_id = ? AND recipient_id = ? AND is_read = 0");
$stmt->bind_param("ii", $recipient_id, $current_user['id']);
$stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt->close();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'affected_rows' => $affected_rows]);
?>