<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Settings - ERDMT Admin Panel';
include 'includes/header.php';

// Handle settings update
if ($_POST && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        switch ($_POST['action']) {
            case 'update_profile':
                // Handle profile update
                $success = 'Profile updated successfully';
                break;
            case 'change_password':
                // Handle password change
                $success = 'Password changed successfully';
                break;
            case 'update_system':
                // Handle system settings update
                $success = 'System settings updated successfully';
                break;
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="devices.php">
                            <i class="fas fa-mobile-alt"></i> Devices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commands.php">
                            <i class="fas fa-terminal"></i> Commands
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="files.php">
                            <i class="fas fa-folder"></i> File Manager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="fas fa-comments"></i> Messages
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Settings</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- System Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">System Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>ERDMT Version:</strong></td>
                                    <td>1.0.0</td>
                                </tr>
                                <tr>
                                    <td><strong>Firebase Project:</strong></td>
                                    <td><?php echo FIREBASE_PROJECT_ID; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Database URL:</strong></td>
                                    <td><small><?php echo FIREBASE_DATABASE_URL; ?></small></td>
                                </tr>
                                <tr>
                                    <td><strong>Storage Bucket:</strong></td>
                                    <td><small><?php echo FIREBASE_STORAGE_BUCKET; ?></small></td>
                                </tr>
                                <tr>
                                    <td><strong>PHP Version:</strong></td>
                                    <td><?php echo phpversion(); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Server Time:</strong></td>
                                    <td><?php echo date('Y-m-d H:i:s T'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 id="totalDevicesStat">-</h4>
                                    <small class="text-muted">Total Devices</small>
                                </div>
                                <div class="col-6">
                                    <h4 id="onlineDevicesStat">-</h4>
                                    <small class="text-muted">Online Now</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h4 id="commandsStat">-</h4>
                                    <small class="text-muted">Commands Today</small>
                                </div>
                                <div class="col-6">
                                    <h4 id="messagesStat">-</h4>
                                    <small class="text-muted">Messages Today</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                <i class="fas fa-user"></i> Profile
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt"></i> Security
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                <i class="fas fa-cogs"></i> System
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button" role="tab">
                                <i class="fas fa-tools"></i> Maintenance
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_profile">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="adminEmail" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="adminEmail" name="email" value="<?php echo $_SESSION['admin_email'] ?? ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="adminName" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="adminName" name="name" value="">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="timezone" class="form-label">Timezone</label>
                                            <select class="form-select" id="timezone" name="timezone">
                                                <option value="UTC">UTC</option>
                                                <option value="America/New_York">Eastern Time</option>
                                                <option value="America/Chicago">Central Time</option>
                                                <option value="America/Denver">Mountain Time</option>
                                                <option value="America/Los_Angeles">Pacific Time</option>
                                                <option value="Europe/London">London</option>
                                                <option value="Europe/Paris">Paris</option>
                                                <option value="Asia/Tokyo">Tokyo</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="language" class="form-label">Language</label>
                                            <select class="form-select" id="language" name="language">
                                                <option value="en">English</option>
                                                <option value="es">Spanish</option>
                                                <option value="fr">French</option>
                                                <option value="de">German</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Change Password</h6>
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="change_password">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-warning">Change Password</button>
                                    </form>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>Security Settings</h6>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="twoFactorAuth">
                                        <label class="form-check-label" for="twoFactorAuth">
                                            Enable Two-Factor Authentication
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="loginNotifications" checked>
                                        <label class="form-check-label" for="loginNotifications">
                                            Email login notifications
                                        </label>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="sessionTimeout" class="form-label">Session Timeout (minutes)</label>
                                        <select class="form-select" id="sessionTimeout">
                                            <option value="30">30 minutes</option>
                                            <option value="60" selected>1 hour</option>
                                            <option value="120">2 hours</option>
                                            <option value="240">4 hours</option>
                                        </select>
                                    </div>
                                    
                                    <button type="button" class="btn btn-outline-secondary" onclick="showActiveSessions()">
                                        View Active Sessions
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- System Tab -->
                        <div class="tab-pane fade" id="system" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_system">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Firebase Settings</h6>
                                        <div class="mb-3">
                                            <label for="firebaseApiKey" class="form-label">API Key</label>
                                            <input type="text" class="form-control" id="firebaseApiKey" value="<?php echo FIREBASE_API_KEY; ?>" readonly>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="firebaseProject" class="form-label">Project ID</label>
                                            <input type="text" class="form-control" id="firebaseProject" value="<?php echo FIREBASE_PROJECT_ID; ?>" readonly>
                                        </div>
                                        
                                        <button type="button" class="btn btn-outline-primary" onclick="testFirebaseConnection()">
                                            Test Firebase Connection
                                        </button>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <h6>System Preferences</h6>
                                        <div class="mb-3">
                                            <label for="autoRefresh" class="form-label">Auto-refresh interval (seconds)</label>
                                            <select class="form-select" id="autoRefresh">
                                                <option value="15">15 seconds</option>
                                                <option value="30" selected>30 seconds</option>
                                                <option value="60">1 minute</option>
                                                <option value="300">5 minutes</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="enableNotifications" checked>
                                            <label class="form-check-label" for="enableNotifications">
                                                Enable browser notifications
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="enableSounds">
                                            <label class="form-check-label" for="enableSounds">
                                                Enable notification sounds
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Save System Settings</button>
                            </form>
                        </div>

                        <!-- Maintenance Tab -->
                        <div class="tab-pane fade" id="maintenance" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Data Management</h6>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary" onclick="exportData()">
                                            <i class="fas fa-download"></i> Export All Data
                                        </button>
                                        <button class="btn btn-outline-info" onclick="cleanupOldData()">
                                            <i class="fas fa-broom"></i> Cleanup Old Data (30+ days)
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="optimizeDatabase()">
                                            <i class="fas fa-database"></i> Optimize Database
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="clearCache()">
                                            <i class="fas fa-trash"></i> Clear Cache
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6>System Status</h6>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Database Connection</span>
                                            <span class="badge bg-success" id="dbStatus">Connected</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Firebase Status</span>
                                            <span class="badge bg-success" id="firebaseStatus">Connected</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Storage Usage</span>
                                            <span id="storageUsage">Loading...</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar" id="storageProgress" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    
                                    <button type="button" class="btn btn-outline-primary" onclick="runSystemCheck()">
                                        <i class="fas fa-check-circle"></i> Run System Check
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Firebase configuration
const firebaseConfig = {
    apiKey: "<?php echo FIREBASE_API_KEY; ?>",
    authDomain: "<?php echo FIREBASE_PROJECT_ID; ?>.firebaseapp.com",
    databaseURL: "<?php echo FIREBASE_DATABASE_URL; ?>",
    projectId: "<?php echo FIREBASE_PROJECT_ID; ?>",
    storageBucket: "<?php echo FIREBASE_STORAGE_BUCKET; ?>",
    messagingSenderId: "<?php echo FIREBASE_PROJECT_NUMBER; ?>",
    appId: "<?php echo FIREBASE_APP_ID; ?>"
};

firebase.initializeApp(firebaseConfig);
const database = firebase.database();

function loadQuickStats() {
    // Load devices
    database.ref('devices').once('value', (snapshot) => {
        const devices = snapshot.val() || {};
        const total = Object.keys(devices).length;
        let online = 0;
        
        Object.values(devices).forEach(device => {
            if (device.status === 'online' && device.lastSeen && 
                (Date.now() - device.lastSeen) < 300000) {
                online++;
            }
        });
        
        document.getElementById('totalDevicesStat').textContent = total;
        document.getElementById('onlineDevicesStat').textContent = online;
    });
    
    // Load today's commands
    const todayStart = new Date();
    todayStart.setHours(0, 0, 0, 0);
    
    database.ref('commands').once('value', (snapshot) => {
        const commands = snapshot.val() || {};
        let todayCommands = 0;
        
        Object.values(commands).forEach(deviceCommands => {
            Object.values(deviceCommands || {}).forEach(command => {
                if (command.timestamp >= todayStart.getTime()) {
                    todayCommands++;
                }
            });
        });
        
        document.getElementById('commandsStat').textContent = todayCommands;
    });
    
    // Load today's messages
    database.ref('messages').once('value', (snapshot) => {
        const messages = snapshot.val() || {};
        let todayMessages = 0;
        
        Object.values(messages).forEach(deviceMessages => {
            Object.values(deviceMessages || {}).forEach(message => {
                if (message.timestamp >= todayStart.getTime()) {
                    todayMessages++;
                }
            });
        });
        
        document.getElementById('messagesStat').textContent = todayMessages;
    });
}

function testFirebaseConnection() {
    showLoading();
    
    database.ref('.info/connected').once('value', (snapshot) => {
        hideLoading();
        if (snapshot.val() === true) {
            showAlert('Firebase connection successful!', 'success');
        } else {
            showAlert('Firebase connection failed!', 'danger');
        }
    }).catch(error => {
        hideLoading();
        showAlert('Firebase connection error: ' + error.message, 'danger');
    });
}

function exportData() {
    if (!confirm('Export all system data? This may take a few minutes.')) return;
    
    showLoading();
    
    const exportData = {};
    const promises = [
        database.ref('devices').once('value').then(snapshot => { exportData.devices = snapshot.val(); }),
        database.ref('commands').once('value').then(snapshot => { exportData.commands = snapshot.val(); }),
        database.ref('messages').once('value').then(snapshot => { exportData.messages = snapshot.val(); }),
        database.ref('file_transfers').once('value').then(snapshot => { exportData.file_transfers = snapshot.val(); }),
        database.ref('activity').once('value').then(snapshot => { exportData.activity = snapshot.val(); })
    ];
    
    Promise.all(promises)
        .then(() => {
            hideLoading();
            
            const dataStr = JSON.stringify(exportData, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `erdmt_export_${new Date().toISOString().split('T')[0]}.json`;
            link.click();
            
            showAlert('Data exported successfully!', 'success');
        })
        .catch(error => {
            hideLoading();
            showAlert('Export failed: ' + error.message, 'danger');
        });
}

function cleanupOldData() {
    if (!confirm('Clean up data older than 30 days? This action cannot be undone.')) return;
    
    showLoading();
    
    const cutoffTime = Date.now() - (30 * 24 * 60 * 60 * 1000); // 30 days ago
    
    // Clean old activities
    database.ref('activity').once('value', (snapshot) => {
        const activities = snapshot.val() || {};
        const updates = {};
        
        Object.entries(activities).forEach(([activityId, activity]) => {
            if (activity.timestamp < cutoffTime) {
                updates[activityId] = null; // Delete
            }
        });
        
        return database.ref('activity').update(updates);
    })
    .then(() => {
        hideLoading();
        showAlert('Old data cleaned up successfully!', 'success');
    })
    .catch(error => {
        hideLoading();
        showAlert('Cleanup failed: ' + error.message, 'danger');
    });
}

function optimizeDatabase() {
    showAlert('Database optimization is not available in Firebase Realtime Database', 'info');
}

function clearCache() {
    // Clear browser cache
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }
    
    // Clear localStorage
    localStorage.clear();
    sessionStorage.clear();
    
    showAlert('Cache cleared successfully!', 'success');
}

function runSystemCheck() {
    showLoading();
    
    const checks = [];
    
    // Check Firebase connection
    checks.push(
        database.ref('.info/connected').once('value')
            .then(snapshot => ({
                name: 'Firebase Connection',
                status: snapshot.val() === true ? 'success' : 'error',
                message: snapshot.val() === true ? 'Connected' : 'Disconnected'
            }))
    );
    
    // Check database write permissions
    checks.push(
        database.ref('system_check').set({ timestamp: Date.now() })
            .then(() => ({
                name: 'Database Write',
                status: 'success',
                message: 'Write permissions OK'
            }))
            .catch(error => ({
                name: 'Database Write',
                status: 'error',
                message: 'Write failed: ' + error.message
            }))
    );
    
    Promise.all(checks)
        .then(results => {
            hideLoading();
            
            let html = '<div class="list-group">';
            results.forEach(result => {
                const icon = result.status === 'success' ? 'check-circle text-success' : 'exclamation-triangle text-danger';
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${result.name}</span>
                        <span>
                            <i class="fas fa-${icon}"></i> ${result.message}
                        </span>
                    </div>
                `;
            });
            html += '</div>';
            
            showModal('System Check Results', html);
        })
        .catch(error => {
            hideLoading();
            showAlert('System check failed: ' + error.message, 'danger');
        });
}

function showActiveSessions() {
    // This would require server-side implementation
    showAlert('Active sessions feature requires server-side implementation', 'info');
}

function showModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">${content}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
    
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
    });
}

// Password confirmation validation
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    
    function validatePassword() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
    
    // Load quick stats
    loadQuickStats();
});
</script>

<?php include 'includes/footer.php'; ?>