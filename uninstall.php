<?php
/**
 * Script di disinstallazione.
 *
 * @package WooCommerceEasyWithdrawal
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Rimuovi opzioni del plugin.
delete_option( 'wew_settings' );
delete_option( 'wew_wizard_completed' );
delete_option( 'wew_redirect_to_wizard' );
delete_option( 'wew_conditions_page_created' );
