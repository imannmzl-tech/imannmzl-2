<?php
/**
 * 🔥 Firebase Configuration
 * 
 * INSTRUKSI SETUP:
 * 1. Buat project di Firebase Console (https://console.firebase.google.com)
 * 2. Enable Realtime Database
 * 3. Enable Authentication (Email/Password)
 * 4. Enable Storage
 * 5. Download firebase-config.js dari Project Settings
 * 6. Update konfigurasi di bawah ini
 */

// Firebase Project Configuration
// Konfigurasi project Firebase Anda
define('FIREBASE_API_KEY', 'AIzaSyBsWwXT8vZ3Y_G_HxLXCOucy8trXZ8vXog');
define('FIREBASE_AUTH_DOMAIN', 'chat-room-realtime.firebaseapp.com');
define('FIREBASE_DATABASE_URL', 'https://chat-room-realtime-default-rtdb.asia-southeast1.firebasedatabase.app');
define('FIREBASE_PROJECT_ID', 'chat-room-realtime');
define('FIREBASE_STORAGE_BUCKET', 'chat-room-realtime.firebasestorage.app');
define('FIREBASE_MESSAGING_SENDER_ID', '952502420326');
define('FIREBASE_APP_ID', '1:952502420326:web:a8d939bbb6c3dbefdbbea7');

// Firebase Security Rules (untuk referensi)
/*
// Realtime Database Rules
{
  "rules": {
    "users": {
      "$userId": {
        ".read": "$userId === auth.uid",
        ".write": "$userId === auth.uid"
      }
    },
    "rooms": {
      "$roomId": {
        ".read": "auth != null && (data.child('members').child(auth.uid).exists() || data.child('createdBy').val() === auth.uid)",
        ".write": "auth != null && (data.child('createdBy').val() === auth.uid || data.child('members').child(auth.uid).exists())"
      }
    },
    "messages": {
      "$roomId": {
        ".read": "auth != null && root.child('rooms').child($roomId).child('members').child(auth.uid).exists()",
        ".write": "auth != null && root.child('rooms').child($roomId).child('members').child(auth.uid).exists()"
      }
    }
  }
}

// Storage Rules
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    match /chat-images/{userId}/{allPaths=**} {
      allow read, write: if request.auth != null && request.auth.uid == userId;
    }
  }
}
*/

// Helper function untuk generate Firebase config JavaScript
function getFirebaseConfigJS() {
    return "
    const firebaseConfig = {
        apiKey: '" . FIREBASE_API_KEY . "',
        authDomain: '" . FIREBASE_AUTH_DOMAIN . "',
        databaseURL: '" . FIREBASE_DATABASE_URL . "',
        projectId: '" . FIREBASE_PROJECT_ID . "',
        storageBucket: '" . FIREBASE_STORAGE_BUCKET . "',
        messagingSenderId: '" . FIREBASE_MESSAGING_SENDER_ID . "',
        appId: '" . FIREBASE_APP_ID . "'
    };
    ";
}

// Helper function untuk check Firebase configuration
function isFirebaseConfigured() {
    return FIREBASE_API_KEY !== 'YOUR_FIREBASE_API_KEY' && 
           FIREBASE_PROJECT_ID !== 'YOUR_PROJECT_ID';
}

// Helper function untuk get Firebase database reference
function getFirebaseDatabaseRef($path = '') {
    return FIREBASE_DATABASE_URL . $path . '.json';
}

// Helper function untuk get Firebase storage reference
function getFirebaseStorageRef($path = '') {
    return 'gs://' . FIREBASE_STORAGE_BUCKET . '/' . $path;
}
?>
