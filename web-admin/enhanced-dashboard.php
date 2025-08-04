<?php
session_start();
require_once 'config/firebase.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pageTitle = 'Enhanced Dashboard - ERDMT Admin Panel';
include 'includes/header.php';

// Get real-time statistics
$stats = getDeviceStatistics();
$devices = getAllDevices();
?>

<style>
/* Styles moved to admin-style.css */

.dashboard-card:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.device-card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.device-card:hover {
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.status-online { color: #28a745; }
.status-offline { color: #dc3545; }
.status-unknown { color: #6c757d; }

.action-btn {
    padding: 8px 16px;
    border-radius: 20px;
    border: none;
    font-size: 12px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-command {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-command:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Enhanced Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                    <h6 class="mt-2">ERDMT Admin</h6>
                    <small class="text-muted">Enhanced Control Panel</small>
                </div>
                
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="enhanced-dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Enhanced Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="devices.php">
                            <i class="fas fa-mobile-alt"></i> Device Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="commands.php">
                            <i class="fas fa-terminal"></i> Command Center
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="files.php">
                            <i class="fas fa-folder"></i> File Manager
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            <i class="fas fa-comments"></i> Messaging
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

        <!-- Enhanced Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt text-primary"></i>
                    Enhanced Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Auto-Refresh: <span id="refresh-status">ON</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stat-number"><?php echo $stats['total_devices']; ?></div>
                                <div>Total Devices</div>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-mobile-alt fa-3x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card success">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stat-number"><?php echo $stats['online_devices']; ?></div>
                                <div class="stat-label">Online Now</div>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-wifi fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card warning">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stat-number"><?php echo $stats['offline_devices']; ?></div>
                                <div class="stat-label">Offline</div>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-exclamation-triangle fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="dashboard-card info">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stat-number"><?php echo $stats['last_24h_active']; ?></div>
                                <div class="stat-label">Active 24h</div>
                            </div>
                            <div class="ms-3">
                                <i class="fas fa-chart-line fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Device List -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-mobile-alt text-primary"></i>
                        Connected Devices
                        <span class="badge bg-primary ms-2"><?php echo count($devices); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($devices)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-mobile-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No devices connected</h5>
                            <p class="text-muted">Install ERDMT app on Android devices to get started</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Device</th>
                                        <th>Status</th>
                                        <th>Last Seen</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($devices as $deviceId => $device): 
                                        $status = $device['status'] ?? 'unknown';
                                        $lastSeen = isset($device['last_seen']) ? date('M j, H:i', $device['last_seen'] / 1000) : 'Never';
                                        $statusClass = $status === 'online' ? 'status-online' : ($status === 'offline' ? 'status-offline' : 'status-unknown');
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                                        <i class="fas fa-mobile-alt"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($device['model'] ?? 'Unknown Device'); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars(substr($deviceId, 0, 8)); ?>...</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $status === 'online' ? 'success' : 'danger'; ?> <?php echo $statusClass; ?>">
                                                    <i class="fas fa-circle me-1"></i>
                                                    <?php echo ucfirst($status); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $lastSeen; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="action-btn btn-command" onclick="sendCommand('<?php echo $deviceId; ?>', 'screenshot')">
                                                        <i class="fas fa-camera"></i> Screenshot
                                                    </button>
                                                    <button type="button" class="action-btn btn-command ms-1" onclick="sendCommand('<?php echo $deviceId; ?>', 'location')">
                                                        <i class="fas fa-map-marker-alt"></i> Location
                                                    </button>
                                                    <button type="button" class="action-btn btn-command ms-1" onclick="openDeviceDetails('<?php echo $deviceId; ?>')">
                                                        <i class="fas fa-info-circle"></i> Details
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Dashboard functionality handled by admin.js -->

<?php include 'includes/footer.php'; ?>