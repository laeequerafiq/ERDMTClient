# Firebase Setup and Configuration

## Overview
This guide explains how to configure Firebase for the ERDMT admin panel and ensure proper integration with your Android application.

## Current Firebase Configuration

Your project is already configured with the following details:
- **Project ID**: `remoteadmin-a1089`
- **API Key**: `AIzaSyAOTx2ur0lCHrhGb7UXwjAUudP7Q3KxJOw`
- **Database URL**: `https://remoteadmin-a1089-default-rtdb.firebaseio.com`
- **Storage Bucket**: `remoteadmin-a1089.firebasestorage.app`
- **App ID**: `1:187299377871:android:ccf92720c908b841d23dba`

## Web App Registration

### 1. Add Web App to Firebase Project
1. Go to [Firebase Console](https://console.firebase.google.com)
2. Select your project: `remoteadmin-a1089`
3. Click the gear icon (Project Settings)
4. Go to "General" tab
5. Scroll down to "Your apps" section
6. Click "Add app" and select Web (</>) icon
7. Configure web app:
   - **App nickname**: `ERDMT Admin Panel`
   - **Hosting**: Check this box if you plan to use Firebase Hosting
   - Click "Register app"

### 2. Get Web App Configuration
After registration, you'll get configuration code like:
```javascript
const firebaseConfig = {
  apiKey: "AIzaSyAOTx2ur0lCHrhGb7UXwjAUudP7Q3KxJOw",
  authDomain: "remoteadmin-a1089.firebaseapp.com",
  databaseURL: "https://remoteadmin-a1089-default-rtdb.firebaseio.com",
  projectId: "remoteadmin-a1089",
  storageBucket: "remoteadmin-a1089.firebasestorage.app",
  messagingSenderId: "187299377871",
  appId: "1:187299377871:web:YOUR_WEB_APP_ID"
};
```

### 3. Update Admin Panel Configuration
Update `config/firebase.php` with the web app ID:
```php
define('FIREBASE_WEB_APP_ID', '1:187299377871:web:YOUR_WEB_APP_ID');
```

## Database Rules Configuration

### 1. Current Database Structure
Your admin panel expects this database structure:
```
remoteadmin-a1089-default-rtdb/
├── devices/
│   └── {device_id}/
│       ├── name
│       ├── status
│       ├── lastSeen
│       ├── location/
│       ├── battery
│       ├── fcm_token
│       └── settings/
├── commands/
│   └── {device_id}/
│       └── {command_id}/
│           ├── command
│           ├── params
│           ├── timestamp
│           ├── status
│           └── result
├── messages/
│   └── {device_id}/
│       └── {message_id}/
│           ├── message
│           ├── timestamp
│           ├── status
│           └── priority
├── file_transfers/
│   └── {device_id}/
│       └── {file_id}/
│           ├── file_name
│           ├── file_data
│           ├── target_path
│           ├── status
│           └── upload_time
├── activity/
│   └── {activity_id}/
│       ├── type
│       ├── description
│       ├── timestamp
│       └── admin_id
└── storage_usage/
    ├── used
    └── total
```

### 2. Security Rules
Configure these rules in Firebase Console > Realtime Database > Rules:
```json
{
  "rules": {
    ".read": false,
    ".write": false,
    "devices": {
      ".read": true,
      ".write": true,
      "$device_id": {
        ".validate": "newData.hasChildren(['name', 'status'])"
      }
    },
    "commands": {
      ".read": true,
      ".write": true,
      "$device_id": {
        "$command_id": {
          ".validate": "newData.hasChildren(['command', 'timestamp', 'status'])"
        }
      }
    },
    "messages": {
      ".read": true,
      ".write": true,
      "$device_id": {
        "$message_id": {
          ".validate": "newData.hasChildren(['message', 'timestamp', 'status'])"
        }
      }
    },
    "file_transfers": {
      ".read": true,
      ".write": true,
      "$device_id": {
        "$file_id": {
          ".validate": "newData.hasChildren(['file_name', 'status', 'upload_time'])"
        }
      }
    },
    "activity": {
      ".read": true,
      ".write": true,
      "$activity_id": {
        ".validate": "newData.hasChildren(['type', 'description', 'timestamp'])"
      }
    },
    "storage_usage": {
      ".read": true,
      ".write": true
    }
  }
}
```

### 3. For Development (Less Restrictive)
During development, you can use more permissive rules:
```json
{
  "rules": {
    ".read": true,
    ".write": true
  }
}
```

**⚠️ Important**: Change to restrictive rules before production!

## Storage Rules Configuration

### 1. Firebase Storage Rules
Go to Firebase Console > Storage > Rules and configure:
```javascript
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    // Allow read/write for file transfers
    match /file_transfers/{deviceId}/{fileName} {
      allow read, write: if true;
    }
    
    // Allow read/write for app data
    match /app_data/{allPaths=**} {
      allow read, write: if true;
    }
    
    // Default deny
    match /{allPaths=**} {
      allow read, write: if false;
    }
  }
}
```

## Authentication Configuration (Optional)

### 1. Enable Authentication Methods
If you want to add user authentication:
1. Go to Firebase Console > Authentication
2. Click "Get started"
3. Go to "Sign-in method" tab
4. Enable desired methods:
   - Email/Password
   - Google
   - Anonymous (for testing)

### 2. Create Admin User (If Using Firebase Auth)
```javascript
// This would be done in the admin panel if using Firebase Auth
firebase.auth().createUserWithEmailAndPassword(email, password)
  .then((userCredential) => {
    // Set custom claims for admin role
    // This requires Admin SDK on server side
  });
```

## Cloud Messaging Setup

### 1. Enable FCM
Firebase Cloud Messaging is already configured for your Android app. For web notifications:

1. Go to Firebase Console > Cloud Messaging
2. Web configuration should inherit from project settings
3. Generate web push certificates if needed

### 2. Web Push Notifications (Optional)
Add to your admin panel for browser notifications:
```javascript
// Request permission for notifications
Notification.requestPermission().then((permission) => {
  if (permission === 'granted') {
    // Initialize FCM for web
    const messaging = firebase.messaging();
    messaging.getToken({ vapidKey: 'YOUR_VAPID_KEY' });
  }
});
```

## Domain Authorization

### 1. Add Authorized Domains
1. Go to Firebase Console > Authentication > Settings > Authorized domains
2. Add your domains:
   - `yourdomain.com`
   - `admin.yourdomain.com`
   - `localhost` (for development)
   - Any other domains you'll use

### 2. CORS Configuration
For web requests, ensure your domain is authorized:
1. Firebase automatically handles CORS for authorized domains
2. API requests from unauthorized domains will be blocked

## Testing Firebase Integration

### 1. Test Database Connection
Use browser console on your admin panel:
```javascript
// Test read
firebase.database().ref('devices').once('value').then((snapshot) => {
  console.log('Database read successful:', snapshot.val());
});

// Test write
firebase.database().ref('test').set({
  timestamp: Date.now(),
  message: 'Test write'
}).then(() => {
  console.log('Database write successful');
});
```

### 2. Test Storage Access
```javascript
// Test storage
const storageRef = firebase.storage().ref();
storageRef.child('test.txt').getDownloadURL()
  .then((url) => console.log('Storage access successful'))
  .catch((error) => console.log('Storage error:', error));
```

## Monitoring and Analytics

### 1. Enable Analytics
1. Go to Firebase Console > Analytics
2. Analytics should already be enabled for your project
3. View user engagement and app performance

### 2. Performance Monitoring
1. Go to Firebase Console > Performance
2. Monitor web app performance
3. Track page load times and user interactions

## Backup Strategy

### 1. Database Backup
Firebase doesn't provide direct database export, but you can:
1. Use the admin panel's export functionality
2. Create scheduled functions to backup data
3. Use Firebase Admin SDK for bulk exports

### 2. Storage Backup
1. Download files through Storage console
2. Use gsutil for bulk operations
3. Set up automated backup scripts

## Security Best Practices

### 1. API Key Security
- Never expose API keys in client-side code (they're public by design)
- Use Firebase Security Rules for actual security
- Monitor usage in Firebase Console

### 2. Database Security
- Use restrictive security rules
- Validate data structures
- Monitor for unusual activity

### 3. Regular Audits
1. Review security rules monthly
2. Monitor usage patterns
3. Check for unauthorized access attempts

## Troubleshooting

### Common Issues

**1. CORS Errors**
- Add domain to authorized domains
- Check Firebase project configuration
- Ensure HTTPS is used

**2. Permission Denied**
- Check Firebase security rules
- Verify API key is correct
- Ensure domain is authorized

**3. Network Errors**
- Check internet connectivity
- Verify Firebase service status
- Check for firewall blocking

**4. Quota Exceeded**
- Monitor usage in Firebase Console
- Upgrade plan if necessary
- Optimize data usage

### Debug Tools

**1. Firebase Console**
- Monitor real-time database activity
- View authentication logs
- Check error reports

**2. Browser Developer Tools**
- Network tab for API calls
- Console for JavaScript errors
- Application tab for storage inspection

**3. Firebase Debug Mode**
```javascript
// Enable debug mode
firebase.database.enableLogging(true);
```

## Support Resources

### Documentation
- [Firebase Web Guide](https://firebase.google.com/docs/web/setup)
- [Realtime Database Web Guide](https://firebase.google.com/docs/database/web/start)
- [Firebase Security Rules](https://firebase.google.com/docs/rules)

### Community
- [Firebase Stack Overflow](https://stackoverflow.com/questions/tagged/firebase)
- [Firebase Community](https://firebase.google.com/community)
- [Firebase GitHub](https://github.com/firebase/)

---

**Note**: Keep your Firebase configuration secure and regularly review your security rules and usage patterns.