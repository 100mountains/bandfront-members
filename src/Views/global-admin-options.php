<?php
// Security check
if (!defined('ABSPATH')) {
    exit;
}

// include resources
wp_enqueue_style( 'bfm-admin-style', BFM_PLUGIN_URL . 'css/style-admin.css', array(), '5.0.181' );
wp_enqueue_style( 'bfm-admin-notices', BFM_PLUGIN_URL . 'css/admin-notices.css', array(), '5.0.181' );
wp_enqueue_media();
wp_enqueue_script( 'bfm-admin-js', BFM_PLUGIN_URL . 'js/admin.js', array(), '5.0.181' );

// Change from 'bfm' to 'bfp' to match what admin.js expects
$bfp_js = array(
	'File Name'         => __( 'File Name', 'bandfront-members' ),
	'Choose file'       => __( 'Choose file', 'bandfront-members' ),
	'Delete'            => __( 'Delete', 'bandfront-members' ),
	'Select audio file' => __( 'Select audio file', 'bandfront-members' ),
	'Select Item'       => __( 'Select Item', 'bandfront-members' ),
);
wp_localize_script( 'bfm-admin-js', 'bfp', $bfp_js );

// Add AJAX URL to localization
wp_localize_script( 'bfm-admin-js', 'bfp_ajax', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'saving_text' => __('Saving settings...', 'bandfront-members'),
    'error_text' => __('An unexpected error occurred. Please try again.', 'bandfront-members'),
    'dismiss_text' => __('Dismiss this notice', 'bandfront-members'),
));

// Get all settings using the new Config class
$config = $GLOBALS['BandfrontMembers']->getConfig();
$settings = $config->getAll();

?>
<h1><?php echo "\xF0\x9F\x8C\x88"; ?> <?php esc_html_e( 'Bandfront Members - Global Settings', 'bandfront-members' ); ?></h1>
<p class="bfp-tagline">membership management for bands</p>

<div class="bfp-tips-container">
	<div id="bandcamp_nuke_tips_header">
		<h2 onclick="jQuery('#bandcamp_nuke_tips_body').toggle();">
			💥 <?php esc_html_e( 'Tips On How To Setup Bandfront Members [+|-]', 'bandfront-members' ); ?>
		</h2>
	</div>
	<div id="bandcamp_nuke_tips_body" class="bfp-tips-body">
		<div class="bfp-tips-grid">
			<div class="bfp-tips-card bfp-tips-card-start">
				<h3>🚀 <?php esc_html_e( 'Getting Started', 'bandfront-members' ); ?></h3>
				<p>
					<?php esc_html_e( 'New to Bandfront Members? Start here for a complete setup guide.', 'bandfront-members' ); ?>
				</p>
				<a href="#" onclick="window.open('/how-to-start-members', '_blank')" class="bfp-tips-link bfp-tips-link-start">
					<?php esc_html_e( 'How To Start Guide →', 'bandfront-members' ); ?>
				</a>
			</div>
			
			<div class="bfp-tips-card bfp-tips-card-shortcodes">
				<h3>👥 <?php esc_html_e( 'Membership Tiers', 'bandfront-members' ); ?></h3>
				<p>
					<?php esc_html_e( 'Learn how to create and manage different membership levels.', 'bandfront-members' ); ?>
				</p>
				<a href="#" onclick="window.open('/membership-tiers', '_blank')" class="bfp-tips-link bfp-tips-link-shortcodes">
					<?php esc_html_e( 'Membership Guide →', 'bandfront-members' ); ?>
				</a>
			</div>
			
			<div class="bfp-tips-card bfp-tips-card-customization">
				<h3>🎨 <?php esc_html_e( 'Customization', 'bandfront-members' ); ?></h3>
				<p>
					<?php esc_html_e( 'Customize your membership pages to match your brand.', 'bandfront-members' ); ?>
				</p>
				<a href="#" onclick="window.open('/customisation', '_blank')" class="bfp-tips-link bfp-tips-link-customization">
					<?php esc_html_e( 'Customization Guide →', 'bandfront-members' ); ?>
				</a>
			</div>
		</div>
		
		<div class="bfp-tips-protip">
			<p>
				🎯 <?php esc_html_e( 'Pro Tip: Set up your backer role and posts page first for quick results!', 'bandfront-members' ); ?>
			</p>
		</div>
	</div>
</div>

<form method="post" enctype="multipart/form-data" id="bfm-settings-form">
<input type="hidden" name="action" value="bfm_save_settings" />
<input type="hidden" name="bfm_nonce" value="<?php echo esc_attr( wp_create_nonce( 'bfm_updating_plugin_settings' ) ); ?>" />

