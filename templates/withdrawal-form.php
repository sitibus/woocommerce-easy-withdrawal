<?php
/**
 * Template: Form di richiesta recesso.
 *
 * Variabili disponibili:
 * @var \WC_Order                        $order
 * @var \WC_Order_Item_Product[]         $items
 * @var bool                             $partial_enabled
 * @var string                           $conditions_url
 * @var int                              $days_remaining
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wew-form-wrap">

	<?php wc_print_notices(); ?>

	<nav class="wew-breadcrumb" aria-label="<?php esc_attr_e( 'Navigazione', 'easy-withdrawal-for-woocommerce' ); ?>">
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">
			← <?php esc_html_e( 'I miei ordini', 'easy-withdrawal-for-woocommerce' ); ?>
		</a>
	</nav>

	<h2 class="wew-title">
		<?php esc_html_e( 'Richiesta di recesso', 'easy-withdrawal-for-woocommerce' ); ?>
	</h2>

	<?php if ( $days_remaining > 0 ) : ?>
		<p class="wew-days-remaining">
			<?php
			printf(
				/* translators: %d = numero di giorni rimasti */
				esc_html( _n(
					'Hai ancora %d giorno per esercitare il diritto di recesso.',
					'Hai ancora %d giorni per esercitare il diritto di recesso.',
					$days_remaining,
					'easy-withdrawal-for-woocommerce'
				) ),
				esc_html( $days_remaining )
			);
			?>
		</p>
	<?php endif; ?>

	<!-- Riepilogo ordine -->
	<section class="wew-order-summary">
		<h3><?php esc_html_e( 'Riepilogo ordine', 'easy-withdrawal-for-woocommerce' ); ?></h3>
		<table class="wew-order-table">
			<tr>
				<th><?php esc_html_e( 'Ordine', 'easy-withdrawal-for-woocommerce' ); ?></th>
				<td>#<?php echo esc_html( $order->get_order_number() ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Data ordine', 'easy-withdrawal-for-woocommerce' ); ?></th>
				<td><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Totale', 'easy-withdrawal-for-woocommerce' ); ?></th>
				<td><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
			</tr>
		</table>
	</section>

	<!-- Form -->
	<form method="post" class="wew-withdrawal-form" id="wew-withdrawal-form">

		<?php wp_nonce_field( 'wew_submit_withdrawal_' . $order->get_id(), 'wew_form_nonce' ); ?>
		<input type="hidden" name="wew_order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
		<input type="hidden" name="wew_submit_withdrawal" value="1">

		<!-- Selezione prodotti -->
		<section class="wew-section">
			<h3>
				<?php if ( $partial_enabled ) : ?>
					<?php esc_html_e( 'Seleziona i prodotti da restituire', 'easy-withdrawal-for-woocommerce' ); ?>
					<span class="wew-hint"><?php esc_html_e( '(seleziona almeno uno)', 'easy-withdrawal-for-woocommerce' ); ?></span>
				<?php else : ?>
					<?php esc_html_e( 'Prodotti inclusi nel recesso', 'easy-withdrawal-for-woocommerce' ); ?>
				<?php endif; ?>
			</h3>

			<table class="wew-items-table">
				<thead>
					<tr>
						<?php if ( $partial_enabled ) : ?>
							<th class="wew-check-col"></th>
						<?php endif; ?>
						<th><?php esc_html_e( 'Prodotto', 'easy-withdrawal-for-woocommerce' ); ?></th>
						<th style="text-align:center;"><?php esc_html_e( 'Qtà ordinata', 'easy-withdrawal-for-woocommerce' ); ?></th>
						<?php if ( $partial_enabled ) : ?>
							<th style="text-align:center;"><?php esc_html_e( 'Qtà da restituire', 'easy-withdrawal-for-woocommerce' ); ?></th>
						<?php endif; ?>
						<th><?php esc_html_e( 'Prezzo', 'easy-withdrawal-for-woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item_id => $item ) :
						/** @var \WC_Order_Item_Product $item */
						$max_qty = $item->get_quantity();
					?>
						<tr class="wew-item-row">
							<?php if ( $partial_enabled ) : ?>
								<td class="wew-check-col">
									<input
										type="checkbox"
										name="wew_items[<?php echo esc_attr( $item_id ); ?>][selected]"
										id="wew_item_<?php echo esc_attr( $item_id ); ?>"
										value="1"
										class="wew-item-checkbox"
										data-item-id="<?php echo esc_attr( $item_id ); ?>"
									>
								</td>
							<?php endif; ?>
							<td>
								<label for="<?php echo $partial_enabled ? 'wew_item_' . esc_attr( $item_id ) : ''; ?>">
									<?php echo wp_kses_post( $item->get_name() ); ?>
								</label>
							</td>
							<td style="text-align:center;"><?php echo esc_html( $max_qty ); ?></td>
							<?php if ( $partial_enabled ) : ?>
								<td style="text-align:center;">
									<input
										type="number"
										name="wew_items[<?php echo esc_attr( $item_id ); ?>][qty]"
										id="wew_qty_<?php echo esc_attr( $item_id ); ?>"
										value="<?php echo esc_attr( $max_qty ); ?>"
										min="1"
										max="<?php echo esc_attr( $max_qty ); ?>"
										class="wew-qty-input"
										data-max="<?php echo esc_attr( $max_qty ); ?>"
										data-item-id="<?php echo esc_attr( $item_id ); ?>"
										disabled
									>
								</td>
							<?php endif; ?>
							<td><?php echo wp_kses_post( wc_price( $order->get_line_total( $item, true ) ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( $partial_enabled ) : ?>
				<button type="button" class="wew-select-all-btn" id="wew-select-all">
					<?php esc_html_e( 'Seleziona tutti', 'easy-withdrawal-for-woocommerce' ); ?>
				</button>
			<?php endif; ?>
		</section>

		<!-- Motivo (facoltativo) -->
		<section class="wew-section">
			<h3>
				<?php esc_html_e( 'Motivo del recesso', 'easy-withdrawal-for-woocommerce' ); ?>
				<span class="wew-hint"><?php esc_html_e( '(facoltativo)', 'easy-withdrawal-for-woocommerce' ); ?></span>
			</h3>
			<textarea
				name="wew_reason"
				id="wew_reason"
				class="wew-textarea"
				rows="4"
				maxlength="1000"
				placeholder="<?php esc_attr_e( 'Descrivi il motivo del recesso...', 'easy-withdrawal-for-woocommerce' ); ?>"
			></textarea>
		</section>

		<!-- Accettazione condizioni -->
		<section class="wew-section wew-accept-section">
			<div class="wew-accept-wrap">
				<input type="checkbox" name="wew_accept_conditions" id="wew_accept_conditions" value="1" required class="wew-accept-checkbox">
				<label for="wew_accept_conditions" class="wew-accept-label">
					<?php if ( $conditions_url ) : ?>
						<?php
						printf(
							wp_kses(
								/* translators: %s = URL pagina condizioni di recesso */
								__( 'Accetto le <a href="%s" target="_blank" rel="noopener">condizioni di recesso</a> e dichiaro di voler esercitare il mio diritto di recesso ai sensi della Direttiva 2011/83/UE.', 'easy-withdrawal-for-woocommerce' ),
								[ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ] ]
							),
							esc_url( $conditions_url )
						);
						?>
					<?php else : ?>
						<?php esc_html_e( 'Dichiaro di voler esercitare il mio diritto di recesso ai sensi della Direttiva 2011/83/UE.', 'easy-withdrawal-for-woocommerce' ); ?>
					<?php endif; ?>
				</label>
			</div>
		</section>

		<!-- Submit -->
		<div class="wew-submit-row">
			<button type="submit" class="wew-btn wew-btn--primary" id="wew-submit-btn">
				<?php esc_html_e( 'Invia richiesta di recesso', 'easy-withdrawal-for-woocommerce' ); ?>
			</button>
			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="wew-btn wew-btn--secondary">
				<?php esc_html_e( 'Annulla', 'easy-withdrawal-for-woocommerce' ); ?>
			</a>
		</div>

	</form>

</div>

<script>
(function() {
	// Abilita/disabilita il campo quantità al cambio checkbox.
	function toggleQty(checkbox) {
		var itemId = checkbox.getAttribute('data-item-id');
		var qtyInput = document.getElementById('wew_qty_' + itemId);
		if (!qtyInput) return;
		if (checkbox.checked) {
			qtyInput.disabled = false;
			qtyInput.focus();
		} else {
			qtyInput.disabled = true;
			// Ripristina il valore massimo quando si deseleziona.
			qtyInput.value = qtyInput.getAttribute('data-max');
		}
	}

	document.querySelectorAll('.wew-item-checkbox').forEach(function(cb) {
		cb.addEventListener('change', function() { toggleQty(this); });
	});

	// Select all button.
	var selectAllBtn = document.getElementById('wew-select-all');
	if (selectAllBtn) {
		var allChecked = false;
		selectAllBtn.addEventListener('click', function() {
			allChecked = !allChecked;
			document.querySelectorAll('.wew-item-checkbox').forEach(function(cb) {
				cb.checked = allChecked;
				toggleQty(cb);
			});
			selectAllBtn.textContent = allChecked
				? '<?php echo esc_js( __( 'Deseleziona tutti', 'easy-withdrawal-for-woocommerce' ) ); ?>'
				: '<?php echo esc_js( __( 'Seleziona tutti', 'easy-withdrawal-for-woocommerce' ) ); ?>';
		});
	}

	// Validazione client-side: almeno un prodotto selezionato + quantità valide.
	var form = document.getElementById('wew-withdrawal-form');
	if (form) {
		form.addEventListener('submit', function(e) {
			var checkboxes = form.querySelectorAll('.wew-item-checkbox');
			if (checkboxes.length === 0) return;

			var atLeastOne = Array.from(checkboxes).some(function(cb) { return cb.checked; });
			if (!atLeastOne) {
				e.preventDefault();
				alert('<?php echo esc_js( __( 'Seleziona almeno un prodotto per procedere.', 'easy-withdrawal-for-woocommerce' ) ); ?>');
				return;
			}

			// Verifica che le quantità siano valide.
			var invalid = false;
			checkboxes.forEach(function(cb) {
				if (!cb.checked) return;
				var itemId = cb.getAttribute('data-item-id');
				var qtyInput = document.getElementById('wew_qty_' + itemId);
				if (!qtyInput) return;
				var qty = parseInt(qtyInput.value, 10);
				var max = parseInt(qtyInput.getAttribute('data-max'), 10);
				if (isNaN(qty) || qty < 1 || qty > max) {
					invalid = true;
				}
			});
			if (invalid) {
				e.preventDefault();
				alert('<?php echo esc_js( __( 'Verifica le quantità inserite.', 'easy-withdrawal-for-woocommerce' ) ); ?>');
			}
		});
	}
})();
</script>
