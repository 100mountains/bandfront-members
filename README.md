# Bandfront Members

A WordPress membership and subscription plugin designed for the Bandfront music platform.

## Overview

Bandfront Members extends the Bandfront ecosystem by providing comprehensive membership and subscription management capabilities for music artists, bands, and music-related websites.

## Features

### Membership Management
- User registration and profile management
- Member tiers and access levels
- Custom member roles and permissions
- Member-only content access

### Subscription System
- Recurring subscription plans
- Multiple payment gateways integration
- Subscription analytics and reporting
- Automated renewal and billing

### Integration
- Seamless integration with Bandfront Player plugin
- Compatible with Bandfront child theme
- WooCommerce integration for e-commerce functionality

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Bandfront Player plugin (recommended)
- Bandfront child theme (recommended)

## Installation

1. Upload the plugin files to `/wp-content/plugins/bandfront-members/`
2. Activate the plugin through the WordPress admin panel
3. Configure membership settings in the admin dashboard
4. Set up payment gateways and subscription plans

## Development

This plugin is part of the Bandfront system:
- **Main Plugin:** [Bandfront Player](https://github.com/100mountains/bandfront-player)
- **Theme:** [Bandfront Child Theme](https://github.com/100mountains/bandfront)
- **Members Plugin:** This repository

## Support

For support and documentation, please visit the main Bandfront repository or contact the development team.

## License

This plugin is proprietary software developed for the Bandfront platform.

## Implementation Guide

### 1. Membership in one file
Create wp-content/plugins/bandfront-members/bandfront-members.php, add a GPL header, then on activation add a custom role and capability:

```php
register_activation_hook( __FILE__, function () {
	add_role(
		'backer',
		__( 'Backer', 'bandfront' ),
		[ 'read' => true, 'access_backstage' => true ]
	);
});
```

Gate protected pages by checking the capability in a wrapper template or a simple content filter:

```php
function bf_backstage_gate( $content ) {
	if ( get_post_meta( get_the_ID(), '_bf_backstage', true ) ) {
		if ( current_user_can( 'access_backstage' ) ) {
			return $content;                       // supporter sees full post
		}
		return '<p>This track is for backers only.
		        <a href="' . esc_url( home_url( '/join/' ) ) . '">Become a backer</a></p>';
	}
	return $content;
}
add_filter( 'the_content', 'bf_backstage_gate', 5 );
```

Add a tiny metabox that lets you tick "Backer-only" on any post, page or product; no big settings screen needed.

### 2. Taking the money
Because you already run WooCommerce, the lightest route is to sell one invisible product called "Backer subscription". When checkout completes, hook woocommerce_payment_complete and call bfas_assign_backer( $order ) to grant the role for a year (or until cancelled).

If you later want recurring billing, the official WooCommerce Subscriptions extension is GPL too, just not free; you can swap it in without touching the gating code.

### 3. Likes and chat on the same page
Turn on WordPress comments for your backstage posts and enqueue a 50-line JS file that replaces the standard "Reply" link with an inline 👍 button. AJAX it to a custom REST route (/bf/v1/like/{post}) that stores a simple integer in post-meta, and print the running total next to the button. Only visitors who pass the same access_backstage capability check can hit the endpoint, so bots and lurkers stay out.

## Configuration

### Backstage Page Setting
The plugin needs to know what page you're using for the backstage/members area. By default, it uses 'backstage' but you can customize this to match your site's needs (e.g., 'members', 'vip', 'supporters', etc.).

```php
// Add this to your plugin settings or use a simple option
function bf_get_backstage_page() {
    return get_option( 'bf_backstage_page', 'backstage' ); // default: 'backstage'
}

// Use in your content gate function
function bf_backstage_gate( $content ) {
    if ( get_post_meta( get_the_ID(), '_bf_backstage', true ) ) {
        if ( current_user_can( 'access_backstage' ) ) {
            return $content;
        }
        $backstage_page = bf_get_backstage_page();
        return '<p>This content is for backers only. 
                <a href="' . esc_url( home_url( '/' . $backstage_page . '/' ) ) . '">Access ' . ucfirst($backstage_page) . '</a></p>';
    }
    return $content;
}
```

**Examples:**
- Default: `/backstage/` 
- Custom: `/members/`, `/vip/`, `/supporters/`, `/fans/`

You can set this via WordPress admin options or directly in the plugin settings.
