<?php
/**
 * Plugin Name:       WooCommerce Easy Withdrawal
 * Plugin URI:        https://github.com/sitibus/woocommerce-easy-withdrawal
 * Description:       EU Right of Withdrawal (Direttiva 2011/83/UE) per WooCommerce. Conforme, sicuro, riutilizzabile.
 * Version:           1.0.0
 * Author:            Gianluca Busetto
 * Author URI:        https://github.com/sitibus
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       woocommerce-easy-withdrawal
 * Domain Path:       /languages
 * Requires at least: 6.8
 * Requires PHP:      8.1
 * WC requires at least: 10.0
 * WC tested up to:   10.9
 *
 * @package WooCommerceEasyWithdrawal
 */

declare( strict_types=1 );

namespace WEW;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'WEW_VERSION',     '1.0.0' );
define( 'WEW_FILE',        __FILE__ );
define( 'WEW_DIR',         plugin_dir_path( __FILE__ ) );
define( 'WEW_URL',         plugin_dir_url( __FILE__ ) );
define( 'WEW_SLUG',        'woocommerce-easy-withdrawal' );
define( 'WEW_TEXT_DOMAIN', 'woocommerce-easy-withdrawal' );

// Autoloader PSR-4 semplice (senza Composer per portabilità massima).
spl_autoload_register( function ( string $class ): void {
	$prefix = 'WEW\\';
	if ( ! str_starts_with( $class, $prefix ) ) {
		return;
	}
	$relative = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $class, strlen( $prefix ) ) );
	$file      = WEW_DIR . 'src' . DIRECTORY_SEPARATOR . $relative . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

// Dichiarazione compatibilità HPOS (WooCommerce High Performance Order Storage).
add_action( 'before_woocommerce_init', function (): void {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
} );

// Bootstrap.
add_action( 'plugins_loaded', function (): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function (): void {
			echo '<div class="notice notice-error"><p>'
				. esc_html__( 'WooCommerce Easy Withdrawal richiede WooCommerce attivo.', 'woocommerce-easy-withdrawal' )
				. '</p></div>';
		} );
		return;
	}

	Core\Plugin::instance()->init();
}, 10 );

// Hooks di attivazione / disattivazione / disinstallazione.
register_activation_hook( __FILE__,   [ Core\Installer::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Core\Installer::class, 'deactivate' ] );
