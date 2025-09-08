# 🔗 PANDUAN IMPLEMENTASI SISTEM HYBRID
## PHP Authentication + Firebase Chat

---

## 🎯 **KONSEP SISTEM HYBRID**

**Yang menggunakan PHP + MySQL:**
- ✅ **User Authentication** (login, register, session)
- ✅ **User Management** (profile, role, online status)

**Yang tetap menggunakan Firebase:**
- 🔥 **Real-time Chat** (messages, typing indicators)
- 🔥 **Room Management** (create, join, leave rooms)
- 🔥 **File Sharing** (images, real-time sync)

**Keuntungan Sistem Hybrid:**
- 🚀 **Authentication lebih cepat** (PHP session)
- 💰 **Hemat biaya** (hanya chat yang pakai Firebase)
- 🛡️ **Security lebih baik** (user data di server Anda)
- 🔄 **Real-time tetap smooth** (Firebase untuk chat)

---

## 📋 **LANGKAH 1: SETUP DATABASE (HANYA AUTHENTICATION)**

### 1.1 Import Database Schema Baru
1. **Login ke phpMyAdmin** di cPanel
2. **Pilih database**: `n1567943_chat-room-realtime_db`
3. **Klik tab "Import"**
4. **Choose File** → pilih file `database-auth-only.sql` (bukan yang lama)
5. **Klik "Go"**

**Database ini hanya akan membuat 2 tabel:**
- `users` - untuk authentication
- `user_sessions` - untuk session management

### 1.2 Verifikasi Database
Setelah import, cek di phpMyAdmin:
- ✅ Tabel `users` ada (dengan 2 akun demo)
- ✅ Tabel `user_sessions` ada
- ✅ **TIDAK ADA** tabel rooms, messages, dll (tetap pakai Firebase)

---

## 📋 **LANGKAH 2: UPDATE FILE APLIKASI**

### 2.1 File yang Perlu Diupload/Update:

```
📁 chat-room-realtime/
├── 📁 api/
│   └── auth.php (sudah diupdate)
├── 📁 assets/js/
│   └── hybrid-auth.js (file baru)
├── 📁 config/
│   ├── auth-hybrid.php (file baru)
│   └── database.php (sudah diupdate dengan kredensial Anda)
├── database-auth-only.sql (file baru)
└── IMPLEMENTASI-HYBRID.md (panduan ini)
```

### 2.2 Update Halaman Login
Ganti bagian JavaScript di `login.php`:

```html
<!-- Ganti Firebase scripts dengan Hybrid Auth -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
<script src="assets/js/hybrid-auth.js"></script>

<script>
// Login form handler
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    if (!email || !password) {
        hybridAuth.showNotification('Please fill in all fields', 'error');
        return;
    }
    
    try {
        hybridAuth.showNotification('Signing in...', 'info');
        
        const result = await hybridAuth.login(email, password);
        
        if (result.success) {
            hybridAuth.showNotification('Login successful! Redirecting...', 'success');
            // Auto redirect akan dilakukan oleh hybridAuth.onAuthStateChange
        } else {
            hybridAuth.showNotification(result.message, 'error');
        }
    } catch (error) {
        hybridAuth.showNotification('Login failed: ' + error.message, 'error');
    }
});
</script>
```

### 2.3 Update Halaman Register
Sama seperti login, ganti JavaScript di `register.php`:

```javascript
// Register form handler
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const role = document.getElementById('selectedRole').value;
    
    // Validation
    if (!name || !email || !password || !confirmPassword || !role) {
        hybridAuth.showNotification('Please fill in all fields', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        hybridAuth.showNotification('Passwords do not match', 'error');
        return;
    }
    
    if (password.length < 6) {
        hybridAuth.showNotification('Password must be at least 6 characters', 'error');
        return;
    }
    
    try {
        hybridAuth.showNotification('Creating account...', 'info');
        
        const result = await hybridAuth.register(name, email, password, role);
        
        if (result.success) {
            hybridAuth.showNotification('Registration successful! Redirecting...', 'success');
            // Auto redirect akan dilakukan oleh hybridAuth.onAuthStateChange
        } else {
            hybridAuth.showNotification(result.message, 'error');
        }
    } catch (error) {
        hybridAuth.showNotification('Registration failed: ' + error.message, 'error');
    }
});
```

