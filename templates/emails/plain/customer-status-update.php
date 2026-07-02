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
	? __( 'APPROVATA', 'woocommerce-easy-withdrawal' )
	: __( 'RESPINTA', 'woocommerce-easy-withdrawal' );

echo "= " . esc_html( $email_heading ) . " =\n\n";

printf( esc_html__( 'Gentile %s,', 'woocommerce-easy-withdrawal' ), esc_html( $order->get_formatted_billing_full_name() ) );
echo "\n\n";

printf(
	esc_html__( 'La tua richiesta di recesso per l\'ordine #%s è stata: %s', 'woocommerce-easy-withdrawal' ),
	esc_html( $order->get_order_number() ),
	esc_html( $label )
);
echo "\n\n";

if ( 'accepted' === $new_status ) {
	echo esc_html__( 'Ti contatteremo a breve con le istruzioni per la restituzione e il rimborso.', 'woocommerce-easy-withdrawal' );
} else {
	echo esc_html__( 'Per ulteriori informazioni contatta il nostro servizio clienti.', 'woocommerce-easy-withdrawal' );
}
echo "\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( $additional_content ) ) . "\n\n";
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) . "\n";
