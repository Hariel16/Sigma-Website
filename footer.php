<?php
// filepath: c:\xampp\htdocs\Sigma-Website\footer.php
// Retrieve configurations from database (fallback if not already set in header)
if (!isset($configs)) {
    $configs = [];
    $config_sql = "SELECT setting_key, setting_value FROM general_config LIMIT 20";
    $config_result = $conn->query($config_sql);
    
    if ($config_result) {
        while($row = $config_result->fetch_assoc()) {
            $configs[$row['setting_key']] = htmlspecialchars($row['setting_value']);
        }
    } else {
        error_log("Footer config query failed: " . $conn->error);
    }
}

// Default values with fallbacks
$instagram_url = $configs['instagram_url'] ?? 'https://instagram.com/sigmaofficial';
$tiktok_url = $configs['tiktok_url'] ?? 'https://tiktok.com/@sigmaofficial';
$contact_email = $configs['contact_email'] ?? 'contact@sigma-alumni.org';
$contact_phone = $configs['contact_phone'] ?? '+33 1 23 45 67 89';
$contact_address = $configs['contact_address'] ?? '123 Rue de l\'Éducation, 75001 Paris, France';
$footer_logo = $configs['footer_logo'] ?? 'img/image.png';

// Sanitize all values
$instagram_url = filter_var($instagram_url, FILTER_VALIDATE_URL) ?: 'https://instagram.com/sigmaofficial';
$tiktok_url = filter_var($tiktok_url, FILTER_VALIDATE_URL) ?: 'https://tiktok.com/@sigmaofficial';
$contact_email = filter_var($contact_email, FILTER_VALIDATE_EMAIL) ?: 'contact@sigma-alumni.org';

$current_year = date('Y');
?>
<footer>
    <div class="footer-content">
        <div class="footer-container">
            <div class="footer-about">
                <div class="footer-logo">
                    <img src="<?php echo htmlspecialchars($footer_logo); ?>" alt="SIGMA Alumni Logo" loading="lazy">
                </div>
                <p>L'association des anciens élèves de SIGMA, unissant science, conscience et méthode depuis 1985.</p>
                <div class="social-links-footer">
                    <a href="<?php echo htmlspecialchars($instagram_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="Suivez-nous sur Instagram">
                        <i class="fab fa-instagram" aria-hidden="true"></i>
                    </a>
                    <a href="<?php echo htmlspecialchars($tiktok_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="Suivez-nous sur TikTok">
                        <i class="fab fa-tiktok" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Liens rapides</h3>
                <nav aria-label="Liens de navigation rapides">
                    <ul>
                        <li><a href="accueil.php">Accueil</a></li>
                        <li><a href="evenements.php">Événements</a></li>
                        <li><a href="bureau.php">Bureau</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </nav>
            </div>

            <div class="footer-section">
                <h3>Ressources</h3>
                <nav aria-label="Ressources">
                    <ul>
                        <li><a href="reglement.php">Règlement</a></li>
                        <li><a href="annuaire.php">Annuaire</a></li>
                        <li><a href="objectifs.php">Objectifs</a></li>
                        <li><a href="elections.php">Élections</a></li>
                    </ul>
                </nav>
            </div>

            <div class="footer-section footer-contact">
                <h3>Contactez-nous</h3>
                <div class="contact-info">
                    <p>
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>">
                            <?php echo htmlspecialchars($contact_email); ?>
                        </a>
                    </p>
                    <p>
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $contact_phone); ?>">
                            <?php echo htmlspecialchars($contact_phone); ?>
                        </a>
                    </p>
                    <p>
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        <span><?php echo htmlspecialchars($contact_address); ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="copyright">
        <p>&copy; <?php echo htmlspecialchars($current_year); ?> SIGMA ALUMNI - Tous droits réservés</p>
    </div>
</footer>

