<?php
namespace bfm;

if (!defined('ABSPATH')) {
    exit;
}

class Roles {
    
    private Config $config;
    
    public function __construct(Config $config) {
        $this->config = $config;
    }
    
    public function init(): void {
        add_action('after_setup_theme', [$this, 'setupRoles']);
        add_action('template_redirect', [$this, 'restrictBackstageAccess']);
        add_shortcode('bandfront_backstage_content', [$this, 'backstageContentShortcode']);
    }
    
    public function setupRoles(): void {
        // Create the default backers role
        if (!get_role('bandfront_backers')) {
            add_role('bandfront_backers', 'Bandfront Backers', array(
                'read' => true,
                'level_0' => true,
                'access_backstage_content' => true,
            ));
        }
        
        // Check if plugin settings specify a different role
        $custom_role = $this->config->get('backer_role', '');
        
        // If a custom role is specified and doesn't exist, create it
        if ($custom_role && $custom_role !== 'bandfront_backers' && !get_role($custom_role)) {
            add_role($custom_role, ucfirst($custom_role), array(
                'read' => true,
                'level_0' => true,
                'access_backstage_content' => true,
            ));
        }
    }
    
    public function restrictBackstageAccess(): void {
        $posts_page = $this->config->get('posts_page', '');
        
        // Check if we're on the restricted page
        if ($posts_page && is_page($posts_page)) {
            // Check if user has the required role (including non-logged-in users)
            if (!$this->userHasBackstageAccess()) {
                // Show a custom template or redirect
                $this->showAccessDeniedMessage();
                exit;
            }
        }
    }
    
    public function userHasBackstageAccess($user_id = null): bool {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        $backer_role = $this->config->get('backer_role', 'bandfront_backers');
        $user = new \WP_User($user_id);
        
        // Debug: Let's make sure we're checking the right role
        error_log('Checking backstage access for user ' . $user_id . ' with role requirement: ' . $backer_role);
        error_log('User roles: ' . print_r($user->roles, true));
        
        // Check if user has the specified role or admin capabilities
        return $user->has_role($backer_role) || $user->has_cap('manage_options');
    }
    
    private function showAccessDeniedMessage(): void {
        // Get the join page URL from plugin settings
        $join_page_id = $this->config->get('join_page');
        $join_page_url = $join_page_id ? get_permalink($join_page_id) : home_url('/become-a-backer/');
        
        get_header();
        ?>
        <div class="backstage-access-denied">
            <div class="container">
                <h1 class="bluu-title">Backstage Access Required</h1>
                <div class="bluu-text">
                    <p>This content is exclusively available to our Bandfront Backers.</p>
                    <p>Join our community of supporters to access exclusive content, early releases, and behind-the-scenes material.</p>
                    <div class="backstage-actions">
                        <a href="<?php echo esc_url($join_page_url); ?>" class="button">Become a Backer</a>
                        <a href="<?php echo home_url(); ?>" class="button secondary">Return Home</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        get_footer();
    }
    
    public function backstageContentShortcode($atts, $content = null): string {
        if (!is_user_logged_in()) {
            return '<div class="backstage-login-required bluu-text">
                <p>Please <a href="' . wp_login_url(get_permalink()) . '">log in</a> to view this content.</p>
            </div>';
        }
        
        if (!$this->userHasBackstageAccess()) {
            $join_page_id = $this->config->get('join_page');
            $join_page_url = $join_page_id ? get_permalink($join_page_id) : home_url('/become-a-backer/');
            
            return '<div class="backstage-membership-required bluu-text">
                <p>This content is for Bandfront Backers only.</p>
                <p><a href="' . esc_url($join_page_url) . '">Become a Backer</a> to access exclusive content.</p>
            </div>';
        }
        
        return do_shortcode($content);
    }
}