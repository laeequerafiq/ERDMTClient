<?php
/**
 * Common functions for ERDMT Admin Panel
 */

// Include Firebase configuration
require_once __DIR__ . '/../config/firebase.php';

/**
 * Enhanced device management functions
 */
function getAllDevices() {
    $devices = firebaseRequest('/devices');
    if (!$devices) {
        return [];
    }
    
    // Convert to array with device IDs as keys
    $deviceList = [];
    foreach ($devices as $deviceId => $deviceData) {
        $deviceList[$deviceId] = $deviceData;
        $deviceList[$deviceId]['device_id'] = $deviceId;
    }
    
    return $deviceList;
}

function getDeviceById($deviceId) {
    return firebaseRequest('/devices/' . $deviceId);
}

function updateDeviceStatus($deviceId, $status) {
    return firebaseRequest('/devices/' . $deviceId, 'PATCH', [
        'status' => $status,
        'last_seen' => time() * 1000
    ]);
}

function sendCommandToDevice($deviceId, $command, $parameters = []) {
    $commandData = [
        'command' => $command,
        'parameters' => $parameters,
        'timestamp' => time() * 1000,
        'status' => 'pending'
    ];
    
    return firebaseRequest('/commands/' . $deviceId . '/' . uniqid(), 'PUT', $commandData);
}

function getDeviceCommands($deviceId) {
    $commands = firebaseRequest('/commands/' . $deviceId);
    return $commands ? $commands : [];
}

/**
 * Enhanced Firebase Cloud Messaging
 */
function sendFCMMessage($deviceToken, $title, $message, $data = []) {
    $fcmData = [
        'to' => $deviceToken,
        'notification' => [
            'title' => $title,
            'body' => $message
        ],
        'data' => $data
    ];
    
    $headers = [
        'Authorization: key=' . FIREBASE_SERVER_KEY,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmData));
    
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $statusCode == 200;
}

/**
 * Device statistics and monitoring
 */
function getDeviceStatistics() {
    $devices = getAllDevices();
    
    $stats = [
        'total_devices' => count($devices),
        'online_devices' => 0,
        'offline_devices' => 0,
        'last_24h_active' => 0
    ];
    
    $oneDayAgo = (time() - 86400) * 1000;
    
    foreach ($devices as $device) {
        if (isset($device['status'])) {
            if ($device['status'] === 'online') {
                $stats['online_devices']++;
            } else {
                $stats['offline_devices']++;
            }
        }
        
        if (isset($device['last_seen']) && $device['last_seen'] > $oneDayAgo) {
            $stats['last_24h_active']++;
        }
    }
    
    return $stats;
}

/**
 * Security and validation functions
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateDeviceId($deviceId) {
    return preg_match('/^[a-zA-Z0-9_-]+$/', $deviceId);
}

// CSRF functions are in auth.php

/**
 * Enhanced logging system
 */
function logActivity($action, $details = '') {
    $logData = [
        'timestamp' => time() * 1000,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Log to Firebase
    firebaseRequest('/admin_logs/' . uniqid(), 'PUT', $logData);
    
    // Also log to local file for debugging
    error_log("[ERDMT] $action: $details");
}

/**
 * Real-time data refresh functions
 */
function getLatestDeviceData() {
    $devices = getAllDevices();
    $stats = getDeviceStatistics();
    
    return [
        'devices' => $devices,
        'statistics' => $stats,
        'timestamp' => time()
    ];
}
?>