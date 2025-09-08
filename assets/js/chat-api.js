/**
 * 🚀 Chat Room API - JavaScript Library untuk PHP Backend
 */

class ChatAPI {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl || window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
        this.currentUser = null;
        this.isAuthenticated = false;
        
        // Auto-check authentication on load
        this.checkAuth();
    }
    
    /**
     * Make API request
     */
    async request(endpoint, options = {}) {
        try {
            const url = `${this.baseUrl}/api/${endpoint}`;
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
    // Authentication Methods
    // ==========================================
    
    /**
     * Login user
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
     * Register user
     */
    async register(name, email, password, role = 'student') {
        try {
            const result = await this.request('auth.php?action=register', {
                method: 'POST',
                body: JSON.stringify({ name, email, password, role })
            });
            
            if (result.success) {
                // Auto-check auth after register
                await this.checkAuth();
            }
            
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Logout user
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
     * Check authentication status
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
     * Check if user is authenticated
     */
    isLoggedIn() {
        return this.isAuthenticated;
    }
    
    /**
     * Auth state change callback (override this)
     */
    onAuthStateChange(user) {
        // Override this method to handle auth state changes
        console.log('Auth state changed:', user ? 'Logged in' : 'Logged out');
    }
    
    // ==========================================
    // Room Methods
    // ==========================================
    
    /**
     * Create room
     */
    async createRoom(name, description = '') {
        return await this.request('rooms.php?action=create', {
            method: 'POST',
            body: JSON.stringify({ name, description })
        });
    }
    
    /**
     * Join room by code
     */
    async joinRoom(roomCode) {
        return await this.request('rooms.php?action=join', {
            method: 'POST',
            body: JSON.stringify({ room_code: roomCode })
        });
    }
    
    /**
     * Leave room
     */
    async leaveRoom(roomId) {
        return await this.request(`rooms.php?action=leave&room_id=${roomId}`, {
            method: 'POST'
        });
    }
    
    /**
     * Get user's rooms
     */
    async getMyRooms() {
        return await this.request('rooms.php?action=my-rooms');
    }
    
    /**
     * Get all available rooms
     */
    async getAllRooms() {
        return await this.request('rooms.php?action=all-rooms');
    }
    
    /**
     * Get room info
     */
    async getRoomInfo(roomId) {
        return await this.request(`rooms.php?action=room-info&room_id=${roomId}`);
    }
    
    /**
     * Delete room
     */
    async deleteRoom(roomId) {
        return await this.request(`rooms.php?action=delete&room_id=${roomId}`, {
            method: 'DELETE'
        });
    }
    
    /**
     * Search rooms
     */
    async searchRooms(query) {
        return await this.request(`rooms.php?action=search&q=${encodeURIComponent(query)}`);
    }
    
    // ==========================================
    // Message Methods
    // ==========================================
    
    /**
     * Send message
     */
    async sendMessage(roomId, message, messageType = 'text', fileUrl = null, fileName = null, fileSize = null) {
        return await this.request('messages.php?action=send', {
            method: 'POST',
            body: JSON.stringify({
                room_id: roomId,
                message,
                message_type: messageType,
                file_url: fileUrl,
                file_name: fileName,
                file_size: fileSize
            })
        });
    }
    
    /**
     * Get room messages
     */
    async getMessages(roomId, limit = 50, offset = 0) {
        return await this.request(`messages.php?action=get&room_id=${roomId}&limit=${limit}&offset=${offset}`);
    }
    
    /**
     * Get recent messages (for real-time updates)
     */
    async getRecentMessages(roomId, lastMessageId = 0) {
        return await this.request(`messages.php?action=recent&room_id=${roomId}&last_message_id=${lastMessageId}`);
    }
    
    /**
     * Delete message
     */
    async deleteMessage(messageId) {
        return await this.request(`messages.php?action=delete&message_id=${messageId}`, {
            method: 'DELETE'
        });
    }
    
    /**
     * Search messages
     */
    async searchMessages(roomId, query, limit = 20) {
        return await this.request(`messages.php?action=search&room_id=${roomId}&q=${encodeURIComponent(query)}&limit=${limit}`);
    }
    
    /**
     * Get message statistics
     */
    async getMessageStats(roomId = null, userId = null) {
        let url = 'messages.php?action=stats';
        if (roomId) url += `&room_id=${roomId}`;
        if (userId) url += `&user_id=${userId}`;
        
        return await this.request(url);
    }
    
    // ==========================================
    // File Upload Methods
    // ==========================================
    
    /**
     * Upload image
     */
    async uploadImage(file) {
        try {
            const formData = new FormData();
            formData.append('image', file);
            
            const response = await fetch(`${this.baseUrl}/api/upload-image.php`, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Upload failed');
            }
            
            return result;
        } catch (error) {
            console.error('Upload error:', error);
            throw error;
        }
    }
    
    // ==========================================
    // Utility Methods
    // ==========================================
    
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
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
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
}

// Create global instance
const chatAPI = new ChatAPI();

// Export for use in other files
window.chatAPI = chatAPI;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Chat API initialized');
});