<table class="widefat bfp-table-noborder">
	<tr>
		<table class="widefat bfp-settings-table bfp-membership-settings-section">
			<tr>
				<td class="bfp-section-header">
					<h2 onclick="jQuery(this).closest('table').find('.bfp-section-content').toggle(); jQuery(this).closest('.bfp-section-header').find('.bfp-section-arrow').toggleClass('bfp-section-arrow-open');" style="cursor: pointer;">
						👥 <?php esc_html_e( 'Membership Settings', 'bandfront-members' ); ?>
					</h2>
					<span class="bfp-section-arrow">▶</span>
				</td>
			</tr>
			<tbody class="bfp-section-content" style="display: block;">
				<tr>
					<td class="bfp-column-30"><label for="membership_enabled">✅ <?php esc_html_e( 'Enable Membership System', 'bandfront-members' ); ?></label></td>
					<td>
						<input type="checkbox" id="membership_enabled" name="membership_enabled" value="1" <?php checked($settings['membership_enabled'], true); ?>>
						<br><em class="bfp-em-text"><?php esc_html_e( 'Master switch to enable/disable the entire membership system', 'bandfront-members' ); ?></em>
					</td>
				</tr>
				<tr>
					<td class="bfp-column-30"><label for="backer_role">🎟️ <?php esc_html_e( 'Backer Role', 'bandfront-members' ); ?></label></td>
					<td>
						<select id="backer_role" name="backer_role" class="bfp-input-full">
							<?php
							$roles = wp_roles()->roles;
							foreach ($roles as $role_key => $role_data) {
								echo '<option value="' . esc_attr($role_key) . '" ' . selected($settings['backer_role'], $role_key, false) . '>' . 
									 esc_html($role_data['name']) . '</option>';
							}
							?>
						</select>
						<br><em class="bfp-em-text"><?php esc_html_e( 'Select which user role should be considered as "backers" with membership access', 'bandfront-members' ); ?></em>
					</td>
				</tr>
				<tr>
					<td class="bfp-column-30"><label for="join_page">🚪 <?php esc_html_e( 'Join Page', 'bandfront-members' ); ?></label></td>
					<td>
						<select id="join_page" name="join_page" class="bfp-input-full">
							<option value=""><?php esc_html_e( 'Select a page...', 'bandfront-members' ); ?></option>
							<?php
							$pages = get_pages();
							foreach ($pages as $page) {
								echo '<option value="' . esc_attr($page->ID) . '" ' . selected($settings['join_page'], $page->ID, false) . '>' . 
									 esc_html($page->post_title) . '</option>';
							}
							?>
						</select>
						<br><em class="bfp-em-text"><?php esc_html_e( 'Choose the page where visitors can sign up for membership', 'bandfront-members' ); ?></em>
					</td>
				</tr>
				<tr>
					<td class="bfp-column-30"><label for="posts_page">📄 <?php esc_html_e( 'Members Posts Page', 'bandfront-members' ); ?></label></td>
					<td>
						<select id="posts_page" name="posts_page" class="bfp-input-full">
							<option value=""><?php esc_html_e( 'Select a page...', 'bandfront-members' ); ?></option>
							<?php
							$pages = get_pages();
							foreach ($pages as $page) {
								echo '<option value="' . esc_attr($page->ID) . '" ' . selected($settings['posts_page'], $page->ID, false) . '>' . 
									 esc_html($page->post_title) . '</option>';
							}
							?>
						</select>
						<br><em class="bfp-em-text"><?php esc_html_e( 'Choose the page where member-only posts will be displayed', 'bandfront-members' ); ?></em>
					</td>
				</tr>
			</tbody>
		</table>
	</tr>
</table>

<table class="widefat bfp-table-noborder">
	<tr>
		<table class="widefat bfp-settings-table bfp-content-settings-section">
			<tr>
				<td class="bfp-section-header">
					<h2 onclick="jQuery(this).closest('table').find('.bfp-section-content').toggle(); jQuery(this).closest('.bfp-section-header').find('.bfp-section-arrow').toggleClass('bfp-section-arrow-open');" style="cursor: pointer;">
						📝 <?php esc_html_e( 'Content Settings', 'bandfront-members' ); ?>
					</h2>
					<span class="bfp-section-arrow">▶</span>
				</td>
			</tr>
			<tbody class="bfp-section-content" style="display: none;">
				<tr>
					<td>
						<p class="bfp-placeholder-text">🚧 <?php esc_html_e( 'Coming soon! This section will include settings for member-only content, post restrictions, and content visibility rules.', 'bandfront-members' ); ?></p>
						<div class="bfp-cloud-features">
							<h4><?php esc_html_e( 'Planned Features:', 'bandfront-members' ); ?></h4>
							<ul>
								<li>🔒 <?php esc_html_e( 'Content access levels and restrictions', 'bandfront-members' ); ?></li>
								<li>📊 <?php esc_html_e( 'Member-only post types', 'bandfront-members' ); ?></li>
								<li>🎨 <?php esc_html_e( 'Custom member content templates', 'bandfront-members' ); ?></li>
								<li>📱 <?php esc_html_e( 'Mobile-optimized member areas', 'bandfront-members' ); ?></li>
							</ul>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</tr>
