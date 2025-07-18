#!/bin/bash

# Build script for Bandfront Player
# Executes all build tasks in the correct order

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    local status=$1
    local message=$2
    case $status in
        "error")
            echo -e "${RED}❌ $message${NC}"
            ;;
        "success")
            echo -e "${GREEN}✅ $message${NC}"
            ;;
        "info")
            echo -e "${YELLOW}ℹ️  $message${NC}"
            ;;
        *)
            echo "$message"
            ;;
    esac
}

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

echo "================================================"
echo "       Bandfront Player Build Process"
echo "================================================"
echo ""

print_status "info" "Plugin directory: $PLUGIN_DIR"
print_status "info" "Build directory: $SCRIPT_DIR"
echo ""

# Check prerequisites
print_status "info" "Checking prerequisites..."

if ! command_exists "wp"; then
    print_status "error" "WP-CLI is required but not installed."
    echo "Please install WP-CLI: https://wp-cli.org/"
    exit 1
fi

if ! command_exists "wget"; then
    print_status "error" "wget is required but not installed."
    echo "Please install wget using your package manager."
    exit 1
fi

print_status "success" "All prerequisites met!"
echo ""

# Change to plugin directory for all operations
cd "$PLUGIN_DIR" || {
    print_status "error" "Failed to change to plugin directory"
    exit 1
}

# Task 1: Update WaveSurfer.js (Critical)
echo "================================================"
echo "Task 1: Updating WaveSurfer.js"
echo "================================================"

if [ -f "$SCRIPT_DIR/update-wavesurfer.sh" ]; then
    chmod +x "$SCRIPT_DIR/update-wavesurfer.sh"
    if bash "$SCRIPT_DIR/update-wavesurfer.sh"; then
        print_status "success" "WaveSurfer.js updated successfully!"
    else
        print_status "error" "Failed to update WaveSurfer.js"
        exit 1
    fi
else
    print_status "error" "update-wavesurfer.sh not found in build directory"
    exit 1
fi

echo ""

# Task 2: Update Translations (Non-critical)
echo "================================================"
echo "Task 2: Updating Translations"
echo "================================================"

if [ -f "$SCRIPT_DIR/update-translations.sh" ]; then
    chmod +x "$SCRIPT_DIR/update-translations.sh"
    if bash "$SCRIPT_DIR/update-translations.sh"; then
        print_status "success" "Translations updated successfully!"
    else
        print_status "info" "Translation update failed (non-critical, continuing...)"
        # Don't exit - translations are non-critical
    fi
else
    print_status "info" "update-translations.sh not found (skipping)"
fi

echo ""

# Task 3: Generate Code Map (Optional)
echo "================================================"
echo "Task 3: Code Map Generation"
echo "================================================"

if [ -f "$SCRIPT_DIR/generate-code-map.sh" ]; then
    print_status "info" "Generating code map..."
    chmod +x "$SCRIPT_DIR/generate-code-map.sh"
    if bash "$SCRIPT_DIR/generate-code-map.sh"; then
        print_status "success" "Code map generated successfully!"
    else
        print_status "info" "Code map generation failed (non-critical)"
    fi
else
    print_status "info" "Code map script not found (skipping)"
fi

echo ""

# Task 4: Generate Tree Map
echo "================================================"
echo "Task 4: Directory Tree Map"
echo "================================================"

if command_exists "tree"; then
    print_status "info" "Generating directory tree map..."
    
    # Create md-files directory if it doesn't exist
    mkdir -p "$PLUGIN_DIR/md-files"
    
    # Generate tree map excluding .git, node_modules, *.log, *.tmp, builders/backup/* and modules/google-drive directories
    if tree -a -I '.git|node_modules|google-drive|*.log|*.tmp|*.md|backup' "$PLUGIN_DIR" 2>/dev/null | grep -v "builders/backup" > "$PLUGIN_DIR/md-files/MAP_TREE.md"; then
        print_status "success" "Tree map generated at md-files/MAP_TREE.md"
    else
        print_status "info" "Tree map generation failed (non-critical)"
    fi
else
    print_status "info" "tree command not found (skipping directory map)"
fi

echo ""


echo "================================================"
echo "Task 5: PHP Syntax Validation"
echo "================================================"

if command_exists "php"; then
    print_status "info" "Checking PHP syntax for all PHP files in src/..."

    PHP_ERROR_COUNT=0
    PHP_FILE_COUNT=0

    while IFS= read -r -d '' php_file; do
        PHP_FILE_COUNT=$((PHP_FILE_COUNT + 1))
        if php -l "$php_file" >/dev/null 2>&1; then
            STATUS="OK"
        else
            STATUS="FAIL"
            PHP_ERROR_COUNT=$((PHP_ERROR_COUNT + 1))
        fi
        # Print status on one line, truncate path for readability
        SHORT_FILE=$(basename "$php_file")
        echo -ne "Checked: $((PHP_FILE_COUNT - PHP_ERROR_COUNT)) OK, $PHP_ERROR_COUNT FAIL | $SHORT_FILE\r"
    done < <(find "$PLUGIN_DIR/src" -name "*.php" -type f -print0 2>/dev/null)

    # Print a newline after the loop
    echo ""

    if [ $PHP_ERROR_COUNT -eq 0 ]; then
        print_status "success" "All $PHP_FILE_COUNT PHP files in src/ passed syntax check"
        PHP_SYNTAX_SUCCESS=true
    else
        print_status "error" "$PHP_ERROR_COUNT out of $PHP_FILE_COUNT PHP files in src/ have syntax errors"
        PHP_SYNTAX_SUCCESS=false
    fi
else
    print_status "info" "PHP command not found (skipping syntax check)"
    PHP_SYNTAX_SUCCESS=true
fi

echo ""

# Summary
echo "================================================"
echo "Build Summary"
echo "================================================"

# Check results
if [ -d "$PLUGIN_DIR/vendor/wavesurfer" ]; then
    print_status "success" "WaveSurfer.js files present"
    BUILD_SUCCESS=true
else
    print_status "error" "WaveSurfer.js files missing"
    BUILD_SUCCESS=false
fi

if [ -f "$PLUGIN_DIR/languages/bandfront-player.pot" ]; then
    print_status "success" "Translation template (POT) present"
else
    print_status "info" "Translation template not generated (non-critical)"
fi

# Check if tree map was generated
if [ -f "$PLUGIN_DIR/md-files/MAP_TREE.md" ]; then
    print_status "success" "Directory tree map present"
else
    print_status "info" "Directory tree map not generated (non-critical)"
fi

# Check PHP syntax results
if [ -n "$PHP_SYNTAX_SUCCESS" ]; then
    if [ "$PHP_SYNTAX_SUCCESS" = true ]; then
        print_status "success" "PHP syntax validation passed"
    else
        print_status "error" "PHP syntax validation failed"
        BUILD_SUCCESS=false
    fi
fi

# Count translation files
PO_COUNT=$(find "$PLUGIN_DIR/languages" -name "*.po" 2>/dev/null | wc -l)
MO_COUNT=$(find "$PLUGIN_DIR/languages" -name "*.mo" 2>/dev/null | wc -l)

echo ""
print_status "info" "Translation files: $PO_COUNT PO files, $MO_COUNT MO files"
print_status "info" "Build completed at: $(date)"

echo ""
if [ "$BUILD_SUCCESS" = true ]; then
    print_status "success" "Build process completed successfully!"
    exit 0
else
    print_status "error" "Build process failed - critical components missing"
    exit 1
fi
echo "================================================"
