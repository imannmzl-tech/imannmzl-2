<?php
require_once '../config/config.php';
require_once '../config/firebase-config.php';

$room_id = $_GET['id'] ?? '';
$page_title = 'Chat Room - Chat Room Realtime';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title . ' - ' . APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            overflow: hidden;
        }
        
        .chat-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 0;
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: white;
        }
        
        .chat-input {
            background: white;
            border-top: 1px solid #dee2e6;
            padding: 1rem;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
        }
        
        .message.own {
            flex-direction: row-reverse;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin: 0 10px;
            flex-shrink: 0;
        }
        
        .message-content {
            max-width: 70%;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
        }
        
        .message.own .message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .message-sender {
            font-weight: bold;
            font-size: 0.9em;
        }
        
        .message-time {
            font-size: 0.8em;
            opacity: 0.7;
        }
        
        .message-text {
            margin: 0;
            word-wrap: break-word;
        }
        
        .message-image {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 5px;
        }
        
        .typing-indicator {
            display: none;
            padding: 10px 15px;
            color: #6c757d;
            font-style: italic;
        }
        
        .typing-indicator.show {
            display: block;
        }
        
        .input-group {
            border-radius: 25px;
            overflow: hidden;
        }
        
        .form-control {
            border: none;
            border-radius: 25px 0 0 25px;
            padding: 12px 20px;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: transparent;
        }
        
        .btn-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 0 25px 25px 0;
            color: white;
            padding: 12px 20px;
        }
        
        .btn-send:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        
        .btn-upload {
            background: #28a745;
            border: none;
            border-radius: 50%;
            color: white;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .btn-upload:hover {
            background: #218838;
            color: white;
        }
        
        .room-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .room-members {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .member-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .online-indicator {
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            position: absolute;
            bottom: 0;
            right: 0;
            border: 2px solid white;
        }
        
        .message-avatar {
            position: relative;
        }
        
        .message-avatar .online-indicator {
            width: 10px;
            height: 10px;
        }
        
        .upload-progress {
            display: none;
            margin-top: 10px;
        }
        
        .upload-progress.show {
            display: block;
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="back-btn" onclick="goBack()">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <div>
                        <h5 class="mb-0" id="roomName">Loading...</h5>
                        <small id="roomDescription">Loading room info...</small>
                    </div>
                </div>
                <div class="room-info">
                    <div class="room-members" id="roomMembers">
                        <i class="bi bi-people me-2"></i>
                        <span id="memberCount">0</span> members
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="message-avatar me-2" id="userAvatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <div class="fw-bold" id="userName">Loading...</div>
                            <small class="opacity-75">Online</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="empty-state">
                <i class="bi bi-chat-dots"></i>
                <h5>Loading messages...</h5>
                <p>Please wait while we load the chat history</p>
            </div>
        </div>
        
        <!-- Typing Indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <i class="bi bi-three-dots"></i> Someone is typing...
        </div>
        
        <!-- Chat Input -->
        <div class="chat-input">
            <div class="d-flex align-items-center">
                <button class="btn-upload" onclick="document.getElementById('imageInput').click()">
                    <i class="bi bi-image"></i>
                </button>
                <div class="input-group flex-grow-1">
                    <input type="text" class="form-control" id="messageInput" placeholder="Type your message..." maxlength="1000">
                    <button class="btn btn-send" type="button" onclick="sendMessage()">
                        <i class="bi bi-send"></i>
                    </button>
                </div>
            </div>
            
            <!-- Upload Progress -->
            <div class="upload-progress" id="uploadProgress">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted">Uploading image...</small>
            </div>
            
            <!-- Hidden file input -->
            <input type="file" id="imageInput" accept="image/*" style="display: none;">
        </div>
    </div>

    <!-- Firebase SDK v8 (Legacy) -->
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
    
    <!-- Firebase Config -->
    <script src="../assets/js/firebase-config.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const roomId = '<?php echo $room_id; ?>';
        let currentRoom = null;
        let currentUser = null;
        let messagesRef = null;
        let typingRef = null;
        let typingTimeout = null;
        let isTyping = false;
        
        // Initialize chat
        document.addEventListener('DOMContentLoaded', function() {
            if (!isAuthenticated()) {
                window.location.href = '../index.php';
                return;
            }
            
            currentUser = getCurrentUser();
            if (!currentUser) {
                window.location.href = '../index.php';
                return;
            }
            
            if (!roomId) {
                showNotification('Room ID is required', 'error');
                goBack();
                return;
            }
            
            initializeChat();
        });
        
        function initializeChat() {
            // Update user info
            updateUserInfo();
            
            // Load room info
            loadRoomInfo();
            
            // Join room
            joinRoom();
            
            // Setup message listeners
            setupMessageListeners();
            
            // Setup typing listeners
            setupTypingListeners();
            
            // Setup input handlers
            setupInputHandlers();
        }
        
        function updateUserInfo() {
            document.getElementById('userName').textContent = currentUser.displayName || currentUser.email.split('@')[0];
            document.getElementById('userAvatar').textContent = (currentUser.displayName || currentUser.email).charAt(0).toUpperCase();
        }
        
        function loadRoomInfo() {
            const roomRef = database.ref('rooms/' + roomId);
            
            roomRef.on('value', (snapshot) => {
                const room = snapshot.val();
                if (!room) {
                    showNotification('Room not found', 'error');
                    goBack();
                    return;
                }
                
                currentRoom = room;
                document.getElementById('roomName').textContent = room.name;
                document.getElementById('roomDescription').textContent = room.description || 'No description';
                document.getElementById('memberCount').textContent = Object.keys(room.members || {}).length;
                
                // Check if user is member
                if (!room.members || !room.members[currentUser.uid]) {
                    showNotification('You are not a member of this room', 'error');
                    goBack();
                    return;
                }
            });
        }
        
        async function joinRoom() {
            try {
                await joinRoom(roomId);
            } catch (error) {
                console.error('Join room error:', error);
                showNotification('Failed to join room: ' + error.message, 'error');
            }
        }
        
        function setupMessageListeners() {
            messagesRef = database.ref('messages/' + roomId);
            
            messagesRef.on('child_added', (snapshot) => {
                const message = snapshot.val();
                displayMessage({
                    id: snapshot.key,
                    ...message
                });
            });
        }
        
        function setupTypingListeners() {
            typingRef = database.ref('typing/' + roomId);
            
            typingRef.on('value', (snapshot) => {
                const typing = snapshot.val();
                if (typing) {
                    const typingUsers = Object.keys(typing).filter(uid => uid !== currentUser.uid && typing[uid]);
                    if (typingUsers.length > 0) {
                        showTypingIndicator(typingUsers);
                    } else {
                        hideTypingIndicator();
                    }
                } else {
                    hideTypingIndicator();
                }
            });
        }
        
        function setupInputHandlers() {
            const messageInput = document.getElementById('messageInput');
            
            // Send message on Enter
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            
            // Typing indicator
            messageInput.addEventListener('input', function() {
                if (!isTyping) {
                    isTyping = true;
                    updateTypingStatus(true);
                }
                
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    isTyping = false;
                    updateTypingStatus(false);
                }, 1000);
            });
            
            // Image upload
            document.getElementById('imageInput').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    uploadImage(file);
                }
            });
        }
        
        function displayMessage(message) {
            const messagesContainer = document.getElementById('chatMessages');
            
            // Remove empty state if exists
            const emptyState = messagesContainer.querySelector('.empty-state');
            if (emptyState) {
                emptyState.remove();
            }
            
            const messageElement = document.createElement('div');
            messageElement.className = `message ${message.senderId === currentUser.uid ? 'own' : ''}`;
            messageElement.id = 'message-' + message.id;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = (message.senderName || 'User').charAt(0).toUpperCase();
            
            const content = document.createElement('div');
            content.className = 'message-content';
            
            const header = document.createElement('div');
            header.className = 'message-header';
            header.innerHTML = `
                <span class="message-sender">${message.senderName || 'User'}</span>
                <span class="message-time">${formatTimestamp(message.timestamp)}</span>
            `;
            
            const text = document.createElement('p');
            text.className = 'message-text';
            text.textContent = message.text || '';
            
            content.appendChild(header);
            content.appendChild(text);
            
            if (message.type === 'image' && message.imageUrl) {
                const image = document.createElement('img');
                image.src = message.imageUrl;
                image.className = 'message-image';
                image.alt = 'Shared image';
                content.appendChild(image);
            }
            
            messageElement.appendChild(avatar);
            messageElement.appendChild(content);
            
            messagesContainer.appendChild(messageElement);
            
            // Scroll to bottom
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function showTypingIndicator(typingUsers) {
            const indicator = document.getElementById('typingIndicator');
            const userNames = typingUsers.map(uid => {
                // Get user name from room members or use 'Someone'
                return 'Someone';
            });
            
            indicator.innerHTML = `<i class="bi bi-three-dots"></i> ${userNames.join(', ')} ${userNames.length === 1 ? 'is' : 'are'} typing...`;
            indicator.classList.add('show');
        }
        
        function hideTypingIndicator() {
            document.getElementById('typingIndicator').classList.remove('show');
        }
        
        function updateTypingStatus(typing) {
            if (typingRef) {
                typingRef.child(currentUser.uid).set(typing);
            }
        }
        
        async function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const text = messageInput.value.trim();
            
            if (!text) return;
            
            try {
                await sendMessage(roomId, text);
                messageInput.value = '';
                updateTypingStatus(false);
            } catch (error) {
                console.error('Send message error:', error);
                showNotification('Failed to send message: ' + error.message, 'error');
            }
        }
        
        async function uploadImage(file) {
            if (!file) return;
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                showNotification('File size must be less than 5MB', 'error');
                return;
            }
            
            // Validate file type
            if (!file.type.startsWith('image/')) {
                showNotification('Please select an image file', 'error');
                return;
            }
            
            try {
                // Show upload progress
                const progressContainer = document.getElementById('uploadProgress');
                const progressBar = progressContainer.querySelector('.progress-bar');
                progressContainer.classList.add('show');
                
                // Upload image
                const imageUrl = await uploadImage(file, roomId);
                
                // Send message with image
                await sendMessage(roomId, '', imageUrl);
                
                // Hide progress
                progressContainer.classList.remove('show');
                progressBar.style.width = '0%';
                
                showNotification('Image uploaded successfully!', 'success');
            } catch (error) {
                console.error('Upload error:', error);
                showNotification('Failed to upload image: ' + error.message, 'error');
                
                // Hide progress
                document.getElementById('uploadProgress').classList.remove('show');
            }
        }
        
        function goBack() {
            const userRole = getUserRole();
            if (userRole === 'teacher') {
                window.location.href = '../dashboard/teacher/index.php';
            } else {
                window.location.href = '../dashboard/student/index.php';
            }
        }
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (typingRef) {
                typingRef.child(currentUser.uid).remove();
            }
        });
    </script>
</body>
</html>
