<?php

date_default_timezone_set('Africa/Abidjan'); // Fuseau horaire de Lomé

// Ensure no whitespace before this line
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_user = 'root'; // Your phpMyAdmin user
$db_pass = ''; // Your phpMyAdmin password
$db_name = 'laho';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Échec de la connexion : " . htmlspecialchars($conn->connect_error));
}

// Définir le fuseau horaire pour MySQL aussi
$conn->query("SET time_zone = '+00:00'");

function configSanitize($data) {
    // Existing code
    global $conn;
    return htmlspecialchars(strip_tags($conn->real_escape_string($data)));
}

// SMTP settings for PHPMailer
define('SMTP_HOST', 'smtp.gmail.com'); // e.g., smtp.gmail.com for Gmail
define('SMTP_USERNAME', 'gojomeh137@gmail.com'); // Your SMTP username
define('SMTP_PASSWORD', 'vvvc qbzg sfey jkvi'); // Your SMTP password
define('SMTP_PORT', 587); // 587 for TLS, 465 for SSL
define('SMTP_FROM_EMAIL', 'gojomeh137@gmail.com'); // Sender email
define('SMTP_FROM_NAME', 'Communauté Sigma'); // Sender name
define('SMTP_REPLY_TO_EMAIL', 'support@votre-domaine.com'); // Reply-to email
define('SMTP_REPLY_TO_NAME', 'Support Communauté Sigma'); // Reply-to name
?>