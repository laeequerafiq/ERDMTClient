<?php
// Firebase Configuration
define('FIREBASE_API_KEY', 'AIzaSyAOTx2ur0lCHrhGb7UXwjAUudP7Q3KxJOw');
define('FIREBASE_PROJECT_ID', 'remoteadmin-a1089');
define('FIREBASE_DATABASE_URL', 'https://remoteadmin-a1089-default-rtdb.firebaseio.com');
define('FIREBASE_STORAGE_BUCKET', 'remoteadmin-a1089.firebasestorage.app');
define('FIREBASE_PROJECT_NUMBER', '187299377871');
define('FIREBASE_APP_ID', '1:187299377871:android:ccf92720c908b841d23dba');
define('FIREBASE_SERVER_KEY', 'AAAA8qI7V4M:APA91bEt3kQlQOQ8Z7xWF0N6bQn0ZYr6-8X8VJ3nPV0mFGc9Y1qC2LTZ8w7fN6rD5K3m2GhS4E6bA1wQY3nLzU5KdT2xF7rV9sP8mEhJ6cN1nAzQ4rT5uD3wS8hK2pY6bN1xV9mR7nE'); // Firebase Server Key for FCM

// Database configuration for Hostinger MySQL
define('DB_HOST', '82.197.82.7');
define('DB_NAME', 'u831449802_erdmt_admin');
define('DB_USER', 'u831449802_adminuser');
define('DB_PASS', '01594WainsWains');
define('DB_PORT', '3306');

// Admin panel configuration
define('ADMIN_EMAIL', 'admin@admin.hirely.me');
define('ADMIN_PASSWORD_HASH', '$2y$10$7M2cTHFJCd788L.n3PcQzuSByww6nIt3zFwDcchggozwEv4M.tvQq'); // Hashed password for 01594Wains
define('SESSION_TIMEOUT', 3600); // 1 hour

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // If database connection fails, try to create database
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        
        // Create admin users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE
            )
        ");
        
        // Create login attempts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id INT PRIMARY KEY AUTO_INCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                email VARCHAR(255) NOT NULL,
                attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                success BOOLEAN DEFAULT FALSE
            )
        ");
        
        // Create admin sessions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_sessions (
                id VARCHAR(128) PRIMARY KEY,
                user_id INT NOT NULL,
                ip_address VARCHAR(45) NOT NULL,
                user_agent VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
            )
        ");
        
        // Insert default admin user if not exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE email = ?");
        $stmt->execute([ADMIN_EMAIL]);
        
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO admin_users (email, password_hash) VALUES (?, ?)");
            $stmt->execute([ADMIN_EMAIL, ADMIN_PASSWORD_HASH]);
        }
        
    } catch (PDOException $createError) {
        die("Database connection failed: " . $createError->getMessage());
    }
}

// Helper function to execute Firebase REST API calls
function firebaseRequest($path, $method = 'GET', $data = null) {
    $url = FIREBASE_DATABASE_URL . $path . '.json';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    } else {
        return false;
    }
}

// FCM function is now in includes/fcm.php to avoid duplication
?>