<?php 
include 'header.php'; 

// Login processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, email, password, full_name, is_admin FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['is_admin'] = $user['is_admin'];
        // Set secure session cookie
        setcookie('PHPSESSID', session_id(), [
            'expires' => strtotime('next year'),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        header("Location: index.php");
        exit();
    } else {
        $login_error = "Email ou mot de passe incorrect.";
    }
    $stmt->close();
}

// Member count
$sql = "SELECT COUNT(*) as member_count FROM users";
$result = $conn->query($sql);
$member_count = $result->fetch_assoc()['member_count'];
?>

<style>
    /* Styles spécifiques à la page d'accueil */
    .news-section .news-item {
        display: flex;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .news-section .news-image {
        width: 120px;
        height: 80px;
        border-radius: 5px;
        overflow: hidden;
        margin-right: 1.5rem;
        flex-shrink: 0;
    }

    .news-section .news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .news-section .news-content {
        flex: 1;
    }

    .news-section .news-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .news-section .news-item h3 {
        color: var(--primary-blue);
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .news-section .news-date {
        font-size: 0.9rem;
        color: #777;
        margin-bottom: 0.5rem;
    }

    .hero {
        position: relative;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: var(--white);
        overflow: hidden;
        margin-top: 70px;
    }

    .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 51, 102, 0.7);
        z-index: 1;
    }

    .hero video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        padding: 0 2rem;
    }

    .hero h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .hero p {
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .btn {
        display: inline-block;
        padding: 0.8rem 1.8rem;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        cursor: pointer;
    }

    .btn-primary {
        background-color: var(--primary-blue);
        color: var(--white);
        border: 2px solid var(--primary-blue);
    }

    .btn-primary:hover {
        background-color: transparent;
        color: var(--primary-blue);
    }

    .features {
        padding: 5rem 5%;
        max-width: 1400px;
        margin: 0 auto;
    }

    .section-title {
        text-align: center;
        margin-bottom: 3rem;
    }

    .section-title h2 {
        font-size: 2.2rem;
        color: var(--dark-blue);
        margin-bottom: 1rem;
    }

    .section-title p {
        color: var(--accent-gray);
        max-width: 700px;
        margin: 0 auto;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
    }

    .feature-card {
        background: var(--white);
        border-radius: 10px;
        padding: 2rem;
        box-shadow: var(--shadow);
        transition: transform 0.3s;
        text-align: center;
        cursor: pointer;
    }

    .feature-card:hover {
        transform: translateY(-10px);
    }

    .feature-icon {
        font-size: 2.5rem;
        color: var(--primary-blue);
        margin-bottom: 1.5rem;
    }

    .feature-card h3 {
        font-size: 1.4rem;
        margin-bottom: 1rem;
        color: var(--dark-blue);
    }

    .news-events {
        background-color: var(--light-blue);
        padding: 5rem 5%;
    }

    .news-container {
        max-width: 1400px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .news-section, .events-section {
        background: var(--white);
        border-radius: 10px;
        padding: 2rem;
        box-shadow: var(--shadow);
    }

    .news-section h2, .events-section h2 {
        color: var(--dark-blue);
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--light-blue);
    }

    .news-item {
        display: flex;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #eee;
    }

    .news-image {
        width: 120px;
        height: 80px;
        border-radius: 5px;
        overflow: hidden;
        margin-right: 1.5rem;
        flex-shrink: 0;
    }

    .news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .news-content {
        flex: 1;
    }

    .news-item:last-child, .event-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .news-item h3 {
        color: var(--primary-blue);
        margin-bottom: 0.5rem;
    }

    .news-date {
        font-size: 0.9rem;
        color: #777;
        margin-bottom: 0.5rem;
    }

    .event-item {
        margin-bottom: 1.5rem;
    }

    .event-date {
        font-weight: bold;
        color: var(--primary-blue);
    }

    .counter-section {
        background-color: var(--dark-blue);
        color: var(--white);
        text-align: center;
        padding: 3rem 5%;
    }

    .counter-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .counter {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 1rem 0;
    }

    /* Events Section Styles */
    .events-section .event-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--primary-blue);
    }

    .events-section .event-date {
        color: var(--primary-blue);
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .events-section .event-item h3 {
        color: var(--dark-blue);
        margin-bottom: 0.8rem;
        font-size: 1.1rem;
    }

    .events-section .event-location {
        color: var(--accent-gray);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }

    .reminder-section {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }

    .btn-reminder {
        background: var(--primary-blue);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-reminder:hover {
        background: var(--dark-blue);
        transform: translateY(-2px);
    }

    .btn-reminder:disabled {
        background: #28a745;
        cursor: not-allowed;
        transform: none;
    }

    .btn-reminder-added {
        background: #28a745 !important;
    }

    .btn-events-more {
        display: inline-block;
        background: transparent;
        color: var(--primary-blue);
        border: 2px solid var(--primary-blue);
        padding: 0.7rem 1.5rem;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        text-align: center;
        margin-top: 1rem;
    }

    .btn-events-more:hover {
        background: var(--primary-blue);
        color: white;
    }

    /* Alert styles */
    .alert {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 5px;
        color: white;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background: #28a745;
    }

    .alert-error {
        background: #dc3545;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Responsive Design pour l'accueil */
    @media (max-width: 1024px) {
        .hero h1 {
            font-size: 2.2rem;
        }
        
        .hero p {
            font-size: 1rem;
        }
        
        .features-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .hero {
            margin-top: 60px;
        }

        .hero h1 {
            font-size: 1.8rem;
        }

        .hero p {
            font-size: 0.9rem;
        }

        .hero video {
            display: none;
        }

        .hero {
            background: linear-gradient(rgba(0, 51, 102, 0.7), rgba(0, 51, 102, 0.7)), url('img/hero-background-mobile.jpg') center/cover no-repeat;
        }

        .news-container {
            grid-template-columns: 1fr;
        }

        .features-grid {
            grid-template-columns: 1fr;
        }

        .news-item {
            flex-direction: column;
        }

        .news-image {
            width: 100%;
            height: 150px;
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .feature-card {
            padding: 1.5rem;
        }

        .section-title h2 {
            font-size: 1.8rem;
        }

        .counter {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .hero h1 {
            font-size: 1.5rem;
        }
        
        .btn {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }
        
        .features, .news-events, .counter-section {
            padding: 3rem 5%;
        }
    }
</style>

<section class="hero">
    <video aria-hidden="true" autoplay muted loop playsinline>
        <source src="path/to/local/video.mp4" type="video/mp4">
        Votre navigateur ne supporte pas la lecture de vidéos.
    </video>
    <div class="hero-content">
        <h1>Bienvenue à SIGMA</h1>
        <p>Reconnectez-vous avec vos anciens camarades, découvrez les événements à venir et contribuez à notre communauté dynamique.</p>
        <div class="hero-buttons">
            <?php if (!isset($_SESSION['full_name'])): ?>
                <a href="verification.php" class="btn btn-primary">Rejoindre la communauté</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features" id="features">
    <div class="section-title">
        <h2>Nos Services</h2>
        <p>Découvrez ce que notre plateforme offre à la communauté SIGMA Alumni</p>
    </div>
    <div class="features-grid">
        <div class="feature-card" role="button" tabindex="0" onclick="window.location.href='connexion.php'" onkeypress="if(event.key === 'Enter') window.location.href='connexion.php'">
            <div class="feature-icon">
                <i class="fas fa-address-book" aria-hidden="true"></i>
            </div>
            <h3>Annuaire</h3>
            <p>Accédez au répertoire complet des anciens élèves et restez en contact avec votre réseau.</p>
        </div>
        <div class="feature-card" role="button" tabindex="0" onclick="window.location.href='connexion.php'" onkeypress="if(event.key === 'Enter') window.location.href='connexion.php'">
            <div class="feature-icon">
                <i class="fas fa-vote-yea" aria-hidden="true"></i>
            </div>
            <h3>Vote</h3>
            <p>Participez aux élections du bureau de l'association.</p>
        </div>
        <div class="feature-card" role="button" tabindex="0" onclick="window.location.href='reglement.php'" onkeypress="if(event.key === 'Enter') window.location.href='reglement.php'">
            <div class="feature-icon">
                <i class="fas fa-clipboard-list" aria-hidden="true"></i>
            </div>
            <h3>Règlement</h3>
            <p>Consultez les règles et valeurs qui guident notre association.</p>
        </div>
        <div class="feature-card" role="button" tabindex="0" onclick="window.location.href='objectifs.php'" onkeypress="if(event.key === 'Enter') window.location.href='objectifs.php'">
            <div class="feature-icon">
                <i class="fas fa-bullseye" aria-hidden="true"></i>
            </div>
            <h3>Objectifs</h3>
            <p>Découvrez les missions et objectifs de notre communauté alumni.</p>
        </div>
    </div>
</section>

<section class="news-events">
    <div class="news-container">
        <div class="news-section">
            <h2>Actualités</h2>
            <?php
            // Fetch active news
            $news_sql = "SELECT * FROM news WHERE is_active = 1 ORDER BY order_index ASC, created_at DESC LIMIT 3";
            $news_result = $conn->query($news_sql);
            
            if ($news_result->num_rows > 0) {
                while($news_item = $news_result->fetch_assoc()) {
                    echo '<div class="news-item">';
                    if ($news_item['image_path']) {
                        echo '<div class="news-image">';
                        echo '<img src="' . htmlspecialchars($news_item['image_path']) . '" alt="' . htmlspecialchars($news_item['title']) . '">';
                        echo '</div>';
                    }
                    echo '<div class="news-content">';
                    echo '<h3>' . htmlspecialchars($news_item['title']) . '</h3>';
                    echo '<div class="news-date">' . date('d/m/Y', strtotime($news_item['created_at'])) . '</div>';
                    echo '<p>' . htmlspecialchars($news_item['excerpt']) . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucune actualité pour le moment.</p>';
            }
            ?>
        </div>
        <div class="events-section">
            <h2>Événements à venir</h2>
            <?php
            // Fetch upcoming events (next 3 events)
            $events_sql = "SELECT * FROM events WHERE event_date > NOW() ORDER BY event_date ASC LIMIT 3";
            $events_result = $conn->query($events_sql);
            
            if ($events_result->num_rows > 0) {
                while($event = $events_result->fetch_assoc()) {
                    echo '<div class="event-item">';
                    echo '<div class="event-date">';
                    echo '<i class="fas fa-calendar-alt" aria-hidden="true"></i> ';
                    echo date('d/m/Y à H:i', strtotime($event['event_date']));
                    echo '</div>';
                    echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
                    echo '<p>' . htmlspecialchars($event['description']) . '</p>';
                    echo '<div class="event-location">';
                    echo '<i class="fas fa-map-marker-alt" aria-hidden="true"></i> ';
                    echo htmlspecialchars($event['location']);
                    echo '</div>';
                    
                    // Add reminder button
                    echo '<div class="reminder-section">';
                    if (isset($_SESSION['user_id'])) {
                        // Check if user already set a reminder
                        $reminder_check_sql = "SELECT id FROM event_reminders WHERE user_id = ? AND event_id = ?";
                        $stmt = $conn->prepare($reminder_check_sql);
                        $stmt->bind_param("ii", $_SESSION['user_id'], $event['id']);
                        $stmt->execute();
                        $reminder_exists = $stmt->get_result()->num_rows > 0;
                        $stmt->close();
                        
                        if ($reminder_exists) {
                            echo '<button class="btn-reminder btn-reminder-added" data-event-id="' . $event['id'] . '" disabled>';
                            echo '<i class="fas fa-bell"></i> Rappel ajouté';
                            echo '</button>';
                        } else {
                            echo '<button class="btn-reminder" data-event-id="' . $event['id'] . '">';
                            echo '<i class="far fa-bell"></i> Ajouter un rappel';
                            echo '</button>';
                        }
                    } else {
                        echo '<button class="btn-reminder" onclick="showLoginAlert()">';
                        echo '<i class="far fa-bell"></i> Ajouter un rappel';
                        echo '</button>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucun événement à venir pour le moment.</p>';
            }
            ?>
            <div class="events-more">
                <a href="evenements.php" class="btn-events-more">Voir tous les événements</a>
            </div>
        </div>
    </div>
</section>

<section class="counter-section">
    <div class="counter-container">
        <h2>Rejoignez notre communauté grandissante</h2>
        <div class="counter" id="member-counter"><?php echo $member_count; ?></div>
        <p>Anciens élèves déjà inscrits</p>
    </div>
</section>

<script>
    function animateCounter(target, start, end, duration) {
        let startTime = null;
        const step = (timestamp) => {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            target.textContent = value.toLocaleString();
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = document.getElementById('member-counter');
                const targetCount = parseInt(counter.textContent);
                animateCounter(counter, 0, targetCount, 2000);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    observer.observe(document.querySelector('.counter-section'));
    
    // Function to show login alert
    function showLoginAlert() {
        showAlert('Veuillez vous connecter pour ajouter un rappel', 'error');
    }

    // Function to show alerts
    function showAlert(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }

    // Handle reminder buttons
    document.addEventListener('DOMContentLoaded', function() {
        const reminderButtons = document.querySelectorAll('.btn-reminder:not(:disabled)');
        
        reminderButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                
                // Show loading state
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
                this.disabled = true;
                
                // Send AJAX request
                fetch('add_reminder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'event_id=' + eventId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.innerHTML = '<i class="fas fa-bell"></i> Rappel ajouté';
                        this.classList.add('btn-reminder-added');
                        this.disabled = true;
                        showAlert('Rappel ajouté avec succès!');
                    } else {
                        this.innerHTML = originalText;
                        this.disabled = false;
                        showAlert(data.message || 'Erreur lors de l\'ajout du rappel', 'error');
                    }
                })
                .catch(error => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    showAlert('Erreur de connexion', 'error');
                    console.error('Error:', error);
                });
            });
        });
    });
</script>

<?php include 'footer.php'; ?>