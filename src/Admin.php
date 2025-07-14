<?php
namespace bfm;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Class - Simplified for settings page only
 */
class Admin {
    
    private Plugin $mainPlugin;
    
    public function __construct(Plugin $mainPlugin) {
        $this->mainPlugin = $mainPlugin;
        $this->initHooks();
        
        // Load view templates
        $this->loadViewTemplates();
    }
    
    /**
     * Load view templates
     */
    private function loadViewTemplates(): void {
        // Include audio engine settings template
        $audioEngineFile = plugin_dir_path(dirname(__FILE__)) . 'src/Views/audio-engine-settings.php';
        if (file_exists($audioEngineFile)) {
            require_once $audioEngineFile;
        }
    }

    /**
     * Initialize WordPress hooks
     */
    private function initHooks(): void {
        add_action('admin_menu', [$this, 'menuLinks']);
        add_action('admin_notices', [$this, 'showAdminNotices']);
        
        // Add AJAX handlers
        add_action('wp_ajax_bfm_save_settings', [$this, 'ajaxSaveSettings']);
    }

    /**
     * Add admin menu
     */
    public function menuLinks(): void {
        add_menu_page(
            'Bandfront Members',
            'Bandfront Members',
            'manage_options',
            'bandfront-members-settings',
            [$this, 'settingsPage'],
            'dashicons-groups',
            30
        );
    }

    /**
     * Settings page callback
     */
    public function settingsPage(): void {
        // Don't process form submission here if it's an AJAX request
        if (isset($_POST['bfm_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bfm_nonce'])), 'bfm_updating_plugin_settings') &&
            !wp_doing_ajax()) {
            $this->saveGlobalSettings();
            
            // Redirect to prevent form resubmission
            wp_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
            exit;
        }

        echo '<div class="wrap">';
        include_once plugin_dir_path(dirname(__FILE__)) . 'src/Views/global-admin-options.php';
        echo '</div>';
    }
    
    /**
     * Save global settings
     */
    private function saveGlobalSettings(): void {
        $_REQUEST = stripslashes_deep($_REQUEST);
        
        // Collect all settings from form
        $settings = [];
        
        // Get all possible setting keys from defaults
        $config = $this->mainPlugin->getConfig();
        $currentSettings = $config->getAll();
        
        // Process each setting
        foreach ($currentSettings as $key => $defaultValue) {
            if (isset($_REQUEST[$key])) {
                $settings[$key] = $this->sanitizeSettingValue($key, $_REQUEST[$key]);
            } else {
                // Handle unchecked checkboxes
                if (is_bool($defaultValue)) {
                    $settings[$key] = false;
                }
            }
        }
        
        // Save settings using the new Config method
        $config->save($settings);
        
        // Set transient for admin notice
        set_transient('bfm_admin_notice', [
            'message' => __('Settings saved successfully!', 'bandfront-members'),
            'type' => 'success'
        ], 30);
    }
    
    /**
     * Sanitize setting value based on key and type
     */
    private function sanitizeSettingValue(string $key, $value) {
        // Handle different types of settings based on key patterns
        if (strpos($key, '_key') !== false || strpos($key, '_secret') !== false) {
            return sanitize_text_field($value);
        } elseif (strpos($key, '_email') !== false) {
            return sanitize_email($value);
        } elseif (strpos($key, '_url') !== false || strpos($key, '_webhook') !== false) {
            return esc_url_raw($value);
        } elseif (strpos($key, '_message') !== false || strpos($key, '_template') !== false) {
            return wp_kses_post($value);
        } elseif (strpos($key, 'color') !== false) {
            return sanitize_hex_color($value);
        } elseif (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        } elseif (is_numeric($value)) {
            return floatval($value);
        } else {
            return sanitize_text_field($value);
        }
    }

    /**
     * Show admin notices
     */
    public function showAdminNotices(): void {
        // Only show on our settings page
        if (!isset($_GET['page']) || $_GET['page'] !== 'bandfront-members-settings') {
            return;
        }
        
        // Don't show notices if this is an AJAX request
        if (wp_doing_ajax()) {
            return;
        }
        
        $notice = get_transient('bfm_admin_notice');
        if ($notice) {
            delete_transient('bfm_admin_notice');
            $class = 'notice notice-' . $notice['type'] . ' is-dismissible';
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($notice['message']));
            return;
        }
        
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Settings saved successfully!', 'bandfront-members'); ?></p>
            </div>
            <?php
        }
    }

    /**
     * AJAX handler for saving settings
     */
    public function ajaxSaveSettings(): void {
        // Check nonce
        if (!isset($_POST['bfm_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bfm_nonce'])), 'bfm_updating_plugin_settings')) {
            wp_send_json_error(['message' => __('Security check failed', 'bandfront-members')]);
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Insufficient permissions', 'bandfront-members')]);
        }
        
        // Save settings
        $this->saveGlobalSettings();
        
        // Send success response
        wp_send_json_success([
            'message' => __('Settings saved successfully!', 'bandfront-members'),
            'details' => []
        ]);
    }
}