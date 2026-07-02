<?php
/**
 * Template: Dashboard lista richieste di recesso.
 *
 * @var \WC_Order[] $orders
 * @var int         $total
 * @var int         $total_pages
 * @var int         $paged
 * @var string      $status
 * @var string      $search
 * @var array       $counts
 * @var string      $export_nonce
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

use WEW\Admin\Dashboard;
use WEW\Helpers\WithdrawalRepository;

$base_url = admin_url( 'admin.php?page=' . Dashboard::PAGE_SLUG );

$status_tabs = [
	''         => __( 'Tutte', 'woocommerce-easy-withdrawal' ),
	'pending'  => __( 'In attesa', 'woocommerce-easy-withdrawal' ),
	'accepted' => __( 'Approvate', 'woocommerce-easy-withdrawal' ),
	'rejected' => __( 'Respinte', 'woocommerce-easy-withdrawal' ),
];

$status_badges = [
	'pending'  => [ __( 'In attesa', 'woocommerce-easy-withdrawal' ), 'pending' ],
	'accepted' => [ __( 'Approvato', 'woocommerce-easy-withdrawal' ), 'accepted' ],
	'rejected' => [ __( 'Respinto', 'woocommerce-easy-withdrawal' ), 'rejected' ],
];
?>
<div class="wrap wew-dashboard">

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Richieste di Recesso', 'woocommerce-easy-withdrawal' ); ?></h1>

	<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wew_export_csv&status=' . $status . '&s=' . rawurlencode( $search ) ), 'wew_export_csv' ) ); ?>"
	   class="page-title-action">
		⬇ <?php esc_html_e( 'Esporta CSV', 'woocommerce-easy-withdrawal' ); ?>
	</a>

	<hr class="wp-header-end">

	<!-- Tab filtri stato -->
	<ul class="subsubsub">
		<?php
		$i = 0;
		foreach ( $status_tabs as $key => $label ) :
			++$i;
			$count = '' === $key ? $counts['total'] : ( $counts[ $key ] ?? 0 );
			$url   = $key ? add_query_arg( 'status', $key, $base_url ) : $base_url;
			$class = $status === $key ? 'current' : '';
			?>
			<li>
				<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>">
					<?php echo esc_html( $label ); ?> <span class="count">(<?php echo esc_html( (string) $count ); ?>)</span>
				</a>
				<?php echo $i < count( $status_tabs ) ? ' | ' : ''; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<!-- Ricerca -->
	<form method="get" class="wew-search-form">
		<input type="hidden" name="page" value="<?php echo esc_attr( Dashboard::PAGE_SLUG ); ?>">
		<?php if ( $status ) : ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
		<?php endif; ?>
		<p class="search-box">
			<label class="screen-reader-text" for="wew-search-input"><?php esc_html_e( 'Cerca richieste', 'woocommerce-easy-withdrawal' ); ?></label>
			<input type="search" id="wew-search-input" name="s" value="<?php echo esc_attr( $search ); ?>"
				   placeholder="<?php esc_attr_e( 'Cerca cliente, email, ordine...', 'woocommerce-easy-withdrawal' ); ?>">
			<input type="submit" class="button" value="<?php esc_attr_e( 'Cerca', 'woocommerce-easy-withdrawal' ); ?>">
		</p>
	</form>

	<!-- Tabella -->
	<table class="wp-list-table widefat fixed striped wew-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Ordine', 'woocommerce-easy-withdrawal' ); ?></th>
				<th><?php esc_html_e( 'Cliente', 'woocommerce-easy-withdrawal' ); ?></th>
				<th><?php esc_html_e( 'Data richiesta', 'woocommerce-easy-withdrawal' ); ?></th>
					<th><?php esc_html_e( 'Totale', 'woocommerce-easy-withdrawal' ); ?></th>
				<th><?php esc_html_e( 'Stato', 'woocommerce-easy-withdrawal' ); ?></th>
				<th><?php esc_html_e( 'Azioni', 'woocommerce-easy-withdrawal' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $orders ) ) : ?>
				<tr>
					<td colspan="7" class="wew-empty-row">
						<?php esc_html_e( 'Nessuna richiesta di recesso trovata.', 'woocommerce-easy-withdrawal' ); ?>
					</td>
				</tr>
			<?php else : ?>
				<?php foreach ( $orders as $order ) :
					/** @var \WC_Order $order */
					$data        = WithdrawalRepository::get_data( $order );
					$badge       = $status_badges[ $data['status'] ] ?? $status_badges['pending'];
					$detail_url  = add_query_arg( [ 'view' => 'detail', 'order_id' => $order->get_id() ], $base_url );
					?>
					<tr>
						<td>
							<a href="<?php echo esc_url( $detail_url ); ?>"><strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong></a>
						</td>
						<td>
							<?php echo esc_html( $order->get_formatted_billing_full_name() ); ?><br>
							<small class="wew-email"><?php echo esc_html( $order->get_billing_email() ); ?></small>
						</td>
						<td>
							<?php
							echo $data['requested_at']
								? esc_html( date_i18n( 'd/m/Y H:i', strtotime( $data['requested_at'] ) ) )
								: '—';
							?>
						</td>
						<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
						<td>
							<span class="wew-badge wew-badge--<?php echo esc_attr( $badge[1] ); ?>">
								<?php echo esc_html( $badge[0] ); ?>
							</span>
						</td>
						<td>
							<a href="<?php echo esc_url( $detail_url ); ?>" class="button button-small">
								<?php esc_html_e( 'Dettaglio', 'woocommerce-easy-withdrawal' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Paginazione -->
	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php
					printf(
						/* translators: %d = numero totale risultati */
						esc_html( _n( '%d elemento', '%d elementi', $total, 'woocommerce-easy-withdrawal' ) ),
						(int) $total
					);
					?>
				</span>
				<span class="pagination-links">
					<?php
					echo paginate_links( [ // phpcs:ignore WordPress.Security.EscapeOutput
						'base'      => add_query_arg( 'paged', '%#%', $base_url . ( $status ? '&status=' . $status : '' ) . ( $search ? '&s=' . rawurlencode( $search ) : '' ) ),
						'format'    => '',
						'current'   => $paged,
						'total'     => $total_pages,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
					] );
					?>
				</span>
			</div>
		</div>
	<?php endif; ?>

</div>
