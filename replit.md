# Overview

ERDMT (Emergency Remote Device Management Tool) is a comprehensive Android device management system consisting of a native Android application and a web-based administration panel. The system enables remote monitoring, control, and management of Android devices through Firebase integration, providing real-time device tracking, command execution, file transfers, and emergency response capabilities.

# User Preferences

Preferred communication style: Simple, everyday language.

## Project Completion Status (August 4, 2025)
- **✅ PROJECT 100% COMPLETE AND READY FOR GITHUB**
- **✅ GitHub Codespaces optimized** with one-click APK building capability
- **✅ Google Blue admin panel** with modern, responsive design and animations
- **✅ All duplicate code eliminated** - clean, optimized Android architecture
- **✅ DeviceManager class** replacing all redundant Firebase implementations
- **✅ Complete documentation** with setup guides and troubleshooting
- **✅ Enhanced security** with CSRF protection and input validation
- **✅ Professional UI/UX** with Google Sans typography and smooth interactions
- **✅ Production-ready deployment** with comprehensive error handling

# System Architecture

## Mobile Application Architecture
- **Platform**: Native Android application built with Java/Kotlin
- **Build System**: Gradle-based build configuration
- **Development Environment**: Android SDK with Java 17 support
- **Package Structure**: Uses `com.system.service` package naming for system-level integration
- **Container Support**: Configured for development in Docker containers and GitHub Codespaces

## Backend Architecture
- **Database**: Firebase Realtime Database for real-time data synchronization
- **Storage**: Firebase Storage for file uploads (screenshots, contacts, SMS exports)
- **Authentication**: Firebase-based authentication system
- **Web Backend**: PHP-based admin panel with MySQL database for admin functionality
- **Real-time Communication**: Firebase Cloud Messaging (FCM) for push notifications

## Data Management
- **Device Data**: Stored in Firebase with indexing on timestamp, status, and registration date
- **File Organization**: Structured storage paths for screenshots, device data, contacts, SMS, and call logs
- **Security Rules**: Open Firebase rules configured for admin access with file size and type restrictions
- **Local Storage**: MySQL database for admin panel user management and configuration

## Web Administration Panel
- **Frontend**: Bootstrap-based responsive web interface
- **Backend**: PHP with MySQL database integration
- **Dashboard**: Real-time statistics, device monitoring, and activity tracking
- **Command Center**: Remote command execution with history tracking
- **File Manager**: Upload/download capabilities with progress tracking
- **Message Center**: Direct and broadcast messaging to devices

## Security Architecture
- **Firebase Security**: Configured with storage rules limiting file sizes and types
- **Admin Authentication**: Password-hashed admin credentials with session management
- **Device Authentication**: Firebase-based device registration and token management
- **Data Encryption**: Firebase handles data encryption in transit and at rest

# External Dependencies

## Firebase Services
- **Firebase Realtime Database**: Primary data store for device information and real-time synchronization
- **Firebase Storage**: File storage for screenshots, exports, and device data
- **Firebase Cloud Messaging**: Push notification delivery to devices
- **Firebase Authentication**: User and device authentication management

## Development Tools
- **Android SDK**: Android development toolkit with API level 34 support
- **Java 17**: Runtime environment for Android application
- **Gradle**: Build automation and dependency management
- **Docker/Codespaces**: Containerized development environment

## Web Technologies
- **PHP 7.4+**: Server-side scripting for admin panel
- **MySQL**: Database for admin panel configuration and user management
- **Bootstrap**: Frontend framework for responsive web interface
- **JavaScript**: Client-side functionality for admin panel interactions

## Hosting Requirements
- **Shared Hosting**: Compatible with services like Hostinger
- **SSL Support**: Required for secure Firebase communication
- **PHP Extensions**: Standard PHP extensions for database connectivity and JSON handling

## API Integrations
- **Google Services**: Firebase project integration with project ID `remoteadmin-a1089`
- **Android APIs**: System-level access for device management functions
- **Web APIs**: RESTful endpoints for admin panel functionality