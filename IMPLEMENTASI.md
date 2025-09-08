# 🚀 PANDUAN IMPLEMENTASI CHAT ROOM REALTIME
## Dari Firebase ke PHP + MySQL

---

## 📋 **LANGKAH 1: SETUP DATABASE**

### 1.1 Buat Database di phpMyAdmin
1. **Login ke cPanel** hosting Anda
2. **Buka phpMyAdmin**
3. **Klik "New"** untuk buat database baru
4. **Nama database**: `chat_room_db` (atau sesuai keinginan)
5. **Klik "Create"**

### 1.2 Import Database Schema
1. **Pilih database** yang baru dibuat
2. **Klik tab "Import"**
3. **Choose File** → pilih file `database.sql`
4. **Klik "Go"**
5. ✅ **Database siap!** (akan ada 5 tabel: users, rooms, room_members, messages, user_sessions)

### 1.3 Update Konfigurasi Database
Buka file `config/database.php` dan sesuaikan:

```php
// Database Configuration - Sesuaikan dengan hosting Anda
define('DB_HOST', 'localhost');           // Host database
define('DB_NAME', 'chat_room_db');        // Nama database Anda
define('DB_USER', 'username_database');   // Username database
define('DB_PASS', 'password_database');   // Password database
```

**💡 Info:** Dapatkan info database dari cPanel → Database → MySQL Databases

---

## 📋 **LANGKAH 2: TEST DATABASE CONNECTION**

### 2.1 Test Koneksi
1. **Buka browser**
2. **Akses**: `https://domain-anda.com/chat-room-realtime/config/database.php`
3. **Jika berhasil**: tidak ada error
4. **Jika gagal**: akan muncul error connection

### 2.2 Test dengan Akun Demo
**Akun yang sudah tersedia:**
- **Teacher**: `teacher@test.com` / `password123`
- **Student**: `student@test.com` / `password123`
- **Room Code**: `DEMO123`

---

## 📋 **LANGKAH 3: UPDATE HALAMAN LOGIN**

### 3.1 Ganti login.php
Backup dulu file lama, lalu replace dengan versi PHP:

```php
<?php
require_once 'config/auth.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user['role'] === 'teacher') {
        header('Location: dashboard/teacher/index.php');
    } else {
        header('Location: dashboard/student/index.php');
    }
    exit;
}

$page_title = 'Login - Chat Room Realtime';
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
    
    <!-- Custom CSS sama seperti sebelumnya -->
</head>
<body>
    <!-- HTML form sama seperti sebelumnya -->
    
    <!-- JavaScript baru -->
    <script src="assets/js/chat-api.js"></script>
    <script>
        // Login form handler
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            if (!email || !password) {
                chatAPI.showNotification('Please fill in all fields', 'error');
                return;
            }
            
            try {
                chatAPI.showNotification('Signing in...', 'info');
                
                const result = await chatAPI.login(email, password);
                
                if (result.success) {
                    chatAPI.showNotification('Login successful! Redirecting...', 'success');
                    
                    // Redirect based on role
                    setTimeout(() => {
                        if (result.user.role === 'teacher') {
                            window.location.href = 'dashboard/teacher/index.php';
                        } else {
                            window.location.href = 'dashboard/student/index.php';
                        }
                    }, 1000);
                } else {
                    chatAPI.showNotification(result.message, 'error');
                }
            } catch (error) {
                chatAPI.showNotification('Login failed: ' + error.message, 'error');
            }
        });
    </script>
</body>
</html>
```

---

## 📋 **LANGKAH 4: UPDATE HALAMAN REGISTER**

