/**
 * ERDMT Admin Panel JavaScript
 * Enhanced functionality with Google Blue theme
 */

// Global variables
let autoRefresh = true;
let refreshInterval;

// Initialize admin panel
document.addEventListener('DOMContentLoaded', function() {
    initializeAdminPanel();
});

function initializeAdminPanel() {
    // Start auto-refresh if on dashboard
    if (window.location.pathname.includes('enhanced-dashboard.php')) {
        startAutoRefresh();
    }
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize notification system
    initializeNotifications();
}

// Auto-refresh functionality
function startAutoRefresh() {
    if (autoRefresh) {
        refreshInterval = setInterval(() => {
            location.reload();
        }, 30000); // 30 seconds
        updateRefreshStatus('ON');
    }
}

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    if (autoRefresh) {
        startAutoRefresh();
    } else {
        clearInterval(refreshInterval);
        updateRefreshStatus('OFF');
    }
}

function updateRefreshStatus(status) {
    const statusElement = document.getElementById('refresh-status');
    if (statusElement) {
        statusElement.textContent = status;
    }
}

// Device management functions
function sendCommand(deviceId, command) {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;
    
    // Simulate command sending (replace with actual Firebase implementation)
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        showNotification(`${command} command sent successfully`, 'success');
    }, 2000);
}

function openDeviceDetails(deviceId) {
    window.open(`device-details.php?id=${deviceId}`, '_blank');
}

// Notification system
function showNotification(message, type = 'info', duration = 3000) {
    const notification = createNotificationElement(message, type);
    document.body.appendChild(notification);
    
    // Trigger fade in animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        removeNotification(notification);
    }, duration);
}

function createNotificationElement(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade notification`;
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${getNotificationIcon(type)} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="removeNotification(this.parentElement.parentElement)"></button>
        </div>
    `;
    return notification;
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function removeNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

// Tooltip initialization
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    form.classList.add('was-validated');
    return form.checkValidity();
}

// Export functionality
function exportReport() {
    showNotification('Generating report...', 'info');
    
    // Simulate report generation
    setTimeout(() => {
        showNotification('Report generated successfully!', 'success');
    }, 2000);
}

// Search functionality
function searchDevices() {
    const searchTerm = document.getElementById('device-search').value.toLowerCase();
    const deviceRows = document.querySelectorAll('.device-row');
    
    deviceRows.forEach(row => {
        const deviceName = row.querySelector('.device-name').textContent.toLowerCase();
        const deviceId = row.querySelector('.device-id').textContent.toLowerCase();
        
        if (deviceName.includes(searchTerm) || deviceId.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Theme management
function toggleTheme() {
    const body = document.body;
    body.classList.toggle('dark-theme');
    
    const isDark = body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    showNotification(`Switched to ${isDark ? 'dark' : 'light'} theme`, 'info');
}

// Load saved theme
function loadSavedTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
    }
}

// Initialize notifications system
function initializeNotifications() {
    // Check for new notifications every 60 seconds
    setInterval(checkForNotifications, 60000);
}

function checkForNotifications() {
    // This would typically check Firebase for new device events
    // For now, it's a placeholder for future implementation
}

// Utility functions
function formatTimestamp(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleString();
}

function formatFileSize(bytes) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Bytes';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success', 1500);
    }).catch(() => {
        showNotification('Failed to copy to clipboard', 'danger');
    });
}

// Loading states
function showLoadingState(element) {
    element.classList.add('loading');
    element.disabled = true;
}

function hideLoadingState(element) {
    element.classList.remove('loading');
    element.disabled = false;
}

// Initialize theme on load
loadSavedTheme();