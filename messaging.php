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

// Validate session
if (!isset($_SESSION['user_email'])) {
    header("Location: connexion.php");
    exit;
}

$user_email = $_SESSION['user_email'];

// Get current user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$stmt->close();

if (!$current_user) {
    header("Location: connexion.php");
    exit;
}

// Initialize users array to avoid undefined variable error
$users = [];

// Fetch all users except the current user
$stmt = $conn->prepare("SELECT id, full_name, email, profile_picture FROM users WHERE email != ? ORDER BY full_name");
if ($stmt) {
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Handle query error
    error_log("Database query error: " . $conn->error);
}

// Generate CSRF token
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Define default profile picture path
$default_profile_picture = 'img/profile_pic.jpeg';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Votre CSS existant reste ici */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #d4af37;
            --success-color: #2e7d32;
            --error-color: #d32f2f;
            --highlight-color: #bfdbfe;
            --unread-color: #2563eb;
            --mobile-breakpoint: 768px;
            --small-mobile-breakpoint: 480px;
        }
        body {
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #bfdbfe, #f8fafc), url('img/2023.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        header .left-icons {
            display: flex;
            align-items: center;
        }
        header .logo {
            width: 40px;
            margin-right: 10px;
        }
        header .center-title {
            flex-grow: 1;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        header .center-title h1 {
            font-size: 20px;
            margin: 0;
        }
        header a, header button {
            color: white;
            text-decoration: none;
            font-size: 20px;
            margin-left: 15px;
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.3s, transform 0.2s;
        }
        header a:hover, header button:hover {
            color: var(--secondary-color);
            transform: scale(1.1);
        }
        .messaging-container {
            display: flex;
            height: calc(100vh - 60px);
        }
        .user-list {
            width: 300px;
            background: linear-gradient(145deg, #ffffff, #f0f4f8);
            border-right: 1px solid #b0bec5;
            overflow-y: auto;
            padding: 15px;
            transition: transform 0.3s ease;
        }
        .user-card {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            background: #fafafa;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            position: relative;
        }
        .user-card:hover {
            background-color: var(--highlight-color);
            transform: translateY(-2px);
        }
        .user-card.active {
            background-color: var(--highlight-color);
            border-left: 4px solid var(--primary-color);
        }
        .user-card.unread::after {
            content: '';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 10px;
            height: 10px;
            background-color: var(--unread-color);
            border-radius: 50%;
        }
        .user-card img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .user-card .info {
            flex: 1;
            min-width: 0; /* Permet au texte de s'adapter correctement */
        }
        .user-card .info h3 {
            font-size: 14px;
            margin: 0;
            color: var(--primary-color);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-card .info p {
            font-size: 12px;
            color: #607d8b;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chat-window {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            position: relative;
        }
        .chat-header {
            padding: 15px;
            background-color: var(--primary-color);
            color: white;
            font-size: 16px;
            font-weight: 500;
            border-bottom: 1px solid #b0bec5;
            display: flex;
            align-items: center;
        }
        .back-button {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            margin-right: 10px;
            cursor: pointer;
        }
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f0f4f8;
            -webkit-overflow-scrolling: touch; /* Améliore le défilement sur iOS */
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            max-width: 85%;
            word-wrap: break-word;
            position: relative;
        }
        .message.sent {
            background-color: var(--primary-color);
            color: white;
            margin-left: auto;
        }
        .message.received {
            background-color: var(--highlight-color);
            color: #34495e;
        }
        .message .timestamp {
            font-size: 10px;
            color: #607d8b;
            margin-top: 5px;
            display: block;
            text-align: right;
        }
        .chat-input {
            display: flex;
            padding: 15px;
            border-top: 1px solid #b0bec5;
            background: #fafafa;
            align-items: center;
        }
        .chat-input textarea {
            flex: 1;
            padding: 12px;
            border: 1px solid #b0bec5;
            border-radius: 20px;
            font-size: 14px;
            resize: none;
            height: 50px;
            min-height: 50px;
            max-height: 120px;
        }
        .chat-input textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 8px rgba(30, 58, 138, 0.2);
            outline: none;
        }
        .chat-input button {
            padding: 10px 20px;
            margin-left: 10px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .chat-input button:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        .no-selection, .error-message, .info-message {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #607d8b;
            text-align: center;
            padding: 20px;
        }
        .error-message {
            color: var(--error-color);
        }
        .info-message {
            color: var(--primary-color);
        }
        .hidden {
            display: none !important;
        }

        /* Mode mobile - Liste des utilisateurs en plein écran */
        @media (max-width: 768px) {
            .messaging-container {
                flex-direction: column;
                height: calc(100vh - 60px);
            }
            
            .user-list {
                width: 100%;
                max-height: none;
                height: 100%;
                position: absolute;
                z-index: 100;
                transform: translateX(0);
            }
            
            .user-list.hidden-mobile {
                transform: translateX(-100%);
            }
            
            .chat-window {
                width: 100%;
                height: 100%;
            }
            
            .back-button {
                display: block;
            }
            
            .message {
                max-width: 80%;
            }
            
            .chat-input {
                padding: 10px;
            }
            
            .chat-input textarea {
                height: 45px;
                min-height: 45px;
            }
            
            .chat-input button {
                width: 45px;
                height: 45px;
                padding: 0;
            }
        }

        /* Mode mobile très petit */
        @media (max-width: 480px) {
            header {
                padding: 10px 15px;
            }
            
            header a, header button {
                font-size: 18px;
                margin-left: 10px;
            }
            
            header .logo {
                width: 30px;
            }
            
            header .center-title h1 {
                font-size: 18px;
            }
            
            .user-card {
                padding: 8px;
            }
            
            .user-card img {
                width: 35px;
                height: 35px;
            }
            
            .user-card .info h3 {
                font-size: 13px;
            }
            
            .user-card .info p {
                font-size: 11px;
            }
            
            .chat-header {
                padding: 12px;
                font-size: 15px;
            }
            
            .chat-messages {
                padding: 10px;
            }
            
            .message {
                padding: 8px;
                max-width: 85%;
            }
            
            .chat-input {
                padding: 8px;
            }
            
            .chat-input textarea {
                font-size: 13px;
                height: 40px;
                min-height: 40px;
                padding: 10px;
            }
            
            .chat-input button {
                width: 40px;
                height: 40px;
                margin-left: 8px;
            }
            
            .no-selection, .error-message, .info-message {
                font-size: 14px;
            }
        }

        /* Améliorations tactiles */
        @media (hover: none) {
            .user-card:hover {
                background-color: #fafafa;
                transform: none;
            }
            
            .user-card:active {
                background-color: var(--highlight-color);
            }
            
            header a:hover, header button:hover,
            .chat-input button:hover {
                color: white;
                transform: none;
            }
            
            header a:active, header button:active {
                color: var(--secondary-color);
            }
            
            .chat-input button:active {
                background-color: var(--secondary-color);
            }
        }

        /* Animation pour les nouveaux messages */
        @keyframes messageSlideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message {
            animation: messageSlideIn 0.3s ease;
        }

        /* Indicateur de connexion */
        .connection-status {
            position: fixed;
            bottom: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            z-index: 1000;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            display: none;
        }
        
        .connection-status.connected {
            background-color: var(--success-color);
        }
        
        .connection-status.disconnected {
            background-color: var(--error-color);
        }
        .no-users-message {
            text-align: center;
            padding: 20px;
            color: #607d8b;
            font-style: italic;
}
        /* ... (le reste de votre CSS) ... */
    </style>
</head>
<body>
<div class="container-fluid">
    <header>
        <div class="left-icons">
            <img src="img/logo.png" alt="Logo" class="logo">
            <h1>Messagerie</h1>
        </div>
    </header>
    <div class="messaging-container">
        <div class="user-list" id="user-list">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <?php
                    // Validate profile picture path
                    $profile_picture = $default_profile_picture;
                    if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])) {
                        $profile_picture = htmlspecialchars($user['profile_picture']);
                    }
                    ?>
                    <div class="user-card" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['full_name']); ?>">
                        <img src="<?php echo $profile_picture; ?>" alt="Photo de profil de <?php echo htmlspecialchars($user['full_name']); ?>">
                        <div class="info">
                            <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-users-message">
                    <p>Aucun autre utilisateur trouvé.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="chat-window">
            <div class="chat-header" id="chat-header">
                <button class="back-button" id="back-button" aria-label="Retour à la liste des contacts">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <span id="chat-title">Sélectionnez un utilisateur pour commencer à discuter</span>
            </div>
            <div class="chat-messages" id="chat-messages">
                <div class="no-selection">Aucun utilisateur sélectionné</div>
            </div>
            <div class="chat-input hidden" id="chat-input">
                <textarea id="message-input" placeholder="Écrivez votre message..." aria-label="Écrire un message"></textarea>
                <button id="send-message" aria-label="Envoyer le message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="connection-status" id="connection-status">Connexion...</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const currentUserId = <?php echo $current_user['id']; ?>;
        let selectedUserId = null;
        let socket = null;
        let isSending = false;
        const displayedMessages = new Set();
        const isMobile = window.innerWidth <= 768;

        // Votre code JavaScript existant reste ici
        function initializeWebSocket() {
            // Afficher le statut de connexion
            const statusElement = document.getElementById('connection-status');
            statusElement.style.display = 'block';
            statusElement.textContent = 'Connexion...';
            statusElement.className = 'connection-status';

            socket = new WebSocket('ws://localhost:8080');
            
            socket.onopen = () => {
                console.log('Connected to WebSocket server');
                statusElement.textContent = 'Connecté';
                statusElement.className = 'connection-status connected';
                setTimeout(() => {
                    statusElement.style.display = 'none';
                }, 2000);
                
                socket.send(JSON.stringify({ type: 'set_user_id', user_id: currentUserId }));
                loadUnreadIndicators();
            };

            // ... (le reste de votre JavaScript) ...
            socket.onmessage = (event) => {
                try {
                    const message = JSON.parse(event.data);
                    if (message.type === 'set_user_id') {
                        console.log('User ID set:', message.user_id);
                        return;
                    }
                    if (message.type === 'error') {
                        console.error('Server error:', message.error);
                        displayError('Erreur serveur: ' + message.error);
                        if (message.message_id && displayedMessages.has(message.message_id)) {
                            const messageDiv = document.querySelector(`.message[data-messageId="${message.message_id}"]`);
                            if (messageDiv) {
                                messageDiv.remove();
                                displayedMessages.delete(message.message_id);
                            }
                            loadMessages(selectedUserId);
                        }
                        return;
                    }
                    if (!selectedUserId) {
                        console.log('Message ignored: no user selected');
                        loadUnreadIndicators();
                        return;
                    }
                    if (message.sender_id && message.recipient_id && message.content && message.sent_at && message.message_id) {
                        const isCurrentConversation = 
                            (message.sender_id === currentUserId && message.recipient_id === parseInt(selectedUserId)) ||
                            (message.sender_id === parseInt(selectedUserId) && message.recipient_id === currentUserId);
                        if (isCurrentConversation && !displayedMessages.has(message.message_id)) {
                            displayMessage(message);
                            displayedMessages.add(message.message_id);
                            if (message.sender_id !== currentUserId) {
                                markAsRead(selectedUserId);
                            }
                        } else {
                            console.log('Message ignored: not for current conversation or already displayed', message);
                            loadUnreadIndicators();
                        }
                    } else {
                        console.log('Invalid message format:', message);
                    }
                } catch (e) {
                    console.error('Error parsing WebSocket message:', e);
                }
            };

            socket.onclose = () => {
                console.log('Disconnected from WebSocket server. Attempting to reconnect...');
                statusElement.textContent = 'Déconnecté - Reconnexion...';
                statusElement.className = 'connection-status disconnected';
                statusElement.style.display = 'block';
                setTimeout(initializeWebSocket, 5000);
            };

            socket.onerror = (error) => {
                console.error('WebSocket error:', error);
                statusElement.textContent = 'Erreur de connexion';
                statusElement.className = 'connection-status disconnected';
                statusElement.style.display = 'block';
                displayError('Erreur de connexion au serveur de messagerie. Veuillez réessayer plus tard.');
            };
        }

        function displayMessage(message) {
            const messagesDiv = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.sender_id === currentUserId ? 'sent' : 'received'}`;
            messageDiv.dataset.messageId = message.message_id || 'temp-' + Date.now();
            messageDiv.innerHTML = `
                <p>${message.content}</p>
                <span class="timestamp">${new Date(message.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
            `;
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            hidePlaceholderMessages();
        }

        function displayError(errorMsg) {
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML = '';
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = errorMsg;
            messagesDiv.appendChild(errorDiv);
            hidePlaceholderMessages();
        }

        function displayInfo(infoMsg) {
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML = '';
            const infoDiv = document.createElement('div');
            infoDiv.className = 'info-message';
            infoDiv.textContent = infoMsg;
            messagesDiv.appendChild(infoDiv);
            hidePlaceholderMessages();
        }

        function hidePlaceholderMessages() {
            const noSelection = document.querySelector('.no-selection');
            if (noSelection) {
                noSelection.classList.add('hidden');
            }
            const errorMessage = document.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.classList.add('hidden');
            }
            const infoMessage = document.querySelector('.info-message');
            if (infoMessage) {
                infoMessage.classList.add('hidden');
            }
        }

        function resetChat() {
            selectedUserId = null;
            document.getElementById('chat-title').textContent = 'Sélectionnez un utilisateur pour commencer à discuter';
            document.getElementById('chat-input').classList.add('hidden');
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML = '';
            const noSelection = document.createElement('div');
            noSelection.className = 'no-selection';
            noSelection.textContent = 'Aucun utilisateur sélectionné';
            messagesDiv.appendChild(noSelection);
            document.querySelectorAll('.user-card').forEach(c => c.classList.remove('active'));
            displayedMessages.clear();
            
            // Sur mobile, afficher à nouveau la liste des utilisateurs
            if (isMobile) {
                document.getElementById('user-list').classList.remove('hidden-mobile');
            }
        }

        function showChat() {
            if (isMobile) {
                document.getElementById('user-list').classList.add('hidden-mobile');
            }
            document.getElementById('chat-input').classList.remove('hidden');
        }

        async function loadMessages(recipientId) {
            try {
                const response = await fetch(`get_messages.php?recipient_id=${recipientId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                }
                const messages = await response.json();
                if (messages.error) {
                    throw new Error(messages.error);
                }
                const messagesDiv = document.getElementById('chat-messages');
                messagesDiv.innerHTML = '';
                displayedMessages.clear();
                
                if (messages.length === 0) {
                    displayInfo('Les messages seront supprimés après 15 jours.');
                } else {
                    // Reverse messages to display oldest first, newest last
                    messages.reverse().forEach(message => {
                        const isValidMessage = 
                            (message.sender_id === currentUserId && message.recipient_id === parseInt(recipientId)) ||
                            (message.sender_id === parseInt(recipientId) && message.recipient_id === currentUserId);
                        if (isValidMessage && !displayedMessages.has(message.message_id || 'temp-' + message.sent_at)) {
                            displayMessage(message);
                            displayedMessages.add(message.message_id || 'temp-' + message.sent_at);
                        }
                    });
                    markAsRead(recipientId);
                }
                loadUnreadIndicators();
            } catch (error) {
                console.error('Error loading messages:', error);
                displayError('Erreur lors du chargement des messages: ' + error.message);
            }
        }

        async function markAsRead(recipientId) {
            try {
                const response = await fetch('mark_messages_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `recipient_id=${recipientId}&csrf_token=<?php echo $csrf_token; ?>`
                });
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                }
                const result = await response.json();
                if (result.success) {
                    console.log(`Messages marked as read for recipient ${recipientId}`);
                    loadUnreadIndicators();
                } else {
                    console.error('Error marking messages as read:', result.error);
                }
            } catch (error) {
                console.error('Error marking messages as read:', error);
            }
        }

        async function loadUnreadIndicators() {
            try {
                const response = await fetch('get_unread_counts.php');
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                }
                const unreadCounts = await response.json();
                if (unreadCounts.error) {
                    throw new Error(unreadCounts.error);
                }
                document.querySelectorAll('.user-card').forEach(card => {
                    const userId = parseInt(card.dataset.id);
                    card.classList.remove('unread');
                    if (unreadCounts[userId] && unreadCounts[userId] > 0) {
                        card.classList.add('unread');
                    }
                });
            } catch (error) {
                console.error('Error loading unread indicators:', error);
            }
        }

        function setActiveUser(card) {
            document.querySelectorAll('.user-card').forEach(c => c.classList.remove('active'));
            card.classList.add('active');
        }

        function sendMessage() {
            if (isSending) return;
            isSending = true;
            const input = document.getElementById('message-input');
            const content = input.value.trim();
            if (!content) {
                displayError('Veuillez entrer un message.');
                isSending = false;
                return;
            }
            if (!selectedUserId) {
                displayError('Veuillez sélectionner un utilisateur.');
                isSending = false;
                return;
            }
            if (!socket || socket.readyState !== WebSocket.OPEN) {
                displayError('Connexion au serveur perdue. Veuillez réessayer.');
                isSending = false;
                return;
            }
            const message_id = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            const message = {
                sender_id: currentUserId,
                recipient_id: parseInt(selectedUserId),
                content: content,
                sent_at: new Date().toISOString(),
                message_id: message_id
            };
            try {
                socket.send(JSON.stringify(message));
                console.log('Message sent:', message);
                input.value = '';
                // Réinitialiser la hauteur du textarea
                input.style.height = '50px';
                if (isMobile) {
                    input.style.height = '45px';
                }
                
                if (!displayedMessages.has(message_id)) {
                    displayMessage(message);
                    displayedMessages.add(message_id);
                }
            } catch (e) {
                console.error('Error sending message:', e);
                displayError('Erreur lors de l\'envoi du message: ' + e.message);
            } finally {
                isSending = false;
            }
        }

        // Gestion des événements
        document.querySelectorAll('.user-card').forEach(card => {
            card.addEventListener('click', () => {
                const userId = parseInt(card.dataset.id);
                if (selectedUserId === userId) {
                    // Close the conversation if the same user is clicked
                    resetChat();
                } else {
                    // Open a new conversation
                    selectedUserId = userId;
                    document.getElementById('chat-title').textContent = card.dataset.name;
                    setActiveUser(card);
                    showChat();
                    loadMessages(selectedUserId);
                }
            });
        });

        // Bouton de retour sur mobile
        document.getElementById('back-button').addEventListener('click', resetChat);

        // Gestion de l'envoi de message
        const sendButton = document.getElementById('send-message');
        sendButton.addEventListener('click', sendMessage);

        // Gestion de la textarea
        const messageInput = document.getElementById('message-input');
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Ajustement automatique de la hauteur de la textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
            if (this.scrollHeight > 120) {
                this.style.overflowY = 'auto';
            } else {
                this.style.overflowY = 'hidden';
            }
        });

        // Gestion du redimensionnement de la fenêtre
        window.addEventListener('resize', function() {
            // Réinitialiser l'interface si on passe du mobile au desktop ou inversement
            if ((window.innerWidth <= 768 && !isMobile) || 
                (window.innerWidth > 768 && isMobile)) {
                location.reload();
            }
        });
        

        // Initialisation
        document.addEventListener('DOMContentLoaded', () => {
            initializeWebSocket();
            
            // Cacher la liste des utilisateurs sur mobile si une conversation est ouverte
            if (isMobile && selectedUserId) {
                document.getElementById('user-list').classList.add('hidden-mobile');
            }
        });
    </script>
</body>
</html>