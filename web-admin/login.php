<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: enhanced-dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        if (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } else {
            $result = login($email, $password);
            if ($result['success']) {
                header('Location: enhanced-dashboard.php');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERDMT Admin - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/admin-style.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in">
            <div class="login-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            
            <h1 class="login-title">Welcome to ERDMT</h1>
            <p class="login-subtitle">Emergency Remote Device Management Tool</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-envelope text-muted"></i>
                        </span>
                        <input type="email" 
                               class="form-control border-start-0" 
                               id="email" 
                               name="email" 
                               placeholder="admin@admin.hirely.me"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-lock text-muted"></i>
                        </span>
                        <input type="password" 
                               class="form-control border-start-0" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                        <button class="btn btn-outline-secondary border-start-0" 
                                type="button" 
                                onclick="togglePassword()"
                                tabindex="-1">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me on this device
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-google">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Sign In to Admin Panel
                </button>
            </form>
            
            <div class="text-center mt-4">
                <div class="border-top pt-4">
                    <p class="text-muted mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Default Credentials
                    </p>
                    <small class="text-muted">
                        <strong>Email:</strong> admin@admin.hirely.me<br>
                        <strong>Password:</strong> 01594Wains
                    </small>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-copyright me-1"></i>
                    2025 ERDMT. Secure Remote Management.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                const forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Auto-focus first input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>