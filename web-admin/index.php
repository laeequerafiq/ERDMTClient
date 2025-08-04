<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Redirect to enhanced dashboard
header('Location: enhanced-dashboard.php');
exit;

$pageTitle = 'Dashboard - ERDMT Admin Panel';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
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
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Devices
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalDevices">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Online Devices
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="onlineDevices">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-wifi fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Commands Sent
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="commandsSent">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-terminal fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Storage Used
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="storageUsed">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-hdd fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                        </div>
                        <div class="card-body">
                            <div id="recentActivity">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="commands.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-terminal"></i> Send Command
                                </a>
                                <a href="files.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-upload"></i> Upload File
                                </a>
                                <a href="messages.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </a>
                                <a href="devices.php" class="list-group-item list-group-item-action">
                                    <i class="fas fa-plus"></i> Add Device
                                </a>
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

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const database = firebase.database();

// Load dashboard data
function loadDashboardData() {
    // Load device statistics
    database.ref('devices').once('value', (snapshot) => {
        const devices = snapshot.val() || {};
        const totalDevices = Object.keys(devices).length;
        let onlineDevices = 0;
        
        Object.values(devices).forEach(device => {
            if (device.status === 'online' && device.lastSeen && 
                (Date.now() - device.lastSeen) < 300000) { // 5 minutes
                onlineDevices++;
            }
        });
        
        document.getElementById('totalDevices').textContent = totalDevices;
        document.getElementById('onlineDevices').textContent = onlineDevices;
    });
    
    // Load command statistics
    database.ref('commands').once('value', (snapshot) => {
        const commands = snapshot.val() || {};
        const commandsSent = Object.keys(commands).length;
        document.getElementById('commandsSent').textContent = commandsSent;
    });
    
    // Load storage statistics
    database.ref('storage_usage').once('value', (snapshot) => {
        const usage = snapshot.val() || { used: 0 };
        const usedMB = Math.round(usage.used / (1024 * 1024) * 100) / 100;
        document.getElementById('storageUsed').textContent = usedMB + ' MB';
    });
    
    // Load recent activity
    database.ref('activity').orderByChild('timestamp').limitToLast(10).once('value', (snapshot) => {
        const activities = snapshot.val() || {};
        let html = '';
        
        Object.values(activities).reverse().forEach(activity => {
            const date = new Date(activity.timestamp).toLocaleString();
            html += `
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-${getActivityIcon(activity.type)} text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold">${activity.description}</div>
                        <div class="text-muted small">${date}</div>
                    </div>
                </div>
            `;
        });
        
        document.getElementById('recentActivity').innerHTML = html || '<p class="text-muted">No recent activity</p>';
    });
}

function getActivityIcon(type) {
    const icons = {
        'device_connect': 'mobile-alt',
        'command_sent': 'terminal',
        'file_upload': 'upload',
        'message_sent': 'paper-plane',
        'default': 'info-circle'
    };
    return icons[type] || icons['default'];
}

function refreshData() {
    loadDashboardData();
}

// Load data on page load
document.addEventListener('DOMContentLoaded', loadDashboardData);

// Auto-refresh every 30 seconds
setInterval(loadDashboardData, 30000);
</script>

<?php include 'includes/footer.php'; ?>