</table>

<table class="widefat bfp-table-noborder">
	<tr>
		<table class="widefat bfp-settings-table bfp-payment-settings-section">
			<tr>
				<td class="bfp-section-header">
					<h2 onclick="jQuery(this).closest('table').find('.bfp-section-content').toggle(); jQuery(this).closest('.bfp-section-header').find('.bfp-section-arrow').toggleClass('bfp-section-arrow-open');" style="cursor: pointer;">
						💳 <?php esc_html_e( 'Payment Integration', 'bandfront-members' ); ?>
					</h2>
					<span class="bfp-section-arrow">▶</span>
				</td>
			</tr>
			<tbody class="bfp-section-content" style="display: none;">
				<tr>
					<td>
						<p class="bfp-cloud-info"><?php esc_html_e( 'Integrate with payment gateways to manage recurring memberships, one-time backing, and subscription billing automatically.', 'bandfront-members' ); ?></p>
						
						<input type="hidden" name="_bfp_cloud_active_tab" id="_bfp_cloud_active_tab" value="<?php echo esc_attr($cloud_active_tab); ?>" />
						
						<div class="bfp-cloud_tabs">
							<div class="bfp-cloud-tab-buttons">
								<button type="button" class="bfp-cloud-tab-btn <?php echo $cloud_active_tab === 'stripe' ? 'bfp-cloud-tab-active' : ''; ?>" data-tab="stripe">
									💳 <?php esc_html_e( 'Stripe', 'bandfront-members' ); ?>
								</button>
								<button type="button" class="bfp-cloud-tab-btn <?php echo $cloud_active_tab === 'paypal' ? 'bfp-cloud-tab-active' : ''; ?>" data-tab="paypal">
									🅿️ <?php esc_html_e( 'PayPal', 'bandfront-members' ); ?>
								</button>
								<button type="button" class="bfp-cloud-tab-btn <?php echo $cloud_active_tab === 'woocommerce' ? 'bfp-cloud-tab-active' : ''; ?>" data-tab="woocommerce">
									🛒 <?php esc_html_e( 'WooCommerce', 'bandfront-members' ); ?>
								</button>
								<button type="button" class="bfp-cloud-tab-btn <?php echo $cloud_active_tab === 'manual' ? 'bfp-cloud-tab-active' : ''; ?>" data-tab="manual">
									✋ <?php esc_html_e( 'Manual', 'bandfront-members' ); ?>
								</button>
							</div>
							
							<div class="bfp-cloud-tab-content">
								<!-- Stripe Tab -->
								<div class="bfp-cloud-tab-panel <?php echo $cloud_active_tab === 'stripe' ? 'bfp-cloud-tab-panel-active' : ''; ?>" data-panel="stripe">
									<div class="bfp-cloud-placeholder">
										<h3>💳 <?php esc_html_e( 'Stripe Integration', 'bandfront-members' ); ?></h3>
										<p><?php esc_html_e( 'Connect with Stripe for seamless recurring subscriptions, one-time payments, and automated billing management.', 'bandfront-members' ); ?></p>
										<div class="bfp-cloud-features">
											<h4><?php esc_html_e( 'Planned Features:', 'bandfront-members' ); ?></h4>
											<ul>
												<li>🔄 <?php esc_html_e( 'Recurring subscription management', 'bandfront-members' ); ?></li>
												<li>💰 <?php esc_html_e( 'Multiple membership tiers', 'bandfront-members' ); ?></li>
												<li>🌍 <?php esc_html_e( 'International payment support', 'bandfront-members' ); ?></li>
												<li>📊 <?php esc_html_e( 'Advanced revenue analytics', 'bandfront-members' ); ?></li>
											</ul>
										</div>
									</div>
								</div>
								
								<!-- PayPal Tab -->
								<div class="bfp-cloud-tab-panel <?php echo $cloud_active_tab === 'paypal' ? 'bfp-cloud-tab-panel-active' : ''; ?>" data-panel="paypal">
									<div class="bfp-cloud-placeholder">
										<h3>🅿️ <?php esc_html_e( 'PayPal Integration', 'bandfront-members' ); ?></h3>
										<p><?php esc_html_e( 'Integrate with PayPal for trusted payment processing and subscription management with global reach.', 'bandfront-members' ); ?></p>
										<div class="bfp-cloud-features">
											<h4><?php esc_html_e( 'Planned Features:', 'bandfront-members' ); ?></h4>
											<ul>
												<li>🔒 <?php esc_html_e( 'Secure PayPal checkout', 'bandfront-members' ); ?></li>
												<li>🔄 <?php esc_html_e( 'Subscription billing automation', 'bandfront-members' ); ?></li>
												<li>💳 <?php esc_html_e( 'Credit card processing via PayPal', 'bandfront-members' ); ?></li>
												<li>🌐 <?php esc_html_e( 'Multi-currency support', 'bandfront-members' ); ?></li>
											</ul>
										</div>
									</div>
								</div>
								
								<!-- WooCommerce Tab -->
								<div class="bfp-cloud-tab-panel <?php echo $cloud_active_tab === 'woocommerce' ? 'bfp-cloud-tab-panel-active' : ''; ?>" data-panel="woocommerce">
									<div class="bfp-cloud-placeholder">
										<h3>🛒 <?php esc_html_e( 'WooCommerce Integration', 'bandfront-members' ); ?></h3>
										<p><?php esc_html_e( 'Seamlessly integrate with WooCommerce for complete e-commerce functionality and membership management.', 'bandfront-members' ); ?></p>
										<div class="bfp-cloud-features">
											<h4><?php esc_html_e( 'Planned Features:', 'bandfront-members' ); ?></h4>
											<ul>
												<li>🛍️ <?php esc_html_e( 'Product-based membership access', 'bandfront-members' ); ?></li>
												<li>🎁 <?php esc_html_e( 'Membership product variations', 'bandfront-members' ); ?></li>
												<li>📦 <?php esc_html_e( 'Order-based role assignment', 'bandfront-members' ); ?></li>
												<li>💼 <?php esc_html_e( 'Full WooCommerce reporting', 'bandfront-members' ); ?></li>
											</ul>
										</div>
									</div>
								</div>
								
								<!-- Manual Tab -->
								<div class="bfp-cloud-tab-panel <?php echo $cloud_active_tab === 'manual' ? 'bfp-cloud-tab-panel-active' : ''; ?>" data-panel="manual">
									<div class="bfp-cloud-placeholder">
										<h3>✋ <?php esc_html_e( 'Manual Management', 'bandfront-members' ); ?></h3>
										<p><?php esc_html_e( 'Manually manage memberships without payment processing - perfect for invite-only or admin-controlled access.', 'bandfront-members' ); ?></p>
										<div class="bfp-cloud-features">
											<h4><?php esc_html_e( 'Planned Features:', 'bandfront-members' ); ?></h4>
											<ul>
												<li>👥 <?php esc_html_e( 'Admin member management dashboard', 'bandfront-members' ); ?></li>
												<li>📅 <?php esc_html_e( 'Membership expiration controls', 'bandfront-members' ); ?></li>
												<li>🎫 <?php esc_html_e( 'Invitation code system', 'bandfront-members' ); ?></li>
												<li>📊 <?php esc_html_e( 'Member activity tracking', 'bandfront-members' ); ?></li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</tr>
