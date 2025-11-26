<?php require 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Créer un compte</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-" crossorigin="anonymous" defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url(img/2023.jpg);
            background-repeat: no-repeat;
            background-position: center;
            background-attachment: fixed;
            background-size: cover;
        }
        .container {
            background: rgba(155, 152, 152, 0.9);
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
        input[type="password"] {
            margin-bottom: 20px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #1e3a8a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        button:hover {
            background-color: #163172;
            transform: scale(1.1);
        }
        button:active {
            background-color: #d4af37; /* Color change when pressed */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <a href="verification.php" class="btn btn-link mb-3" aria-label="Retour"><i class="fas fa-arrow-left"></i> Retour</a>
        <div class="card p-4 shadow">
            <img src="img/image.png" alt="Sigma Logo" class="card-img-top mx-auto" style="width: 100px;">
            <h2 class="card-title text-center">Créer un compte</h2>
            <form method="POST" action="signup.php">
                <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Votre adresse email" required>
                </div>
                <?php if (isset($_SESSION['error']) && $_SESSION['error'] === "Email déjà utilisé.") { ?>
                    <div class="alert alert-danger">Email déjà utilisé.</div>
                <?php } ?>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Mot de passe" required>
                </div>
                <?php if (isset($_SESSION['error']) && $_SESSION['error'] !== "Email déjà utilisé.") { ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                <?php } ?>
                <button type="submit" class="btn btn-primary w-100">Créer mon compte</button>
            </form>
        </div>
    </div>
</body>
</html>