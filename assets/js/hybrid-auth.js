/**
 * 🔗 Hybrid Authentication System
 * PHP Authentication + Firebase Chat Integration
 */

class HybridAuth {
    constructor() {
        this.currentUser = null;
        this.isAuthenticated = false;
        this.firebase = null;
        
        // Initialize Firebase for chat only
        this.initializeFirebase();
        
        // Check authentication status
        this.checkAuth();
    }
    
    /**
     * Initialize Firebase (for chat functionality only)
     */
    initializeFirebase() {
        try {
            // Firebase Configuration
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
            if (typeof firebase !== 'undefined') {
                firebase.initializeApp(firebaseConfig);
                this.firebase = {
                    database: firebase.database(),
                    storage: firebase.storage() // Optional
                };
                console.log('🔥 Firebase initialized for chat');
            }
        } catch (error) {
            console.error('Firebase initialization error:', error);
        }
    }
    
    /**
     * Make API request to PHP backend
     */
    async request(endpoint, options = {}) {
        try {
            const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            const url = `${baseUrl}/api/${endpoint}`;
            
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            };
            
            const response = await fetch(url, { ...defaultOptions, ...options });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    // ==========================================
    // PHP Authentication Methods
    // ==========================================
    
    /**
     * Login user (PHP backend)
     */
    async login(email, password) {
        try {
            const result = await this.request('auth.php?action=login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });
            
            if (result.success) {
                this.currentUser = result.user;
                this.isAuthenticated = true;
                this.onAuthStateChange(this.currentUser);
            }
            
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Register user (PHP backend)
     */
    async register(name, email, password, role = 'student') {
        try {
            const result = await this.request('auth.php?action=register', {
                method: 'POST',
                body: JSON.stringify({ name, email, password, role })
            });
            
            if (result.success) {
                this.currentUser = result.user;
                this.isAuthenticated = true;
                this.onAuthStateChange(this.currentUser);
            }
            
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Logout user (PHP backend)
     */
    async logout() {
        try {
            const result = await this.request('auth.php?action=logout');
            
            if (result.success) {
                this.currentUser = null;
                this.isAuthenticated = false;
                this.onAuthStateChange(null);
            }
            
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Check authentication status (PHP backend)
     */
    async checkAuth() {
        try {
            const result = await this.request('auth.php?action=check-auth');
            
            if (result.success && result.authenticated) {
                this.currentUser = result.user;
                this.isAuthenticated = true;
            } else {
                this.currentUser = null;
                this.isAuthenticated = false;
            }
            
            this.onAuthStateChange(this.currentUser);
            return result;
        } catch (error) {
            this.currentUser = null;
            this.isAuthenticated = false;
            this.onAuthStateChange(null);
            return { success: false, authenticated: false };
        }
    }
    
    /**
     * Get current user
     */
    getCurrentUser() {
        return this.currentUser;
    }
    
    /**
     * Get current user in Firebase format
     */
    getCurrentUserForFirebase() {
        if (!this.currentUser) return null;
        
        return {
            uid: 'php_user_' + this.currentUser.id,
            displayName: this.currentUser.name,
            email: this.currentUser.email,
            role: this.currentUser.role,
            id: this.currentUser.id
        };
    }
    
    /**
     * Check if user is authenticated
     */
    isLoggedIn() {
        return this.isAuthenticated;
    }
    
    /**
     * Auth state change callback (override this)
     */
    onAuthStateChange(user) {
        console.log('🔐 Auth state changed:', user ? `Logged in as ${user.name}` : 'Logged out');
        
        // Auto redirect logic
        if (user) {
            // Only redirect if we're on auth pages
            const currentPath = window.location.pathname;
            const isAuthPage = currentPath.includes('login.php') || 
                              currentPath.includes('register.php') || 
                              currentPath.includes('index.php') ||
                              currentPath === '/' ||
                              currentPath.endsWith('/');
            
            if (isAuthPage) {
                setTimeout(() => {
                    if (user.role === 'teacher') {
                        window.location.href = 'dashboard/teacher/index.php';
                    } else {
                        window.location.href = 'dashboard/student/index.php';
                    }
                }, 1000);
            }
        } else {
            // Only redirect to login if we're on protected pages
            const currentPath = window.location.pathname;
            const isProtectedPage = currentPath.includes('dashboard/') || 
                                   currentPath.includes('chat/');
            
            if (isProtectedPage) {
                window.location.href = '/workspace/login.php';
            }
        }
    }
    
    // ==========================================
    // Firebase Chat Methods
    // ==========================================
    
    /**
     * Send message to Firebase
     */
    async sendMessage(roomId, text, imageUrl = null) {
        if (!this.firebase || !this.currentUser) {
            throw new Error('Not authenticated or Firebase not initialized');
        }
        
        const messageRef = this.firebase.database.ref('messages/' + roomId).push();
        const firebaseUser = this.getCurrentUserForFirebase();
        
        return messageRef.set({
            text: text,
            senderId: firebaseUser.uid,
            senderName: firebaseUser.displayName,
            senderEmail: firebaseUser.email,
            timestamp: firebase.database.ServerValue.TIMESTAMP,
            type: imageUrl ? 'image' : 'text',
            imageUrl: imageUrl
        });
    }
    
    /**
     * Create room in Firebase
     */
    async createRoom(name, description) {
        if (!this.firebase || !this.currentUser) {
            throw new Error('Not authenticated or Firebase not initialized');
        }
        
        const roomRef = this.firebase.database.ref('rooms').push();
        const firebaseUser = this.getCurrentUserForFirebase();
        
        return roomRef.set({
            name: name,
            description: description,
            createdBy: firebaseUser.uid,
            createdByName: firebaseUser.displayName,
            createdAt: firebase.database.ServerValue.TIMESTAMP,
            members: {
                [firebaseUser.uid]: {
                    name: firebaseUser.displayName,
                    email: firebaseUser.email,
                    role: firebaseUser.role,
                    joinedAt: firebase.database.ServerValue.TIMESTAMP
                }
            }
        }).then(() => {
            return roomRef.key;
        });
    }
    
    /**
     * Join room in Firebase
     */
    async joinRoom(roomId) {
        if (!this.firebase || !this.currentUser) {
            throw new Error('Not authenticated or Firebase not initialized');
        }
        
        const firebaseUser = this.getCurrentUserForFirebase();
        const memberRef = this.firebase.database.ref('rooms/' + roomId + '/members/' + firebaseUser.uid);
        
        return memberRef.set({
            name: firebaseUser.displayName,
            email: firebaseUser.email,
            role: firebaseUser.role,
            joinedAt: firebase.database.ServerValue.TIMESTAMP
        });
    }
    
    /**
     * Leave room in Firebase
     */
    async leaveRoom(roomId) {
        if (!this.firebase || !this.currentUser) {
            throw new Error('Not authenticated or Firebase not initialized');
        }
        
        const firebaseUser = this.getCurrentUserForFirebase();
        const memberRef = this.firebase.database.ref('rooms/' + roomId + '/members/' + firebaseUser.uid);
        
        return memberRef.remove();
    }
    
    /**
     * Get room messages from Firebase
     */
    getRoomMessages(roomId, callback) {
        if (!this.firebase) {
            throw new Error('Firebase not initialized');
        }
        
        const messagesRef = this.firebase.database.ref('messages/' + roomId);
        
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
    
    /**
     * Get user rooms from Firebase
     */
    getUserRooms(callback) {
        if (!this.firebase || !this.currentUser) {
            callback([]);
            return;
        }
        
        const firebaseUser = this.getCurrentUserForFirebase();
        const roomsRef = this.firebase.database.ref('rooms');
        
        roomsRef.on('value', (snapshot) => {
            const rooms = [];
            snapshot.forEach((childSnapshot) => {
                const room = childSnapshot.val();
                if (room.members && room.members[firebaseUser.uid]) {
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
    
    // ==========================================
    // Utility Methods
    // ==========================================
    
    /**
     * Upload image (using existing PHP upload)
     */
    async uploadImage(file) {
        try {
            const formData = new FormData();
            formData.append('image', file);
            
            const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            const response = await fetch(`${baseUrl}/api/upload-image.php`, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Upload failed');
            }
            
            return result.data.url;
        } catch (error) {
            console.error('Upload error:', error);
            throw error;
        }
    }
    
    /**
     * Format timestamp
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'baru saja';
        if (diff < 3600000) return Math.floor(diff / 60000) + ' menit yang lalu';
        if (diff < 86400000) return Math.floor(diff / 3600000) + ' jam yang lalu';
        
        return date.toLocaleDateString('id-ID') + ' ' + date.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
}

// Create global instance
const hybridAuth = new HybridAuth();

// Export for backward compatibility
window.hybridAuth = hybridAuth;
window.chatAPI = hybridAuth; // Alias for existing code

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔗 Hybrid Auth System initialized');
});