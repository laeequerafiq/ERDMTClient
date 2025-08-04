<?php
// Authentication functions

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function login($email, $password) {
    global $pdo;
    
    // Check if IP is locked out
    if (isIPLockedOut($_SERVER['REMOTE_ADDR'])) {
        return ['success' => false, 'message' => 'Too many failed attempts. Please try again later.'];
    }
    
    // Log login attempt
    logLoginAttempt($_SERVER['REMOTE_ADDR'], $email, false);
    
    // Find user
    $stmt = $pdo->prepare("SELECT id, email, password_hash FROM admin_users WHERE email = ? AND is_active = TRUE");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_login_time'] = time();
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        // Log successful attempt
        logLoginAttempt($_SERVER['REMOTE_ADDR'], $email, true);
        
        // Create session record
        createAdminSession($user['id']);
        
        return ['success' => true, 'message' => 'Login successful'];
    } else {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
}

function logout() {
    // Invalidate session in database
    if (isset($_SESSION['admin_session_id'])) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE admin_sessions SET is_active = FALSE WHERE id = ?");
        $stmt->execute([$_SESSION['admin_session_id']]);
    }
    
    // Clear session
    session_unset();
    session_destroy();
    
    header('Location: login.php');
    exit;
}

function isIPLockedOut($ip) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE ip_address = ? 
        AND success = FALSE 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->execute([$ip, LOGIN_LOCKOUT_TIME]);
    $result = $stmt->fetch();
    
    return $result['attempts'] >= MAX_LOGIN_ATTEMPTS;
}

function logLoginAttempt($ip, $email, $success) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, email, success) VALUES (?, ?, ?)");
    $stmt->execute([$ip, $email, $success]);
}

function createAdminSession($userId) {
    global $pdo;
    
    $sessionId = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', time() + SESSION_TIMEOUT);
    
    $stmt = $pdo->prepare("
        INSERT INTO admin_sessions (id, user_id, ip_address, user_agent, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $sessionId,
        $userId,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $expiresAt
    ]);
    
    $_SESSION['admin_session_id'] = $sessionId;
}

function checkSessionTimeout() {
    if (isset($_SESSION['admin_login_time'])) {
        if (time() - $_SESSION['admin_login_time'] > SESSION_TIMEOUT) {
            logout();
        }
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function cleanOldSessions() {
    global $pdo;
    
    // Clean expired sessions
    $stmt = $pdo->prepare("DELETE FROM admin_sessions WHERE expires_at < NOW()");
    $stmt->execute();
    
    // Clean old login attempts (older than 24 hours)
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $stmt->execute();
}

// Check session timeout on every request
if (isLoggedIn()) {
    checkSessionTimeout();
}

// Clean old data periodically (1% chance per request)
if (rand(1, 100) === 1) {
    cleanOldSessions();
}
?>