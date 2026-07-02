<?php
/**
 * Template: Dettaglio richiesta di recesso, con azioni Approva/Respingi.
 *
 * @var \WC_Order $order
 * @var array     $data
 * @var string    $action_nonce
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

use WEW\Admin\Dashboard;

$back_url = admin_url( 'admin.php?page=' . Dashboard::PAGE_SLUG );

$status_badges = [
	'pending'  => [ __( 'In attesa', 'woocommerce-easy-withdrawal' ), 'pending' ],
	'accepted' => [ __( 'Approvato', 'woocommerce-easy-withdrawal' ), 'accepted' ],
	'rejected' => [ __( 'Respinto', 'woocommerce-easy-withdrawal' ), 'rejected' ],
];
$badge = $status_badges[ $data['status'] ] ?? $status_badges['pending'];
?>
<div class="wrap wew-dashboard wew-detail">

	<a href="<?php echo esc_url( $back_url ); ?>" class="wew-back-link">
		← <?php esc_html_e( 'Torna alla lista', 'woocommerce-easy-withdrawal' ); ?>
	</a>

	<h1 class="wp-heading-inline">
		<?php
		printf(
			/* translators: %s = numero ordine */
			esc_html__( 'Richiesta di recesso — Ordine #%s', 'woocommerce-easy-withdrawal' ),
			esc_html( $order->get_order_number() )
		);
		?>
		<span class="wew-badge wew-badge--<?php echo esc_attr( $badge[1] ); ?> wew-badge--large">
			<?php echo esc_html( $badge[0] ); ?>
		</span>
	</h1>

	<hr class="wp-header-end">

	<div class="wew-detail-grid">

		<!-- Colonna principale -->
		<div class="wew-detail-main">

			<div class="wew-card">
				<h2><?php esc_html_e( 'Dati cliente', 'woocommerce-easy-withdrawal' ); ?></h2>
				<table class="wew-detail-table">
					<tr>
						<th><?php esc_html_e( 'Nome', 'woocommerce-easy-withdrawal' ); ?></th>
						<td><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Email', 'woocommerce-easy-withdrawal' ); ?></th>
						<td><a href="mailto:<?php echo esc_attr( $order->get_billing_email() ); ?>"><?php echo esc_html( $order->get_billing_email() ); ?></a></td>
					</tr>
					<?php if ( $order->get_billing_phone() ) : ?>
					<tr>
						<th><?php esc_html_e( 'Telefono', 'woocommerce-easy-withdrawal' ); ?></th>
						<td><?php echo esc_html( $order->get_billing_phone() ); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th><?php esc_html_e( 'Data richiesta', 'woocommerce-easy-withdrawal' ); ?></th>
						<td>
							<?php
							echo $data['requested_at']
								? esc_html( date_i18n( 'd/m/Y H:i', strtotime( $data['requested_at'] ) ) )
								: '—';
							?>
						</td>
					</tr>
				</table>
			</div>

			<div class="wew-card">
				<h2><?php esc_html_e( 'Prodotti inclusi nel recesso', 'woocommerce-easy-withdrawal' ); ?></h2>
				<?php if ( ! empty( $data['items'] ) ) : ?>
					<table class="wew-detail-table wew-items-table-detail">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Prodotto', 'woocommerce-easy-withdrawal' ); ?></th>
								<th><?php esc_html_e( 'Quantità', 'woocommerce-easy-withdrawal' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $data['items'] as $item ) : ?>
								<tr>
									<td><?php echo esc_html( $item['name'] ); ?></td>
									<td><?php echo esc_html( $item['qty'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'Nessun prodotto registrato.', 'woocommerce-easy-withdrawal' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $data['reason'] ) : ?>
			<div class="wew-card">
				<h2><?php esc_html_e( 'Motivo dichiarato', 'woocommerce-easy-withdrawal' ); ?></h2>
				<p class="wew-reason-text"><?php echo esc_html( $data['reason'] ); ?></p>
			</div>
			<?php endif; ?>

		</div>

		<!-- Sidebar azioni -->
		<div class="wew-detail-sidebar">

			<div class="wew-card wew-actions-card">
				<h2><?php esc_html_e( 'Azioni', 'woocommerce-easy-withdrawal' ); ?></h2>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="wew_update_status">
					<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<?php wp_nonce_field( 'wew_update_status_' . $order->get_id() ); ?>

					<?php if ( 'pending' === $data['status'] ) : ?>
						<button type="submit" name="new_status" value="accepted" class="button button-primary wew-action-btn wew-action-btn--accept">
							✔ <?php esc_html_e( 'Approva richiesta', 'woocommerce-easy-withdrawal' ); ?>
						</button>
						<button type="submit" name="new_status" value="rejected" class="button wew-action-btn wew-action-btn--reject">
							✕ <?php esc_html_e( 'Respingi richiesta', 'woocommerce-easy-withdrawal' ); ?>
						</button>
					<?php elseif ( 'accepted' === $data['status'] ) : ?>
						<p class="wew-status-info wew-status-info--accepted">
							✔ <?php esc_html_e( 'Richiesta già approvata.', 'woocommerce-easy-withdrawal' ); ?>
						</p>
						<button type="submit" name="new_status" value="rejected" class="button wew-action-btn wew-action-btn--reject">
							<?php esc_html_e( 'Revoca e respingi', 'woocommerce-easy-withdrawal' ); ?>
						</button>
					<?php else : ?>
						<p class="wew-status-info wew-status-info--rejected">
							✕ <?php esc_html_e( 'Richiesta respinta.', 'woocommerce-easy-withdrawal' ); ?>
						</p>
						<button type="submit" name="new_status" value="accepted" class="button button-primary wew-action-btn wew-action-btn--accept">
							<?php esc_html_e( 'Riconsidera e approva', 'woocommerce-easy-withdrawal' ); ?>
						</button>
					<?php endif; ?>
				</form>
			</div>

			<div class="wew-card">
				<h2><?php esc_html_e( 'Ordine collegato', 'woocommerce-easy-withdrawal' ); ?></h2>
				<table class="wew-detail-table">
					<tr>
						<th><?php esc_html_e( 'Totale ordine', 'woocommerce-easy-withdrawal' ); ?></th>
						<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
					</tr>
					<?php
					// Calcola il totale da rimborsare per i prodotti selezionati.
					$refund_total = 0.0;
					$selected_ids = array_keys( $data['items'] );
					$is_partial   = count( $selected_ids ) < count( $order->get_items() );

					foreach ( $order->get_items() as $item_id => $item ) {
						/** @var \WC_Order_Item_Product $item */
						if ( empty( $data['items'][ $item_id ] ) ) {
							continue;
						}
						$item_data     = $data['items'][ $item_id ];
						$qty_requested = (int) ( $item_data['qty']         ?? $item->get_quantity() );
						$qty_ordered   = (int) ( $item_data['qty_ordered'] ?? $item->get_quantity() );
						$line_total    = (float) $order->get_line_total( $item, true, true );
						// Proporziona il totale alla quantità richiesta.
						$unit_price    = $qty_ordered > 0 ? $line_total / $qty_ordered : $line_total;
						$refund_total += $unit_price * $qty_requested;
					}

					// Se non abbiamo trovato corrispondenze per item_id (dati vecchi senza ID),
					// usiamo il totale intero come fallback.
					if ( $refund_total <= 0 ) {
						$refund_total = (float) $order->get_total();
						$is_partial   = false;
					}
					?>
					<tr>
						<th><?php esc_html_e( 'Da rimborsare', 'woocommerce-easy-withdrawal' ); ?></th>
						<td>
							<strong style="color:#0f5132;font-size:15px;">
								<?php echo wp_kses_post( wc_price( $refund_total, [ 'currency' => $order->get_currency() ] ) ); ?>
							</strong>
							<?php if ( $is_partial ) : ?>
								<br><small style="color:#888;"><?php esc_html_e( 'Recesso parziale — solo prodotti selezionati', 'woocommerce-easy-withdrawal' ); ?></small>
							<?php else : ?>
								<br><small style="color:#888;"><?php esc_html_e( 'Recesso totale', 'woocommerce-easy-withdrawal' ); ?></small>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Stato ordine', 'woocommerce-easy-withdrawal' ); ?></th>
						<td><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></td>
					</tr>
				</table>
				<p class="wew-refund-note">
					⚠️ <?php esc_html_e( 'Importo indicativo IVA inclusa. Il rimborso va effettuato manualmente.', 'woocommerce-easy-withdrawal' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>" class="button">
						<?php esc_html_e( 'Apri ordine in WooCommerce →', 'woocommerce-easy-withdrawal' ); ?>
					</a>
				</p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wew_download_pdf&order_id=' . $order->get_id() ), 'wew_pdf_' . $order->get_id() ) ); ?>"
					   class="button" target="_blank">
						📄 <?php esc_html_e( 'Scarica PDF recesso', 'woocommerce-easy-withdrawal' ); ?>
					</a>
				</p>
			</div>

		</div>

	</div>

</div>
