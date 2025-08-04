# ERDMT Web Admin Panel

## Overview
Complete web-based administration panel for the ERDMT (Emergency Remote Device Management Tool) Android application. This panel provides a comprehensive interface to manage and monitor Android devices remotely through Firebase integration.

## 🚀 Features

### Dashboard
- **Real-time Statistics**: Live device count, online status, and activity metrics
- **Recent Activity**: Monitor latest device connections and command executions
- **Quick Actions**: Fast access to common administrative tasks
- **Storage Usage**: Track Firebase storage consumption

### Device Management
- **Device Overview**: View all registered devices with status indicators
- **Real-time Monitoring**: Live status updates (online/offline/recently active)
- **Device Details**: Comprehensive device information including battery, location, and system specs
- **Bulk Operations**: Execute commands on multiple devices simultaneously

### Command Center
- **Remote Commands**: Send various commands to devices:
  - Get device location
  - Take screenshots
  - Access contacts and SMS
  - Lock/unlock devices
  - Install/uninstall applications
  - Device information retrieval
  - Emergency functions (wipe, sound alerts)
- **Command History**: Track all executed commands with status and results
- **Quick Commands**: Pre-configured commands for common tasks
- **Emergency Actions**: Mass device locking and critical alerts

### File Manager
- **File Transfers**: Upload files to devices with progress tracking
- **File Preview**: View uploaded images and text files
- **Storage Management**: Monitor file transfer status and storage usage
- **Bulk File Operations**: Send files to multiple devices

### Message Center
- **Direct Messaging**: Send messages to individual devices
- **Broadcast Messages**: Send messages to all or selected devices
- **Message Templates**: Quick message templates for common scenarios
- **Delivery Tracking**: Monitor message delivery and read status
- **Priority Messaging**: High-priority and emergency message support

### Settings & Administration
- **User Management**: Admin profile and security settings
- **System Configuration**: Firebase settings and system preferences
- **Security Options**: Password changes, session management
- **Maintenance Tools**: Data cleanup, export functionality, system health checks

## 🛠 Technical Stack

### Backend
- **PHP 7.4+**: Core server-side logic
- **MySQL**: Local database for admin data and sessions
- **Firebase Integration**: 
  - Realtime Database for device communication
  - Cloud Storage for file transfers
  - Cloud Messaging for push notifications
  - Analytics for usage tracking

### Frontend
- **Bootstrap 5**: Responsive UI framework
- **JavaScript (ES6+)**: Client-side functionality
- **Firebase Web SDK**: Real-time data synchronization
- **Chart.js**: Data visualization
- **Font Awesome**: Icon library

### Security
- **CSRF Protection**: Cross-site request forgery prevention
- **Input Sanitization**: XSS and injection attack prevention
- **Session Management**: Secure session handling with timeouts
- **Password Hashing**: bcrypt for secure password storage
- **Login Attempt Limiting**: Brute force attack prevention

## 📦 Installation

### Quick Start
1. Upload all files to your web server
2. Create MySQL database and configure credentials
3. Set admin login credentials
4. Access the panel and login

### Detailed Setup Guides
- [General Installation Guide](installation/README.md)
- [Hostinger Shared Hosting Setup](installation/hostinger-setup.md)
- [Firebase Configuration Guide](installation/firebase-setup.md)

## 🔧 Configuration

### Database Configuration
Edit `config/firebase.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Firebase Configuration
```php
define('FIREBASE_API_KEY', 'your_api_key');
define('FIREBASE_PROJECT_ID', 'your_project_id');
define('FIREBASE_DATABASE_URL', 'your_database_url');
define('FIREBASE_STORAGE_BUCKET', 'your_storage_bucket');
```

### Admin Credentials
```php
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('ADMIN_PASSWORD_HASH', '$2y$10$...');
```

## 🔐 Default Credentials
- **Email**: `admin@yourdomain.com`
- **Password**: `password`

**⚠️ IMPORTANT**: Change these credentials immediately after installation!

## 📱 Android App Integration

This admin panel is designed to work with the ERDMT Android application using the same Firebase project:
- **Package Name**: `com.system.service`
- **Firebase Project**: `remoteadmin-a1089`
- **Database**: `https://remoteadmin-a1089-default-rtdb.firebaseio.com`

