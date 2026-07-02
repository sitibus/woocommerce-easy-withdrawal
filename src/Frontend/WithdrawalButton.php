<?php
/**
 * Pulsante "Richiedi recesso" nella pagina I miei ordini.
 * Condizionato: completato + pagato + entro X giorni + nessuna richiesta esistente.
 *
 * @package WooCommerceEasyWithdrawal\Frontend
 */

declare( strict_types=1 );

namespace WEW\Frontend;

use WEW\Helpers\OrderHelper;
use WEW\Helpers\SettingsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WithdrawalButton
 */
final class WithdrawalButton {

	/** Registra hook WordPress. */
	public function init(): void {
		// Colonna extra nella tabella ordini frontend.
		add_filter( 'woocommerce_my_account_my_orders_actions', [ $this, 'add_withdrawal_action' ], 10, 2 );

		// CSS e JS frontend.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Aggiunge l'azione "Richiedi recesso" alla riga dell'ordine.
	 *
	 * @param array<string, array<string, string>> $actions Azioni esistenti.
	 * @param \WC_Order                            $order   Ordine corrente.
	 * @return array<string, array<string, string>>
	 */
	public function add_withdrawal_action( array $actions, \WC_Order $order ): array {
		if ( ! OrderHelper::is_eligible_for_withdrawal( $order ) ) {
			// Se c'è già una richiesta, mostra lo stato corrente.
			if ( OrderHelper::has_existing_withdrawal( $order ) ) {
				$data    = json_decode( $order->get_meta( '_wew_withdrawal_data', true ), true );
				$wstatus = $data['status'] ?? 'pending';

				$labels = [
					'pending'  => '⏳ ' . __( 'Recesso in attesa', 'woocommerce-easy-withdrawal' ),
					'accepted' => '✔ ' . __( 'Recesso approvato', 'woocommerce-easy-withdrawal' ),
					'rejected' => '✕ ' . __( 'Recesso respinto', 'woocommerce-easy-withdrawal' ),
				];

				$actions['wew_status'] = [
					'url'  => '#',
					'name' => $labels[ $wstatus ] ?? $labels['pending'],
				];
			}
			return $actions;
		}

		$actions['wew_withdrawal'] = [
			'url'  => $this->get_withdrawal_url( $order ),
			'name' => esc_html( SettingsHelper::button_text() ),
		];

		return $actions;
	}

	/**
	 * URL della pagina di recesso per questo ordine.
	 *
	 * @param \WC_Order $order Ordine.
	 * @return string
	 */
	private function get_withdrawal_url( \WC_Order $order ): string {
		$base = wc_get_account_endpoint_url( 'orders' );

		return add_query_arg(
			[
				'wew_action'   => 'request',
				'wew_order_id' => $order->get_id(),
				'wew_nonce'    => wp_create_nonce( 'wew_withdrawal_' . $order->get_id() ),
			],
			$base
		);
	}

	/** Carica assets CSS/JS frontend (solo nelle pagine account). */
	public function enqueue_assets(): void {
		if ( ! is_account_page() ) {
			return;
		}

		wp_enqueue_style(
			'wew-frontend',
			WEW_URL . 'assets/css/frontend.css',
			[],
			WEW_VERSION
		);
	}
}
