<?php
session_start();
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'laho';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter']);
    exit;
}

// Check if event_id is provided
if (!isset($_POST['event_id']) || empty($_POST['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Événement non spécifié']);
    exit;
}

$user_id = $_SESSION['user_id'];
$event_id = intval($_POST['event_id']);

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

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Rappel ajouté avec succès']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout du rappel']);
}

$stmt->close();
$conn->close();
?>