### 2.4 Update Dashboard Pages
Di `dashboard/teacher/index.php` dan `dashboard/student/index.php`, tambahkan di bagian atas:

```php
<?php
require_once '../../config/auth-hybrid.php';

// Require authentication
requireLogin();

// Require specific role
if (basename(dirname(__FILE__)) === 'teacher') {
    requireRole('teacher');
} else {
    requireRole('student');
}

$page_title = 'Dashboard - Chat Room Realtime';
$currentUser = getCurrentUser();
?>
```

Dan ganti JavaScript:

```html
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
<script src="../../assets/js/hybrid-auth.js"></script>

<script>
// Update user info from PHP
function updateUserInfo() {
    const user = <?php echo json_encode($currentUser); ?>;
    document.getElementById('userName').textContent = user.name;
    document.getElementById('userAvatar').textContent = user.name.charAt(0).toUpperCase();
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    updateUserInfo();
    loadDashboardData();
});

// Load dashboard data
async function loadDashboardData() {
    // Get user rooms from Firebase
    hybridAuth.getUserRooms((rooms) => {
        displayRooms(rooms);
        updateStats(rooms);
    });
}

// Create room function
async function createRoom(name, description) {
    try {
        const roomId = await hybridAuth.createRoom(name, description);
        hybridAuth.showNotification('Room created successfully!', 'success');
        return roomId;
    } catch (error) {
        hybridAuth.showNotification('Failed to create room: ' + error.message, 'error');
        throw error;
    }
}

// Logout function
async function signOut() {
    try {
        await hybridAuth.logout();
        window.location.href = '../../login.php';
    } catch (error) {
        console.error('Logout error:', error);
        window.location.href = '../../login.php';
    }
}
</script>
```

### 2.5 Update Chat Room
Di `chat/room.php`:

```php
<?php
require_once '../config/auth-hybrid.php';

// Require login
requireLogin();

$room_id = $_GET['id'] ?? '';
if (empty($room_id)) {
    $user = getCurrentUser();
    $redirectUrl = $user['role'] === 'teacher' ? '../dashboard/teacher/index.php' : '../dashboard/student/index.php';
    header("Location: $redirectUrl");
    exit;
}

$page_title = 'Chat Room - Chat Room Realtime';
$currentUser = getCurrentUser();
?>

<!-- HTML tetap sama -->

<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
<script src="../assets/js/hybrid-auth.js"></script>

<script>
const roomId = '<?php echo $room_id; ?>';
const currentUser = <?php echo json_encode($currentUser); ?>;

// Initialize chat
document.addEventListener('DOMContentLoaded', function() {
    initializeChat();
});

async function initializeChat() {
    // Update user info
    document.getElementById('userName').textContent = currentUser.name;
    document.getElementById('userAvatar').textContent = currentUser.name.charAt(0).toUpperCase();
    
    // Join room in Firebase
    try {
        await hybridAuth.joinRoom(roomId);
        console.log('✅ Joined room:', roomId);
    } catch (error) {
        console.error('Failed to join room:', error);
    }
    
    // Load room info and messages
    loadRoomInfo();
    setupMessageListeners();
}

function loadRoomInfo() {
    // Get room info from Firebase
    const roomRef = hybridAuth.firebase.database.ref('rooms/' + roomId);
    roomRef.on('value', (snapshot) => {
        const room = snapshot.val();
        if (room) {
            document.getElementById('roomName').textContent = room.name;
            document.getElementById('roomDescription').textContent = room.description || 'No description';
            
            const memberCount = Object.keys(room.members || {}).length;
            document.getElementById('memberCount').textContent = memberCount;
        }
    });
}

function setupMessageListeners() {
    // Listen for messages from Firebase
    hybridAuth.getRoomMessages(roomId, (messages) => {
        displayMessages(messages);
    });
}

function displayMessages(messages) {
    const container = document.getElementById('chatMessages');
    container.innerHTML = '';
    
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-chat-dots"></i>
                <h5>No messages yet</h5>
                <p>Start the conversation!</p>
            </div>
        `;
        return;
    }
    
    messages.forEach(message => {
        displayMessage(message);
    });
}

