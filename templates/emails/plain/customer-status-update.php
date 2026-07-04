<?php
/**
 * Template plain text — Cliente: aggiornamento stato richiesta.
 *
 * @var \WC_Order $order
 * @var string    $new_status
 * @var string    $email_heading
 * @var string    $additional_content
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

$label = 'accepted' === $new_status
	? esc_html__( 'APPROVATA', 'easy-withdrawal-for-woocommerce' )
	: esc_html__( 'RESPINTA', 'easy-withdrawal-for-woocommerce' );

echo "= " . esc_html( $email_heading ) . " =\n\n";

/* translators: %s = nome cliente */
printf( esc_html__( 'Gentile %s,', 'easy-withdrawal-for-woocommerce' ), esc_html( $order->get_formatted_billing_full_name() ) );
echo "\n\n";

printf(
	/* translators: 1: numero ordine, 2: stato approvato o respinto */
	esc_html__( 'La tua richiesta di recesso per l\'ordine #%1$s è stata: %2$s', 'easy-withdrawal-for-woocommerce' ),
	esc_html( $order->get_order_number() ),
	esc_html( $label )
);
echo "\n\n";

if ( 'accepted' === $new_status ) {
	echo esc_html__( 'Ti contatteremo a breve con le istruzioni per la restituzione e il rimborso.', 'easy-withdrawal-for-woocommerce' );
} else {
	echo esc_html__( 'Per ulteriori informazioni contatta il nostro servizio clienti.', 'easy-withdrawal-for-woocommerce' );
}
echo "\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( $additional_content ) ) . "\n\n";
}

echo esc_html( wp_strip_all_tags( (string) apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) . "\n";
