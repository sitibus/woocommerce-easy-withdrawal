<?php
/**
 * Template email plain text — Admin.
 *
 * @var \WC_Order                                 $order
 * @var array<int, array{name: string, qty: int}> $withdrawal_items
 * @var string                                    $withdrawal_reason
 * @var string                                    $email_heading
 * @var string                                    $additional_content
 *
 * @package WooCommerceEasyWithdrawal
 */

defined( 'ABSPATH' ) || exit;

echo "= " . esc_html( $email_heading ) . " =\n\n";

printf(
	esc_html__( 'Cliente: %s <%s>', 'woocommerce-easy-withdrawal' ),
	esc_html( $order->get_formatted_billing_full_name() ),
	esc_html( $order->get_billing_email() )
);
echo "\n";

printf(
	esc_html__( 'Ordine: #%s', 'woocommerce-easy-withdrawal' ),
	esc_html( $order->get_order_number() )
);
echo "\n";

printf(
	esc_html__( 'Data richiesta: %s', 'woocommerce-easy-withdrawal' ),
	esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) )
);
echo "\n\n";

if ( ! empty( $withdrawal_items ) ) {
	echo esc_html__( 'PRODOTTI DA RESTITUIRE:', 'woocommerce-easy-withdrawal' ) . "\n";
	foreach ( $withdrawal_items as $item ) {
		echo '- ' . esc_html( $item['name'] ) . ' x ' . esc_html( $item['qty'] ) . "\n";
	}
	echo "\n";
}

if ( $withdrawal_reason ) {
	echo esc_html__( 'MOTIVO:', 'woocommerce-easy-withdrawal' ) . "\n";
	echo esc_html( $withdrawal_reason ) . "\n\n";
}

if ( $refund_formatted ) {
	echo esc_html__( 'DA RIMBORSARE:', 'woocommerce-easy-withdrawal' ) . ' ' . wp_strip_all_tags( $refund_formatted ) . "\n\n";
}

echo esc_html__( 'Gestisci ordine:', 'woocommerce-easy-withdrawal' ) . ' ' . esc_url( $order->get_edit_order_url() ) . "\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( $additional_content ) ) . "\n\n";
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) . "\n";