### 4.1 Ganti register.php
Update dengan sistem PHP yang sama:

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
        chatAPI.showNotification('Please fill in all fields', 'error');
        return;
    }
    
    if (password !== confirmPassword) {
        chatAPI.showNotification('Passwords do not match', 'error');
        return;
    }
    
    if (password.length < 6) {
        chatAPI.showNotification('Password must be at least 6 characters', 'error');
        return;
    }
    
    try {
        chatAPI.showNotification('Creating account...', 'info');
        
        const result = await chatAPI.register(name, email, password, role);
        
        if (result.success) {
            chatAPI.showNotification('Registration successful! Redirecting...', 'success');
            
            setTimeout(() => {
                if (role === 'teacher') {
                    window.location.href = 'dashboard/teacher/index.php';
                } else {
                    window.location.href = 'dashboard/student/index.php';
                }
            }, 1000);
        } else {
            chatAPI.showNotification(result.message, 'error');
        }
    } catch (error) {
        chatAPI.showNotification('Registration failed: ' + error.message, 'error');
    }
});
```

---

## 📋 **LANGKAH 5: UPDATE DASHBOARD PAGES**

### 5.1 Update Teacher Dashboard
Di `dashboard/teacher/index.php`, ganti bagian JavaScript:

```php
<?php
require_once '../../config/auth.php';

// Require teacher role
requireRole('teacher');

$page_title = 'Teacher Dashboard - Chat Room Realtime';
?>

<!-- HTML tetap sama -->

