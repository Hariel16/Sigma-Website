<?php
require 'config.php';

// Initialize error message
$error = '';

// Sanitization function
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    // ... rest of your code ...

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
            $error = "Erreur de validation CSRF.";
        } else {
            // Simple rate limiting per session
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_login_attempt'] = time();
            }
            if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_login_attempt']) < 600) {
                $error = "Trop de tentatives de connexion. Veuillez réessayer dans 10 minutes.";
            }

            // Check if email exists
        $result = execute_query($conn, "SELECT id, email, password, full_name FROM users WHERE email = ?", [$email], "s");
        $user = $result->fetch_assoc();

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session
                    // Regenerate session ID after login
                    session_regenerate_id(true);
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];

                    // reset login attempts
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['last_login_attempt'] = time();

                // Increment login_count if column exists
                $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'login_count'");
                if ($check_column && $check_column->num_rows > 0) {
                    $stmt = $conn->prepare("UPDATE users SET login_count = login_count + 1 WHERE id = ?");
                    $stmt->bind_param("i", $user['id']);
                    $stmt->execute();
                    $stmt->close();
                }

                header("Location: dashboard.php");
                exit;
            } else {
                // Incorrect password
                $error = "Email ou mot de passe incorrect.";
                $_SESSION['login_attempts']++;
                $_SESSION['last_login_attempt'] = time();
            }
        } else {
            // Email doesn't exist
            $error = "Email inexistant.";
                $_SESSION['login_attempts']++;
                $_SESSION['last_login_attempt'] = time();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('img/2024.jpg');
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
            background-size: cover;
        }
        .container {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            width: 300px;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .back-arrow {
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #1e3a8a;
            text-decoration: none;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .back-arrow:hover {
            color: #d4af37;
            transform: scale(1.1);
        }
        .logo {
            width: 100px;
            margin-bottom: 20px;
        }
        h2 {
            color: #1e3a8a;
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #d32f2f;
        }
        .error {
            color: #dc2626;
            font-size: 14px;
            margin-top: 10px;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .forgot-password a {
            color: #1e3a8a;
            text-decoration: none;
            font-size: 12px;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="accueil.php" class="back-arrow" aria-label="Retour à l'accueil"><i class="fas fa-arrow-left"></i></a>
        <img src="img/image.png" alt="Sigma Logo" class="logo">
        <h2>Se connecter</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
            <input type="email" name="email" placeholder="Votre adresse email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        <div class="forgot-password">
            <a href="verification.php">Sans compte ?Créer-en-un</a>
            <a href="password_reset.php">Mot de passe oublié ?</a>
        </div>
    </div>
</body>
</html>