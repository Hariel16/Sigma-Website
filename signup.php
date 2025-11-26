<?php
require 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enforce HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = "Erreur de validation CSRF.";
        header("Location: creation_compte.php");
        exit;
    }

    // Sanitize and validate user inputs
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Adresse e-mail invalide.";
        header("Location: creation_compte.php");
        exit;
    }

    $password = trim($_POST['password']);
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères.";
        header("Location: creation_compte.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email déjà utilisé.";
        header("Location: creation_compte.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['user_email'] = $email;
        unset($_SESSION['error']); // Effacer l'erreur si l'inscription réussit
        header("Location: creation_profil.php");
    } else {
        $_SESSION['error'] = "Erreur lors de la création du compte.";
        header("Location: creation_compte.php");
    }
    $stmt->close();
}
?>