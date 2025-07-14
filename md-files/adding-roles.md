There are several ways to add roles in WordPress:

## 1. Using `add_role()` Function (Most Common)

```php
// Add this to your plugin activation hook or theme's functions.php
add_action('init', 'add_custom_user_roles');

function add_custom_user_roles() {
    // Check if role doesn't already exist
    if (!get_role('backer')) {
        add_role(
            'backer',                    // Role slug
            'Backer',                    // Role display name
            array(                       // Capabilities
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'upload_files' => false,
            )
        );
    }
}
```

## 2. On Plugin Activation Hook (Recommended)

```php
// In your main plugin file
register_activation_hook(__FILE__, 'bfm_create_roles');

function bfm_create_roles() {
    add_role('backer', 'Backer', array(
        'read' => true,
        'level_0' => true,
        // Add other capabilities as needed
    ));
    
    // You can add multiple roles
    add_role('premium_backer', 'Premium Backer', array(
        'read' => true,
        'level_0' => true,
        'upload_files' => true,
    ));
}

// Remove roles on deactivation
register_deactivation_hook(__FILE__, 'bfm_remove_roles');

function bfm_remove_roles() {
    remove_role('backer');
    remove_role('premium_backer');
}
```

## 3. WordPress Built-in Capabilities

Common capabilities you might want to grant:

```php
$capabilities = array(
    'read' => true,                    // Can access admin area
    'edit_posts' => true,              // Can edit posts
    'edit_published_posts' => true,    // Can edit published posts
    'publish_posts' => true,           // Can publish posts
    'delete_posts' => true,            // Can delete posts
    'upload_files' => true,            // Can upload files
    'edit_pages' => true,              // Can edit pages
    'read_private_posts' => true,      // Can read private posts
    'edit_others_posts' => true,       // Can edit others' posts
    'level_0' => true,                 // Basic user level
);
```

## 4. Check if Role Exists

```php
function check_if_role_exists($role_name) {
    return get_role($role_name) !== null;
}

// Usage
if (!check_if_role_exists('backer')) {
    add_role('backer', 'Backer', array('read' => true));
}
```

## 5. Add Custom Capabilities to Existing Roles

```php
// Add capability to existing role
$role = get_role('backer');
if ($role) {
    $role->add_cap('access_member_content');
    $role->add_cap('view_exclusive_posts');
}

// Remove capability
$role->remove_cap('upload_files');
```

## 6. For Your Membership Plugin

Based on your code, here's what I'd recommend for your membership plugin:

```php
// Add to your plugin activation
register_activation_hook(__FILE__, 'bfm_create_membership_roles');

function bfm_create_membership_roles() {
    // Basic backer role
    if (!get_role('backer')) {
        add_role('backer', 'Backer', array(
            'read' => true,
            'level_0' => true,
            'access_member_content' => true,  // Custom capability
        ));
    }
    
    // Premium backer role (if you want tiers)
    if (!get_role('premium_backer')) {
        add_role('premium_backer', 'Premium Backer', array(
            'read' => true,
            'level_0' => true,
            'access_member_content' => true,
            'access_premium_content' => true,
            'upload_files' => true,
        ));
    }
}

// Clean up on deactivation
register_deactivation_hook(__FILE__, 'bfm_remove_membership_roles');

function bfm_remove_membership_roles() {
    remove_role('backer');
    remove_role('premium_backer');
}
```

## 7. Assign Role to User

```php
// Assign role to user
function assign_backer_role($user_id) {
    $user = new WP_User($user_id);
    $user->add_role('backer');
}

// Remove role from user
function remove_backer_role($user_id) {
    $user = new WP_User($user_id);
    $user->remove_role('backer');
}

// Change user's role completely
function change_user_role($user_id, $new_role) {
    $user = new WP_User($user_id);
    $user->set_role($new_role);
}
```

**Important Notes:**
- Always check if a role exists before adding it
- Use plugin activation/deactivation hooks for role management
- Custom capabilities should be prefixed to avoid conflicts
- Roles persist in the database even after plugin deactivation unless explicitly removed

For your membership plugin, I'd recommend creating the roles on plugin activation and then using the settings you've already created to determine which role should be considered the "backer" role.


but then the role dies when you turn it off and this is part of a bandfront system for open source music selling. theres also the problem that as soon as you turn it off your backstage page premium content becomes visible to everyoneEditYou're absolutely right! That's a major security issue. For a membership system, you need the roles to persist and the content to remain protected even if the plugin is deactivated. Here are better approaches:
1. Don't Remove Roles on Deactivation
php// Only create roles on activation - DON'T remove them on deactivation
register_activation_hook(__FILE__, 'bfm_create_membership_roles');

function bfm_create_membership_roles() {
    if (!get_role('backer')) {
        add_role('backer', 'Backer', array(
            'read' => true,
            'level_0' => true,
        ));
    }
}

