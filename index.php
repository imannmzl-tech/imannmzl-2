<?php
// Redirect to login page
header('Location: login.php');
exit;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .role-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .role-option.active {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .role-option:hover {
            border-color: #667eea;
        }
        
        .loading {
            display: none;
        }
        
        .loading.show {
            display: inline-block;
        }
        
        .firebase-config-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card">
                    <div class="login-header">
                        <h2><i class="bi bi-chat-dots me-2"></i>Chat Room Realtime</h2>
                        <p class="mb-0">Login untuk mulai chatting</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if (!isFirebaseConfigured()): ?>
                        <div class="firebase-config-warning">
                            <h6><i class="bi bi-exclamation-triangle me-2"></i>Firebase Belum Dikonfigurasi</h6>
                            <p class="mb-0">Silakan update konfigurasi Firebase di <code>config/firebase-config.php</code> dan <code>assets/js/firebase-config.js</code></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Login Form -->
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <span class="login-text">Login</span>
                                    <span class="loading">
                                        <i class="bi bi-arrow-clockwise spin"></i> Loading...
                                    </span>
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <!-- Register Form -->
                        <h5 class="text-center mb-3">Belum punya akun? Daftar sekarang!</h5>
                        
                        <form id="registerForm">
                            <div class="mb-3">
                                <label for="regName" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="regName" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="regEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="regEmail" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="regPassword" class="form-label">Password</label>
                                <input type="password" class="form-control" id="regPassword" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Pilih Role</label>
                                <div class="role-selector">
                                    <div class="role-option" data-role="teacher">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <div>Guru</div>
                                        <small>Buat dan kelola room</small>
                                    </div>
                                    <div class="role-option" data-role="student">
                                        <i class="bi bi-person me-2"></i>
                                        <div>Siswa</div>
                                        <small>Join room dan chat</small>
                                    </div>
                                </div>
                                <input type="hidden" id="selectedRole" name="role" value="student">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-login">
                                    <span class="register-text">Daftar</span>
                                    <span class="loading">
                                        <i class="bi bi-arrow-clockwise spin"></i> Loading...
                                    </span>
                                </button>
                            </div>
                        </form>
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
                
                // Redirect based on role
                checkUserRole(user.uid);
            } else {
                currentUser = null;
                console.log('❌ User logged out');
            }
        });
        
        // Check user role and redirect
        function checkUserRole(userId) {
            const userRef = database.ref('users/' + userId);
            
            userRef.once('value', (snapshot) => {
                const userData = snapshot.val();
                
                if (userData && userData.role) {
                    if (userData.role === 'teacher') {
                        window.location.href = 'dashboard/teacher/index.php';
                    } else if (userData.role === 'student') {
                        window.location.href = 'dashboard/student/index.php';
                    }
                }
            });
        }
        
        // Helper function untuk sign in
        function signIn(email, password) {
            return auth.signInWithEmailAndPassword(email, password);
        }
        
        // Helper function untuk sign up
        function signUp(email, password, name, role) {
            return auth.createUserWithEmailAndPassword(email, password)
                .then((userCredential) => {
                    const user = userCredential.user;
                    
                    // Update user profile
                    return user.updateProfile({
                        displayName: name
                    }).then(() => {
                        // Save user data to database
                        return database.ref('users/' + user.uid).set({
                            name: name,
                            email: email,
                            role: role,
                            createdAt: firebase.database.ServerValue.TIMESTAMP,
                            isOnline: true,
                            lastSeen: firebase.database.ServerValue.TIMESTAMP
                        });
                    });
                });
        }
    </script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Role selector
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('selectedRole').value = this.dataset.role;
            });
        });
        
        // Set default role
        document.querySelector('.role-option[data-role="student"]').classList.add('active');
        
        // Login form
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Show loading
            submitBtn.querySelector('.login-text').classList.add('d-none');
            submitBtn.querySelector('.loading').classList.add('show');
            submitBtn.disabled = true;
            
            try {
                await signIn(email, password);
                showNotification('Login berhasil!', 'success');
            } catch (error) {
                console.error('Login error:', error);
                showNotification('Login gagal: ' + error.message, 'error');
            } finally {
                // Hide loading
                submitBtn.querySelector('.login-text').classList.remove('d-none');
                submitBtn.querySelector('.loading').classList.remove('show');
                submitBtn.disabled = false;
            }
        });
        
        // Register form
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('regName').value;
            const email = document.getElementById('regEmail').value;
            const password = document.getElementById('regPassword').value;
            const role = document.getElementById('selectedRole').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Show loading
            submitBtn.querySelector('.register-text').classList.add('d-none');
            submitBtn.querySelector('.loading').classList.add('show');
            submitBtn.disabled = true;
            
            try {
                await signUp(email, password, name, role);
                showNotification('Pendaftaran berhasil!', 'success');
            } catch (error) {
                console.error('Register error:', error);
                showNotification('Pendaftaran gagal: ' + error.message, 'error');
            } finally {
                // Hide loading
                submitBtn.querySelector('.register-text').classList.remove('d-none');
                submitBtn.querySelector('.loading').classList.remove('show');
                submitBtn.disabled = false;
            }
        });
        
        // CSS for loading spinner
        const style = document.createElement('style');
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
