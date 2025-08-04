<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Message Center - ERDMT Admin Panel';
include 'includes/header.php';

// Handle message sending
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else if (isset($_POST['device_id']) && isset($_POST['message'])) {
        $deviceId = $_POST['device_id'];
        $message = sanitizeInput($_POST['message']);
        
        if (sendMessageToDevice($deviceId, $message)) {
            $success = 'Message sent successfully';
        } else {
            $error = 'Failed to send message';
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
                        <a class="nav-link active" href="messages.php">
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
                <h1 class="h2">Message Center</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadMessages()">
                            <i class="fas fa-sync-alt"></i> Refresh
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

            <!-- Send Message Section -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Send Message</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="send_message">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="deviceSelect" class="form-label">Select Device</label>
                                            <select class="form-select" name="device_id" id="deviceSelect" required>
                                                <option value="">Loading devices...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="messageType" class="form-label">Message Type</label>
                                            <select class="form-select" id="messageType" onchange="updateMessageTemplate()">
                                                <option value="custom">Custom Message</option>
                                                <option value="alert">Alert Message</option>
                                                <option value="warning">Warning</option>
                                                <option value="info">Information</option>
                                                <option value="emergency">Emergency</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="messageContent" class="form-label">Message</label>
                                    <textarea class="form-control" name="message" id="messageContent" rows="4" required placeholder="Enter your message here..."></textarea>
                                    <div class="form-text">Maximum 500 characters</div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Messages</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="sendQuickMessage('location', 'Please share your current location immediately.')">
                                    <i class="fas fa-map-marker-alt"></i> Request Location
                                </button>
                                <button class="btn btn-outline-success" onclick="sendQuickMessage('checkin', 'Please confirm you are safe by responding to this message.')">
                                    <i class="fas fa-check-circle"></i> Safety Check-in
                                </button>
                                <button class="btn btn-outline-warning" onclick="sendQuickMessage('warning', 'IMPORTANT: Please contact admin immediately.')">
                                    <i class="fas fa-exclamation-triangle"></i> Contact Admin
                                </button>
                                <button class="btn btn-outline-danger" onclick="sendQuickMessage('emergency', 'EMERGENCY: Follow emergency protocol immediately!')">
                                    <i class="fas fa-exclamation-circle"></i> Emergency Alert
                                </button>
                                <button class="btn btn-outline-info" onclick="sendBroadcastMessage()">
                                    <i class="fas fa-broadcast-tower"></i> Broadcast to All
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message History -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Message History</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" onclick="filterMessages('all')">All</button>
                        <button class="btn btn-outline-success" onclick="filterMessages('sent')">Sent</button>
                        <button class="btn btn-outline-info" onclick="filterMessages('delivered')">Delivered</button>
                        <button class="btn btn-outline-warning" onclick="filterMessages('read')">Read</button>
                        <button class="btn btn-outline-danger" onclick="filterMessages('failed')">Failed</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Device</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Delivered</th>
                                    <th>Read</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="messagesTableBody">
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

<!-- Broadcast Message Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Broadcast Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    This message will be sent to all registered devices.
                </div>
                <form id="broadcastForm">
                    <div class="mb-3">
                        <label for="broadcastMessage" class="form-label">Broadcast Message</label>
                        <textarea class="form-control" id="broadcastMessage" rows="4" required placeholder="Enter broadcast message..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="broadcastPriority" class="form-label">Priority</label>
                        <select class="form-select" id="broadcastPriority">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="onlineOnly">
                        <label class="form-check-label" for="onlineOnly">
                            Send only to online devices
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendBroadcast()">Send Broadcast</button>
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
let messagesData = {};
let currentFilter = 'all';

const messageTemplates = {
    alert: "ALERT: This is an important notification. Please respond immediately.",
    warning: "WARNING: Please review and take necessary action.",
    info: "INFO: This is an informational message.",
    emergency: "EMERGENCY: This is an emergency alert. Follow emergency protocols immediately!"
};

function loadDevices() {
    database.ref('devices').on('value', (snapshot) => {
        devicesData = snapshot.val() || {};
        updateDeviceSelect();
    });
}

function loadMessages() {
    database.ref('messages').on('value', (snapshot) => {
        messagesData = snapshot.val() || {};
        updateMessagesTable();
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

function updateMessageTemplate() {
    const messageType = document.getElementById('messageType').value;
    const messageContent = document.getElementById('messageContent');
    
    if (messageType !== 'custom' && messageTemplates[messageType]) {
        messageContent.value = messageTemplates[messageType];
    } else if (messageType === 'custom') {
        messageContent.value = '';
    }
}

function updateMessagesTable() {
    const tbody = document.getElementById('messagesTableBody');
    let html = '';
    let allMessages = [];
    
    // Flatten messages from all devices
    Object.entries(messagesData).forEach(([deviceId, messages]) => {
        Object.entries(messages || {}).forEach(([messageId, message]) => {
            allMessages.push({
                ...message,
                deviceId: deviceId,
                messageId: messageId,
                deviceName: devicesData[deviceId]?.name || deviceId
            });
        });
    });
    
    // Sort by timestamp (newest first)
    allMessages.sort((a, b) => b.timestamp - a.timestamp);
    
    // Filter messages
    if (currentFilter !== 'all') {
        allMessages = allMessages.filter(msg => msg.status === currentFilter);
    }
    
    // Limit to last 100 messages for performance
    allMessages = allMessages.slice(0, 100);
    
    if (allMessages.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-muted">No messages found</td></tr>';
    } else {
        allMessages.forEach(message => {
            const statusBadge = getMessageStatusBadge(message.status);
            const deliveredTime = message.delivered_time ? formatTimestamp(message.delivered_time) : '-';
            const readTime = message.read_time ? formatTimestamp(message.read_time) : '-';
            const truncatedMessage = message.message.length > 50 ? 
                message.message.substring(0, 50) + '...' : message.message;
            
            html += `
                <tr>
                    <td>${formatTimestamp(message.timestamp)}</td>
                    <td>${message.deviceName}</td>
                    <td>
                        <span title="${message.message}">${truncatedMessage}</span>
                        ${message.priority === 'high' ? '<i class="fas fa-exclamation-triangle text-warning ms-1"></i>' : ''}
                        ${message.priority === 'critical' ? '<i class="fas fa-exclamation-circle text-danger ms-1"></i>' : ''}
                    </td>
                    <td>${statusBadge}</td>
                    <td>${deliveredTime}</td>
                    <td>${readTime}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewFullMessage('${message.deviceId}', '${message.messageId}')" title="View Full Message">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resendMessage('${message.deviceId}', '${message.messageId}')" title="Resend">
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

function getMessageStatusBadge(status) {
    const badges = {
        'sent': '<span class="badge bg-primary">Sent</span>',
        'delivered': '<span class="badge bg-info">Delivered</span>',
        'read': '<span class="badge bg-success">Read</span>',
        'failed': '<span class="badge bg-danger">Failed</span>'
    };
    
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function filterMessages(filter) {
    currentFilter = filter;
    updateMessagesTable();
    
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function sendQuickMessage(type, message) {
    const onlineDevices = Object.entries(devicesData).filter(([id, device]) => 
        getDeviceStatus(device).text === 'Online'
    );
    
    if (onlineDevices.length === 0) {
        showAlert('No online devices found', 'warning');
        return;
    }
    
    const deviceId = onlineDevices[0][0]; // Send to first online device
    sendMessageToDevice(deviceId, message, type);
}

function sendMessageToDevice(deviceId, message, priority = 'normal') {
    const messageData = {
        message: message,
        timestamp: Date.now(),
        status: 'sent',
        priority: priority,
        admin_id: '<?php echo $_SESSION['admin_user_id'] ?? 'unknown'; ?>',
        admin_email: '<?php echo $_SESSION['admin_email'] ?? 'unknown'; ?>'
    };
    
    const messageId = 'msg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    database.ref(`messages/${deviceId}/${messageId}`).set(messageData)
        .then(() => {
            showAlert('Message sent successfully', 'success');
        })
        .catch(error => {
            showAlert('Failed to send message: ' + error.message, 'danger');
        });
}

function sendBroadcastMessage() {
    new bootstrap.Modal(document.getElementById('broadcastModal')).show();
}

function sendBroadcast() {
    const message = document.getElementById('broadcastMessage').value;
    const priority = document.getElementById('broadcastPriority').value;
    const onlineOnly = document.getElementById('onlineOnly').checked;
    
    if (!message.trim()) {
        showAlert('Please enter a broadcast message', 'danger');
        return;
    }
    
    let targetDevices = Object.entries(devicesData);
    
    if (onlineOnly) {
        targetDevices = targetDevices.filter(([id, device]) => 
            getDeviceStatus(device).text === 'Online'
        );
    }
    
    if (targetDevices.length === 0) {
        showAlert('No target devices found', 'warning');
        return;
    }
    
    const confirmMessage = `Send broadcast to ${targetDevices.length} device(s)?`;
    if (!confirm(confirmMessage)) return;
    
    showLoading();
    
    const promises = targetDevices.map(([deviceId]) => {
        const messageData = {
            message: message,
            timestamp: Date.now(),
            status: 'sent',
            priority: priority,
            type: 'broadcast',
            admin_id: '<?php echo $_SESSION['admin_user_id'] ?? 'unknown'; ?>',
            admin_email: '<?php echo $_SESSION['admin_email'] ?? 'unknown'; ?>'
        };
        
        const messageId = 'broadcast_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        return database.ref(`messages/${deviceId}/${messageId}`).set(messageData);
    });
    
    Promise.all(promises)
        .then(() => {
            hideLoading();
            showAlert(`Broadcast sent to ${targetDevices.length} devices`, 'success');
            bootstrap.Modal.getInstance(document.getElementById('broadcastModal')).hide();
            document.getElementById('broadcastForm').reset();
        })
        .catch(error => {
            hideLoading();
            showAlert('Failed to send broadcast: ' + error.message, 'danger');
        });
}

function viewFullMessage(deviceId, messageId) {
    const message = messagesData[deviceId]?.[messageId];
    if (!message) return;
    
    const content = `
        <div class="mb-3">
            <h6>Message Details</h6>
            <table class="table table-sm">
                <tr><td><strong>Device:</strong></td><td>${devicesData[deviceId]?.name || deviceId}</td></tr>
                <tr><td><strong>Sent:</strong></td><td>${formatTimestamp(message.timestamp)}</td></tr>
                <tr><td><strong>Status:</strong></td><td>${getMessageStatusBadge(message.status)}</td></tr>
                <tr><td><strong>Priority:</strong></td><td><span class="badge bg-secondary">${message.priority || 'normal'}</span></td></tr>
                ${message.delivered_time ? `<tr><td><strong>Delivered:</strong></td><td>${formatTimestamp(message.delivered_time)}</td></tr>` : ''}
                ${message.read_time ? `<tr><td><strong>Read:</strong></td><td>${formatTimestamp(message.read_time)}</td></tr>` : ''}
            </table>
        </div>
        <div class="mb-3">
            <h6>Message Content</h6>
            <div class="alert alert-light">
                ${message.message}
            </div>
        </div>
    `;
    
    showModal('Message Details', content);
}

function resendMessage(deviceId, messageId) {
    const message = messagesData[deviceId]?.[messageId];
    if (!message) return;
    
    sendMessageToDevice(deviceId, message.message, message.priority);
}

function showModal(title, content) {
    // Create a temporary modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
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
    
    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        document.body.removeChild(modal);
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

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDevices();
    loadMessages();
});

// Auto-refresh every 30 seconds
startAutoRefresh(() => {
    loadMessages();
}, 30000);

// Character counter for message textarea
document.getElementById('messageContent').addEventListener('input', function() {
    const maxLength = 500;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // Find or create character counter
    let counter = this.parentElement.querySelector('.char-counter');
    if (!counter) {
        counter = document.createElement('div');
        counter.className = 'char-counter form-text text-end';
        this.parentElement.appendChild(counter);
    }
    
    counter.textContent = `${currentLength}/${maxLength} characters`;
    counter.style.color = remaining < 50 ? '#dc3545' : '#6c757d';
});
</script>

<?php include 'includes/footer.php'; ?>