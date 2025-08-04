# ERDMT Web Admin Panel Installation Guide

## Overview
This guide will help you install and configure the ERDMT (Emergency Remote Device Management Tool) web admin panel on shared hosting services like Hostinger.

## Prerequisites
- PHP 7.4 or higher
- MySQL database
- Web hosting with PHP support
- Firebase project (already configured)

## Installation Steps

### 1. Upload Files
1. Download all files from the `web-admin` folder
2. Upload them to your hosting's public_html or www directory
3. Ensure all files maintain their directory structure

### 2. Database Setup
1. Create a new MySQL database through your hosting control panel
2. Note down the database credentials:
   - Host (usually localhost)
   - Database name
   - Username
   - Password

### 3. Configuration
1. Edit `config/firebase.php`
2. Update the database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

3. Update the admin login credentials:
```php
define('ADMIN_EMAIL', 'your-admin@email.com');
define('ADMIN_PASSWORD_HASH', '$2y$10$...'); // Generate new hash
```

### 4. Generate Password Hash
Use this PHP script to generate a password hash:
```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

### 5. Set File Permissions
Set the following permissions:
- All PHP files: 644
- Directories: 755
- config/ directory: 700 (recommended)

### 6. Access the Panel
1. Navigate to your domain in a web browser
2. You should see the login page
3. Login with your configured credentials

## Default Credentials
- Email: admin@yourdomain.com
- Password: password

**IMPORTANT:** Change these immediately after first login!

## Features Included

### Dashboard
- Real-time device statistics
- Recent activity monitoring
- Quick action buttons
- Storage usage tracking

### Device Management
- View all connected devices
- Monitor device status (online/offline)
- Send commands to individual devices
- Bulk operations for multiple devices

### Command Center
- Send various commands to devices
- Command history and status tracking
- Quick commands for common tasks
- Emergency actions (lock all devices)

### File Manager
- Upload files to devices
- Monitor file transfers
- File preview and download
- Storage management

### Message Center
- Send messages to devices
- Broadcast messages to all devices
- Message history and delivery status
- Quick message templates

### Settings
- Admin profile management
- Security settings
- System configuration
- Maintenance tools

## Security Features

### Authentication
- Secure login system
- Session management
- CSRF protection
- Login attempt limiting
- IP-based lockouts

### Data Protection
- Password hashing
- Input sanitization
- SQL injection prevention
- XSS protection

## Firebase Integration

The admin panel integrates with Firebase services:
- **Realtime Database**: Device data and commands
- **Cloud Storage**: File transfers
- **Cloud Messaging**: Push notifications
- **Analytics**: Usage tracking

## Hostinger-Specific Setup

### 1. File Manager Upload
1. Login to Hostinger control panel
2. Go to File Manager
3. Navigate to public_html
4. Upload the entire web-admin folder contents
5. Extract if uploaded as ZIP

### 2. Database Creation
1. Go to MySQL Databases in control panel
2. Create new database
3. Create database user
4. Assign user to database with all privileges

### 3. Domain Configuration
If using subdomain:
1. Create subdomain (e.g., admin.yourdomain.com)
2. Point it to the admin panel directory
3. Update Firebase authorized domains

## Troubleshooting

### Common Issues

**1. Database Connection Error**
- Check database credentials in config/firebase.php
- Ensure database user has proper privileges
- Verify database server is running

**2. Firebase Connection Error**
- Check Firebase API key and project ID
- Verify Firebase project is active
- Check internet connectivity

**3. Login Issues**
- Verify admin credentials in config/firebase.php
- Check password hash generation
- Clear browser cache

**4. File Upload Errors**
- Check PHP upload_max_filesize setting
- Verify directory permissions
- Ensure sufficient disk space

### Log Files
Check these logs for errors:
- PHP error log
- Web server error log
- Browser console for JavaScript errors

## Security Recommendations

### 1. Change Default Credentials
- Update admin email and password
- Use strong passwords (12+ characters)
- Enable two-factor authentication

### 2. Regular Updates
- Keep PHP updated
- Monitor for security patches
- Regular database backups

### 3. Access Control
- Restrict admin panel access by IP
- Use HTTPS (SSL certificate)
- Regular security audits

### 4. File Permissions
- Restrict config directory access
- Use .htaccess for additional security
- Regular permission audits

## Backup Strategy

### 1. Database Backup
```sql
mysqldump -u username -p database_name > backup.sql
```

### 2. File Backup
- Backup entire web-admin directory
- Include configuration files
- Store backups securely offsite

### 3. Automated Backups
- Set up cron jobs for regular backups
- Use hosting backup features
- Test restore procedures regularly

## Performance Optimization

### 1. Caching
- Enable PHP OPcache
- Use browser caching
- Optimize database queries

### 2. Resource Optimization
- Minify CSS/JavaScript
- Optimize images
- Use CDN for static assets

### 3. Database Optimization
- Regular table optimization
- Index optimization
- Query performance monitoring

## Support

For technical support:
1. Check troubleshooting section
2. Review error logs
3. Contact hosting provider for server issues
4. Firebase console for Firebase-related issues

## Version Information
- Version: 1.0.0
- Last Updated: January 2025
- PHP Compatibility: 7.4+
- Database: MySQL 5.7+ / MariaDB 10.2+

---

**Note**: This admin panel is specifically designed for the ERDMT Android application. Ensure your Android app is properly configured with the same Firebase project for full functionality.