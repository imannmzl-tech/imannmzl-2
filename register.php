<?php
require_once 'config/config.php';
require_once 'config/firebase-config.php';

$page_title = 'Register - Chat Room Realtime';
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
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
            max-width: 450px;
            margin: 0 auto;
        }
        
        .register-header {
            background: #ffffff;
            color: #333;
            border-radius: 15px 15px 0 0;
            padding: 2rem 2rem 1rem 2rem;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        
        .register-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: #6c757d;
            margin-bottom: 0;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #ffffff;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            background: #ffffff;
        }
        
        .btn-register {
            background: #007bff;
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-register:hover {
            background: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
            color: white;
        }
        
        .btn-login {
            background: transparent;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 600;
            color: #007bff;
            transition: all 0.3s ease;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-login:hover {
            background: #007bff;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
            text-decoration: none;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .btn-link {
            color: #007bff;
            text-decoration: none;
        }
        
        .btn-link:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }
        
        .divider span {
            background: #ffffff;
            padding: 0 1rem;
            color: #6c757d;
            font-size: 14px;
        }
        
        .app-logo {
            width: 60px;
            height: 60px;
            background: #007bff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 8px 0 0 8px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #007bff;
            background: #f8f9fa;
        }
        
        .role-option {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            margin-bottom: 10px;
        }
        
        .role-option:hover {
            border-color: #007bff;
            background: #f8f9ff;
        }
        
        .role-option.active {
            border-color: #007bff;
            background: #007bff;
            color: white;
        }
        
        .role-option i {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        
        .role-option h6 {
            margin: 0;
            font-weight: 600;
        }
        
        .role-option small {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <div class="col-12 d-flex align-items-center justify-content-center">
                <div class="register-container">
                    <!-- Header -->
                    <div class="register-header">
                        <div class="app-logo">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h2>Create Account</h2>
                        <p>Join our chat community</p>
                    </div>
                    
                    <!-- Body -->
                    <div class="register-body">
                        <!-- Alert Messages -->
                        <div id="alertContainer"></div>
                        
                        <!-- Register Form -->
                        <form id="registerForm">
                            <div class="mb-3">
                                <label for="registerName" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" class="form-control" id="registerName" placeholder="Enter your full name" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registerEmail" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="registerEmail" placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="registerPassword" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="registerPassword" placeholder="Create a password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm your password" required>
                                </div>
                            </div>
                            
                            <!-- Role Selection -->
                            <div class="mb-3">
                                <label class="form-label">I am a:</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="role-option" data-role="teacher">
                                            <i class="bi bi-person-badge"></i>
                                            <h6>Teacher</h6>
                                            <small>Create and manage rooms</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="role-option" data-role="student">
                                            <i class="bi bi-person"></i>
                                            <h6>Student</h6>
                                            <small>Join and participate</small>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="selectedRole" value="">
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    I agree to the <a href="#" class="btn-link">Terms of Service</a> and <a href="#" class="btn-link">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-register mb-3">
                                <i class="bi bi-person-plus me-2"></i>Create Account
                            </button>
                        </form>
                        
                        <!-- Divider -->
                        <div class="divider">
                            <span>Already have an account?</span>
                        </div>
                        
                        <!-- Login Button -->
                        <a href="login.php" class="btn btn-login">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </a>
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
            // Skip redirect if already on correct dashboard
            if (window.location.pathname.includes('dashboard/')) {
                return;
            }
            
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
        // Show alert function
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            alertContainer.innerHTML = alertHtml;
        }
        
        // Role selection handler
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('selectedRole').value = this.dataset.role;
            });
        });
        
        // Register form handler
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const role = document.getElementById('selectedRole').value;
            const agreeTerms = document.getElementById('agreeTerms').checked;
            
            // Validation
            if (!name || !email || !password || !confirmPassword || !role) {
                showAlert('Please fill in all fields');
                return;
            }
            
            if (password !== confirmPassword) {
                showAlert('Passwords do not match');
                return;
            }
            
            if (password.length < 6) {
                showAlert('Password must be at least 6 characters long');
                return;
            }
            
            if (!agreeTerms) {
                showAlert('Please agree to the Terms of Service and Privacy Policy');
                return;
            }
            
            try {
                showAlert('Creating account...', 'info');
                
                await signUp(email, password, name, role);
                
                showAlert('Account created successfully! Redirecting...', 'success');
                
            } catch (error) {
                console.error('Registration error:', error);
                
                let errorMessage = 'Registration failed. Please try again.';
                
                switch (error.code) {
                    case 'auth/email-already-in-use':
                        errorMessage = 'An account with this email already exists.';
                        break;
                    case 'auth/invalid-email':
                        errorMessage = 'Invalid email address.';
                        break;
                    case 'auth/weak-password':
                        errorMessage = 'Password is too weak. Please choose a stronger password.';
                        break;
                    case 'auth/operation-not-allowed':
                        errorMessage = 'Registration is currently disabled.';
                        break;
                }
                
                showAlert(errorMessage);
            }
        });
    </script>
</body>
</html>
