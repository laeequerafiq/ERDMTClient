# ERDMT Deployment Guide

## 🚀 Quick Deploy to GitHub

### 1. GitHub Repository Setup
```bash
# Initialize repository
git init
git add .
git commit -m "Initial commit: Complete ERDMT project"

# Add remote and push
git remote add origin https://github.com/yourusername/erdmt.git
git branch -M main
git push -u origin main
```

### 2. GitHub Codespaces Deployment
```bash
# Open in Codespaces
# Click "Code" → "Codespaces" → "Create codespace on main"

# Build APK (one command)
./build-apk.sh

# Start web admin
cd web-admin && php -S 0.0.0.0:5000
```

## 🌐 Web Admin Deployment

### Option 1: Shared Hosting (Hostinger/cPanel)
```bash
# Upload web-admin/ folder to public_html/
# Configure database in config/firebase.php
# Set file permissions: 755 for directories, 644 for files
```

### Option 2: VPS/Cloud Server
```bash
# Install PHP 8.0+
sudo apt update && sudo apt install php8.2 php8.2-mysql

# Clone repository
git clone https://github.com/yourusername/erdmt.git
cd erdmt/web-admin

# Start server
php -S 0.0.0.0:5000
```

### Option 3: Replit Deployment
```bash
# Fork repository to Replit
# Configure secrets in Environment variables
# Use "Web Admin" workflow for auto-deployment
```

## 📱 Android APK Distribution

### Development Testing
```bash
# Build APK in Codespaces
./build-apk.sh

# Download APK from browser
# Install via ADB: adb install app-debug.apk
```

### Production Distribution
```bash
# Generate signed APK for Play Store
./gradlew assembleRelease

# Or distribute directly as APK file
# Upload to your hosting/Google Drive/GitHub Releases
```

## 🔧 Configuration Requirements

### Firebase Setup
1. **Create Firebase Project**
   - Go to https://console.firebase.google.com/
   - Create new project: "erdmt-yourname"
   - Enable Realtime Database, Storage, FCM

2. **Configure Android App**
   - Add Android app with package: `com.system.service`
   - Download `google-services.json` to `app/` directory

3. **Update Web Admin Config**
   ```php
   // web-admin/config/firebase.php
   define('FIREBASE_PROJECT_ID', 'your-project-id');
   define('FIREBASE_DATABASE_URL', 'your-database-url');
   ```

### Database Setup
```sql
-- MySQL database will be created automatically
-- Default admin credentials:
-- Email: admin@admin.hirely.me
-- Password: 01594Wains
```

## 🔒 Security Checklist

### Production Deployment
- [ ] Change default admin password
- [ ] Enable HTTPS/SSL certificate
- [ ] Configure Firebase security rules
- [ ] Set proper file permissions (755/644)
- [ ] Enable error logging
- [ ] Configure backup system

### Firebase Security Rules
```javascript
{
  "rules": {
    "devices": {
      ".read": "auth != null",
      ".write": "auth != null"
    }
  }
}
```

## 📊 Monitoring & Maintenance

### Performance Monitoring
- Check Firebase usage quotas
- Monitor server resources
- Review error logs regularly
- Test APK functionality monthly

### Updates & Maintenance
```bash
# Pull latest changes
git pull origin main

# Rebuild APK
./build-apk.sh

# Update web admin
# Upload new files to hosting
```

## 🆘 Troubleshooting

### Common Issues
1. **APK build fails**
   - Check Android SDK installation
   - Verify Java 17+ is installed
   - Run: `./gradlew clean assembleDebug --info`

2. **Web admin login fails**
   - Check database connection
   - Verify admin credentials
   - Test with: `php web-admin/test-firebase.php`

3. **Firebase connection issues**
   - Verify project configuration
   - Check API keys and URLs
   - Test Firebase rules

### Support Resources
- **Documentation**: See `COMPLETE_SETUP_GUIDE.md`
- **Firebase Console**: https://console.firebase.google.com/
- **Android Studio**: For advanced debugging
- **GitHub Issues**: Report bugs and get help

---

## ✅ Deployment Checklist

- [ ] Firebase project created and configured
- [ ] Android app builds successfully in Codespaces
- [ ] Web admin panel accessible and functional
- [ ] Default admin login working
- [ ] Firebase connection tested
- [ ] Security settings configured
- [ ] Documentation reviewed
- [ ] Repository pushed to GitHub

**Your ERDMT project is ready for production deployment!**