<script src="../../assets/js/chat-api.js"></script>
<script>
    let userRooms = [];
    let allUsers = [];
    
    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', async function() {
        // Load dashboard data
        await loadDashboardData();
        updateUserInfo();
        setupTabNavigation();
    });
    
    async function loadDashboardData() {
        try {
            // Load user's rooms
            const roomsResult = await chatAPI.getMyRooms();
            if (roomsResult.success) {
                userRooms = roomsResult.rooms;
                displayRooms(userRooms);
            }
            
            updateStats();
        } catch (error) {
            console.error('Error loading dashboard:', error);
        }
    }
    
    // Create room function
    async function createRoom(name, description) {
        try {
            const result = await chatAPI.createRoom(name, description);
            if (result.success) {
                chatAPI.showNotification('Room created successfully!', 'success');
                await loadDashboardData(); // Reload data
                return result;
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            chatAPI.showNotification('Failed to create room: ' + error.message, 'error');
            throw error;
        }
    }
    
    // Logout function
    async function signOut() {
        try {
            await chatAPI.logout();
            window.location.href = '../../login.php';
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = '../../login.php';
        }
    }
    
    // Other functions tetap sama...
</script>
```

### 5.2 Update Student Dashboard
Sama seperti teacher, tapi ganti `requireRole('student')`

---

## 📋 **LANGKAH 6: UPDATE CHAT ROOM**

### 6.1 Update chat/room.php
```php
<?php
require_once '../config/auth.php';
require_once '../config/rooms.php';

// Require login
requireLogin();

$room_id = $_GET['id'] ?? '';
if (empty($room_id)) {
    header('Location: ../dashboard/student/index.php');
    exit;
}

// Check if user is room member
if (!rooms()->isRoomMember($room_id)) {
    header('Location: ../dashboard/student/index.php');
    exit;
}

$page_title = 'Chat Room - Chat Room Realtime';
?>

<!-- HTML tetap sama -->

<script src="../assets/js/chat-api.js"></script>
<script>
    const roomId = '<?php echo $room_id; ?>';
    let lastMessageId = 0;
    let messages = [];
    
    // Initialize chat
    document.addEventListener('DOMContentLoaded', async function() {
        await initializeChat();
        startMessagePolling();
    });
    
    async function initializeChat() {
        try {
            // Load room info
            const roomResult = await chatAPI.getRoomInfo(roomId);
            if (roomResult.success) {
                updateRoomInfo(roomResult.room, roomResult.members);
            }
            
            // Load messages
            const messagesResult = await chatAPI.getMessages(roomId);
            if (messagesResult.success) {
                messages = messagesResult.messages;
                displayMessages(messages);
                if (messages.length > 0) {
                    lastMessageId = messages[messages.length - 1].id;
                }
            }
        } catch (error) {
            console.error('Error initializing chat:', error);
        }
    }
    
    // Send message
    async function sendChatMessage() {
        const messageInput = document.getElementById('messageInput');
        const text = messageInput.value.trim();
        
        if (!text) return;
        
        try {
            const result = await chatAPI.sendMessage(roomId, text);
            if (result.success) {
                messageInput.value = '';
                // Message akan muncul via polling
            } else {
                chatAPI.showNotification('Failed to send message: ' + result.message, 'error');
            }
        } catch (error) {
            chatAPI.showNotification('Failed to send message: ' + error.message, 'error');
        }
    }
    
    // Polling for new messages
    function startMessagePolling() {
        setInterval(async () => {
            try {
                const result = await chatAPI.getRecentMessages(roomId, lastMessageId);
                if (result.success && result.messages.length > 0) {
                    result.messages.forEach(message => {
                        displayMessage(message);
                        messages.push(message);
                    });
                    lastMessageId = result.messages[result.messages.length - 1].id;
                }
            } catch (error) {
                console.error('Error polling messages:', error);
            }
        }, 2000); // Poll every 2 seconds
    }
    
    // Display message
    function displayMessage(message) {
        const messagesContainer = document.getElementById('chatMessages');
        const currentUser = chatAPI.getCurrentUser();
        
        const messageElement = document.createElement('div');
        messageElement.className = `message ${message.user_id == currentUser.id ? 'own' : ''}`;
        messageElement.innerHTML = `
            <div class="message-avatar">
                ${message.user_name.charAt(0).toUpperCase()}
            </div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-sender">${message.user_name}</span>
                    <span class="message-time">${chatAPI.formatTimestamp(message.created_at)}</span>
                </div>
                <p class="message-text">${message.message || ''}</p>
                ${message.message_type === 'image' && message.file_url ? 
                    `<img src="${message.file_url}" class="message-image" alt="Shared image">` : ''}
            </div>
        `;
        
        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Other functions...
</script>
```

---

## 📋 **LANGKAH 7: TESTING**

### 7.1 Test Login
1. **Buka** `https://domain-anda.com/chat-room-realtime/login.php`
2. **Login dengan**:
   - Email: `teacher@test.com`
   - Password: `password123`
3. **Harus redirect** ke teacher dashboard

### 7.2 Test Register
1. **Daftar akun baru** dengan role student
2. **Harus auto-login** dan redirect ke student dashboard

### 7.3 Test Chat
1. **Login sebagai student**
2. **Join room** dengan code: `DEMO123`
3. **Kirim pesan**
4. **Buka tab baru**, login sebagai teacher
5. **Masuk room DEMO123**
6. **Harus bisa lihat pesan student**

---

## 📋 **LANGKAH 8: PRODUCTION SETUP**

### 8.1 Security Settings
Di `config/database.php`, untuk production:

```php
// Error reporting untuk production
error_reporting(0);
ini_set('display_errors', 0);
```

### 8.2 Update APP_URL
Di `config/config.php`:

```php
define('APP_URL', 'https://domain-anda.com/chat-room-realtime');
```

### 8.3 File Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/chat-images/
```

---

## 🎉 **SELESAI!**

**Aplikasi sekarang menggunakan:**
- ✅ **PHP Authentication** (bukan Firebase)
- ✅ **MySQL Database** (bukan Firebase Realtime DB)
- ✅ **PHP Sessions** (bukan Firebase Auth)
- ✅ **Real-time via Polling** (bukan Firebase listeners)
- ✅ **PHP File Upload** (bukan Firebase Storage)

**Keuntungan:**
- 🚀 **Lebih cepat** untuk hosting shared
- 💰 **Lebih murah** (tidak perlu Firebase)
- 🔧 **Lebih mudah maintain**
- 🛡️ **Lebih secure** (server-side validation)
- 📊 **Database lebih fleksibel**

**Need Help?**
- Cek error di browser Console (F12)
- Cek error log di cPanel
- Test API endpoints langsung di browser

---

## 🔧 **TROUBLESHOOTING**

### Database Connection Error
```
❌ Database Connection Error: Access denied
```
**Solusi:** Update username/password database di `config/database.php`

### API Not Working
```
❌ 404 Not Found
```
**Solusi:** Pastikan folder `api/` ada dan file PHP bisa diakses

### Login Redirect Loop
**Solusi:** Clear browser cookies dan session

### Upload Not Working
**Solusi:** Pastikan folder `uploads/` ada dan writable (755)

---

**Happy Coding! 🚀**