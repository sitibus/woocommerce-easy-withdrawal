<?php
/**
 * Template: Conferma richiesta di recesso inviata.
 *
 * @var \WC_Order $order
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

$withdrawal_data = json_decode( $order->get_meta( '_wew_withdrawal_data', true ), true );
$items           = $withdrawal_data['items']        ?? [];
$reason          = $withdrawal_data['reason']       ?? '';
$requested_at    = $withdrawal_data['requested_at'] ?? '';
$wstatus         = $withdrawal_data['status']       ?? 'pending';

$status_map = [
	'pending'  => [ __( 'In attesa di presa in carico', 'easy-withdrawal-for-woocommerce' ), 'wew-status--pending' ],
	'accepted' => [ __( 'Approvato', 'easy-withdrawal-for-woocommerce' ), 'wew-status--accepted' ],
	'rejected' => [ __( 'Respinto', 'easy-withdrawal-for-woocommerce' ), 'wew-status--rejected' ],
];
[ $status_label, $status_class ] = $status_map[ $wstatus ] ?? $status_map['pending'];
?>

<div class="wew-confirmed-wrap">

	<div class="wew-confirmed-header">
		<span class="wew-confirmed-icon" aria-hidden="true">✔</span>
		<h2><?php esc_html_e( 'Richiesta di recesso inviata', 'easy-withdrawal-for-woocommerce' ); ?></h2>
		<p class="wew-confirmed-subtitle">
			<?php
			printf(
				/* translators: %s = numero ordine */
				esc_html__( 'La tua richiesta per l\'ordine #%s è stata ricevuta correttamente.', 'easy-withdrawal-for-woocommerce' ),
				esc_html( $order->get_order_number() )
			);
			?>
		</p>
	</div>

	<!-- Tracking stato -->
	<div class="wew-tracking">
		<div class="wew-tracking-step <?php echo 'rejected' !== $wstatus ? 'wew-tracking-step--done' : 'wew-tracking-step--done wew-tracking-step--rejected'; ?>">
			<span class="wew-tracking-dot"></span>
			<span><?php esc_html_e( 'In attesa', 'easy-withdrawal-for-woocommerce' ); ?></span>
		</div>
		<div class="wew-tracking-line <?php echo 'pending' !== $wstatus ? 'wew-tracking-line--done' : ''; ?>"></div>
		<div class="wew-tracking-step <?php echo 'pending' !== $wstatus ? 'wew-tracking-step--done' : ''; ?> <?php echo 'rejected' === $wstatus ? 'wew-tracking-step--rejected' : ''; ?>">
			<span class="wew-tracking-dot"></span>
			<span><?php echo 'rejected' === $wstatus ? esc_html__( 'Respinto', 'easy-withdrawal-for-woocommerce' ) : esc_html__( 'Preso in carico', 'easy-withdrawal-for-woocommerce' ); ?></span>
		</div>
		<div class="wew-tracking-line <?php echo 'accepted' === $wstatus ? 'wew-tracking-line--done' : ''; ?>"></div>
		<div class="wew-tracking-step <?php echo 'accepted' === $wstatus ? 'wew-tracking-step--done' : ''; ?>">
			<span class="wew-tracking-dot"></span>
			<span><?php esc_html_e( 'Approvato', 'easy-withdrawal-for-woocommerce' ); ?></span>
		</div>
	</div>

	<div class="wew-confirmed-detail">

		<?php if ( $requested_at ) : ?>
			<p class="wew-confirmed-date">
				<strong><?php esc_html_e( 'Data richiesta:', 'easy-withdrawal-for-woocommerce' ); ?></strong>
				<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $requested_at ) ) ); ?>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<h3><?php esc_html_e( 'Prodotti inclusi nel recesso', 'easy-withdrawal-for-woocommerce' ); ?></h3>
			<ul class="wew-confirmed-items">
				<?php foreach ( $items as $item ) : ?>
					<li>
						<?php echo esc_html( $item['name'] ); ?>
						<span class="wew-confirmed-qty">
							× <?php echo esc_html( $item['qty'] ); ?>
							<?php if ( ! empty( $item['qty_ordered'] ) && (int) $item['qty'] < (int) $item['qty_ordered'] ) : ?>
								<small style="color:#aaa;">(<?php /* translators: %d = numero di pezzi ordinati */
								echo esc_html( sprintf( __( 'di %d ordinati', 'easy-withdrawal-for-woocommerce' ), $item['qty_ordered'] ) ); ?>)</small>
							<?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $reason ) : ?>
			<h3><?php esc_html_e( 'Motivo dichiarato', 'easy-withdrawal-for-woocommerce' ); ?></h3>
			<p class="wew-confirmed-reason"><?php echo esc_html( $reason ); ?></p>
		<?php endif; ?>

		<div class="wew-confirmed-status-box <?php echo esc_attr( $status_class ); ?>">
			<span class="wew-status-dot"></span>
			<?php echo esc_html( $status_label ); ?>
		</div>

		<p class="wew-confirmed-info">
			<?php esc_html_e( 'Riceverai una email di conferma a breve. Il nostro team ti contatterà per le istruzioni sulla restituzione.', 'easy-withdrawal-for-woocommerce' ); ?>
		</p>

	</div>

	<div class="wew-confirmed-actions">
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="wew-btn wew-btn--secondary">
			← <?php esc_html_e( 'Torna ai miei ordini', 'easy-withdrawal-for-woocommerce' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( [
			'wew_action'   => 'pdf',
			'wew_order_id' => $order->get_id(),
			'wew_nonce'    => wp_create_nonce( 'wew_pdf_customer_' . $order->get_id() ),
		], wc_get_account_endpoint_url( 'orders' ) ) ); ?>" class="wew-btn wew-btn--primary" target="_blank">
			📄 <?php esc_html_e( 'Scarica PDF', 'easy-withdrawal-for-woocommerce' ); ?>
		</a>
	</div>

</div>
