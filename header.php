<?php
// filepath: c:\xampp\htdocs\Sigma-Website\header.php
// Vérifier si une session n'est pas déjà active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Use centralized database connection from config.php
require_once 'config.php';

// Fetch configurations with error handling
$configs = [];
$config_sql = "SELECT setting_key, setting_value FROM general_config LIMIT 50";
$config_result = $conn->query($config_sql);

if ($config_result) {
    while($row = $config_result->fetch_assoc()) {
        $configs[$row['setting_key']] = htmlspecialchars($row['setting_value']);
    }
} else {
    error_log("Config query failed: " . $conn->error);
}

$isLoggedIn = isset($_SESSION['user_id']);
$current_page = basename($_SERVER['PHP_SELF']);
$user_full_name = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="SIGMA Alumni - Communauté d'anciens élèves">
    <title>SIGMA Alumni - <?php echo ucfirst(str_replace('.php', '', $current_page)); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-p7QstKSQlW1/7gLoDLzPDVfMQj36m64SWkMsZrzIZms00u2+cnDyWp2LWL9suYOvhI9xVDFjJohIta9nmmVAww==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <style>
        /* CSS Variables */
        :root {
            --primary-blue: #0056b3;
            --dark-blue: #003366;
            --light-blue: #e6f0ff;
            --accent-gray: #4a4a4a;
            --light-gray: #f5f5f5;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--accent-gray);
            line-height: 1.6;
            padding-top: 70px;
        }

        /* Header Styles */
        header {
            background-color: var(--white);
            box-shadow: var(--shadow);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .logo {
            display: flex;
            align-items: center;
            z-index: 1001;
            text-decoration: none;
            color: inherit;
            gap: 0.75rem;
        }

        .logo img {
            height: 50px;
            width: auto;
            object-fit: contain;
        }

        .logo-text-wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-blue);
            line-height: 1;
            letter-spacing: 0.5px;
        }

        .logo-subtext {
            font-size: 0.65rem;
            color: var(--accent-gray);
            margin-top: 2px;
            letter-spacing: 0.3px;
            font-weight: 500;
        }

        nav {
            display: flex;
            align-items: center;
            flex: 1;
            margin-left: 3rem;
        }

        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 0;
        }

        nav ul li {
            margin-left: 0;
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            color: var(--accent-gray);
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 1.25rem;
            display: inline-block;
            position: relative;
            font-size: 0.95rem;
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
            bottom: 0.4rem;
            left: 1.25rem;
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after,
        nav ul li a.active::after {
            width: calc(100% - 2.5rem);
        }

        .nav-spacer {
            flex: 1;
        }

        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-left: auto;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-greeting {
            font-size: 0.95rem;
            color: var(--accent-gray);
            font-weight: 500;
        }

        .btn {
            background-color: var(--primary-blue);
            color: var(--white);
            border: 2px solid var(--primary-blue);
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            font-size: 0.9rem;
            text-align: center;
            font-family: inherit;
        }

        .btn:hover {
            background-color: transparent;
            color: var(--primary-blue);
            transform: translateY(-2px);
        }

        .btn-logout {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 0.6rem 1.2rem;
        }

        .btn-logout:hover {
            background-color: transparent;
            color: #dc3545;
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--accent-gray);
            font-weight: 500;
            cursor: pointer;
            font-size: 0.95rem;
            padding: 0.5rem 1.25rem;
            position: relative;
            font-family: inherit;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition);
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
            bottom: 0.4rem;
            left: 1.25rem;
            transition: width 0.3s ease;
        }

        .logout-btn:hover::after {
            width: calc(100% - 2.5rem);
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 2rem;
            height: 1.5rem;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
            margin-left: auto;
        }

        .menu-toggle span {
            width: 2rem;
            height: 0.25rem;
            background: var(--dark-blue);
            border-radius: 10px;
            transition: var(--transition);
            position: relative;
            transform-origin: center;
        }

        .menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(10px, 10px);
        }

        .menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .header-container {
                padding: 0.75rem 4%;
            }

            nav {
                margin-left: 2rem;
            }

            nav ul li a {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }

            nav ul li a::after {
                left: 0.75rem;
            }

            nav ul li a:hover::after,
            nav ul li a.active::after {
                width: calc(100% - 1.5rem);
            }

            .auth-buttons {
                gap: 0.5rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 60px;
            }

            .header-container {
                padding: 0.75rem 1rem;
            }

            .menu-toggle {
                display: flex;
            }

            nav {
                position: fixed;
                top: 60px;
                left: 0;
                height: calc(100vh - 60px);
                width: 100%;
                background: var(--white);
                flex-direction: column;
                justify-content: flex-start;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                margin: 0;
                z-index: 999;
                overflow-y: auto;
                padding-top: 1rem;
            }

            nav.active {
                transform: translateX(0);
            }

            nav ul {
                flex-direction: column;
                width: 100%;
                gap: 0;
                padding: 0;
            }

            nav ul li {
                width: 100%;
                margin: 0;
                border-bottom: 1px solid var(--light-gray);
            }

            nav ul li a {
                display: block;
                padding: 1rem 1.5rem;
                font-size: 1rem;
                width: 100%;
            }

            nav ul li a::after {
                display: none;
            }

            nav ul li a.active {
                background: var(--light-blue);
                border-left: 4px solid var(--primary-blue);
                padding-left: calc(1.5rem - 4px);
            }

            .auth-buttons {
                flex-direction: column;
                width: 100%;
                padding: 1rem 1.5rem;
                gap: 0.75rem;
                border-top: 1px solid var(--light-gray);
                margin-left: 0;
            }

            .btn {
                width: 100%;
                padding: 0.75rem;
            }

            nav {
                margin-left: 0;
            }

            .logo img {
                height: 40px;
            }

            .logo-text {
                font-size: 1.2rem;
            }

            .logo-subtext {
                font-size: 0.6rem;
            }

            .user-greeting {
                display: none;
            }

            .user-menu {
                gap: 0;
            }
        }

        @media (max-width: 480px) {
            .header-container {
                padding: 0.5rem;
            }

            .logo {
                gap: 0.5rem;
            }

            .logo img {
                height: 35px;
            }

            .logo-text {
                font-size: 1rem;
            }

            .logo-subtext {
                font-size: 0.55rem;
                display: none;
            }

            nav ul li a {
                padding: 0.75rem 1.25rem;
                font-size: 0.9rem;
            }

            .auth-buttons {
                padding: 0.75rem 1.25rem;
            }

            .btn {
                padding: 0.6rem;
                font-size: 0.8rem;
            }

            .menu-toggle span {
                width: 1.75rem;
                height: 0.2rem;
            }
        }

        /* Print styles */
        @media print {
            header {
                position: static;
            }

            body {
                padding-top: 0;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'accueil.php'; ?>" class="logo" aria-label="SIGMA Alumni - Accueil">
                <img src="img/image.png" alt="SIGMA Alumni Logo" loading="lazy">
                <div class="logo-text-wrapper">
                    <div class="logo-text">SIGMA</div>
                    <div class="logo-subtext">SCIENCE-CONSCIENCE-METHODE</div>
                </div>
            </a>
            
            <button type="button" class="menu-toggle" aria-label="Activer le menu de navigation" aria-expanded="false" aria-controls="main-nav">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </button>
            
            <nav id="main-nav" role="navigation" aria-label="Navigation principale">
                <ul>
                    <li>
                        <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'accueil.php'; ?>" 
                           <?php echo ($current_page == 'accueil.php' || $current_page == 'dashboard.php') ? 'class="active"' : ''; ?>>
                           <i class="fas fa-home"></i> <span>Accueil</span>
                        </a>
                    </li>
                    <li>
                        <a href="evenements.php" <?php echo $current_page == 'evenements.php' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-calendar-alt"></i> <span>Événements</span>
                        </a>
                    </li>
                    <li>
                        <a href="bureau.php" <?php echo $current_page == 'bureau.php' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-users"></i> <span>Bureau</span>
                        </a>
                    </li>
                    <li>
                        <a href="contact.php" <?php echo $current_page == 'contact.php' ? 'class="active"' : ''; ?>>
                            <i class="fas fa-envelope"></i> <span>Contact</span>
                        </a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                        <li>
                            <a href="mod_prof.php" <?php echo $current_page == 'profil.php' ? 'class="active"' : ''; ?>>
                                <i class="fas fa-user-circle"></i> <span>Profil</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="logout-btn" id="logoutLink" role="button" tabindex="0">
                                <i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="auth-buttons">
                            <a href="verification.php" class="btn">
                                <i class="fas fa-user-plus"></i> S'inscrire
                            </a>
                            <a href="connexion.php" class="btn">
                                <i class="fas fa-sign-in-alt"></i> Se connecter
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('#main-nav');
        const navLinks = document.querySelectorAll('#main-nav a');

        function closeMenu() {
            nav.classList.remove('active');
            menuToggle.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
        }

        menuToggle.addEventListener('click', () => {
            const isExpanded = nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
            menuToggle.setAttribute('aria-expanded', isExpanded);
        });

        // Close menu when a link is clicked
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Don't close menu for logout or other special links
                if (!link.id.includes('logout')) {
                    closeMenu();
                }
            });
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('header') && nav.classList.contains('active')) {
                closeMenu();
            }
        });

        // Close menu on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('active')) {
                closeMenu();
            }
        });

        // Logout functionality
        const logoutLink = document.getElementById('logoutLink');
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    window.location.href = 'logout.php';
                }
            });

            // Allow keyboard activation
            logoutLink.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        }

        // Header scroll effect (optional)
        let lastScrollTop = 0;
        const header = document.querySelector('header');

        window.addEventListener('scroll', () => {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                header.style.boxShadow = '0 -2px 6px rgba(0, 0, 0, 0.1)';
            } else {
                // Scrolling up
                header.style.boxShadow = 'var(--shadow)';
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });
    </script>
</body>
</html>