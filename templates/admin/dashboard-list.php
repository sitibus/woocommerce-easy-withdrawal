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
	''         => __( 'Tutte', 'easy-withdrawal-for-woocommerce' ),
	'pending'  => __( 'In attesa', 'easy-withdrawal-for-woocommerce' ),
	'accepted' => __( 'Approvate', 'easy-withdrawal-for-woocommerce' ),
	'rejected' => __( 'Respinte', 'easy-withdrawal-for-woocommerce' ),
];

$status_badges = [
	'pending'  => [ __( 'In attesa', 'easy-withdrawal-for-woocommerce' ), 'pending' ],
	'accepted' => [ __( 'Approvato', 'easy-withdrawal-for-woocommerce' ), 'accepted' ],
	'rejected' => [ __( 'Respinto', 'easy-withdrawal-for-woocommerce' ), 'rejected' ],
];
?>
<div class="wrap wew-dashboard">

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Richieste di Recesso', 'easy-withdrawal-for-woocommerce' ); ?></h1>

	<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wew_export_csv&status=' . $status . '&s=' . rawurlencode( $search ) ), 'wew_export_csv' ) ); ?>"
	   class="page-title-action">
		⬇ <?php esc_html_e( 'Esporta CSV', 'easy-withdrawal-for-woocommerce' ); ?>
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
			<label class="screen-reader-text" for="wew-search-input"><?php esc_html_e( 'Cerca richieste', 'easy-withdrawal-for-woocommerce' ); ?></label>
			<input type="search" id="wew-search-input" name="s" value="<?php echo esc_attr( $search ); ?>"
				   placeholder="<?php esc_attr_e( 'Cerca cliente, email, ordine...', 'easy-withdrawal-for-woocommerce' ); ?>">
			<input type="submit" class="button" value="<?php esc_attr_e( 'Cerca', 'easy-withdrawal-for-woocommerce' ); ?>">
		</p>
	</form>

	<!-- Tabella -->
	<table class="wp-list-table widefat fixed striped wew-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Ordine', 'easy-withdrawal-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Cliente', 'easy-withdrawal-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Data richiesta', 'easy-withdrawal-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Totale', 'easy-withdrawal-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Stato', 'easy-withdrawal-for-woocommerce' ); ?></th>
				<th><?php esc_html_e( 'Azioni', 'easy-withdrawal-for-woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $orders ) ) : ?>
				<tr>
					<td colspan="7" class="wew-empty-row">
						<?php esc_html_e( 'Nessuna richiesta di recesso trovata.', 'easy-withdrawal-for-woocommerce' ); ?>
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
								<?php esc_html_e( 'Dettaglio', 'easy-withdrawal-for-woocommerce' ); ?>
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
						esc_html( _n( '%d elemento', '%d elementi', $total, 'easy-withdrawal-for-woocommerce' ) ),
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