function displayMessage(message) {
    const container = document.getElementById('chatMessages');
    const currentUserFirebase = hybridAuth.getCurrentUserForFirebase();
    
    const messageElement = document.createElement('div');
    messageElement.className = `message ${message.senderId === currentUserFirebase.uid ? 'own' : ''}`;
    messageElement.innerHTML = `
        <div class="message-avatar">
            ${message.senderName.charAt(0).toUpperCase()}
        </div>
        <div class="message-content">
            <div class="message-header">
                <span class="message-sender">${message.senderName}</span>
                <span class="message-time">${hybridAuth.formatTimestamp(message.timestamp)}</span>
            </div>
            <p class="message-text">${message.text || ''}</p>
            ${message.type === 'image' && message.imageUrl ? 
                `<img src="${message.imageUrl}" class="message-image" alt="Shared image">` : ''}
        </div>
    `;
    
    container.appendChild(messageElement);
    container.scrollTop = container.scrollHeight;
}

// Send message function
async function sendChatMessage() {
    const messageInput = document.getElementById('messageInput');
    const text = messageInput.value.trim();
    
    if (!text) return;
    
    try {
        await hybridAuth.sendMessage(roomId, text);
        messageInput.value = '';
    } catch (error) {
        hybridAuth.showNotification('Failed to send message: ' + error.message, 'error');
    }
}

// Setup input handlers
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendChatMessage();
    }
});
</script>
```

---

## 📋 **LANGKAH 3: TESTING SISTEM HYBRID**

### 3.1 Test Authentication (PHP)
1. **Akses**: `https://domain-anda.com/chat-room-realtime/login.php`
2. **Login dengan akun demo**:
   - Email: `teacher@test.com`
   - Password: `password123`
3. **Harus redirect** ke teacher dashboard
4. **Cek di phpMyAdmin** → tabel `users` → field `is_online` harus jadi `1`

### 3.2 Test Registration (PHP)
1. **Register akun baru**
2. **Harus auto-login** dan redirect ke dashboard
3. **Cek di database** → user baru harus muncul di tabel `users`

### 3.3 Test Chat (Firebase)
1. **Login sebagai teacher** → create room baru
2. **Login sebagai student** di tab lain → join room
3. **Kirim pesan** dari kedua akun
4. **Harus real-time** (Firebase)

### 3.4 Test Logout (PHP)
1. **Logout** dari dashboard
2. **Harus redirect** ke login
3. **Cek database** → `is_online` harus jadi `0`

---

## 🎉 **SISTEM HYBRID SUDAH SIAP!**

**Akun Demo untuk Testing:**
- **Teacher**: `teacher@test.com` / `password123`
- **Student**: `student@test.com` / `password123`

**Cara Kerja Sistem:**
1. 🔐 **User login** → PHP cek database → buat session
2. 🚀 **Masuk dashboard** → PHP cek session → load Firebase
3. 💬 **Chat/Room** → Firebase real-time (dengan user info dari PHP)
4. 🚪 **Logout** → PHP hapus session → redirect login

**Keuntungan:**
- ⚡ **Login super cepat** (PHP session)
- 🔥 **Chat tetap real-time** (Firebase)
- 💰 **Hemat Firebase quota** (hanya chat)
- 🛡️ **Data user aman** (di server Anda)

---

## 🔧 **TROUBLESHOOTING**

### Error "Database connection failed"
**Solusi**: Cek kredensial di `config/database.php`

### Error "Firebase not initialized"
**Solusi**: Pastikan Firebase scripts dimuat sebelum `hybrid-auth.js`

### Login berhasil tapi tidak redirect
**Solusi**: Cek console browser (F12) untuk error JavaScript

### Chat tidak real-time
**Solusi**: Pastikan Firebase config benar dan network tidak diblokir

### Session expired
**Solusi**: Clear browser cookies dan login ulang

---

**Happy Coding! 🚀🔥**