#!/bin/bash

# ERDMT Android APK Build Script for GitHub Codespaces
# Optimized for browser-based development environment

echo "🚀 ERDMT APK Builder - GitHub Codespaces Optimized"
echo "================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check environment
check_environment() {
    print_status "Checking build environment..."
    
    if [ -n "$CODESPACES" ]; then
        print_success "Running in GitHub Codespaces"
    else
        print_warning "Not in Codespaces - may need manual configuration"
    fi
}

# Setup Android SDK for Codespaces
setup_android_sdk() {
    print_status "Setting up Android SDK..."
    
    # Set environment variables
    export ANDROID_HOME=/usr/lib/android-sdk
    export ANDROID_SDK_ROOT=$ANDROID_HOME
    export PATH=$PATH:$ANDROID_HOME/cmdline-tools/latest/bin:$ANDROID_HOME/platform-tools
    
    # Create local.properties
    cat > local.properties << EOF
ANDROID_HOME=/usr/lib/android-sdk
sdk.dir=/usr/lib/android-sdk
EOF
    
    # Install Android SDK if not present
    if [ ! -d "$ANDROID_HOME" ]; then
        print_status "Installing Android SDK components..."
        
        # Update package list
        sudo apt-get update -qq
        
        # Install required packages
        sudo apt-get install -y \
            openjdk-17-jdk \
            android-sdk \
            android-sdk-platform-tools \
            android-sdk-build-tools \
            wget \
            unzip
        
        # Create SDK directories
        sudo mkdir -p $ANDROID_HOME/cmdline-tools
        
        print_success "Android SDK components installed"
    fi
    
    # Set Java environment
    export JAVA_HOME=/usr/lib/jvm/java-17-openjdk-amd64
    export PATH=$JAVA_HOME/bin:$PATH
    
    print_success "Android development environment configured"
}

# Verify Gradle wrapper
setup_gradle() {
    print_status "Setting up Gradle..."
    
    # Make gradlew executable
    chmod +x gradlew
    
    # Verify Gradle wrapper
    if [ ! -f "gradle/wrapper/gradle-wrapper.jar" ]; then
        print_error "Gradle wrapper not found"
        return 1
    fi
    
    print_success "Gradle wrapper configured"
}

# Clean and prepare project
clean_project() {
    print_status "Cleaning project..."
    
    # Clean previous builds
    ./gradlew clean --no-daemon --quiet
    
    # Remove old APKs
    find . -name "*.apk" -type f -delete 2>/dev/null || true
    
    print_success "Project cleaned"
}

# Build the APK
build_apk() {
    print_status "Building ERDMT Browser APK..."
    print_status "This process may take 3-5 minutes on first build..."
    
    # Build debug APK with optimized settings for Codespaces
    ./gradlew assembleDebug \
        --no-daemon \
        --no-build-cache \
        --stacktrace \
        --quiet \
        -Dorg.gradle.jvmargs="-Xmx2g -XX:MaxMetaspaceSize=512m" \
        -Dorg.gradle.parallel=false
    
    if [ $? -eq 0 ]; then
        print_success "APK build completed successfully!"
        
        # Find the built APK
        APK_PATH=$(find app/build/outputs/apk/debug -name "*.apk" -type f 2>/dev/null | head -n1)
        
        if [ -n "$APK_PATH" ] && [ -f "$APK_PATH" ]; then
            APK_SIZE=$(du -h "$APK_PATH" | cut -f1)
            
            # Create output directory and copy APK
            mkdir -p build-output
            cp "$APK_PATH" build-output/erdmt-browser.apk
            
            print_success "APK Details:"
            print_success "  ├─ Original: $APK_PATH"
            print_success "  ├─ Download: build-output/erdmt-browser.apk"
            print_success "  └─ Size: $APK_SIZE"
            
            echo ""
            echo "📱 DOWNLOAD INSTRUCTIONS:"
            echo "   1. Navigate to 'build-output' folder in file explorer"
            echo "   2. Right-click on 'erdmt-browser.apk'"
            echo "   3. Select 'Download' to save to your computer"
            echo "   4. Install on Android device via ADB or file manager"
            echo ""
            
            return 0
        else
            print_error "APK file not found after successful build"
            return 1
        fi
    else
        print_error "APK build failed"
        echo ""
        echo "🔧 TROUBLESHOOTING:"
        echo "   1. Check internet connection for dependency downloads"
        echo "   2. Verify Android SDK installation"  
        echo "   3. Run: ./gradlew assembleDebug --stacktrace --info"
        echo "   4. Check build logs above for specific errors"
        echo ""
        return 1
    fi
}

# Main execution flow
main() {
    print_status "Starting ERDMT APK build process..."
    echo ""
    
    # Execute build steps
    check_environment && \
    setup_android_sdk && \
    setup_gradle && \
    clean_project && \
    build_apk
    
    BUILD_RESULT=$?
    
    echo ""
    if [ $BUILD_RESULT -eq 0 ]; then
        print_success "🎉 BUILD COMPLETED SUCCESSFULLY!"
        print_success "Your ERDMT Browser APK is ready for download and testing"
        echo ""
        echo "Next steps:"
        echo "  1. Download the APK from build-output/ folder"
        echo "  2. Install on Android test device"
        echo "  3. Configure Firebase project settings"
        echo "  4. Test device management features"
    else
        print_error "❌ BUILD FAILED"
        print_error "Please review the error messages above and try again"
        echo ""
        echo "For support:"
        echo "  • Check COMPLETE_SETUP_GUIDE.md"
        echo "  • Verify all prerequisites are installed"
        echo "  • Run build with --info flag for detailed logs"
        exit 1
    fi
}

# Execute main function
main "$@"