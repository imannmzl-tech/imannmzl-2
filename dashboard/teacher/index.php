<?php
require_once '../../config/config.php';

$page_title = 'Teacher Dashboard - Chat Room Realtime';
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
            border: 1px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .room-card {
            border-left: 4px solid #007bff;
        }
        
        .room-card .card-header {
            background: #007bff;
            color: white;
            border-radius: 12px 12px 0 0;
        }
        
        .btn-create-room {
            background: #007bff;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-create-room:hover {
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
                            <a class="nav-link" href="#" id="createRoomTab">
                                <i class="bi bi-plus-circle me-2"></i>Create Room
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" id="usersTab">
                                <i class="bi bi-people me-2"></i>Users
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
                                <small class="text-muted">Teacher</small>
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
                        <h2><i class="bi bi-house me-2"></i>Teacher Dashboard</h2>
                        <button class="btn btn-create-room" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                            <i class="bi bi-plus-circle me-2"></i>Create New Room
                        </button>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="totalRooms">0</h3>
                                    <p class="mb-0">Total Rooms</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="totalUsers">0</h3>
                                    <p class="mb-0">Total Users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="activeUsers">0</h3>
                                    <p class="mb-0">Active Users</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stats-card">
                                <div class="card-body text-center">
                                    <h3 id="totalMessages">0</h3>
                                    <p class="mb-0">Total Messages</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Rooms -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-door-open me-2"></i>Recent Rooms</h5>
                                </div>
                                <div class="card-body">
                                    <div id="recentRooms">
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
                                    <h5><i class="bi bi-people me-2"></i>Online Users</h5>
                                </div>
                                <div class="card-body">
                                    <div id="onlineUsers">
                                        <div class="text-center text-muted">
                                            <i class="bi bi-hourglass-split"></i>
                                            <p>Loading users...</p>
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
                        <button class="btn btn-create-room" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                            <i class="bi bi-plus-circle me-2"></i>Create New Room
                        </button>
                    </div>
                    
                    <div class="row" id="roomsList">
                        <div class="text-center text-muted">
                            <i class="bi bi-hourglass-split"></i>
                            <p>Loading rooms...</p>
                        </div>
                    </div>
                </div>
                
                <!-- Create Room Tab -->
                <div id="createRoomContent" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-plus-circle me-2"></i>Create New Room</h2>
                    </div>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <form id="createRoomForm">
                                        <div class="mb-3">
                                            <label for="roomName" class="form-label">Room Name</label>
                                            <input type="text" class="form-control" id="roomName" name="name" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="roomDescription" class="form-label">Description</label>
                                            <textarea class="form-control" id="roomDescription" name="description" rows="3"></textarea>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-create-room">
                                                <i class="bi bi-plus-circle me-2"></i>Create Room
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Tab -->
                <div id="usersContent" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-people me-2"></i>All Users</h2>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div id="allUsers">
                                <div class="text-center text-muted">
                                    <i class="bi bi-hourglass-split"></i>
                                    <p>Loading users...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Room Modal -->
    <div class="modal fade" id="createRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="modalCreateRoomForm">
                        <div class="mb-3">
                            <label for="modalRoomName" class="form-label">Room Name</label>
                            <input type="text" class="form-control" id="modalRoomName" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modalRoomDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="modalRoomDescription" name="description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="createRoomFromModal()">Create Room</button>
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
        let allUsers = [];
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            if (!isAuthenticated()) {
                window.location.href = '../../index.php';
                return;
            }
            
            // Check if user is teacher
            getUserRole().then(role => {
                if (role !== 'teacher') {
                    window.location.href = '../student/index.php';
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
                    if (room.createdBy === user.uid) {
                        rooms.push({
                            id: childSnapshot.key,
                            ...room
                        });
                    }
                });
                
                userRooms = rooms;
                displayRooms(rooms);
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
                displayAllUsers(users);
                displayOnlineUsers(users);
                updateStats();
            });
        }
        
        function displayRooms(rooms) {
            const container = document.getElementById('roomsList');
            const recentContainer = document.getElementById('recentRooms');
            
            if (rooms.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center text-muted">
                        <i class="bi bi-door-closed"></i>
                        <p>No rooms created yet</p>
                        <button class="btn btn-create-room" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                            <i class="bi bi-plus-circle me-2"></i>Create Your First Room
                        </button>
                    </div>
                `;
                recentContainer.innerHTML = `
                    <div class="text-center text-muted">
                        <i class="bi bi-door-closed"></i>
                        <p>No rooms created yet</p>
                    </div>
                `;
                return;
            }
            
            // Sort by creation date
            rooms.sort((a, b) => b.createdAt - a.createdAt);
            
            // Display all rooms
            container.innerHTML = rooms.map(room => `
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
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRoom('${room.id}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Display recent rooms (max 5)
            const recentRooms = rooms.slice(0, 5);
            recentContainer.innerHTML = recentRooms.map(room => `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <h6 class="mb-0">${room.name}</h6>
                        <small class="text-muted">${formatTimestamp(room.createdAt)}</small>
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="joinRoom('${room.id}')">
                        <i class="bi bi-door-open me-1"></i>Enter
                    </button>
                </div>
            `).join('');
        }
        
        function displayAllUsers(users) {
            const container = document.getElementById('allUsers');
            
            container.innerHTML = users.map(user => `
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-3 position-relative">
                            ${user.name.charAt(0).toUpperCase()}
                            ${user.isOnline ? '<div class="online-indicator"></div>' : ''}
                        </div>
                        <div>
                            <h6 class="mb-0">${user.name}</h6>
                            <small class="text-muted">${user.email} • ${user.role}</small>
                        </div>
                    </div>
                    <span class="badge bg-${user.role === 'teacher' ? 'primary' : 'success'}">
                        ${user.role}
                    </span>
                </div>
            `).join('');
        }
        
        function displayOnlineUsers(users) {
            const container = document.getElementById('onlineUsers');
            const onlineUsers = users.filter(user => user.isOnline);
            
            container.innerHTML = onlineUsers.map(user => `
                <div class="member-item">
                    <div class="user-avatar me-3 position-relative">
                        ${user.name.charAt(0).toUpperCase()}
                        <div class="online-indicator"></div>
                    </div>
                    <div>
                        <h6 class="mb-0">${user.name}</h6>
                        <small class="text-muted">${user.role}</small>
                    </div>
                </div>
            `).join('');
        }
        
        function updateStats() {
            document.getElementById('totalRooms').textContent = userRooms.length;
            document.getElementById('totalUsers').textContent = allUsers.length;
            document.getElementById('activeUsers').textContent = allUsers.filter(user => user.isOnline).length;
            
            // Count total messages
            let totalMessages = 0;
            userRooms.forEach(room => {
                const messagesRef = database.ref('messages/' + room.id);
                messagesRef.once('value', (snapshot) => {
                    totalMessages += snapshot.numChildren();
                    document.getElementById('totalMessages').textContent = totalMessages;
                });
            });
        }
        
        function setupTabNavigation() {
            document.getElementById('dashboardTab').addEventListener('click', () => showTab('dashboard'));
            document.getElementById('roomsTab').addEventListener('click', () => showTab('rooms'));
            document.getElementById('createRoomTab').addEventListener('click', () => showTab('createRoom'));
            document.getElementById('usersTab').addEventListener('click', () => showTab('users'));
        }
        
        function showTab(tabName) {
            // Hide all content
            document.getElementById('dashboardContent').style.display = 'none';
            document.getElementById('roomsContent').style.display = 'none';
            document.getElementById('createRoomContent').style.display = 'none';
            document.getElementById('usersContent').style.display = 'none';
            
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
        
        // Create room functions
        document.getElementById('createRoomForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('roomName').value;
            const description = document.getElementById('roomDescription').value;
            
            try {
                const roomId = await createRoom(name, description);
                showNotification('Room created successfully!', 'success');
                document.getElementById('createRoomForm').reset();
                showTab('rooms');
            } catch (error) {
                console.error('Create room error:', error);
                showNotification('Failed to create room: ' + error.message, 'error');
            }
        });
        
        function createRoomFromModal() {
            const name = document.getElementById('modalRoomName').value;
            const description = document.getElementById('modalRoomDescription').value;
            
            if (!name.trim()) {
                showNotification('Room name is required', 'error');
                return;
            }
            
            createRoom(name, description).then(roomId => {
                showNotification('Room created successfully!', 'success');
                document.getElementById('modalCreateRoomForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('createRoomModal')).hide();
                showTab('rooms');
            }).catch(error => {
                console.error('Create room error:', error);
                showNotification('Failed to create room: ' + error.message, 'error');
            });
        }
        
        function joinRoom(roomId) {
            window.location.href = `../../chat/room.php?id=${roomId}`;
        }
        
        function deleteRoom(roomId) {
            if (confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
                const roomRef = database.ref('rooms/' + roomId);
                const messagesRef = database.ref('messages/' + roomId);
                
                // Delete room and messages
                roomRef.remove();
                messagesRef.remove();
                
                showNotification('Room deleted successfully', 'success');
            }
        }
    </script>
</body>
</html>