<style>
    /* Footer Styles */
    footer {
        background-color: #333;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: #ecf0f1;
        padding: 3rem 5% 1rem;
        margin-top: auto;
        border-top: 4px solid var(--primary-blue);
        font-size: 0.95rem;
        line-height: 1.8;
    }

    .footer-content {
        max-width: 1400px;
        margin: 0 auto;
    }

    .footer-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 2.5rem;
        margin-bottom: 2rem;
    }

    .footer-about {
        display: flex;
        flex-direction: column;
    }

    .footer-about p {
        margin-bottom: 1.5rem;
        line-height: 1.6;
        color: #bdc3c7;
    }

    .footer-logo {
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .footer-logo img {
        height: 50px;
        width: auto;
        object-fit: contain;
        filter: brightness(0) invert(1);
    }

    .footer-section {
        display: flex;
        flex-direction: column;
    }

    .footer-section h3 {
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .footer-section ul {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .footer-section ul li {
        margin-bottom: 0;
    }

    .footer-section ul li a {
        color: #bdc3c7;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        padding: 0.4rem 0;
        position: relative;
    }

    .footer-section ul li a::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary-blue);
        transition: width 0.3s ease;
    }

    .footer-section ul li a:hover {
        color: #fff;
        padding-left: 0.5rem;
    }

    .footer-section ul li a:hover::before {
        width: 100%;
    }

    .social-links-footer {
        display: flex;
        gap: 1.25rem;
        align-items: center;
    }

    .social-links-footer a {
        color: #ecf0f1;
        font-size: 1.5rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }

    .social-links-footer a:hover {
        color: var(--primary-blue);
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-3px);
    }

    .footer-contact {
        display: flex;
        flex-direction: column;
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .contact-info p {
        margin: 0;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        color: #bdc3c7;
    }

    .contact-info i {
        color: var(--primary-blue);
        margin-top: 0.2rem;
        min-width: 18px;
        text-align: center;
    }

    .contact-info a {
        color: #bdc3c7;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .contact-info a:hover {
        color: var(--primary-blue);
        text-decoration: underline;
    }

    .copyright {
        text-align: center;
        padding-top: 2rem;
        margin-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: #95a5a6;
        font-size: 0.9rem;
    }

    .copyright p {
        margin: 0;
    }

    /* Responsive Footer Design */
    @media (max-width: 1024px) {
        footer {
            padding: 2.5rem 4%;
        }

        .footer-container {
            gap: 2rem;
        }

        .footer-section h3 {
            font-size: 1rem;
        }

        .footer-section ul li a {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 768px) {
        footer {
            padding: 2rem 4%;
        }

        .footer-container {
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .footer-about {
            grid-column: 1 / -1;
        }

        .footer-about p {
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .footer-logo img {
            height: 45px;
        }

        .footer-section h3 {
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }

        .footer-section ul li a {
            font-size: 0.85rem;
            padding: 0.35rem 0;
        }

        .social-links-footer {
            gap: 1rem;
        }

        .social-links-footer a {
            width: 36px;
            height: 36px;
            font-size: 1.2rem;
        }

        .contact-info p {
            font-size: 0.85rem;
            gap: 0.6rem;
        }

        .footer-contact {
            grid-column: 1 / -1;
        }

        .copyright {
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        footer {
            padding: 1.5rem 3%;
            border-top-width: 3px;
        }

        .footer-container {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .footer-about {
            text-align: center;
        }

        .footer-about p {
            margin-bottom: 0.75rem;
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .footer-logo {
            justify-content: center;
            margin-bottom: 1rem;
        }

        .footer-logo img {
            height: 40px;
        }

        .social-links-footer {
            justify-content: center;
            gap: 0.75rem;
        }

        .social-links-footer a {
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }

        .footer-section {
            text-align: center;
        }

        .footer-section h3 {
            font-size: 0.85rem;
            margin-bottom: 0.75rem;
            letter-spacing: 0.3px;
        }

        .footer-section ul li {
            margin-bottom: 0.4rem;
        }

        .footer-section ul li a {
            font-size: 0.75rem;
            padding: 0.25rem 0;
        }

        .footer-section ul li a:hover {
            padding-left: 0;
        }

        .footer-section ul li a::before {
            display: none;
        }

        .footer-contact {
            text-align: center;
        }

        .contact-info p {
            font-size: 0.75rem;
            justify-content: center;
            gap: 0.5rem;
        }

        .contact-info i {
            font-size: 0.85rem;
        }

        .copyright {
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 0.7rem;
        }
    }

    /* Touch-friendly adjustments */
    @media (hover: none) and (pointer: coarse) {
        .footer-section ul li a,
        .social-links-footer a,
        .contact-info a {
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-section ul li a::before {
            display: none;
        }
    }
</style>

<?php
// Close database connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
</body>
</html>