// NO deactivation hook for removing roles!
// register_deactivation_hook(__FILE__, 'bfm_remove_membership_roles'); // DON'T DO THIS
2. Use Built-in WordPress Roles Instead
Even better approach - use existing WordPress roles and just track membership status separately:
php// Instead of custom roles, use existing ones and track membership via user meta
function make_user_backer($user_id) {
    update_user_meta($user_id, 'bfm_is_backer', true);
    update_user_meta($user_id, 'bfm_backer_since', current_time('timestamp'));
    
    // Optionally also change their role to subscriber if they're not already higher
    $user = new WP_User($user_id);
    if (!$user->has_cap('edit_posts')) {
        $user->set_role('subscriber');
    }
}

function is_user_backer($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    return get_user_meta($user_id, 'bfm_is_backer', true) === true;
}
3. Content Protection That Survives Plugin Deactivation
Create a separate protection plugin or use shortcodes that fail safely:
php// Create a shortcode that checks for both plugin AND user status
add_shortcode('bfm_member_content', 'bfm_member_content_shortcode');

function bfm_member_content_shortcode($atts, $content = null) {
    // If plugin is deactivated, this shortcode won't exist and content stays hidden
    
    if (!is_user_logged_in()) {
        return '<p>Please log in to view this content.</p>';
    }
    
    // Check if user is backer
    if (!is_user_backer()) {
        return '<p>This content is for backers only. <a href="/become-backer">Become a backer</a></p>';
    }
    
    return do_shortcode($content);
}

// Usage in posts:
// [bfm_member_content]
// This is premium content only backers can see!
// [/bfm_member_content]
4. Database Table Approach (Most Secure)
Store membership data in a custom table that persists:
php// Create table on plugin activation
register_activation_hook(__FILE__, 'bfm_create_membership_table');

function bfm_create_membership_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'bfm_memberships';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        membership_type varchar(50) NOT NULL DEFAULT 'backer',
        status varchar(20) NOT NULL DEFAULT 'active',
        created_date datetime DEFAULT CURRENT_TIMESTAMP,
        expires_date datetime NULL,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Check membership function
function is_user_backer($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'bfm_memberships';
    
    $membership = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d AND status = 'active'",
        $user_id
    ));
    
    return $membership !== null;
}
5. Template Protection
Protect your templates with checks:
php// In your theme template files
<?php if (function_exists('is_user_backer') && is_user_backer()): ?>
    <div class="premium-content">
        <!-- Premium content here -->
    </div>
<?php else: ?>
    <div class="membership-required">
        <p>This content requires a membership. <a href="/join">Join now</a></p>
    </div>
<?php endif; ?>
6. Recommended Architecture for Bandfront
For your open source music selling system, I'd recommend:
php// 1. Use WordPress built-in roles
// 2. Track membership via user meta or custom table
// 3. Create protection functions that work even if plugin is off

class BandfrontMembership {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'create_roles'));
        add_shortcode('bandfront_members_only', array(__CLASS__, 'members_only_shortcode'));
    }
    
    public static function create_roles() {
        // Only create if doesn't exist - never remove
        if (!get_role('bandfront_backer')) {
            add_role('bandfront_backer', 'Bandfront Backer', array(
                'read' => true,
                'level_0' => true,
            ));
        }
    }
    
    public static function is_backer($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        $user = new WP_User($user_id);
        return $user->has_role('bandfront_backer') || get_user_meta($user_id, 'bandfront_is_backer', true);
    }
    
    public static function members_only_shortcode($atts, $content = null) {
        if (!is_user_logged_in()) {
            return '<div class="bandfront-login-required">Please log in to view this content.</div>';
        }
        
        if (!self::is_backer()) {
            return '<div class="bandfront-membership-required">This content is for backers only.</div>';
        }
        
        return do_shortcode($content);
    }
}

BandfrontMembership::init();
7. Content Protection Strategy

Never remove roles on plugin deactivation
Use shortcodes that hide content when plugin is off
Store membership data in user meta or custom tables
Create fallback protection in themes
Use capability checks rather than role checks when possible

This way, if the plugin is deactivated, the premium content either stays hidden (via shortcodes) or shows a "membership required" message, but never becomes publicly visible.RetryKWi have a child theme that has to run for this plugin to work - could add the role from there ? wheres the best practice to do itEditYes, adding the role from a child theme is actually a great approach for a membership system! Since the child theme is required for the plugin to work, this ensures the role persists even if the main plugin is deactivated temporarily.
Best Practices for Adding Roles in Child Theme
1. In functions.php (Most Common)
php// In your child theme's functions.php
add_action('after_setup_theme', 'bandfront_create_membership_roles');

function bandfront_create_membership_roles() {
    // Only create if doesn't exist
    if (!get_role('bandfront_backer')) {
        add_role('bandfront_backer', 'Bandfront Backer', array(
            'read' => true,
            'level_0' => true,
        ));
    }
    
    // Add premium tier if needed
    if (!get_role('bandfront_premium_backer')) {
        add_role('bandfront_premium_backer', 'Premium Backer', array(
            'read' => true,
            'level_0' => true,
            'upload_files' => true,
        ));
    }
}
2. Better: Use init Hook with Version Check
php// In child theme functions.php
add_action('init', 'bandfront_setup_roles');

