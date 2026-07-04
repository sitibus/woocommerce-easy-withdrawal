<?php
/**
 * Metabox nell'ordine WooCommerce: dettaglio richiesta di recesso.
 * Compatibile con HPOS (usa WC_Meta_Box style).
 *
 * @package WooCommerceEasyWithdrawal\Admin
 */

declare( strict_types=1 );

namespace WEW\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WithdrawalMetabox
 */
final class WithdrawalMetabox {

	/** Registra hook. */
	public function init(): void {
		// HPOS: usa l'hook woocommerce_order_data_after_order_details se disponibile,
		// altrimenti il classico add_meta_boxes.
		add_action( 'add_meta_boxes', [ $this, 'register_metabox' ] );

		// Compatibilità HPOS (WC 7.1+).
		add_action( 'woocommerce_order_data_after_billing_address', [ $this, 'maybe_render_inline' ], 10, 1 );
	}

	/** Registra il metabox classico (ordini legacy / post). */
	public function register_metabox(): void {
		// Supporta sia HPOS che tabelle legacy in modo sicuro.
		$screens = [ 'shop_order', 'woocommerce_page_wc-orders' ];

		foreach ( $screens as $screen ) {
			add_meta_box(
				'wew_withdrawal_metabox',
				__( '🔄 Recesso Easy Withdrawal', 'easy-withdrawal-for-woocommerce' ),
				[ $this, 'render' ],
				$screen,
				'side',
				'high'
			);
		}
	}

	/**
	 * Render inline aggiuntivo per HPOS (pagina ordine nuova).
	 *
	 * @param \WC_Order $order Ordine.
	 */
	public function maybe_render_inline( \WC_Order $order ): void {
		// Evita doppio render: il metabox classico è già presente.
	}

	/**
	 * Render del contenuto del metabox.
	 *
	 * @param \WP_Post|\WC_Order $post_or_order Post o ordine (dipende da HPOS).
	 */
	public function render( \WP_Post|\WC_Order $post_or_order ): void {
		$order = $post_or_order instanceof \WC_Order
			? $post_or_order
			: wc_get_order( $post_or_order->ID );

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		$requested = $order->get_meta( '_wew_withdrawal_requested', true );

		if ( ! $requested ) {
			echo '<p style="color:#888;font-size:12px;">'
				. esc_html__( 'Nessuna richiesta di recesso per questo ordine.', 'easy-withdrawal-for-woocommerce' )
				. '</p>';
			return;
		}

		$data   = json_decode( $order->get_meta( '_wew_withdrawal_data', true ), true );
		$items  = $data['items']        ?? [];
		$reason = $data['reason']       ?? '';
		$date   = $data['requested_at'] ?? '';
		$status = $data['status']       ?? 'pending';

		$status_labels = [
			'pending'  => [ __( 'In attesa', 'easy-withdrawal-for-woocommerce' ), '#ffc107' ],
			'accepted' => [ __( 'Approvato', 'easy-withdrawal-for-woocommerce' ), '#28a745' ],
			'rejected' => [ __( 'Respinto', 'easy-withdrawal-for-woocommerce' ),  '#dc3545' ],
		];
		[ $status_label, $status_color ] = $status_labels[ $status ] ?? $status_labels['pending'];
		?>

		<div class="wew-metabox">

			<p>
				<strong><?php esc_html_e( 'Data richiesta:', 'easy-withdrawal-for-woocommerce' ); ?></strong><br>
				<?php echo $date ? esc_html( date_i18n( 'd/m/Y H:i', strtotime( $date ) ) ) : '—'; ?>
			</p>

			<p>
				<strong><?php esc_html_e( 'Stato:', 'easy-withdrawal-for-woocommerce' ); ?></strong><br>
				<span style="
					display:inline-block;
					padding:2px 10px;
					border-radius:10px;
					background:<?php echo esc_attr( $status_color ); ?>22;
					color:<?php echo esc_attr( $status_color ); ?>;
					border:1px solid <?php echo esc_attr( $status_color ); ?>55;
					font-weight:600;
					font-size:12px;
				">
					<?php echo esc_html( $status_label ); ?>
				</span>
			</p>

			<?php if ( ! empty( $items ) ) : ?>
				<p><strong><?php esc_html_e( 'Prodotti:', 'easy-withdrawal-for-woocommerce' ); ?></strong></p>
				<ul style="margin:0 0 8px 16px;padding:0;font-size:12px;">
					<?php foreach ( $items as $item ) : ?>
						<li><?php echo esc_html( $item['name'] ); ?> &times; <?php echo esc_html( $item['qty'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $reason ) : ?>
				<p>
					<strong><?php esc_html_e( 'Motivo:', 'easy-withdrawal-for-woocommerce' ); ?></strong><br>
					<em style="font-size:12px;color:#555;"><?php echo esc_html( $reason ); ?></em>
				</p>
			<?php endif; ?>

		</div>

		<style>
		.wew-metabox p { margin-bottom: 10px; font-size: 13px; }
		.wew-metabox ul li { font-size: 12px; margin-bottom: 3px; }
		</style>

		<?php
	}
}
