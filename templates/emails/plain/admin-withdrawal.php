<?php
/**
 * Template email plain text — Admin.
 *
 * @var \WC_Order                                 $order
 * @var array<int, array{name: string, qty: int}> $withdrawal_items
 * @var string                                    $withdrawal_reason
 * @var string                                    $refund_formatted
 * @var string                                    $email_heading
 * @var string                                    $additional_content
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

echo "= " . esc_html( $email_heading ) . " =\n\n";

/* translators: 1: nome cliente, 2: email cliente */
printf( esc_html__( 'Cliente: %1$s <%2$s>', 'easy-withdrawal-for-woocommerce' ),
	esc_html( $order->get_formatted_billing_full_name() ),
	esc_html( $order->get_billing_email() )
);
echo "\n";

/* translators: %s = numero ordine */
printf( esc_html__( 'Ordine: #%s', 'easy-withdrawal-for-woocommerce' ), esc_html( $order->get_order_number() ) );
echo "\n";

/* translators: %s = data e ora della richiesta */
printf( esc_html__( 'Data richiesta: %s', 'easy-withdrawal-for-woocommerce' ), esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) );
echo "\n\n";

if ( ! empty( $withdrawal_items ) ) {
	echo esc_html__( 'PRODOTTI DA RESTITUIRE:', 'easy-withdrawal-for-woocommerce' ) . "\n";
	foreach ( $withdrawal_items as $item ) {
		echo '- ' . esc_html( $item['name'] ) . ' x ' . esc_html( (string) $item['qty'] ) . "\n";
	}
	echo "\n";
}

if ( $withdrawal_reason ) {
	echo esc_html__( 'MOTIVO:', 'easy-withdrawal-for-woocommerce' ) . "\n";
	echo esc_html( $withdrawal_reason ) . "\n\n";
}

if ( ! empty( $refund_formatted ) ) {
	/* translators: %s = importo da rimborsare */
	printf( esc_html__( 'DA RIMBORSARE: %s', 'easy-withdrawal-for-woocommerce' ), esc_html( wp_strip_all_tags( $refund_formatted ) ) );
	echo "\n\n";
}

/* translators: %s = URL pagina ordine admin */
printf( esc_html__( 'Gestisci ordine: %s', 'easy-withdrawal-for-woocommerce' ), esc_url( $order->get_edit_order_url() ) );
echo "\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( $additional_content ) ) . "\n\n";
}

echo esc_html( wp_strip_all_tags( (string) apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) . "\n";