</table>

<table class="widefat bfp-table-noborder">
	<tr>
		<table class="widefat bfp-settings-table bfp-notifications-section">
			<tr>
				<td class="bfp-section-header">
					<h2 onclick="jQuery(this).closest('table').find('.bfp-section-content').toggle(); jQuery(this).closest('.bfp-section-header').find('.bfp-section-arrow').toggleClass('bfp-section-arrow-open');" style="cursor: pointer;">
						🔔 <?php esc_html_e( 'Notifications & Communication', 'bandfront-members' ); ?>
					</h2>
					<span class="bfp-section-arrow">▶</span>
				</td>
			</tr>
			<tbody class="bfp-section-content" style="display: none;">
				<tr>
					<td>
						<p class="bfp-placeholder-text">🚧 <?php esc_html_e( 'Email notifications, member communication tools, and automated messaging system.', 'bandfront-members' ); ?></p>
						<div class="bfp-cloud-features">
							<h4><?php esc_html_e( 'Planned Features:', 'bandfront-members' ); ?></h4>
							<ul>
								<li>📧 <?php esc_html_e( 'Welcome emails for new members', 'bandfront-members' ); ?></li>
								<li>🔔 <?php esc_html_e( 'New content notifications', 'bandfront-members' ); ?></li>
								<li>📊 <?php esc_html_e( 'Member engagement tracking', 'bandfront-members' ); ?></li>
								<li>💬 <?php esc_html_e( 'Built-in messaging system', 'bandfront-members' ); ?></li>
							</ul>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</tr>
