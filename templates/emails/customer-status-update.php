<?php
/**
 * Template email HTML — Cliente: aggiornamento stato richiesta.
 *
 * @var \WC_Order $order
 * @var string    $new_status   accepted|rejected
 * @var string    $email_heading
 * @var string    $additional_content
 * @var \WC_Email $email
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

$is_accepted = 'accepted' === $new_status;
$color       = $is_accepted ? '#375623' : '#842029';
$bg          = $is_accepted ? '#e2efda' : '#f8d7da';
$icon        = $is_accepted ? '✔' : '✕';
$label       = $is_accepted
	? __( 'Approvata', 'easy-withdrawal-for-woocommerce' )
	: __( 'Respinta', 'easy-withdrawal-for-woocommerce' );

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php
printf(
	/* translators: %s = nome cliente */
	esc_html__( 'Gentile %s,', 'easy-withdrawal-for-woocommerce' ),
	esc_html( $order->get_formatted_billing_full_name() )
); ?></p>

<p><?php
printf(
	/* translators: %s = numero ordine */
	esc_html__( 'ti informiamo che la tua richiesta di recesso per l\'ordine #%s è stata aggiornata.', 'easy-withdrawal-for-woocommerce' ),
	esc_html( $order->get_order_number() )
); ?></p>

<div style="text-align:center;margin:28px 0;">
	<span style="
		display:inline-block;
		padding:12px 28px;
		background:<?php echo esc_attr( $bg ); ?>;
		color:<?php echo esc_attr( $color ); ?>;
		border-radius:4px;
		font-size:18px;
		font-weight:700;
		border:1px solid <?php echo esc_attr( $color ); ?>44;
	">
		<?php echo esc_html( $icon . ' ' . $label ); ?>
	</span>
</div>

<?php if ( $is_accepted ) : ?>
<p><?php esc_html_e( 'La tua richiesta è stata approvata. Ti contatteremo a breve con le istruzioni per la restituzione del prodotto e il rimborso.', 'easy-withdrawal-for-woocommerce' ); ?></p>
<?php else : ?>
<p><?php esc_html_e( 'La tua richiesta è stata respinta. Per ulteriori informazioni, rispondi a questa email o contatta il nostro servizio clienti.', 'easy-withdrawal-for-woocommerce' ); ?></p>
<?php endif; ?>

<table cellspacing="0" cellpadding="6" style="width:100%;border-collapse:collapse;margin-top:20px;">
	<tr>
		<th style="text-align:left;background:#f8f8f8;padding:8px 10px;border-bottom:1px solid #e8e8e8;width:150px;"><?php esc_html_e( 'Ordine', 'easy-withdrawal-for-woocommerce' ); ?></th>
		<td style="padding:8px 10px;border-bottom:1px solid #e8e8e8;">#<?php echo esc_html( $order->get_order_number() ); ?></td>
	</tr>
	<tr>
		<th style="text-align:left;background:#f8f8f8;padding:8px 10px;"><?php esc_html_e( 'Stato richiesta', 'easy-withdrawal-for-woocommerce' ); ?></th>
		<td style="padding:8px 10px;font-weight:600;color:<?php echo esc_attr( $color ); ?>;"><?php echo esc_html( $label ); ?></td>
	</tr>
</table>

<?php
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}
do_action( 'woocommerce_email_footer', $email );
