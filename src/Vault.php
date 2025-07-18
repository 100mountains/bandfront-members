<?php
namespace bfm;

if (!defined('ABSPATH')) {
    exit;
}

class Vault {
    
    private Config $config;
    private Roles $roles;
    
    public function __construct(Config $config, Roles $roles) {
        $this->config = $config;
        $this->roles = $roles;
    }
    
    public function init(): void {
        // Register vault endpoint
        add_action('init', [$this, 'addVaultEndpoint']);
        add_filter('query_vars', [$this, 'addVaultQueryVar'], 0);
        add_filter('woocommerce_account_menu_items', [$this, 'addVaultMenuItem']);
        add_action('woocommerce_account_vault_endpoint', [$this, 'renderVaultContent']);
    }
    
    public function addVaultEndpoint(): void {
        add_rewrite_endpoint('vault', EP_ROOT | EP_PAGES);
    }
    
    public function addVaultQueryVar($vars): array {
        $vars[] = 'vault';
        return $vars;
    }
    
    public function addVaultMenuItem($items): array {
        // Only show vault to backers
        if ($this->roles->userHasBackstageAccess()) {
            $logout = $items['customer-logout'];
            unset($items['customer-logout']);
            $items['vault'] = __('Vault', 'bandfront-members');
            $items['customer-logout'] = $logout;
        }
        return $items;
    }
    
    public function renderVaultContent(): void {
        if (!$this->roles->userHasBackstageAccess()) {
            echo '<p>' . __('You need to be a backer to access the vault.', 'bandfront-members') . '</p>';
            return;
        }
        
        // You can either include a template file or render directly
        $this->displayVaultContent();
    }
    
    private function displayVaultContent(): void {
        ?>
        <div class="vault-content">
            <h2>🎉 Welcome to The Vault! 🎉</h2>
            
            <p>Hello peeps! 😄✨</p>
            
            <p>🔥 I am now testing this area! We now have a code for use on therob.lol! Add the album to your basket, go to checkout and add the coupon there, you will see the price go to 0. once thats done you can click go to paypal. it wont go to paypal it will just give you the album 🚀</p>
            <p> 💥 I will be adding more albums and codes soon, so keep checking back! 💥</p>
            
            <p> 💖 Thank you for your support! 💖</p>

            <div class="album-section">
               more codes soon! 
            </div>
            
            <hr style="margin: 30px 0; border: 2px dashed #333;">
            
            <div style="text-align: center; margin-top: 30px;">
                <p>🎊 Enjoy your free music! 🎊</p>
                <p>💝 More goodies coming soon! 💝</p>
            </div>
        </div>
        <?php
    }
}
