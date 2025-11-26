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

// Clear session data
$_SESSION = [];

// Clear session cookies
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Destroy the session
session_destroy();

// Redirect to the homepage with a logout confirmation message
header('Location: accueil.php?logout=success');
exit;
?>