## 🌐 Browser Compatibility

### Supported Browsers
- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

### Mobile Support
- Responsive design works on tablets and smartphones
- Touch-friendly interface
- Mobile-optimized tables and forms

## 📊 Data Structure

### Firebase Realtime Database
```
├── devices/
│   └── {device_id}/
│       ├── name, status, lastSeen
│       ├── location, battery, network
│       └── settings, fcm_token
├── commands/
│   └── {device_id}/{command_id}/
├── messages/
│   └── {device_id}/{message_id}/
├── file_transfers/
│   └── {device_id}/{file_id}/
└── activity/
    └── {activity_id}/
```

### Local MySQL Database
- **admin_users**: Administrator accounts
- **login_attempts**: Security logging
- **admin_sessions**: Session management

## 🔍 API Endpoints

### REST API for External Integration
The panel provides REST endpoints for external systems:
- `GET /api/devices` - List all devices
- `POST /api/commands` - Send commands
- `GET /api/status` - System status
- `POST /api/messages` - Send messages

## 🚨 Security Features

### Authentication & Authorization
- Secure login with password hashing
- Session management with timeouts
- CSRF token protection
- IP-based login attempt limiting

### Data Protection
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- Secure file upload handling

### Firebase Security
- Database security rules
- Storage access controls
- API key management
- Domain authorization

## 📈 Performance Optimization

### Frontend Optimization
- Lazy loading for large datasets
- Real-time data caching
- Optimized Firebase queries
- Responsive image loading

### Backend Optimization
- Database query optimization
- Session cleanup automation
- File size limitations
- Memory usage monitoring

## 🧪 Testing

### Manual Testing Checklist
- [ ] Login/logout functionality
- [ ] Device list display and refresh
- [ ] Command sending and status tracking
- [ ] File upload and transfer
- [ ] Message sending and delivery
- [ ] Settings and configuration changes

### Browser Testing
- [ ] Chrome desktop/mobile
- [ ] Firefox desktop/mobile
- [ ] Safari desktop/mobile
- [ ] Edge desktop

## 📚 Documentation

### User Guides
- Admin panel navigation
- Device management procedures
- Command execution guide
- File transfer procedures
- Message management

### Technical Documentation
- Firebase integration details
- Database schema documentation
- API reference
- Security implementation

## 🛠 Maintenance

### Regular Tasks
- Database cleanup (monthly)
- Log file rotation
- Security audit
- Performance monitoring
- Backup verification

### Updates
- Security patches
- Feature updates
- Firebase SDK updates
- PHP version compatibility

## 🆘 Troubleshooting

### Common Issues
1. **Database connection errors**: Check credentials and server status
2. **Firebase connection issues**: Verify API keys and network connectivity
3. **Login problems**: Check password hash and session configuration
4. **File upload failures**: Verify permissions and PHP settings

### Debug Tools
- Browser developer console
- PHP error logs
- Firebase console monitoring
- Network request inspection

## 🤝 Support

### Getting Help
1. Check troubleshooting guides
2. Review error logs
3. Consult Firebase documentation
4. Contact hosting provider for server issues

### Reporting Issues
When reporting issues, please include:
- PHP version and server environment
- Browser and version
- Error messages and logs
- Steps to reproduce the issue

## 📄 License

This software is provided as-is for the ERDMT project. Please ensure compliance with Firebase terms of service and local regulations regarding device management.

---

## Version Information
- **Version**: 1.0.0
- **Release Date**: January 2025
- **PHP Compatibility**: 7.4+
- **Firebase SDK**: 9.22.0
- **Bootstrap**: 5.3.0

**Last Updated**: January 2025