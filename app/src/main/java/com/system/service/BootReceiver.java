package com.system.service;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.util.Log;

/**
 * Broadcast Receiver to handle boot completed events
 * Automatically starts the app after device reboot
 */
public class BootReceiver extends BroadcastReceiver {
    private static final String TAG = "BootReceiver";
    private static final String PREFS_NAME = "erdmt_prefs";
    private static final String KEY_PERMISSIONS_GRANTED = "permissions_granted";

    @Override
    public void onReceive(Context context, Intent intent) {
        String action = intent.getAction();
        
        if (Intent.ACTION_BOOT_COMPLETED.equals(action) || 
            "android.intent.action.QUICKBOOT_POWERON".equals(action)) {
            
            Log.d(TAG, "Boot completed received");
            
            // Check if permissions were previously granted
            SharedPreferences prefs = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE);
            boolean permissionsGranted = prefs.getBoolean(KEY_PERMISSIONS_GRANTED, false);
            
            if (permissionsGranted) {
                // Start the main activity
                Intent mainIntent = new Intent(context, MainActivity.class);
                mainIntent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
                context.startActivity(mainIntent);
                
                Log.d(TAG, "App started after boot");
            } else {
                Log.d(TAG, "Permissions not granted, skipping auto-start");
            }
        }
    }
}