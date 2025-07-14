<?php
/**
 * Plugin Name: Bandfront Members
 * Description: Bandfront Members Admin Interface
 * Version: 0.1
 * Author: Bandfront
 * Text Domain: bandfront-members
 */

if (!defined('ABSPATH')) {
    exit;
}

// Constants
define('BFM_VERSION', '0.1');
define('BFM_PLUGIN_PATH', __FILE__);
define('BFM_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('BFM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Simple autoloader for our classes
spl_autoload_register(function ($class) {
    $prefix = 'bfm\\';
    $base_dir = __DIR__ . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
global $BandfrontMembers;
$BandfrontMembers = new bfm\Plugin();
