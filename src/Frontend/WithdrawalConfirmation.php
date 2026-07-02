<?php
/**
 * Pagina di conferma recesso dopo l'invio del form.
 *
 * @package WooCommerceEasyWithdrawal\Frontend
 */

declare( strict_types=1 );

namespace WEW\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WithdrawalConfirmation
 */
final class WithdrawalConfirmation {

	/** Registra hook. */
	public function init(): void {
		add_action( 'template_redirect', [ $this, 'handle_confirmation_display' ] );
	}

	/** Intercetta GET ?wew_action=confirmed e mostra la conferma. */
	public function handle_confirmation_display(): void {
		if ( ! is_account_page() ) {
			return;
		}

		$action = sanitize_key( $_GET['wew_action'] ?? '' );
		if ( 'confirmed' !== $action ) {
			return;
		}

		$order_id = absint( $_GET['wew_order_id'] ?? 0 );
		$nonce    = sanitize_text_field( wp_unslash( $_GET['wew_nonce'] ?? '' ) );

		if ( ! $order_id || ! wp_verify_nonce( $nonce, 'wew_confirmed_' . $order_id ) ) {
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order || (int) $order->get_customer_id() !== get_current_user_id() ) {
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		add_action( 'woocommerce_account_content', function () use ( $order ): void {
			wc_get_template(
				'withdrawal-confirmed.php',
				[ 'order' => $order ],
				'woocommerce-easy-withdrawal/',
				WEW_DIR . 'templates/'
			);
		}, 5 );

		remove_action( 'woocommerce_account_orders_endpoint', 'woocommerce_account_orders' );
	}
}
