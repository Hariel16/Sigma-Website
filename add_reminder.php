<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php'; // Centralized database connection
require_once 'csrf.php'; // CSRF protection helper

// Centralized error handling function
function handle_error($message, $log_message = null) {
    if ($log_message) {
        error_log($log_message);
    }
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Improved database connection check
if ($conn->connect_error) {
    handle_error('Erreur de connexion à la base de données', 'Connection failed: ' . $conn->connect_error);
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'])) {
    handle_error('Requête invalide', 'Invalid CSRF token');
}

// Sanitize and validate event_id
$event_id = filter_var($_POST['event_id'], FILTER_VALIDATE_INT);
if (!$event_id) {
    handle_error('Requête invalide', 'Invalid event_id: ' . $_POST['event_id']);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter']);
    exit;
}

try {
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verify event exists
$stmt = $conn->prepare("SELECT id, event_date FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Événement non trouvé']);
    exit;
}

$event = $result->fetch_assoc();
$stmt->close();

// Calculate reminder date (1 day before event)
$reminder_date = date('Y-m-d H:i:s', strtotime($event['event_date'] . ' -1 day'));

// Check if reminder already exists
$stmt = $conn->prepare("SELECT id FROM event_reminders WHERE user_id = ? AND event_id = ?");
$stmt->bind_param("ii", $user_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Rappel déjà existant']);
    exit;
}
$stmt->close();

// Add reminder
$stmt = $conn->prepare("INSERT INTO event_reminders (user_id, event_id, reminder_date) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $event_id, $reminder_date);

// Improved error logging for reminder addition
if (!$stmt->execute()) {
    handle_error('Erreur lors de l\'ajout du rappel', 'Failed to add reminder: ' . $stmt->error);
}

echo json_encode(['success' => true, 'message' => 'Rappel ajouté avec succès']);
$stmt->close();
$conn->close();
?>