<?php
/**
 * Firebase Connection Test - For GitHub Codespaces deployment
 */
require_once 'config/firebase.php';

echo "<h2>🔥 Firebase Connection Test</h2>";

// Test Firebase Database connection
echo "<h3>Database Connection</h3>";
$testData = firebaseRequest('/test');
if ($testData !== false) {
    echo "✅ Firebase Database: Connected<br>";
} else {
    echo "❌ Firebase Database: Connection failed<br>";
}

// Test configuration
echo "<h3>Configuration</h3>";
echo "📊 Project ID: " . FIREBASE_PROJECT_ID . "<br>";
echo "🔗 Database URL: " . FIREBASE_DATABASE_URL . "<br>";
echo "📱 Storage Bucket: " . FIREBASE_STORAGE_BUCKET . "<br>";

// Test MySQL connection
echo "<h3>MySQL Connection</h3>";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✅ MySQL Database: Connected<br>";
} catch (Exception $e) {
    echo "❌ MySQL Database: " . $e->getMessage() . "<br>";
}

echo "<br><a href='enhanced-dashboard.php'>← Back to Dashboard</a>";
?>