<?php
/**
 * Stato ordine personalizzato: "Recesso richiesto".
 *
 * @package WooCommerceEasyWithdrawal\Email
 */

declare( strict_types=1 );

namespace WEW\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OrderStatus
 */
final class OrderStatus {

	/** Slug dello stato personalizzato. */
	public const STATUS = 'wew-requested';

	/** Registra hook. */
	public function init(): void {
		add_action( 'init',                         [ $this, 'register_status' ] );
		add_filter( 'wc_order_statuses',            [ $this, 'add_to_wc_list' ] );
		add_filter( 'woocommerce_order_is_paid_statuses', [ $this, 'mark_as_paid' ] );
	}

	/** Registra lo status come post_status WordPress. */
	public function register_status(): void {
		register_post_status( 'wc-' . self::STATUS, [
			'label'                     => _x( 'Recesso richiesto', 'Order status', 'easy-withdrawal-for-woocommerce' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s = numero ordini */
			'label_count'               => _n_noop(
				'Recesso richiesto <span class="count">(%s)</span>',
				'Recesso richiesto <span class="count">(%s)</span>',
				'easy-withdrawal-for-woocommerce'
			),
		] );
	}

	/**
	 * Aggiunge lo stato alla lista WooCommerce.
	 *
	 * @param array<string, string> $statuses Stati esistenti.
	 * @return array<string, string>
	 */
	public function add_to_wc_list( array $statuses ): array {
		$statuses[ 'wc-' . self::STATUS ] = _x( 'Recesso richiesto', 'Order status', 'easy-withdrawal-for-woocommerce' );
		return $statuses;
	}

	/**
	 * Lo stato viene considerato "pagato" (non blocca rimborsi o report).
	 *
	 * @param string[] $statuses Stati pagati esistenti.
	 * @return string[]
	 */
	public function mark_as_paid( array $statuses ): array {
		$statuses[] = self::STATUS;
		return $statuses;
	}

	/**
	 * Imposta lo stato "Recesso richiesto" sull'ordine e aggiunge nota.
	 *
	 * @param \WC_Order $order Ordine.
	 */
	public static function set_withdrawal_requested( \WC_Order $order ): void {
		$order->update_status(
			self::STATUS,
			__( 'Richiesta di recesso inviata dal cliente.', 'easy-withdrawal-for-woocommerce' )
		);

		// Nota ordine con dettaglio.
		$withdrawal_data = json_decode( $order->get_meta( '_wew_withdrawal_data', true ), true );
		$items           = $withdrawal_data['items']  ?? [];
		$reason          = $withdrawal_data['reason'] ?? '';

		$item_names = implode( ', ', array_column( $items, 'name' ) );

		$note = sprintf(
			/* translators: 1: data, 2: prodotti, 3: motivo */
			__( '🔄 Recesso richiesto il %1$s. Prodotti: %2$s.%3$s', 'easy-withdrawal-for-woocommerce' ),
			current_time( 'd/m/Y H:i' ),
			$item_names ?: __( 'tutti', 'easy-withdrawal-for-woocommerce' ),
			/* translators: %s = motivo del recesso */
			$reason ? ' ' . sprintf( __( 'Motivo: "%s".', 'easy-withdrawal-for-woocommerce' ), $reason ) : ''
		);

		$order->add_order_note( $note, false, false );
		$order->save();
	}
}
