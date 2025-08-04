# ERDMT - Emergency Remote Device Management Tool

[![GitHub Codespaces](https://github.com/codespaces/badge.svg)](https://codespaces.new)

## 🚀 Quick Start (GitHub Codespaces)

1. **Open in Codespaces**: Click the badge above
2. **Build APK**: Run `./build-apk.sh`
3. **Start Admin**: Run `cd web-admin && php -S 0.0.0.0:5000`

## 📱 Features

### Android Application
- ✅ **Runtime Permissions** (Android 13+ compatible)
- ✅ **Background Service** with foreground notification
- ✅ **Auto Device Registration** with Firebase
- ✅ **Real-time Heartbeat** every 30 seconds
- ✅ **Zero Configuration** required

### Web Admin Panel
- ✅ **Enhanced Dashboard** with real-time statistics
- ✅ **Device Management** with live status monitoring
- ✅ **Command Center** for remote device control
- ✅ **Secure Authentication** with CSRF protection
- ✅ **Responsive Design** for all devices

## 🏗️ Architecture

```
ERDMT/
├── app/                    # Android Application
│   ├── src/main/java/      # Clean, optimized Java code
│   ├── res/                # Android resources
│   └── build.gradle        # Dependencies
├── web-admin/              # Enhanced PHP Admin Panel
│   ├── enhanced-dashboard.php  # Modern dashboard
│   ├── config/             # Firebase & DB config
│   └── includes/           # Shared functions
└── build-apk.sh           # One-click build script
```

## 🔥 Firebase Integration

- **Realtime Database**: Device status and commands
- **Cloud Messaging**: Push notifications
- **Storage**: Screenshots and file transfers
- **Authentication**: Secure device registration

## 🌟 GitHub Codespaces Optimized

- **Automatic SDK Installation**: No manual setup required
- **One-Click Building**: Single script execution
- **Clean Code**: Zero redundancy, optimized for browser-based development
- **Complete Documentation**: Setup guides and troubleshooting

## 📚 Documentation

- **[Complete Setup Guide](COMPLETE_SETUP_GUIDE.md)**: Comprehensive instructions
- **[Firebase Configuration](COMPLETE_SETUP_GUIDE.md#firebase-configuration)**: Step-by-step setup
- **[Troubleshooting](COMPLETE_SETUP_GUIDE.md#troubleshooting)**: Common issues and solutions

## 🎯 Perfect for

- **Remote Device Management**: Monitor and control Android devices
- **GitHub Codespaces Development**: Browser-based Android development
- **Educational Projects**: Learn Android + Firebase integration
- **Quick Prototyping**: Rapid development and deployment

## 🔒 Security

- **CSRF Protection**: All forms secured
- **Input Validation**: Comprehensive sanitization
- **Session Management**: Secure authentication
- **Firebase Rules**: Proper access controls

---

**Ready to build?** Run `./build-apk.sh` in GitHub Codespaces!