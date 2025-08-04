<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Device Management - ERDMT Admin Panel';
include 'includes/header.php';

// Handle device actions
if ($_POST && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else {
        switch ($_POST['action']) {
            case 'send_command':
                if (isset($_POST['device_id']) && isset($_POST['command'])) {
                    $params = [];
                    if (isset($_POST['params'])) {
                        parse_str($_POST['params'], $params);
                    }
                    
                    if (validateCommandParams($_POST['command'], $params)) {
                        if (sendCommandToDevice($_POST['device_id'], $_POST['command'], $params)) {
                            $success = 'Command sent successfully';
                        } else {
                            $error = 'Failed to send command';
                        }
                    } else {
                        $error = 'Invalid command parameters';
                    }
                }
                break;
                
            case 'update_settings':
                if (isset($_POST['device_id']) && isset($_POST['settings'])) {
                    $settings = json_decode($_POST['settings'], true);
                    if ($settings && updateDeviceSettings($_POST['device_id'], $settings)) {
                        $success = 'Settings updated successfully';
                    } else {
                        $error = 'Failed to update settings';
                    }
                }
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
                        <a class="nav-link active" href="devices.php">
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
                <h1 class="h2">Device Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadDevices()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                            <i class="fas fa-plus"></i> Add Device
                        </button>
                    </div>
                </div>
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

            <!-- Device Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Total Devices</h5>
                            <h3 id="totalDevicesCount">
                                <div class="spinner-border spinner-border-sm"></div>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-success">Online</h5>
                            <h3 id="onlineDevicesCount">
                                <div class="spinner-border spinner-border-sm"></div>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-warning">Offline</h5>
                            <h3 id="offlineDevicesCount">
                                <div class="spinner-border spinner-border-sm"></div>
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-info">Connected Today</h5>
                            <h3 id="todayDevicesCount">
                                <div class="spinner-border spinner-border-sm"></div>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Devices Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Devices</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="devicesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Device ID</th>
                                    <th>Device Name</th>
                                    <th>Status</th>
                                    <th>Last Seen</th>
                                    <th>Location</th>
                                    <th>Battery</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="devicesTableBody">
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDeviceForm">
                    <div class="mb-3">
                        <label for="deviceId" class="form-label">Device ID</label>
                        <input type="text" class="form-control" id="deviceId" required>
                        <div class="form-text">Unique identifier for the device</div>
                    </div>
                    <div class="mb-3">
                        <label for="deviceName" class="form-label">Device Name</label>
                        <input type="text" class="form-control" id="deviceName" required>
                    </div>
                    <div class="mb-3">
                        <label for="deviceOwner" class="form-label">Owner</label>
                        <input type="text" class="form-control" id="deviceOwner">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addDevice()">Add Device</button>
            </div>
        </div>
    </div>
</div>

<!-- Device Details Modal -->
<div class="modal fade" id="deviceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Device Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="deviceDetailsContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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

let devicesData = {};

function loadDevices() {
    // Use orderBy to prevent Firebase indexing warnings
    database.ref('devices').orderByChild('lastSeen').on('value', (snapshot) => {
        devicesData = snapshot.val() || {};
        console.log('Loaded devices:', Object.keys(devicesData).length);
        updateDevicesTable();
        updateDeviceStats();
    });
}

function updateDevicesTable() {
    const tbody = document.getElementById('devicesTableBody');
    let html = '';
    
    if (Object.keys(devicesData).length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted">No devices found</td></tr>';
    } else {
        Object.entries(devicesData).forEach(([deviceId, device]) => {
            const status = getDeviceStatus(device);
            const lastSeen = device.lastSeen ? formatTimestamp(device.lastSeen) : 'Never';
            const location = device.location ? `${device.location.latitude}, ${device.location.longitude}` : 'Unknown';
            const battery = device.battery ? `${device.battery}%` : 'Unknown';
            
            html += `
                <tr>
                    <td><code>${deviceId}</code></td>
                    <td>${device.device_name || device.name || 'Unknown'}</td>
                    <td><span class="badge bg-${status.color}">${status.text}</span></td>
                    <td>${lastSeen}</td>
                    <td>${location}</td>
                    <td>${battery}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-outline-primary" onclick="showDeviceDetails('${deviceId}')" title="Details">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button class="btn btn-outline-success" onclick="sendQuickCommand('${deviceId}', 'get_location')" title="Get Location">
                                <i class="fas fa-map-marker-alt"></i>
                            </button>
                            <button class="btn btn-outline-warning" onclick="sendQuickCommand('${deviceId}', 'take_screenshot')" title="Screenshot">
                                <i class="fas fa-camera"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="sendQuickCommand('${deviceId}', 'lock_device')" title="Lock Device">
                                <i class="fas fa-lock"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    tbody.innerHTML = html;
}

function updateDeviceStats() {
    const total = Object.keys(devicesData).length;
    let online = 0;
    let offline = 0;
    let todayConnected = 0;
    
    const todayStart = new Date();
    todayStart.setHours(0, 0, 0, 0);
    
    Object.values(devicesData).forEach(device => {
        const status = getDeviceStatus(device);
        if (status.text === 'Online') {
            online++;
        } else {
            offline++;
        }
        
        if (device.lastSeen && device.lastSeen >= todayStart.getTime()) {
            todayConnected++;
        }
    });
    
    document.getElementById('totalDevicesCount').textContent = total;
    document.getElementById('onlineDevicesCount').textContent = online;
    document.getElementById('offlineDevicesCount').textContent = offline;
    document.getElementById('todayDevicesCount').textContent = todayConnected;
}

function getDeviceStatus(device) {
    if (!device.lastSeen) return { text: 'Never Connected', color: 'secondary' };
    
    const now = Date.now();
    const lastSeen = device.lastSeen;
    const timeDiff = now - lastSeen;
    
    if (timeDiff < 300000) { // 5 minutes
        return { text: 'Online', color: 'success' };
    } else if (timeDiff < 3600000) { // 1 hour
        return { text: 'Recently Active', color: 'warning' };
    } else {
        return { text: 'Offline', color: 'danger' };
    }
}

function addDevice() {
    const deviceId = document.getElementById('deviceId').value;
    const deviceName = document.getElementById('deviceName').value;
    const deviceOwner = document.getElementById('deviceOwner').value;
    
    if (!deviceId || !deviceName) {
        showAlert('Please fill in all required fields', 'danger');
        return;
    }
    
    const deviceData = {
        name: deviceName,
        owner: deviceOwner || '',
        status: 'offline',
        created: Date.now(),
        lastSeen: null
    };
    
    database.ref(`devices/${deviceId}`).set(deviceData)
        .then(() => {
            showAlert('Device added successfully', 'success');
            document.getElementById('addDeviceForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('addDeviceModal')).hide();
        })
        .catch(error => {
            showAlert('Failed to add device: ' + error.message, 'danger');
        });
}

function showDeviceDetails(deviceId) {
    const device = devicesData[deviceId];
    if (!device) return;
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6>Device Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Device ID:</strong></td><td><code>${deviceId}</code></td></tr>
                    <tr><td><strong>Name:</strong></td><td>${device.name || 'Unknown'}</td></tr>
                    <tr><td><strong>Owner:</strong></td><td>${device.owner || 'Unknown'}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge bg-${getDeviceStatus(device).color}">${getDeviceStatus(device).text}</span></td></tr>
                    <tr><td><strong>Created:</strong></td><td>${device.created ? formatTimestamp(device.created) : 'Unknown'}</td></tr>
                    <tr><td><strong>Last Seen:</strong></td><td>${device.lastSeen ? formatTimestamp(device.lastSeen) : 'Never'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Device Stats</h6>
                <table class="table table-sm">
                    <tr><td><strong>Battery:</strong></td><td>${device.battery ? device.battery + '%' : 'Unknown'}</td></tr>
                    <tr><td><strong>Location:</strong></td><td>${device.location ? `${device.location.latitude}, ${device.location.longitude}` : 'Unknown'}</td></tr>
                    <tr><td><strong>Network:</strong></td><td>${device.network || 'Unknown'}</td></tr>
                    <tr><td><strong>OS Version:</strong></td><td>${device.osVersion || 'Unknown'}</td></tr>
                    <tr><td><strong>App Version:</strong></td><td>${device.appVersion || 'Unknown'}</td></tr>
                </table>
            </div>
        </div>
        <div class="mt-3">
            <h6>Quick Actions</h6>
            <div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-primary" onclick="sendQuickCommand('${deviceId}', 'get_device_info')">
                    <i class="fas fa-info"></i> Get Info
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="sendQuickCommand('${deviceId}', 'get_location')">
                    <i class="fas fa-map-marker-alt"></i> Location
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="sendQuickCommand('${deviceId}', 'take_screenshot')">
                    <i class="fas fa-camera"></i> Screenshot
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="sendQuickCommand('${deviceId}', 'play_sound', {duration: 10})">
                    <i class="fas fa-volume-up"></i> Play Sound
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="sendQuickCommand('${deviceId}', 'lock_device')">
                    <i class="fas fa-lock"></i> Lock
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('deviceDetailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('deviceDetailsModal')).show();
}

function sendQuickCommand(deviceId, command, params = {}) {
    showLoading();
    
    const commandData = {
        command: command,
        params: params,
        timestamp: Date.now(),
        status: 'pending',
        admin_id: '<?php echo $_SESSION['admin_user_id'] ?? 'unknown'; ?>'
    };
    
    const commandId = 'cmd_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    database.ref(`commands/${deviceId}/${commandId}`).set(commandData)
        .then(() => {
            hideLoading();
            showAlert(`Command '${command}' sent successfully`, 'success');
            
            // Send FCM notification if device has token
            const device = devicesData[deviceId];
            if (device && device.fcm_token) {
                // FCM would be sent via server-side script
                console.log('FCM notification would be sent to:', device.fcm_token);
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('Failed to send command: ' + error.message, 'danger');
        });
}

// Load devices on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Devices page loaded, initializing...');
    loadDevices();
    
    // Start auto-refresh after a brief delay to ensure functions are loaded
    setTimeout(() => {
        if (typeof startAutoRefresh === 'function') {
            startAutoRefresh(loadDevices, 30000);
        } else {
            setInterval(loadDevices, 30000);
        }
    }, 1000);
});
</script>

<?php include 'includes/footer.php'; ?>