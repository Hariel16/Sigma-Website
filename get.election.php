<?php
require 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID manquant']);
    exit;
}

$id = (int)$_GET['id'];
// Add input validation for ID
if (!filter_var($id, FILTER_VALIDATE_INT)) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

// Add HTTPS enforcement
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Élection non trouvée']);
    exit;
}

echo json_encode($result->fetch_assoc());
?>