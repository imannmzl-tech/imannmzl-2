<?php
require_once '../../config/config.php';

$page_title = 'Student Dashboard - Chat Room Realtime';
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
        }
        
        .sidebar {
            background: #ffffff;
            min-height: 100vh;
            color: #333;
            border-right: 1px solid #e9ecef;
        }
        
        .sidebar .nav-link {
            color: #6c757d;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #007bff;
            color: white;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .room-card {
            border-left: 4px solid #007bff;
        }
        
        .room-card .card-header {
            background: #007bff;
            color: white;
            border-radius: 12px 12px 0 0;
        }
        
        .btn-join-room {
            background: #007bff;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-join-room:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
            color: white;
        }
        
        .stats-card {
            background: #007bff;
            color: white;
            border-radius: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .online-indicator {
            width: 12px;
            height: 12px;
            background: #28a745;
            border-radius: 50%;
            position: absolute;
            bottom: 0;
            right: 0;
            border: 2px solid white;
        }
        
        .room-members {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .member-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .member-item:last-child {
            border-bottom: none;
        }
        
        .join-room-form {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3">
                    <h4><i class="bi bi-chat-dots me-2"></i>Chat Room</h4>
                    <hr>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" id="dashboardTab">
                                <i class="bi bi-house me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="roomsTab">
                                <i class="bi bi-door-open me-2"></i>My Rooms
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="joinRoomTab">
                                <i class="bi bi-plus-circle me-2"></i>Join Room
                            </a>
                        </li>
                    </ul>
                    
                    <hr>
                    
                    <div class="user-info">
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-3" id="userAvatar">
                                <i class="bi bi-person"></i>
                            </div>
                            <div>
                                <div class="fw-bold" id="userName">Loading...</div>
                                <small class="text-muted">Student</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <button class="btn btn-outline-light w-100" onclick="signOut()">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Dashboard Tab -->
                <div id="dashboardContent">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-house me-2"></i>Student Dashboard</h2>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="joinedRooms">0</h3>
                                    <p class="mb-0">Joined Rooms</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="totalMessages">0</h3>
                                    <p class="mb-0">Messages Sent</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="onlineFriends">0</h3>
                                    <p class="mb-0">Online Friends</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="activeRooms">0</h3>
                                    <p class="mb-0">Active Rooms</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-door-open me-2"></i>My Rooms</h5>
                                </div>
                                <div class="card-body">
                                    <div id="myRooms">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-hourglass-split"></i>
                                            <p>Loading rooms...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-people me-2"></i>Online Friends</h5>
                                </div>
                                <div class="card-body">
                                    <div id="onlineFriends">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-hourglass-split"></i>
                                            <p>Loading friends...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rooms Tab -->
                <div id="roomsContent" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-door-open me-2"></i>My Rooms</h2>
                    </div>
                    
                    <div class="row" id="roomsList">
                        <div class="text-center text-muted">
                            <i class="bi bi-hourglass-split"></i>
                            <p>Loading rooms...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Join Room Tab -->
                <div id="joinRoomContent" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-plus-circle me-2"></i>Join Room</h2>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="join-room-form">
                                <form id="joinRoomForm">
                                    <div class="mb-3">
                                        <label for="roomCode" class="form-label">Room Code</label>
                                        <input type="text" class="form-control" id="roomCode" name="roomCode" placeholder="Enter room code" required>
                                        <div class="form-text">Ask your teacher for the room code</div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-join-room">
                                            <i class="bi bi-door-open me-2"></i>Join Room
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Available Rooms -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-list me-2"></i>Available Rooms</h5>
                                </div>
                                <div class="card-body">
                                    <div id="availableRooms">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-hourglass-split"></i>
                                            <p>Loading available rooms...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Firebase SDK v8 (Legacy) -->
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
    
    <!-- Firebase Config (Direct - No Redirect Logic) -->
    <script>
        // Firebase Configuration (Direct - No redirect logic)
        const firebaseConfig = {
            apiKey: "AIzaSyBsWwXT8vZ3Y_G_HxLXCOucy8trXZ8vXog",
            authDomain: "chat-room-realtime.firebaseapp.com",
            databaseURL: "https://chat-room-realtime-default-rtdb.asia-southeast1.firebasedatabase.app",
            projectId: "chat-room-realtime",
            storageBucket: "chat-room-realtime.firebasestorage.app",
            messagingSenderId: "952502420326",
            appId: "1:952502420326:web:a8d939bbb6c3dbefdbbea7"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const auth = firebase.auth();
        const database = firebase.database();
        
        // Global variables
        let currentUser = null;
        
        // Auth state listener (simplified)
        auth.onAuthStateChanged((user) => {
            if (user) {
                currentUser = user;
                console.log('✅ User authenticated:', user.email);
            } else {
                currentUser = null;
                console.log('❌ User logged out');
                // Redirect to login page
                window.location.href = '../../login.php';
            }
        });
        
        // Helper functions
        function isAuthenticated() {
            return currentUser !== null;
        }
        
        function signOut() {
            auth.signOut().then(() => {
                window.location.href = '../../login.php';
            }).catch((error) => {
                console.error('Sign out error:', error);
            });
        }
    </script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let userRooms = [];
        let allRooms = [];
        let allUsers = [];
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            if (!isAuthenticated()) {
                window.location.href = '../../index.php';
                return;
            }
            
            // Check if user is student
            getUserRole().then(role => {
                if (role !== 'student') {
                    window.location.href = '../teacher/index.php';
                    return;
                }
                
                initializeDashboard();
            });
        });
        
        function initializeDashboard() {
            // Update user info
            updateUserInfo();
            
            // Load dashboard data
            loadDashboardData();
            
            // Setup tab navigation
            setupTabNavigation();
            
            // Setup real-time listeners
            setupRealtimeListeners();
        }
        
        function updateUserInfo() {
            const user = getCurrentUser();
            if (user) {
                document.getElementById('userName').textContent = user.displayName || user.email.split('@')[0];
                document.getElementById('userAvatar').textContent = (user.displayName || user.email).charAt(0).toUpperCase();
            }
        }
        
        function loadDashboardData() {
            loadUserRooms();
            loadAllRooms();
            loadAllUsers();
            loadStats();
        }
        
        function loadUserRooms() {
            const user = getCurrentUser();
            if (!user) return;
            
            const roomsRef = database.ref('rooms');
            
            roomsRef.on('value', (snapshot) => {
                const rooms = [];
                snapshot.forEach((childSnapshot) => {
                    const room = childSnapshot.val();
                    if (room.members && room.members[user.uid]) {
                        rooms.push({
                            id: childSnapshot.key,
                            ...room
                        });
                    }
                });
                
                userRooms = rooms;
                displayUserRooms(rooms);
                updateStats();
            });
        }
        
        function loadAllRooms() {
            const roomsRef = database.ref('rooms');
            
            roomsRef.on('value', (snapshot) => {
                const rooms = [];
                snapshot.forEach((childSnapshot) => {
                    rooms.push({
                        id: childSnapshot.key,
                        ...childSnapshot.val()
                    });
                });
                
                allRooms = rooms;
                displayAvailableRooms(rooms);
                updateStats();
            });
        }
        
        function loadAllUsers() {
            const usersRef = database.ref('users');
            
            usersRef.on('value', (snapshot) => {
                const users = [];
                snapshot.forEach((childSnapshot) => {
                    users.push({
                        id: childSnapshot.key,
                        ...childSnapshot.val()
                    });
                });
                
                allUsers = users;
                displayOnlineFriends(users);
                updateStats();
            });
        }
        
        function displayUserRooms(rooms) {
            const container = document.getElementById('myRooms');
            const roomsListContainer = document.getElementById('roomsList');
            
            if (rooms.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-door-closed"></i>
                        <p>You haven't joined any rooms yet</p>
                        <button class="btn btn-join-room" onclick="showTab('joinRoom')">
                            <i class="bi bi-plus-circle me-2"></i>Join a Room
                        </button>
                    </div>
                `;
                roomsListContainer.innerHTML = `
                    <div class="col-12 text-center text-muted">
                        <i class="bi bi-door-closed"></i>
                        <p>You haven't joined any rooms yet</p>
                        <button class="btn btn-join-room" onclick="showTab('joinRoom')">
                            <i class="bi bi-plus-circle me-2"></i>Join a Room
                        </button>
                    </div>
                `;
                return;
            }
            
            // Sort by last activity
            rooms.sort((a, b) => (b.lastActivity || b.createdAt) - (a.lastActivity || a.createdAt));
            
            // Display in dashboard
            container.innerHTML = rooms.map(room => `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <h6 class="mb-0">${room.name}</h6>
                        <small class="text-muted">${room.description || 'No description'}</small>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary" onclick="joinRoom('${room.id}')">
                            <i class="bi bi-door-open me-1"></i>Enter
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="leaveRoom('${room.id}')">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </div>
            `).join('');
            
            // Display in rooms list
            roomsListContainer.innerHTML = rooms.map(room => `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card room-card">
                        <div class="card-header">
                            <h6 class="mb-0">${room.name}</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">${room.description || 'No description'}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-people me-1"></i>
                                    ${Object.keys(room.members || {}).length} members
                                </small>
                                <div>
                                    <button class="btn btn-sm btn-primary" onclick="joinRoom('${room.id}')">
                                        <i class="bi bi-door-open me-1"></i>Enter
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="leaveRoom('${room.id}')">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function displayAvailableRooms(rooms) {
            const container = document.getElementById('availableRooms');
            const user = getCurrentUser();
            
            // Filter rooms that user hasn't joined
            const availableRooms = rooms.filter(room => !room.members || !room.members[user.uid]);
            
            if (availableRooms.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-door-closed"></i>
                        <p>No available rooms to join</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = availableRooms.map(room => `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <h6 class="mb-0">${room.name}</h6>
                        <small class="text-muted">${room.description || 'No description'}</small>
                        <br>
                        <small class="text-muted">
                            <i class="bi bi-people me-1"></i>
                            ${Object.keys(room.members || {}).length} members
                        </small>
                    </div>
                    <button class="btn btn-sm btn-join-room" onclick="joinRoomById('${room.id}')">
                        <i class="bi bi-plus-circle me-1"></i>Join
                    </button>
                </div>
            `).join('');
        }
        
        function displayOnlineFriends(users) {
            const container = document.getElementById('onlineFriends');
            const user = getCurrentUser();
            
            // Filter online users (excluding current user)
            const onlineFriends = users.filter(u => u.isOnline && u.id !== user.uid);
            
            if (onlineFriends.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-person-x"></i>
                        <p>No friends online</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = onlineFriends.map(friend => `
                <div class="member-item">
                    <div class="user-avatar me-3 position-relative">
                        ${friend.name.charAt(0).toUpperCase()}
                        <div class="online-indicator"></div>
                    </div>
                    <div>
                        <h6 class="mb-0">${friend.name}</h6>
                        <small class="text-muted">${friend.role}</small>
                    </div>
                </div>
            `).join('');
        }
        
        function updateStats() {
            document.getElementById('joinedRooms').textContent = userRooms.length;
            document.getElementById('onlineFriends').textContent = allUsers.filter(user => user.isOnline && user.id !== getCurrentUser().uid).length;
            document.getElementById('activeRooms').textContent = userRooms.filter(room => room.members && Object.keys(room.members).length > 1).length;
            
            // Count total messages sent by user
            let totalMessages = 0;
            const user = getCurrentUser();
            userRooms.forEach(room => {
                const messagesRef = database.ref('messages/' + room.id);
                messagesRef.once('value', (snapshot) => {
                    snapshot.forEach((childSnapshot) => {
                        const message = childSnapshot.val();
                        if (message.senderId === user.uid) {
                            totalMessages++;
                        }
                    });
                    document.getElementById('totalMessages').textContent = totalMessages;
                });
            });
        }
        
        function setupTabNavigation() {
            document.getElementById('dashboardTab').addEventListener('click', () => showTab('dashboard'));
            document.getElementById('roomsTab').addEventListener('click', () => showTab('rooms'));
            document.getElementById('joinRoomTab').addEventListener('click', () => showTab('joinRoom'));
        }
        
        function showTab(tabName) {
            // Hide all content
            document.getElementById('dashboardContent').style.display = 'none';
            document.getElementById('roomsContent').style.display = 'none';
            document.getElementById('joinRoomContent').style.display = 'none';
            
            // Remove active class from all tabs
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            
            // Show selected content
            document.getElementById(tabName + 'Content').style.display = 'block';
            document.getElementById(tabName + 'Tab').classList.add('active');
        }
        
        function setupRealtimeListeners() {
            // Listen for new messages
            userRooms.forEach(room => {
                const messagesRef = database.ref('messages/' + room.id);
                messagesRef.on('child_added', () => {
                    updateStats();
                });
            });
        }
        
        // Join room functions
        document.getElementById('joinRoomForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const roomCode = document.getElementById('roomCode').value.trim();
            
            if (!roomCode) {
                showNotification('Please enter a room code', 'error');
                return;
            }
            
            try {
                await joinRoomById(roomCode);
                showNotification('Successfully joined room!', 'success');
                document.getElementById('joinRoomForm').reset();
                showTab('rooms');
            } catch (error) {
                console.error('Join room error:', error);
                showNotification('Failed to join room: ' + error.message, 'error');
            }
        });
        
        async function joinRoomById(roomId) {
            const user = getCurrentUser();
            if (!user) return;
            
            // Check if room exists
            const roomRef = database.ref('rooms/' + roomId);
            const roomSnapshot = await roomRef.once('value');
            
            if (!roomSnapshot.exists()) {
                throw new Error('Room not found');
            }
            
            // Join room
            await joinRoom(roomId);
        }
        
        function joinRoom(roomId) {
            window.location.href = `../../chat/room.php?id=${roomId}`;
        }
        
        async function leaveRoom(roomId) {
            if (confirm('Are you sure you want to leave this room?')) {
                try {
                    await leaveRoom(roomId);
                    showNotification('Left room successfully', 'success');
                } catch (error) {
                    console.error('Leave room error:', error);
                    showNotification('Failed to leave room: ' + error.message, 'error');
                }
            }
        }
    </script>
</body>
</html>
