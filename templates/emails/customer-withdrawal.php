<?php
/**
 * Template email HTML — Cliente: conferma richiesta di recesso.
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

/*
 * Usa l'header WooCommerce standard (rispetta logo, colori del tema email).
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php
printf(
	/* translators: %s = nome cliente */
	esc_html__( 'Gentile %s,', 'easy-withdrawal-for-woocommerce' ),
	esc_html( $order->get_formatted_billing_full_name() )
);
?></p>

<p><?php
printf(
	/* translators: %s = numero ordine */
	esc_html__( 'abbiamo ricevuto la tua richiesta di recesso per l\'ordine #%s. La esamineremo al più presto e ti contatteremo con le istruzioni per la restituzione.', 'easy-withdrawal-for-woocommerce' ),
	esc_html( $order->get_order_number() )
);
?></p>

<?php /* Riepilogo prodotti */ ?>
<?php if ( ! empty( $withdrawal_items ) ) : ?>
<h2><?php esc_html_e( 'Prodotti inclusi nel recesso', 'easy-withdrawal-for-woocommerce' ); ?></h2>
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
<em><?php echo esc_html( $withdrawal_reason ); ?></em></p>
<?php endif; ?>

<?php /* Data */ ?>
<p style="color:#888;font-size:13px;">
	<?php
	printf(
		/* translators: %s = data/ora */
		esc_html__( 'Richiesta inviata il %s.', 'easy-withdrawal-for-woocommerce' ),
		esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) )
	);
	?>
</p>

<p><?php esc_html_e( 'Se hai domande, contatta il nostro servizio clienti rispondendo a questa email.', 'easy-withdrawal-for-woocommerce' ); ?></p>

<?php
/* Contenuto aggiuntivo (configurabile in WC > Impostazioni > Email). */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/* Footer WooCommerce standard. */
do_action( 'woocommerce_email_footer', $email );
