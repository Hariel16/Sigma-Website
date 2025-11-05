<?php
// Récupération des configurations depuis la base
$config_sql = "SELECT setting_key, setting_value FROM general_config";
$config_result = $conn->query($config_sql);
$configs = [];
while($row = $config_result->fetch_assoc()) {
    $configs[$row['setting_key']] = $row['setting_value'];
}

// Valeurs par défaut
$instagram_url = $configs['instagram_url'] ?? 'https://instagram.com/sigmaofficial';
$tiktok_url = $configs['tiktok_url'] ?? 'https://tiktok.com/@sigmaofficial';
$contact_email = $configs['contact_email'] ?? 'contact@sigma-alumni.org';
$contact_phone = $configs['contact_phone'] ?? '+33 1 23 45 67 89';
$contact_address = $configs['contact_address'] ?? '123 Rue de l\'Éducation, 75001 Paris, France';
$footer_logo = $configs['footer_logo'] ?? 'img/image.png';
?>
<footer>
    <div class="footer-container">
        <div class="footer-about">
            <div class="footer-logo">
                <img src="<?php echo htmlspecialchars($footer_logo); ?>" alt="SIGMA Alumni Logo">
            </div>
            <p>L'association des anciens élèves de SIGMA, unissant science, conscience et méthode depuis 1985.</p>
            <div class="social-links-footer">
                <a href="<?php echo htmlspecialchars($instagram_url); ?>" target="_blank" aria-label="Suivez-nous sur Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="<?php echo htmlspecialchars($tiktok_url); ?>" target="_blank" aria-label="Suivez-nous sur TikTok">
                    <i class="fab fa-tiktok"></i>
                </a>
            </div>
        </div>

        <div class="footer-links">
            <h3>Liens rapides</h3>
            <ul>
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="bureau.php">Bureau</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h3>Ressources</h3>
            <ul>
                <li><a href="reglement.php">Règlement</a></li>
                <li><a href="annuaire.php">Annuaire</a></li>
                <li><a href="objectifs.php">Objectifs</a></li>
                <li><a href="elections.php">Élections</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h3>Contactez-nous</h3>
            <p><i class="fas fa-envelope" aria-hidden="true"></i> <?php echo htmlspecialchars($contact_email); ?></p>
            <p><i class="fas fa-phone" aria-hidden="true"></i> <?php echo htmlspecialchars($contact_phone); ?></p>
            <p><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?php echo htmlspecialchars($contact_address); ?></p>
        </div>
    </div>
    <div class="copyright">
        <p>© <?php echo date('Y'); ?> SIGMA ALUMNI - Administration</p>
    </div>
</footer>

<style>
    footer {
        background-color: var(--accent-gray);
        color: var(--white);
        padding: 3rem 5% 1rem;
        margin-top: auto;
    }

    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
    }

    .footer-logo {
        margin-bottom: 1rem;
    }

    .footer-logo img {
        height: 40px;
    }

    .footer-links h3 {
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
    }

    .footer-links ul {
        list-style: none;
    }

    .footer-links ul li {
        margin-bottom: 0.8rem;
    }

    .footer-links ul li a {
        color: #ccc;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-links ul li a:hover {
        color: var(--white);
    }

    .social-links-footer {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .social-links-footer a {
        color: var(--white);
        font-size: 1.5rem;
        transition: color 0.3s;
    }

    .social-links-footer a:hover {
        color: var(--primary-blue);
    }

    .copyright {
        text-align: center;
        padding-top: 2rem;
        margin-top: 2rem;
        border-top: 1px solid #666;
    }

    /* Responsive footer */
    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .social-links-footer {
            justify-content: center;
        }
    }
</style>

<?php
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    session_destroy();
    header("Location: accueil.php");
    exit();
}

$conn->close();
?>
</body>
</html>