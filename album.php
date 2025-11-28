<?php
require 'config.php';
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion.php");
    exit;
}

// Directory for yearbook media
$uploads_dir = 'uploads/';
$media_extensions = ['jpg', 'jpeg', 'png', 'mp4', 'webm'];

// Get list of year folders
$year_folders = glob($uploads_dir . '*_pic', GLOB_ONLYDIR);
$years = array_map(function($folder) {
    return preg_replace('/.*\/(\d{4})_pic$/', '$1', $folder);
}, $year_folders);
rsort($years); // Sort years in descending order

// Initial filter and pagination
$bac_year = isset($_GET['bac_year']) && in_array($_GET['bac_year'], $years) ? $_GET['bac_year'] : '';
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$offset = $page * $limit;

// Fetch media from folder
$media = [];
if ($bac_year) {
    $year_dir = $uploads_dir . $bac_year . '_pic/';
    $files = glob($year_dir . '*.{jpg,jpeg,png,mp4,webm}', GLOB_BRACE);
    foreach ($files as $index => $file) {
        if ($index < $offset) continue;
        if (count($media) >= $limit) break;
        $media[] = [
            'id' => $index + 1,
            'media_path' => $file,
            'bac_year' => $bac_year,
            'type' => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['mp4', 'webm']) ? 'video' : 'image'
        ];
    }
} else {
    foreach ($years as $year) {
        $year_dir = $uploads_dir . $year . '_pic/';
        $files = glob($year_dir . '*.{jpg,jpeg,png,mp4,webm}', GLOB_BRACE);
        foreach ($files as $index => $file) {
            if ($index < $offset) continue;
            if (count($media) >= $limit) break;
            $media[] = [
                'id' => $index + 1,
                'media_path' => $file,
                'bac_year' => $year,
                'type' => in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['mp4', 'webm']) ? 'video' : 'image'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }
        :root {
            --wood-brown: #4A2F1A;
            --leather-dark: #3C2F2F;
            --parchment: #F4E8C1;
            --text-dark: #2C1F0F;
            --border-gold: #D4A017;
            --shadow: rgba(0, 0, 0, 0.3);
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            background: linear-gradient(180deg, #E8D9A9, #F4E8C1);
            color: var(--text-dark);
            touch-action: manipulation;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--wood-brown);
            border-bottom: 3px solid var(--border-gold);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px var(--shadow);
        }
        header a {
            color: var(--border-gold);
            text-decoration: none;
            font-size: 20px;
            transition: color 0.3s;
            padding: 8px;
        }
        header a:hover {
            color: #FFF;
        }
        header h1 {
            font-size: 28px;
            color: var(--border-gold);
        }
        .bookshelf {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background: url('https://www.transparenttextures.com/patterns/wood-pattern.png');
            perspective: 1000px;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Navigation mobile pour les années */
        .mobile-year-selector {
            display: none;
            width: 100%;
            margin-bottom: 20px;
            position: relative;
        }
        
        .mobile-year-selector select {
            width: 100%;
            padding: 12px 15px;
            font-size: 16px;
            background: var(--leather-dark);
            color: var(--border-gold);
            border: 2px solid var(--border-gold);
            border-radius: 6px;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
        }
        
        .mobile-year-selector::after {
            content: "▼";
            font-size: 14px;
            color: var(--border-gold);
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }
        
        .book-stack {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            justify-content: center;
            max-height: none;
        }
        .book {
            width: 220px;
            height: 45px;
            background: var(--leather-dark);
            color: var(--border-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            border: 2px solid var(--border-gold);
            border-radius: 4px;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
            box-shadow: 2px 2px 5px var(--shadow);
        }
        .book:hover, .book:active {
            transform: translateX(10px);
            background: #2c2222;
        }
        .book.selected {
            transform: translateX(20px);
            background: #2c2222;
        }
        .book-container {
            display: none;
            width: 80%;
            max-width: 900px;
            margin: 0 auto;
        }
        .book-container.open {
            display: block;
        }
        .book-open {
            position: relative;
            width: 100%;
            height: 600px;
            background: var(--leather-dark);
            border: 3px solid var(--border-gold);
            box-shadow: 0 0 10px var(--shadow);
        }
        .page {
            position: absolute;
            width: 50%;
            height: 100%;
            backface-visibility: hidden;
            background: var(--parchment);
            border: 1px solid var(--border-gold);
            display: flex;
            flex-wrap: wrap;
            padding: 20px;
            gap: 20px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .page.left {
            transform-origin: right;
            left: 0;
        }
        .page.right {
            transform-origin: left;
            right: 0;
            transform: rotateY(180deg);
        }
        .page.turn {
            transition: transform 0.7s ease-in-out;
        }
        .page.left.turn {
            transform: rotateY(-180deg);
        }
        .page.right.turn {
            transform: rotateY(0deg);
        }
        .media-card {
            width: 140px;
            height: 180px;
            overflow: hidden;
            border-radius: 3px;
            box-shadow: 2px 2px 5px var(--shadow);
            background: #FFF;
            cursor: pointer;
            position: relative;
            transition: transform 0.2s;
        }
        .media-card:active {
            transform: scale(0.95);
        }
        .media-card img, .media-card video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 2px solid var(--border-gold);
        }
        .media-card video {
            display: block;
        }
        .media-card .play-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 30px;
            color: var(--border-gold);
            opacity: 0.8;
            pointer-events: none;
        }
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            gap: 10px;
        }
        .nav-buttons button {
            padding: 12px 20px;
            background: var(--leather-dark);
            color: var(--border-gold);
            border: 1px solid var(--border-gold);
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16px;
            transition: background 0.3s;
            flex: 1;
            min-height: 44px;
        }
        .nav-buttons button:hover, .nav-buttons button:active {
            background: var(--border-gold);
            color: var(--leather-dark);
        }
        .nav-buttons button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            justify-content: center;
            align-items: center;
            z-index: 2000;
            touch-action: pinch-zoom;
        }
        .modal-content {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .modal .close {
            position: fixed;
            top: 20px;
            right: 20px;
            font-size: 40px;
            cursor: pointer;
            color: var(--border-gold);
            z-index: 2001;
            width: 44px;
            height: 44px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
        }
        .modal img, .modal video {
            max-width: 95%;
            max-height: 80%;
            object-fit: contain;
            border: 3px solid var(--border-gold);
            border-radius: 5px;
            box-shadow: 0 0 15px var(--shadow);
            display: block;
            margin: auto;
            touch-action: manipulation;
        }
        .modal video {
            width: auto;
            height: auto;
            max-width: 95%;
            max-height: 80%;
        }
        .modal .info {
            position: fixed;
            bottom: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: #FFF;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }
        
        /* Navigation par gestes pour mobile */
        .swipe-area {
            position: fixed;
            top: 0;
            width: 50px;
            height: 100%;
            z-index: 2002;
            display: none;
        }
        .swipe-area.left {
            left: 0;
        }
        .swipe-area.right {
            right: 0;
        }
        
        /* Styles pour mobile */
        @media (max-width: 768px) {
            header {
                padding: 12px 15px;
            }
            
            header h1 {
                font-size: 22px;
            }
            
            header a {
                font-size: 18px;
            }
            
            .mobile-year-selector {
                display: block;
            }
            
            .book-stack {
                display: none;
            }
            
            .bookshelf {
                padding: 15px;
            }
            
            .book-container {
                width: 95%;
            }
            
            .book-open {
                height: 500px;
            }
            
            .page {
                padding: 15px;
                gap: 12px;
                justify-content: center;
            }
            
            .media-card {
                width: calc(50% - 10px);
                height: 160px;
                margin-bottom: 5px;
            }
            
            .nav-buttons {
                flex-direction: column;
            }
            
            .nav-buttons button {
                width: 100%;
            }
            
            .modal img, .modal video {
                max-width: 98%;
                max-height: 70%;
            }
            
            .swipe-area {
                display: block;
            }
        }
        
        @media (max-width: 480px) {
            header h1 {
                font-size: 20px;
            }
            
            .book-open {
                height: 400px;
            }
            
            .page {
                padding: 10px;
                gap: 8px;
            }
            
            .media-card {
                width: calc(50% - 6px);
                height: 130px;
            }
            
            .modal .close {
                top: 10px;
                right: 10px;
                font-size: 35px;
            }
            
            .modal .info {
                bottom: 10px;
                font-size: 12px;
                padding: 8px 15px;
            }
            
            .nav-buttons button {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
        
        /* Animation pour le chargement */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .media-card {
            animation: fadeIn 0.3s ease;
        }
        
        /* Indicateur de chargement */
        .loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 1001;
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">Chargement...</div>
    
    <header>
        <div>
            <a href="yearbook.php" aria-label="Aller au Yearbook"><i class="fas fa-book-open"></i></a>
        </div>
        <h1>Album</h1>
    </header>
    
    <div class="bookshelf">
        <!-- Sélecteur d'année pour mobile -->
        <div class="mobile-year-selector">
            <select id="yearSelect">
                <option value="">Sélectionnez une année</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $bac_year == $year ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="book-stack">
            <?php foreach ($years as $year): ?>
                <div class="book <?php echo $bac_year == $year ? 'selected' : ''; ?>" data-year="<?php echo $year; ?>">
                    <?php echo $year; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="book-container <?php echo $bac_year ? 'open' : ''; ?>">
            <div class="book-open" id="bookOpen">
                <div class="page left" id="leftPage">
                    <?php for ($i = 0; $i < min(6, count($media)); $i++): ?>
                        <div class="media-card" data-id="<?php echo $media[$i]['id']; ?>" data-year="<?php echo $media[$i]['bac_year']; ?>" data-type="<?php echo $media[$i]['type']; ?>">
                            <?php if ($media[$i]['type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($media[$i]['media_path']); ?>" alt="Photo de l'année <?php echo $media[$i]['bac_year']; ?>">
                            <?php else: ?>
                                <video preload="metadata">
                                    <source src="<?php echo htmlspecialchars($media[$i]['media_path']); ?>" type="video/<?php echo pathinfo($media[$i]['media_path'], PATHINFO_EXTENSION); ?>">
                                    Votre navigateur ne supporte pas la lecture de vidéos.
                                </video>
                                <i class="fas fa-play play-icon"></i>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
                <div class="page right" id="rightPage">
                    <?php for ($i = 6; $i < min(12, count($media)); $i++): ?>
                        <div class="media-card" data-id="<?php echo $media[$i]['id']; ?>" data-year="<?php echo $media[$i]['bac_year']; ?>" data-type="<?php echo $media[$i]['type']; ?>">
                            <?php if ($media[$i]['type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($media[$i]['media_path']); ?>" alt="Photo de l'année <?php echo $media[$i]['bac_year']; ?>">
                            <?php else: ?>
                                <video preload="metadata">
                                    <source src="<?php echo htmlspecialchars($media[$i]['media_path']); ?>" type="video/<?php echo pathinfo($media[$i]['media_path'], PATHINFO_EXTENSION); ?>">
                                    Votre navigateur ne supporte pas la lecture de vidéos.
                                </video>
                                <i class="fas fa-play play-icon"></i>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="nav-buttons">
                <button id="prevPage" <?php echo $page == 0 ? 'disabled' : ''; ?>>Page Précédente</button>
                <button id="nextPage" <?php echo count($media) < $limit ? 'disabled' : ''; ?>>Page Suivante</button>
            </div>
        </div>
    </div>
    
    <div class="modal" id="modal" role="dialog" aria-labelledby="modal-year">
        <div class="swipe-area left" id="swipeLeft"></div>
        <div class="swipe-area right" id="swipeRight"></div>
        <div class="modal-content">
            <span class="close" onclick="closeModal()">×</span>
            <div id="modal-media"></div>
            <div class="info">
                <p>Année : <span id="modal-year"></span></p>
            </div>
        </div>
    </div>

    <script>
        let currentPage = <?php echo $page; ?>;
        let media = <?php echo json_encode($media); ?>;
        const mediaPerPage = 12;
        let touchStartX = 0;
        let touchStartY = 0;

        // Gestion du sélecteur d'année mobile
        document.getElementById('yearSelect').addEventListener('change', function() {
            if (this.value) {
                showLoading();
                window.location.href = `album.php?bac_year=${encodeURIComponent(this.value)}`;
            }
        });

        // Gestion des livres (années) pour desktop
        document.querySelectorAll('.book').forEach(book => {
            book.addEventListener('click', () => {
                showLoading();
                document.querySelectorAll('.book').forEach(b => b.classList.remove('selected'));
                book.classList.add('selected');
                window.location.href = `album.php?bac_year=${encodeURIComponent(book.dataset.year)}`;
            });
        });

        // Navigation entre les pages
        document.getElementById('nextPage').addEventListener('click', () => {
            if (media.length < mediaPerPage) return;
            navigateToPage(currentPage + 1);
        });

        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage === 0) return;
            navigateToPage(currentPage - 1);
        });

        function navigateToPage(page) {
            document.getElementById('leftPage').classList.add('turn');
            document.getElementById('rightPage').classList.add('turn');
            showLoading();
            setTimeout(() => {
                window.location.href = `album.php?bac_year=${encodeURIComponent('<?php echo $bac_year; ?>')}&page=${page}`;
            }, 500);
        }

        // Fonction pour afficher le modal
        function openModal(element) {
            const year = element.dataset.year;
            const type = element.dataset.type;
            const mediaPath = element.querySelector(type === 'image' ? 'img' : 'video source').getAttribute(type === 'image' ? 'src' : 'src');
            const modalMedia = document.getElementById('modal-media');
            
            modalMedia.innerHTML = '';
            if (type === 'image') {
                const img = document.createElement('img');
                img.src = mediaPath;
                img.alt = `Photo de l'année ${year}`;
                modalMedia.appendChild(img);
                
                // Permettre le zoom sur image
                img.addEventListener('touchstart', handleTouchStart, false);
                img.addEventListener('touchmove', handleTouchMove, false);
            } else {
                const video = document.createElement('video');
                video.controls = true;
                video.autoplay = true;
                const source = document.createElement('source');
                source.src = mediaPath;
                source.type = `video/${mediaPath.split('.').pop()}`;
                video.appendChild(source);
                video.innerHTML += 'Votre navigateur ne supporte pas la lecture de vidéos.';
                modalMedia.appendChild(video);
            }
            
            document.getElementById('modal-year').textContent = year;
            document.getElementById('modal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Ajouter la possibilité de fermer le modal en tapant à l'extérieur
            document.getElementById('modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        }

        // Fonction pour fermer le modal
        function closeModal() {
            document.getElementById('modal').style.display = 'none';
            document.body.style.overflow = 'auto';
            const modalMedia = document.getElementById('modal-media');
            modalMedia.innerHTML = '';
        }

        // Gestion des cartes média
        document.querySelectorAll('.media-card').forEach(card => {
            card.addEventListener('click', () => openModal(card));
        });

        // Navigation au clavier
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && document.getElementById('modal').style.display === 'flex') {
                closeModal();
            }
        });

        // Fonction pour afficher l'indicateur de chargement
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
        }

        // Gestion des gestes de navigation pour le modal
        function handleTouchStart(e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }

        function handleTouchMove(e) {
            if (!touchStartX || !touchStartY) return;
            
            const touchEndX = e.touches[0].clientX;
            const touchEndY = e.touches[0].clientY;
            
            const diffX = touchStartX - touchEndX;
            const diffY = touchStartY - touchEndY;
            
            // Seulement si le mouvement est principalement horizontal
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    // Swipe gauche - prochaine image
                    console.log('Swipe gauche');
                } else {
                    // Swipe droite - image précédente
                    console.log('Swipe droite');
                }
                
                // Réinitialiser pour le prochain geste
                touchStartX = 0;
                touchStartY = 0;
            }
        }

        // Détection de l'appareil et ajustements
        function isMobileDevice() {
            return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
        }

        // Ajustements initiaux selon l'appareil
        if (isMobileDevice()) {
            document.body.classList.add('mobile');
        }
    </script>
</body>
</html>