<?php
/**
 * Firebase Cloud Messaging Helper Functions
 */

function sendFCMMessage($token, $title, $body, $data = []) {
    $url = 'https://fcm.googleapis.com/fcm/send';
    
    $notification = [
        'title' => $title,
        'body' => $body,
        'icon' => 'ic_notification',
        'sound' => 'default'
    ];
    
    $fields = [
        'to' => $token,
        'notification' => $notification,
        'data' => $data,
        'priority' => 'high'
    ];
    
    $headers = [
        'Authorization: key=' . FIREBASE_SERVER_KEY,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $response = json_decode($result, true);
        return $response['success'] > 0;
    }
    
    return false;
}

function sendFCMToMultipleDevices($tokens, $title, $body, $data = []) {
    $url = 'https://fcm.googleapis.com/fcm/send';
    
    $notification = [
        'title' => $title,
        'body' => $body,
        'icon' => 'ic_notification',
        'sound' => 'default'
    ];
    
    $fields = [
        'registration_ids' => $tokens,
        'notification' => $notification,
        'data' => $data,
        'priority' => 'high'
    ];
    
    $headers = [
        'Authorization: key=' . FIREBASE_SERVER_KEY,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $response = json_decode($result, true);
        return $response;
    }
    
    return false;
}

function broadcastToAllDevices($title, $body, $data = []) {
    $devices = firebaseRequest('/devices');
    if (!$devices) return false;
    
    $tokens = [];
    foreach ($devices as $deviceId => $device) {
        if (isset($device['fcm_token']) && !empty($device['fcm_token'])) {
            $tokens[] = $device['fcm_token'];
        }
    }
    
    if (empty($tokens)) return false;
    
    // FCM supports max 1000 tokens per request
    $chunks = array_chunk($tokens, 1000);
    $results = [];
    
    foreach ($chunks as $chunk) {
        $result = sendFCMToMultipleDevices($chunk, $title, $body, $data);
        $results[] = $result;
    }
    
    return $results;
}
?>