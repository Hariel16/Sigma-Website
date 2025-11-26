<?php

date_default_timezone_set('Africa/Abidjan'); // Fuseau horaire de Lomé

// Ensure no whitespace before this line
if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookie params
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Prefer environment variables when available
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'laho';

// Initialize database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$conn->set_charset('utf8mb4');

// Add database connection retry logic
$max_retries = 3;
$retry_count = 0;
while ($retry_count < $max_retries && $conn->connect_error) {
    $retry_count++;
    sleep(1); // Wait before retrying
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
}
if ($conn->connect_error) {
    // For security, don't show DB errors to end users
    error_log("DB Connection Error: " . $conn->connect_error);
    die("Échec de la connexion. Veuillez contacter l'administrateur.");
}

// Définir le fuseau horaire pour MySQL aussi
// Make DB use UTC neutral time zone (recommended for distributed apps)
$conn->query("SET time_zone = '+00:00'");

// Simple output escaping helper
function e($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Input sanitization utility
function sanitize_input($data) {
    return trim(strip_tags($data));
}

// CSRF helpers
function get_csrf_token() {
    if (session_status() == PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (session_status() == PHP_SESSION_NONE) session_start();
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Uploads configuration and directories
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'uploads');
}
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}

// SMTP settings, prefer environment variables
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.example.com');
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'no-reply@example.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Communauté Sigma');
define('SMTP_REPLY_TO_EMAIL', getenv('SMTP_REPLY_TO_EMAIL') ?: 'support@example.com');
define('SMTP_REPLY_TO_NAME', getenv('SMTP_REPLY_TO_NAME') ?: 'Support Communauté Sigma');
// Admin contact
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'admin@example.com');
define('ADMIN_NAME', getenv('ADMIN_NAME') ?: 'Administrateur');

// Enhanced error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
set_error_handler(function ($severity, $message, $file, $line) {
    error_log("Error [$severity]: $message in $file on line $line");
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage());
    http_response_code(500);
    echo "Une erreur interne est survenue. Veuillez réessayer plus tard.";
});

// Centralized database query helper
function execute_query($conn, $query, $params, $types) {
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}