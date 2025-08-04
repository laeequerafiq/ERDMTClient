# Hostinger Shared Hosting Setup Guide

## Step-by-Step Installation for Hostinger

### Step 1: Access Hostinger Control Panel
1. Login to your Hostinger account at https://hostinger.com
2. Go to your hosting control panel (hPanel)
3. Select the domain where you want to install the admin panel

### Step 2: Create MySQL Database
1. In hPanel, click on "MySQL Databases"
2. Create a new database:
   - Database name: `erdmt_admin` (or your preferred name)
   - Click "Create Database"
3. Create a database user:
   - Username: `erdmt_user` (or your preferred name)
   - Password: Generate a strong password
   - Click "Create User"
4. Add user to database:
   - Select the database and user
   - Grant all privileges
   - Click "Add User to Database"
5. **Save these credentials** - you'll need them later!

### Step 3: Upload Admin Panel Files
1. In hPanel, click on "File Manager"
2. Navigate to `public_html` directory
3. Create a new folder called `admin` (optional - for subdirectory installation)
4. Upload all files from the `web-admin` folder:
   - You can drag and drop files directly
   - Or use the "Upload Files" button
   - Maintain the folder structure exactly as provided

### Step 4: Configure Database Connection
1. In File Manager, navigate to `config/firebase.php`
2. Click "Edit" on the file
3. Update the database configuration:
```php
// Replace these with your actual database credentials from Step 2
define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456789_erdmt_admin'); // Your actual database name
define('DB_USER', 'u123456789_erdmt_user');  // Your actual username
define('DB_PASS', 'your_strong_password');    // Your actual password
```
4. Save the file

### Step 5: Set Admin Credentials
1. Generate a password hash using online PHP tools or create a temporary file:
```php
<?php
echo password_hash('YourSecurePassword123!', PASSWORD_DEFAULT);
?>
```
2. Update the admin credentials in `config/firebase.php`:
```php
define('ADMIN_EMAIL', 'admin@yourdomain.com');
define('ADMIN_PASSWORD_HASH', '$2y$10$...[your generated hash]');
```
3. Save the file and delete any temporary hash generation files

### Step 6: Set File Permissions (If Needed)
Hostinger usually sets correct permissions automatically, but if you encounter issues:
1. Select all files in File Manager
2. Right-click and choose "Permissions"
3. Set directories to 755 and files to 644

### Step 7: Test Installation
1. Open your web browser
2. Navigate to your domain:
   - Main domain: `https://yourdomain.com`
   - Subdirectory: `https://yourdomain.com/admin`
   - Subdomain: `https://admin.yourdomain.com` (if configured)
3. You should see the ERDMT Admin login page

### Step 8: First Login
1. Enter your admin email and password
2. If successful, you'll see the dashboard
3. **Immediately change the default password** in Settings

## Hostinger-Specific Configuration

### PHP Version
1. In hPanel, go to "PHP Configuration"
2. Ensure PHP 7.4 or higher is selected
3. Enable these extensions if not already enabled:
   - mysqli
   - curl
   - json
   - openssl

### SSL Certificate (Recommended)
1. In hPanel, go to "SSL/TLS"
2. Enable "Force HTTPS Redirect"
3. Install Let's Encrypt certificate (free)

### Subdomain Setup (Optional)
1. In hPanel, go to "Subdomains"
2. Create subdomain: `admin.yourdomain.com`
3. Point it to the admin panel directory
4. Update Firebase authorized domains to include the subdomain

## Firebase Configuration for Hostinger

### Update Firebase Console
1. Go to Firebase Console: https://console.firebase.google.com
2. Select your project (`remoteadmin-a1089`)
3. Go to Project Settings > General
4. Under "Your apps", find the web app configuration
5. Add your domain to "Authorized domains":
   - `yourdomain.com`
   - `admin.yourdomain.com` (if using subdomain)

### Database Rules (If Needed)
Update Firebase Realtime Database rules for web access:
```json
{
  "rules": {
    ".read": "auth != null",
    ".write": "auth != null"
  }
}
```

## Testing Checklist

### ✅ Basic Functionality
- [ ] Login page loads correctly
- [ ] Can login with admin credentials
- [ ] Dashboard displays without errors
- [ ] Device list loads (may be empty initially)
- [ ] Commands page is accessible
- [ ] File manager loads
- [ ] Messages page works
- [ ] Settings page opens

### ✅ Database Connection
- [ ] No database connection errors
- [ ] Admin user can be created
- [ ] Session management works
- [ ] Login attempts are logged

### ✅ Firebase Integration
- [ ] Firebase connection successful
- [ ] Can read from Realtime Database
- [ ] Can write to Realtime Database
- [ ] No CORS errors in browser console

## Common Hostinger Issues & Solutions

### Issue 1: Database Connection Failed
**Problem**: "Database connection failed" error
**Solution**:
- Double-check database credentials
- Ensure database user has all privileges
- Check if database name includes prefix (e.g., `u123456789_`)

### Issue 2: File Permissions Error
**Problem**: Cannot write to files or directories
**Solution**:
- Set directory permissions to 755
- Set file permissions to 644
- Check if config directory is writable

### Issue 3: PHP Errors
**Problem**: PHP errors or blank pages
**Solution**:
- Check PHP error logs in hPanel
- Ensure PHP 7.4+ is enabled
- Enable required PHP extensions

### Issue 4: Firebase CORS Errors
**Problem**: CORS errors in browser console
**Solution**:
- Add domain to Firebase authorized domains
- Ensure HTTPS is enabled
- Check Firebase project configuration

### Issue 5: Session Issues
**Problem**: Frequent logouts or session errors
**Solution**:
- Check session configuration in PHP
- Ensure cookies are enabled
- Verify secure connection (HTTPS)

## Hostinger Support Resources

### Knowledge Base
- PHP Configuration: https://support.hostinger.com/en/articles/1583579
- MySQL Databases: https://support.hostinger.com/en/articles/1583417
- File Manager: https://support.hostinger.com/en/articles/1583369

### Contact Support
- Live Chat: Available 24/7 in hPanel
- Email: support@hostinger.com
- Knowledge Base: https://support.hostinger.com

## Security Recommendations for Hostinger

### 1. Enable Security Features
- Enable "Website Security" in hPanel
- Use Cloudflare (free tier available)
- Enable firewall protection

### 2. Regular Backups
- Enable automatic backups in hPanel
- Download manual backups regularly
- Test backup restoration process

### 3. Update Regularly
- Keep admin panel files updated
- Monitor security notifications
- Update PHP version when new versions are available

### 4. Access Control
- Use strong passwords
- Enable two-factor authentication for Hostinger account
- Limit admin panel access to specific IPs if possible

## Performance Tips for Hostinger

### 1. Enable Caching
- Use Hostinger's built-in caching
- Enable browser caching
- Optimize database queries

### 2. Resource Management
- Monitor resource usage in hPanel
- Optimize large files
- Use efficient database queries

### 3. CDN Usage
- Consider using Cloudflare CDN
- Optimize image sizes
- Minify CSS/JavaScript files

---

**Note**: Hostinger configurations may vary slightly based on your hosting plan and location. Always refer to the latest Hostinger documentation for the most current information.