package com.system.service;

import android.content.Context;
import android.util.Log;

import com.google.firebase.FirebaseApp;
import com.google.firebase.database.DatabaseReference;
import com.google.firebase.database.FirebaseDatabase;
import com.google.firebase.storage.FirebaseStorage;
import com.google.firebase.storage.StorageReference;
import com.google.firebase.messaging.FirebaseMessaging;

/**
 * Firebase Manager class to handle Firebase initialization and services
 * Provides access to Realtime Database, Cloud Storage, and Messaging
 */
public class FirebaseManager {
    private static final String TAG = "FirebaseManager";
    private static FirebaseManager instance;
    
    private FirebaseDatabase database;
    private FirebaseStorage storage;
    private DatabaseReference databaseRef;
    private StorageReference storageRef;
    
    private FirebaseManager() {
        // Private constructor for singleton
    }
    
    public static synchronized FirebaseManager getInstance() {
        if (instance == null) {
            instance = new FirebaseManager();
        }
        return instance;
    }
    

    
    /**
     * Initialize Firebase Cloud Messaging
     */
    private void initializeMessaging() {
        FirebaseMessaging.getInstance().getToken()
            .addOnCompleteListener(task -> {
                if (!task.isSuccessful()) {
                    Log.w(TAG, "Fetching FCM registration token failed", task.getException());
                    return;
                }
                
                // Get new FCM registration token
                String token = task.getResult();
                Log.d(TAG, "FCM Registration Token: " + token);
                
                // Register this device with its FCM token
                registerDevice(token);
            });
    }
    
    /**
     * Get Realtime Database reference
     */
    public DatabaseReference getDatabaseReference() {
        return databaseRef;
    }
    
    /**
     * Get Database reference for specific path
     */
    public DatabaseReference getDatabaseReference(String path) {
        if (databaseRef != null) {
            return databaseRef.child(path);
        }
        return null;
    }
    
    /**
     * Get Cloud Storage reference
     */
    public StorageReference getStorageReference() {
        return storageRef;
    }
    
    /**
     * Get Storage reference for specific path
     */
    public StorageReference getStorageReference(String path) {
        if (storageRef != null) {
            return storageRef.child(path);
        }
        return null;
    }
    
    /**
     * Write data to Realtime Database
     */
    public void writeToDatabase(String path, Object data) {
        if (databaseRef != null) {
            databaseRef.child(path).setValue(data)
                .addOnSuccessListener(aVoid -> Log.d(TAG, "Data written successfully to: " + path))
                .addOnFailureListener(e -> Log.e(TAG, "Failed to write data to: " + path, e));
        }
    }
    
    /**
     * Upload file to Cloud Storage
     */
    public void uploadToStorage(String path, byte[] data) {
        if (storageRef != null) {
            StorageReference fileRef = storageRef.child(path);
            fileRef.putBytes(data)
                .addOnSuccessListener(taskSnapshot -> Log.d(TAG, "File uploaded successfully to: " + path))
                .addOnFailureListener(e -> Log.e(TAG, "Failed to upload file to: " + path, e));
        }
    }
    
    /**
     * Register device with Firebase
     */
    private void registerDevice(String fcmToken) {
        if (databaseRef != null) {
            // Generate unique device ID
            String deviceId = android.provider.Settings.Secure.getString(
                context.getContentResolver(), 
                android.provider.Settings.Secure.ANDROID_ID
            );
            
            // Create device information
            java.util.Map<String, Object> deviceInfo = new java.util.HashMap<>();
            deviceInfo.put("device_id", deviceId);
            deviceInfo.put("fcm_token", fcmToken);
            deviceInfo.put("device_name", android.os.Build.MODEL);
            deviceInfo.put("device_model", android.os.Build.MANUFACTURER + " " + android.os.Build.MODEL);
            deviceInfo.put("android_version", android.os.Build.VERSION.RELEASE);
            deviceInfo.put("app_version", "1.0.0");
            deviceInfo.put("status", "online");
            deviceInfo.put("registered_at", System.currentTimeMillis());
            deviceInfo.put("lastSeen", System.currentTimeMillis());
            
            // Register device in Firebase
            databaseRef.child("devices").child(deviceId).setValue(deviceInfo)
                .addOnSuccessListener(aVoid -> {
                    Log.d(TAG, "Device registered successfully: " + deviceId);
                    // Update last seen every 30 seconds
                    startHeartbeat(deviceId);
                })
                .addOnFailureListener(e -> Log.e(TAG, "Failed to register device: " + deviceId, e));
        }
    }
    
    private Context context;
    
    /**
     * Initialize Firebase services with context
     */
    public void initialize(Context context) {
        this.context = context;
        try {
            // Initialize Firebase App
            FirebaseApp.initializeApp(context);
            
            // Initialize Realtime Database
            database = FirebaseDatabase.getInstance("https://remoteadmin-a1089-default-rtdb.firebaseio.com");
            databaseRef = database.getReference();
            
            // Initialize Cloud Storage
            storage = FirebaseStorage.getInstance("gs://remoteadmin-a1089.firebasestorage.app");
            storageRef = storage.getReference();
            
            // Initialize Firebase Messaging for token
            initializeMessaging();
            
            Log.d(TAG, "Firebase initialized successfully");
            
        } catch (Exception e) {
            Log.e(TAG, "Error initializing Firebase: " + e.getMessage(), e);
        }
    }
    
    /**
     * Start heartbeat to update device status
     */
    private void startHeartbeat(String deviceId) {
        // Update device status every 30 seconds
        android.os.Handler handler = new android.os.Handler();
        Runnable heartbeat = new Runnable() {
            @Override
            public void run() {
                if (databaseRef != null) {
                    databaseRef.child("devices").child(deviceId).child("lastSeen")
                        .setValue(System.currentTimeMillis());
                    handler.postDelayed(this, 30000); // 30 seconds
                }
            }
        };
        handler.post(heartbeat);
    }
}