<?php
// Vérifier si une session n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    die("Error: " . $e->getMessage());
}

// Fetch configurations
$config_sql = "SELECT setting_key, setting_value FROM general_config";
$config_result = $conn->query($config_sql);
$configs = [];
while($row = $config_result->fetch_assoc()) {
    $configs[$row['setting_key']] = $row['setting_value'];
}

$isLoggedIn = isset($_SESSION['user_id']);
$current_page = basename($_SERVER['PHP_SELF']);

// NE PAS gérer la déconnexion ici si on ne peut pas modifier les headers
// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');
// Allow cdn.jsdelivr & fonts.googleapis for styles and scripts while being restrictive for everything else
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:;");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SIGMA Alumni - <?php echo isset($current_page) ? e(ucfirst(str_replace('.php', '', $current_page))) : 'SIGMA'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Votre CSS existant reste le même */
        :root {
            --primary-blue: #0056b3;
            --dark-blue: #003366;
            --light-blue: #e6f0ff;
            --accent-gray: #4a4a4a;
            --light-gray: #f5f5f5;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-gray);
            color: var(--accent-gray);
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            z-index: 1001;
        }

        .logo img {
            height: 40px;
            margin-right: 10px;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark-blue);
        }

        .logo-subtext {
            font-size: 0.7rem;
            color: var(--accent-gray);
            margin-top: -5px;
        }

        nav {
            display: flex;
            align-items: center;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 1.5rem;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--accent-gray);
            font-weight: 500;
            transition: color 0.3s;
            padding: 0.5rem 0;
            position: relative;
        }

        nav ul li a:hover {
            color: var(--primary-blue);
        }

        nav ul li a.active {
            color: var(--primary-blue);
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary-blue);
            bottom: 0;
            left: 0;
            transition: width 0.3s;
        }

        nav ul li a:hover::after,
        nav ul li a.active::after {
            width: 100%;
        }

        .auth-buttons .btn {
            margin-left: 1rem;
            background-color: var(--primary-blue);
            color: var(--white);
            border: 2px solid var(--primary-blue);
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .auth-buttons .btn:hover {
            background-color: transparent;
            color: var(--primary-blue);
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--accent-gray);
            font-weight: 500;
            cursor: pointer;
            font-size: 1rem;
            padding: 0.5rem 0;
            position: relative;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            color: var(--primary-blue);
        }

        .logout-btn::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--primary-blue);
            bottom: 0;
            left: 0;
            transition: width 0.3s;
        }

        .logout-btn:hover::after {
            width: 100%;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 2rem;
            height: 2rem;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
        }

        .menu-toggle span {
            width: 2rem;
            height: 0.25rem;
            background: var(--dark-blue);
            border-radius: 10px;
            transition: all 0.3s linear;
            position: relative;
            transform-origin: 1px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                padding: 1rem;
            }

            .menu-toggle {
                display: flex;
            }

            nav ul {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: 100%;
                background: var(--white);
                flex-direction: column;
                justify-content: center;
                align-items: center;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                margin: 0;
                z-index: 1000;
            }

            nav ul.active {
                transform: translateX(0);
            }

            nav ul li {
                margin: 1.5rem 0;
            }

            .auth-buttons {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .auth-buttons .btn {
                margin: 0.5rem 0;
            }
        }

        @media (max-width: 480px) {
            .logo-text {
                font-size: 1.2rem;
            }
            
            .logo img {
                height: 35px;
            }
        }
    </style>
    
    <!-- Optional JS for bootstrap functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-" crossorigin="anonymous" defer></script>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <img src="img/image.png" alt="SIGMA Alumni Logo">
                <div>
                    <div class="logo-text">SIGMA</div>
                    <div class="logo-subtext">SCIENCE-CONSCIENCE-METHODE</div>
                </div>
            </div>
            
            <button type="button" class="menu-toggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <nav>
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'accueil.php' || $current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo $isLoggedIn ? 'dashboard.php' : 'accueil.php'; ?>" aria-current="<?php echo ($current_page == 'accueil.php' || $current_page == 'dashboard.php') ? 'page' : 'false'; ?>">Accueil</a>
                    </li>
                    <li><a href="evenements.php" <?php echo $current_page == 'evenements.php' ? 'class="active"' : ''; ?>>Événements</a></li>
                    <li><a href="bureau.php" <?php echo $current_page == 'bureau.php' ? 'class="active"' : ''; ?>>Bureau</a></li>
                    <li><a href="contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item"><a href="#" class="logout-btn nav-link" id="logoutLink">Déconnexion</a></li>
                    <?php else: ?>
                        <li class="auth-buttons nav-item d-flex align-items-center">
                            <a href="verification.php" class="btn btn-primary">S'inscrire</a>
                            <a href="connexion.php" class="btn btn-link ms-2">Se connecter</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav ul');
        
        menuToggle.addEventListener('click', () => {
            const isExpanded = nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
            menuToggle.setAttribute('aria-expanded', isExpanded);
        });

        document.querySelectorAll('nav ul li a').forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('active');
                menuToggle.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Gestion de la déconnexion par JavaScript
        document.getElementById('logoutLink').addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                // Rediriger vers la page de déconnexion
                window.location.href = 'logout.php';
            }
        });
    </script>