<?php
/**
 * Generatore PDF della richiesta di recesso.
 * Genera un HTML ottimizzato per la stampa e lo serve come PDF via browser,
 * senza dipendenze esterne (nessun Composer, nessuna libreria terza).
 *
 * @package WooCommerceEasyWithdrawal\PDF
 */

declare( strict_types=1 );

namespace WEW\PDF;

use WEW\Helpers\WithdrawalRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PdfGenerator
 */
final class PdfGenerator {

	/** Registra hook. */
	public function init(): void {
		// Download PDF dalla dashboard admin.
		add_action( 'admin_post_wew_download_pdf', [ $this, 'handle_download' ] );

		// Download PDF dall'area cliente (My Account).
		add_action( 'template_redirect', [ $this, 'handle_customer_download' ] );
	}

	// ── Admin download ────────────────────────────────────────────────────────

	/** Gestisce il download PDF dall'admin. */
	public function handle_download(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'woocommerce-easy-withdrawal' ) );
		}

		$order_id = absint( $_GET['order_id'] ?? 0 );
		$nonce    = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );

		if ( ! wp_verify_nonce( $nonce, 'wew_pdf_' . $order_id ) ) {
			wp_die( esc_html__( 'Richiesta non valida.', 'woocommerce-easy-withdrawal' ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			wp_die( esc_html__( 'Ordine non trovato.', 'woocommerce-easy-withdrawal' ) );
		}

		$this->output_pdf( $order );
	}

	/** Gestisce il download PDF dal frontend cliente. */
	public function handle_customer_download(): void {
		if ( ! is_account_page() ) {
			return;
		}

		if ( 'GET' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			return;
		}

		if ( 'pdf' !== sanitize_key( $_GET['wew_action'] ?? '' ) ) {
			return;
		}

		$order_id = absint( $_GET['wew_order_id'] ?? 0 );
		$nonce    = sanitize_text_field( wp_unslash( $_GET['wew_nonce'] ?? '' ) );

		if ( ! $order_id || ! wp_verify_nonce( $nonce, 'wew_pdf_customer_' . $order_id ) ) {
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		if ( ! is_user_logged_in() || (int) $order->get_customer_id() !== get_current_user_id() ) {
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		if ( ! $order->get_meta( '_wew_withdrawal_data', true ) ) {
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		$this->output_pdf( $order );
	}

	// ── PDF output ────────────────────────────────────────────────────────────

	/**
	 * Genera e invia il PDF al browser.
	 *
	 * @param \WC_Order $order Ordine.
	 */
	private function output_pdf( \WC_Order $order ): void {
		$data     = WithdrawalRepository::get_data( $order );
		$html     = $this->build_html( $order, $data );
		$filename = 'recesso-ordine-' . $order->get_order_number() . '.pdf';

		// Se disponibile wkhtmltopdf o mPDF, si potrebbe usare quello.
		// Qui usiamo l'approccio universale: HTML con CSS @media print
		// servito come text/html con JS che triggera window.print() e salvataggio.
		// Per massima compatibilità con tutti i server hosting condivisi.
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	}

	/**
	 * Costruisce l'HTML del PDF.
	 *
	 * @param \WC_Order                                           $order Ordine.
	 * @param array{items: array, reason: string, requested_at: string, status: string} $data  Dati recesso.
	 * @return string HTML completo.
	 */
	private function build_html( \WC_Order $order, array $data ): string {
		$shop_name    = get_bloginfo( 'name' );
		$shop_url     = get_bloginfo( 'url' );
		$logo_id      = get_theme_mod( 'custom_logo' );
		$logo_url     = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
		$order_number = $order->get_order_number();
		$order_date   = wc_format_datetime( $order->get_date_created() );
		$customer     = $order->get_formatted_billing_full_name();
		$email        = $order->get_billing_email();
		$address      = $order->get_formatted_billing_address();
		$total        = wp_strip_all_tags( $order->get_formatted_order_total() );

		// Calcola il totale parziale da rimborsare.
		$refund_total = 0.0;
		foreach ( $order->get_items() as $item_id => $item ) {
			if ( empty( $data['items'][ $item_id ] ) ) {
				continue;
			}
			$item_data     = $data['items'][ $item_id ];
			$qty_requested = (int) ( $item_data['qty']         ?? $item->get_quantity() );
			$qty_ordered   = (int) ( $item_data['qty_ordered'] ?? $item->get_quantity() );
			$line_total    = (float) $order->get_line_total( $item, true, true );
			$unit_price    = $qty_ordered > 0 ? $line_total / $qty_ordered : $line_total;
			$refund_total += $unit_price * $qty_requested;
		}
		if ( $refund_total <= 0 ) {
			$refund_total = (float) $order->get_total();
		}
		$refund_formatted = wp_strip_all_tags( wc_price( $refund_total, [ 'currency' => $order->get_currency() ] ) );
		$is_partial       = $refund_total < (float) $order->get_total();
		$requested_at = $data['requested_at']
			? date_i18n( 'd/m/Y H:i', strtotime( $data['requested_at'] ) )
			: '—';
		$reason       = $data['reason'] ?: '—';

		$status_labels = [
			'pending'  => 'In attesa',
			'accepted' => 'Approvata',
			'rejected' => 'Respinta',
		];
		$status_label = $status_labels[ $data['status'] ] ?? 'In attesa';

		// Righe prodotti.
		$items_html = '';
		foreach ( $data['items'] as $item ) {
			$items_html .= sprintf(
				'<tr><td>%s</td><td style="text-align:center;">%d</td></tr>',
				esc_html( $item['name'] ),
				(int) $item['qty']
			);
		}

		ob_start();
		?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Modulo di Recesso – Ordine #<?php echo esc_html( $order_number ); ?></title>
<style>
	* { box-sizing: border-box; margin: 0; padding: 0; }
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 13px;
		color: #222;
		background: #fff;
		padding: 40px;
		max-width: 800px;
		margin: 0 auto;
	}
	.header {
		display: flex;
		justify-content: space-between;
		align-items: flex-start;
		border-bottom: 2px solid #1f3864;
		padding-bottom: 16px;
		margin-bottom: 24px;
	}
	.logo img { max-height: 60px; max-width: 180px; }
	.shop-name { font-size: 18px; font-weight: 700; color: #1f3864; }
	.doc-title {
		font-size: 20px;
		font-weight: 700;
		color: #1f3864;
		text-align: center;
		margin-bottom: 6px;
		letter-spacing: 0.5px;
	}
	.doc-subtitle {
		text-align: center;
		color: #666;
		font-size: 12px;
		margin-bottom: 28px;
	}
	.section { margin-bottom: 22px; }
	.section-title {
		font-size: 12px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.8px;
		color: #1f3864;
		border-bottom: 1px solid #ddd;
		padding-bottom: 5px;
		margin-bottom: 10px;
	}
	table.info { width: 100%; border-collapse: collapse; }
	table.info th {
		text-align: left;
		width: 160px;
		font-weight: 600;
		color: #555;
		padding: 5px 0;
		vertical-align: top;
	}
	table.info td { padding: 5px 0; }
	table.items {
		width: 100%;
		border-collapse: collapse;
		margin-top: 6px;
	}
	table.items th {
		background: #1f3864;
		color: #fff;
		padding: 7px 10px;
		text-align: left;
		font-size: 12px;
	}
	table.items td {
		padding: 7px 10px;
		border-bottom: 1px solid #eee;
	}
	.status-badge {
		display: inline-block;
		padding: 4px 14px;
		border-radius: 12px;
		font-weight: 700;
		font-size: 12px;
	}
	.status-pending  { background: #fff3cd; color: #856404; }
	.status-accepted { background: #d1e7dd; color: #0f5132; }
	.status-rejected { background: #f8d7da; color: #842029; }
	.legal-note {
		font-size: 11px;
		color: #777;
		border-top: 1px solid #eee;
		padding-top: 16px;
		margin-top: 28px;
		line-height: 1.6;
	}
	.footer {
		text-align: center;
		font-size: 11px;
		color: #aaa;
		margin-top: 20px;
		border-top: 1px solid #eee;
		padding-top: 12px;
	}
	.print-btn {
		display: block;
		width: 180px;
		margin: 0 auto 30px;
		padding: 10px 20px;
		background: #1f3864;
		color: #fff;
		text-align: center;
		border: none;
		border-radius: 4px;
		font-size: 14px;
		font-weight: 600;
		cursor: pointer;
	}
	@media print {
		.print-btn { display: none; }
		body { padding: 20px; }
	}
</style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨 Stampa / Salva PDF</button>

<div class="header">
	<div class="logo">
		<?php if ( $logo_url ) : ?>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $shop_name ); ?>">
		<?php else : ?>
			<span class="shop-name"><?php echo esc_html( $shop_name ); ?></span>
		<?php endif; ?>
	</div>
	<div style="text-align:right;">
		<div style="font-size:11px;color:#888;"><?php echo esc_html( $shop_url ); ?></div>
	</div>
</div>

<div class="doc-title">MODULO DI RECESSO</div>
<div class="doc-subtitle">Ai sensi della Direttiva UE 2011/83/UE – Art. 49</div>

<!-- Dati ordine -->
<div class="section">
	<div class="section-title">Dati ordine</div>
	<table class="info">
		<tr><th>Numero ordine</th><td>#<?php echo esc_html( $order_number ); ?></td></tr>
		<tr><th>Data ordine</th><td><?php echo esc_html( $order_date ); ?></td></tr>
		<tr><th>Totale ordine</th><td><?php echo esc_html( $total ); ?></td></tr>
		<tr>
			<th>Da rimborsare</th>
			<td>
				<strong style="color:#0f5132;"><?php echo esc_html( $refund_formatted ); ?></strong>
				<?php if ( $is_partial ) : ?><br><small style="color:#888;">(recesso parziale)</small><?php endif; ?>
			</td>
		</tr>
	</table>
</div>

<!-- Dati cliente -->
<div class="section">
	<div class="section-title">Dati cliente</div>
	<table class="info">
		<tr><th>Nome</th><td><?php echo esc_html( $customer ); ?></td></tr>
		<tr><th>Email</th><td><?php echo esc_html( $email ); ?></td></tr>
		<tr>
			<th>Indirizzo</th>
			<td><?php echo esc_html( wp_strip_all_tags( $address ) ); ?></td>
		</tr>
	</table>
</div>

<!-- Prodotti -->
<div class="section">
	<div class="section-title">Prodotti oggetto del recesso</div>
	<table class="items">
		<thead>
			<tr>
				<th>Prodotto</th>
				<th style="text-align:center;width:80px;">Quantità</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $items_html; // phpcs:ignore ?>
		</tbody>
	</table>
</div>

<!-- Motivo -->
<?php if ( $reason !== '—' ) : ?>
<div class="section">
	<div class="section-title">Motivo del recesso</div>
	<p style="font-style:italic;color:#555;padding:8px;background:#f9f9f9;border-left:3px solid #ddd;"><?php echo esc_html( $reason ); ?></p>
</div>
<?php endif; ?>

<!-- Stato e date -->
<div class="section">
	<div class="section-title">Stato richiesta</div>
	<table class="info">
		<tr>
			<th>Data richiesta</th>
			<td><?php echo esc_html( $requested_at ); ?></td>
		</tr>
		<tr>
			<th>Stato</th>
			<td>
				<span class="status-badge status-<?php echo esc_attr( $data['status'] ); ?>">
					<?php echo esc_html( $status_label ); ?>
				</span>
			</td>
		</tr>
	</table>
</div>

<!-- Nota legale -->
<div class="legal-note">
	<strong>Riferimento normativo:</strong> Il diritto di recesso è esercitato ai sensi dell'Art. 52 del D.Lgs. 206/2005
	(Codice del Consumo) e della Direttiva UE 2011/83/UE. Il consumatore ha diritto di recedere dal contratto entro
	14 giorni senza dover fornire alcuna motivazione. Il termine decorre dal giorno in cui il consumatore o un terzo
	da lui designato acquisisce il possesso fisico dei beni.
</div>

<div class="footer">
	<?php echo esc_html( $shop_name ); ?> – <?php echo esc_html( $shop_url ); ?> –
	Documento generato il <?php echo esc_html( date_i18n( 'd/m/Y H:i' ) ); ?>
</div>

</body>
</html>
		<?php
		return (string) ob_get_clean();
	}
}
