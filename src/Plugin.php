<?php
namespace bfm;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin Class - Simplified for admin page only
 */
class Plugin {
    
    private ?Config $config = null;
    private ?Admin $admin = null;
    
    public function __construct() {
        // Initialize config
        $this->config = new Config();
        
        // Only initialize admin in admin area
        if (is_admin()) {
            $this->admin = new Admin($this);
        }
    }
    
    /**
     * Get config instance
     */
    public function getConfig(): Config {
        return $this->config;
    }
    
    /**
     * Legacy method for compatibility
     */
    public function get_config(): Config {
        return $this->config;
    }
}