</table>

<table class="widefat bfp-table-noborder">
	<tr>
		<table class="widefat bfp-settings-table bfp-analytics-section">
			<tr>
				<td class="bfp-section-header">
					<h2 onclick="jQuery(this).closest('table').find('.bfp-section-content').toggle(); jQuery(this).closest('.bfp-section-header').find('.bfp-section-arrow').toggleClass('bfp-section-arrow-open');" style="cursor: pointer;">
						📈 <?php esc_html_e( 'Analytics & Reporting', 'bandfront-members' ); ?>
					</h2>
					<span class="bfp-section-arrow">▶</span>
				</td>
			</tr>
			<tbody class="bfp-section-content" style="display: none;">
				<tr>
					<td>
						<p class="bfp-placeholder-text">🚧 <?php esc_html_e( 'Member activity tracking, engagement metrics, and detailed reporting dashboard.', 'bandfront-members' ); ?></p>
						<div class="bfp-cloud-features">
							<h4><?php esc_html_e( 'Planned Features:', 'bandfront-members' ); ?></h4>
							<ul>
								<li>📊 <?php esc_html_e( 'Member activity dashboards', 'bandfront-members' ); ?></li>
								<li>💰 <?php esc_html_e( 'Revenue and subscription analytics', 'bandfront-members' ); ?></li>
								<li>👥 <?php esc_html_e( 'Member growth tracking', 'bandfront-members' ); ?></li>
								<li>🎯 <?php esc_html_e( 'Content engagement metrics', 'bandfront-members' ); ?></li>
							</ul>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</tr>
</table>

<table class="widefat bfp-table-noborder">
	<tr>
		<table class="widefat bfp-settings-table bfp-troubleshoot-section">
			<tr>
				<td class="bfp-section-header">
					<h2 onclick="jQuery(this).closest('table').find('.bfp-section-content').toggle(); jQuery(this).closest('.bfp-section-header').find('.bfp-section-arrow').toggleClass('bfp-section-arrow-open');" style="cursor: pointer;">
						🔧 <?php esc_html_e( 'Troubleshooting', 'bandfront-members' ); ?>
					</h2>
					<span class="bfp-section-arrow">▶</span>
				</td>
			</tr>
			<tbody class="bfp-section-content" style="display: none;">
				<tr>
					<td class="bfp-troubleshoot-item">
						<p>👥 <?php esc_html_e( 'Members not getting proper access?', 'bandfront-members' ); ?></p>
						<label>
							<input aria-label="<?php esc_attr_e( 'Reset all member roles and permissions', 'bandfront-members' ); ?>" type="checkbox" name="_bfp_reset_member_roles" />
							<?php esc_html_e( 'Reset member roles and permissions', 'bandfront-members' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td class="bfp-troubleshoot-item">
						<p>🔄 <?php esc_html_e( 'Member data seems corrupted?', 'bandfront-members' ); ?></p>
						<label>
							<input aria-label="<?php esc_attr_e( 'Clear member cache and rebuild data', 'bandfront-members' ); ?>" type="checkbox" name="_bfp_clear_member_cache" />
							<?php esc_html_e( 'Clear member cache and rebuild data', 'bandfront-members' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td class="bfp-troubleshoot-item">
						<p>🚫 <?php esc_html_e( 'Content restrictions not working?', 'bandfront-members' ); ?></p>
						<label>
							<input aria-label="<?php esc_attr_e( 'Force refresh content access rules', 'bandfront-members' ); ?>" type="checkbox" name="_bfp_refresh_content_rules" />
							<?php esc_html_e( 'Refresh content access rules', 'bandfront-members' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td>
						<p class="bfp-troubleshoot-protip">💡 <?php esc_html_e( 'After changing troubleshooting settings, clear your website and browser caches for best results.', 'bandfront-members' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
	</tr>
</table>

<div class="bfp-submit-wrapper"><input type="submit" value="<?php esc_attr_e( 'Save settings', 'bandfront-members' ); ?>" class="button-primary" /></div>
</form>