function bandfront_setup_roles() {
    $roles_version = get_option('bandfront_roles_version', '1.0');
    $current_version = '1.1'; // Increment when you need to update roles
    
    if (version_compare($roles_version, $current_version, '<')) {
        bandfront_create_roles();
        update_option('bandfront_roles_version', $current_version);
    }
}

function bandfront_create_roles() {
    // Create/update roles
    if (!get_role('bandfront_backer')) {
        add_role('bandfront_backer', 'Bandfront Backer', array(
            'read' => true,
            'level_0' => true,
        ));
    }
    
    // Update existing role capabilities if needed
    $backer_role = get_role('bandfront_backer');
    if ($backer_role) {
        $backer_role->add_cap('access_bandfront_content');
    }
}
3. Create a Separate Include File
Create /inc/roles.php in your child theme:
php<?php
// /inc/roles.php
if (!defined('ABSPATH')) {
    exit;
}

class BandfrontRoles {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'setup_roles'));
    }
    
    public static function setup_roles() {
        self::create_backer_role();
        self::create_premium_backer_role();
    }
    
    private static function create_backer_role() {
        if (!get_role('bandfront_backer')) {
            add_role('bandfront_backer', __('Bandfront Backer', 'bandfront-members'), array(
                'read' => true,
                'level_0' => true,
                'access_bandfront_content' => true,
            ));
        }
    }
    
    private static function create_premium_backer_role() {
        if (!get_role('bandfront_premium_backer')) {
            add_role('bandfront_premium_backer', __('Premium Backer', 'bandfront-members'), array(
                'read' => true,
                'level_0' => true,
                'access_bandfront_content' => true,
                'access_premium_content' => true,
                'upload_files' => true,
            ));
        }
    }
    
    public static function is_backer($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        $user = new WP_User($user_id);
        return $user->has_role('bandfront_backer') || $user->has_role('bandfront_premium_backer');
    }
}

BandfrontRoles::init();
Then in your functions.php:
php// In child theme functions.php
require_once get_stylesheet_directory() . '/inc/roles.php';
4. With Theme Check and Fallback
php// In child theme functions.php
add_action('init', 'bandfront_ensure_roles_exist');

function bandfront_ensure_roles_exist() {
    // Only run if this is the active theme
    if (get_template() !== 'your-parent-theme-name') {
        return;
    }
    
    // Check if plugin is active (optional)
    if (!class_exists('BandfrontMembers')) {
        return;
    }
    
    bandfront_create_membership_roles();
}

function bandfront_create_membership_roles() {
    $roles_to_create = array(
        'bandfront_backer' => array(
            'name' => 'Bandfront Backer',
            'capabilities' => array(
                'read' => true,
                'level_0' => true,
                'access_bandfront_content' => true,
            )
        ),
        'bandfront_premium_backer' => array(
            'name' => 'Premium Backer',
            'capabilities' => array(
                'read' => true,
                'level_0' => true,
                'access_bandfront_content' => true,
                'access_premium_content' => true,
                'upload_files' => true,
            )
        )
    );
    
    foreach ($roles_to_create as $role_slug => $role_data) {
        if (!get_role($role_slug)) {
            add_role($role_slug, $role_data['name'], $role_data['capabilities']);
        }
    }
}
5. Integration with Your Plugin Settings
Since your plugin already has the backer role setting, you can make it work with theme-created roles:
php// In your plugin settings, populate the dropdown with theme-created roles
function get_bandfront_roles() {
    $all_roles = wp_roles()->roles;
    $bandfront_roles = array();
    
    foreach ($all_roles as $role_key => $role_data) {
        // Include bandfront-specific roles or let admin choose any role
        if (strpos($role_key, 'bandfront_') === 0 || in_array($role_key, ['subscriber', 'contributor'])) {
            $bandfront_roles[$role_key] = $role_data['name'];
        }
    }
    
    return $bandfront_roles;
}

// Update your settings dropdown
$bandfront_roles = get_bandfront_roles();
$selected_role = $settings['_bfp_backer_role'] ?? 'bandfront_backer';
foreach ($bandfront_roles as $role_key => $role_name) {
    echo '<option value="' . esc_attr($role_key) . '" ' . selected($selected_role, $role_key, false) . '>' . 
         esc_html($role_name) . '</option>';
}
Recommended Approach for Bandfront
For your open source music selling system, I'd recommend:

Create roles in child theme using approach #2 or #3
Use descriptive role names like bandfront_backer to avoid conflicts
Add custom capabilities like access_bandfront_content
Version your roles so you can update them later
Never remove roles - let them persist even if theme changes

This ensures:

✅ Roles persist even if plugin is deactivated
✅ Content stays protected
✅ Clean upgrade path for role updates
✅ No conflicts with other plugins
✅ Works with your existing plugin settings

The child theme approach is perfect for your use case since it's required anyway!