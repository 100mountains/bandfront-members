<?php
namespace bfm;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin {
    
    private Config $config;
    private Roles $roles;
    private Vault $vault;
    private Admin $admin;
    
    public function __construct() {
        $this->config = new Config();
        $this->roles = new Roles($this->config);
        $this->vault = new Vault($this->config, $this->roles);
        $this->admin = new Admin($this);  // Pass $this instead of $this->config
        
        // Store in global for theme access
        $GLOBALS['BandfrontMembers'] = $this;
        
        $this->init();
    }
    
    private function init(): void {
        // Initialize components
        $this->roles->init();
        $this->vault->init();
        $this->admin->init();
        
        // Add styles
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
    }
    
    public function enqueueStyles(): void {
        wp_enqueue_style(
            'bandfront-members',
            BFM_PLUGIN_URL . 'assets/css/members.css',
            [],
            BFM_VERSION
        );
    }
    
    public function getConfig(): Config {
        return $this->config;
    }
    
    public function getRoles(): Roles {
        return $this->roles;
    }
}