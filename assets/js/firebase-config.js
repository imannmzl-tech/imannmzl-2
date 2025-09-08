/**
 * 🔥 Firebase Configuration JavaScript
 * 
 * INSTRUKSI:
 * 1. Ganti konfigurasi di bawah ini dengan data dari Firebase Console
 * 2. File ini akan di-include di semua halaman yang menggunakan Firebase
 */

// Firebase Configuration
// Konfigurasi project Firebase Anda
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

// Initialize Firebase services (Storage disabled - using PHP upload)
const auth = firebase.auth();
const database = firebase.database();
// const storage = firebase.storage(); // Disabled - using PHP upload instead

// Global variables
let currentUser = null;
let currentRoom = null;

// Auth state change listener
auth.onAuthStateChanged((user) => {
    if (user) {
        currentUser = user;
        console.log('✅ User logged in:', user.email);
        
        // Update user info in database
        updateUserInfo(user);
        
        // Redirect based on role
        checkUserRole(user.uid);
    } else {
        currentUser = null;
        console.log('❌ User logged out');
        
        // Redirect to login (only if not on login page)
        if (!window.location.pathname.includes('index.php') && !window.location.pathname.includes('test-')) {
            window.location.href = 'index.php';
        }
    }
});

// Update user info in database
function updateUserInfo(user) {
    const userRef = database.ref('users/' + user.uid);
    
    userRef.update({
        name: user.displayName || user.email.split('@')[0],
        email: user.email,
        lastLogin: firebase.database.ServerValue.TIMESTAMP,
        isOnline: true
    });
    
    // Set user offline when they disconnect
    userRef.onDisconnect().update({
        isOnline: false,
        lastSeen: firebase.database.ServerValue.TIMESTAMP
    });
}

// Check user role and redirect
function checkUserRole(userId) {
    // Skip redirect if on test pages or already on correct dashboard
    if (window.location.pathname.includes('test-') || 
        window.location.pathname.includes('dashboard/')) {
        return;
    }
    
    const userRef = database.ref('users/' + userId);
    
    userRef.once('value', (snapshot) => {
        const userData = snapshot.val();
        
        if (userData && userData.role) {
            if (userData.role === 'teacher' && !window.location.pathname.includes('teacher')) {
                window.location.href = 'dashboard/teacher/index.php';
            } else if (userData.role === 'student' && !window.location.pathname.includes('student')) {
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
                // Save user role to database
                return database.ref('users/' + user.uid).set({
                    name: name,
                    email: email,
                    role: role,
                    createdAt: firebase.database.ServerValue.TIMESTAMP,
                    isOnline: true
                });
            });
        });
}

// Helper function untuk sign out
function signOut() {
    return auth.signOut();
}

// Helper function untuk get current user
function getCurrentUser() {
    return currentUser;
}

// Helper function untuk check if user is authenticated
function isAuthenticated() {
    return currentUser !== null;
}

// Helper function untuk get user role
function getUserRole() {
    if (!currentUser) return null;
    
    return new Promise((resolve) => {
        const userRef = database.ref('users/' + currentUser.uid + '/role');
        userRef.once('value', (snapshot) => {
            resolve(snapshot.val());
        });
    });
}

// Helper function untuk upload image via PHP (Alternative to Firebase Storage)
function uploadImage(file, roomId) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('image', file);
        
        fetch(APP_URL + '/api/upload-image.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resolve(data.data.url);
            } else {
                reject(new Error(data.message));
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

// Helper function untuk send message
function sendMessage(roomId, text, imageUrl = null) {
    if (!currentUser) return Promise.reject('User not authenticated');
    
    const messageRef = database.ref('messages/' + roomId).push();
    
    return messageRef.set({
        text: text,
        senderId: currentUser.uid,
        senderName: currentUser.displayName || currentUser.email.split('@')[0],
        timestamp: firebase.database.ServerValue.TIMESTAMP,
        type: imageUrl ? 'image' : 'text',
        imageUrl: imageUrl
    });
}

// Helper function untuk create room
function createRoom(name, description) {
    if (!currentUser) return Promise.reject('User not authenticated');
    
    const roomRef = database.ref('rooms').push();
    
    return roomRef.set({
        name: name,
        description: description,
        createdBy: currentUser.uid,
        createdAt: firebase.database.ServerValue.TIMESTAMP,
        members: {
            [currentUser.uid]: true
        }
    }).then(() => {
        return roomRef.key;
    });
}

// Helper function untuk join room
function joinRoom(roomId) {
    if (!currentUser) return Promise.reject('User not authenticated');
    
    const roomRef = database.ref('rooms/' + roomId + '/members/' + currentUser.uid);
    return roomRef.set(true);
}

// Helper function untuk leave room
function leaveRoom(roomId) {
    if (!currentUser) return Promise.reject('User not authenticated');
    
    const roomRef = database.ref('rooms/' + roomId + '/members/' + currentUser.uid);
    return roomRef.remove();
}

// Helper function untuk get room messages
function getRoomMessages(roomId, callback) {
    const messagesRef = database.ref('messages/' + roomId);
    
    messagesRef.on('value', (snapshot) => {
        const messages = [];
        snapshot.forEach((childSnapshot) => {
            messages.push({
                id: childSnapshot.key,
                ...childSnapshot.val()
            });
        });
        callback(messages);
    });
    
    return messagesRef;
}

// Helper function untuk get user rooms
function getUserRooms(callback) {
    if (!currentUser) return;
    
    const roomsRef = database.ref('rooms');
    
    roomsRef.on('value', (snapshot) => {
        const rooms = [];
        snapshot.forEach((childSnapshot) => {
            const room = childSnapshot.val();
            if (room.members && room.members[currentUser.uid]) {
                rooms.push({
                    id: childSnapshot.key,
                    ...room
                });
            }
        });
        callback(rooms);
    });
    
    return roomsRef;
}

// Helper function untuk format timestamp
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) return 'baru saja';
    if (diff < 3600000) return Math.floor(diff / 60000) + ' menit yang lalu';
    if (diff < 86400000) return Math.floor(diff / 3600000) + ' jam yang lalu';
    
    return date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

// Error handling
auth.onAuthStateChanged((user) => {
    // Handle auth errors
}, (error) => {
    console.error('Auth error:', error);
    showNotification('Error: ' + error.message, 'error');
});

// Helper function untuk show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔥 Firebase initialized');
});
