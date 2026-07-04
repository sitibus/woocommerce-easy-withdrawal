<?php
/**
 * Template email HTML — Admin: nuova richiesta di recesso.
 *
 * @var \WC_Order                                 $order
 * @var array<int, array{name: string, qty: int}> $withdrawal_items
 * @var string                                    $withdrawal_reason
 * @var string                                    $email_heading
 * @var string                                    $additional_content
 * @var \WC_Email                                 $email
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php
printf(
	/* translators: %s = nome cliente */
	esc_html__( 'Il cliente %s ha inviato una richiesta di recesso.', 'easy-withdrawal-for-woocommerce' ),
	'<strong>' . esc_html( $order->get_formatted_billing_full_name() ) . '</strong>'
);
?></p>

<?php /* Dati ordine */ ?>
<table cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
	<tr>
		<th style="text-align:left;background:#f8f8f8;padding:8px 10px;border-bottom:1px solid #e8e8e8;width:160px;"><?php esc_html_e( 'Ordine', 'easy-withdrawal-for-woocommerce' ); ?></th>
		<td style="padding:8px 10px;border-bottom:1px solid #e8e8e8;">
			<a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>">#<?php echo esc_html( $order->get_order_number() ); ?></a>
		</td>
	</tr>
	<tr>
		<th style="text-align:left;background:#f8f8f8;padding:8px 10px;border-bottom:1px solid #e8e8e8;"><?php esc_html_e( 'Cliente', 'easy-withdrawal-for-woocommerce' ); ?></th>
		<td style="padding:8px 10px;border-bottom:1px solid #e8e8e8;"><?php echo esc_html( $order->get_formatted_billing_full_name() ); ?> &lt;<?php echo esc_html( $order->get_billing_email() ); ?>&gt;</td>
	</tr>
	<tr>
		<th style="text-align:left;background:#f8f8f8;padding:8px 10px;border-bottom:1px solid #e8e8e8;"><?php esc_html_e( 'Totale ordine', 'easy-withdrawal-for-woocommerce' ); ?></th>
		<td style="padding:8px 10px;border-bottom:1px solid #e8e8e8;"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></td>
	</tr>
	<tr>
		<th style="text-align:left;background:#f8f8f8;padding:8px 10px;border-bottom:1px solid #e8e8e8;"><?php esc_html_e( 'Data richiesta', 'easy-withdrawal-for-woocommerce' ); ?></th>
		<td style="padding:8px 10px;border-bottom:1px solid #e8e8e8;"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td>
	</tr>
	<?php if ( $refund_formatted ) : ?>
	<tr>
		<th style="text-align:left;background:#f8f8f8;padding:8px 10px;"><?php esc_html_e( 'Da rimborsare', 'easy-withdrawal-for-woocommerce' ); ?></th>
		<td style="padding:8px 10px;font-weight:700;color:#0f5132;font-size:16px;"><?php echo wp_kses_post( $refund_formatted ); ?></td>
	</tr>
	<?php endif; ?>
</table>

<?php /* Prodotti */ ?>
<?php if ( ! empty( $withdrawal_items ) ) : ?>
<h2><?php esc_html_e( 'Prodotti da restituire', 'easy-withdrawal-for-woocommerce' ); ?></h2>
<table cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
	<thead>
		<tr>
			<th style="text-align:left;border-bottom:1px solid #e8e8e8;padding:8px 10px;background:#f8f8f8;"><?php esc_html_e( 'Prodotto', 'easy-withdrawal-for-woocommerce' ); ?></th>
			<th style="text-align:center;border-bottom:1px solid #e8e8e8;padding:8px 10px;background:#f8f8f8;width:60px;"><?php esc_html_e( 'Qtà', 'easy-withdrawal-for-woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $withdrawal_items as $item ) : ?>
		<tr>
			<td style="padding:8px 10px;border-bottom:1px solid #f0f0f0;"><?php echo esc_html( $item['name'] ); ?></td>
			<td style="padding:8px 10px;border-bottom:1px solid #f0f0f0;text-align:center;"><?php echo esc_html( $item['qty'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>

<?php /* Motivo */ ?>
<?php if ( $withdrawal_reason ) : ?>
<p><strong><?php esc_html_e( 'Motivo dichiarato:', 'easy-withdrawal-for-woocommerce' ); ?></strong><br>
<em style="color:#555;"><?php echo esc_html( $withdrawal_reason ); ?></em></p>
<?php endif; ?>

<p>
	<a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>"
	   style="display:inline-block;padding:10px 20px;background:#1f3864;color:#fff;text-decoration:none;border-radius:3px;font-weight:600;">
		<?php esc_html_e( 'Gestisci ordine →', 'easy-withdrawal-for-woocommerce' ); ?>
	</a>
</p>

<?php
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
