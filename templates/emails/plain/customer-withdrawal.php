<?php
/**
 * Template email plain text — Cliente.
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
	esc_html__( 'Gentile %s,', 'woocommerce-easy-withdrawal' ),
	esc_html( $order->get_formatted_billing_full_name() )
);
echo "\n\n";

printf(
	esc_html__( 'abbiamo ricevuto la tua richiesta di recesso per l\'ordine #%s.', 'woocommerce-easy-withdrawal' ),
	esc_html( $order->get_order_number() )
);
echo "\n\n";

if ( ! empty( $withdrawal_items ) ) {
	echo esc_html__( 'PRODOTTI INCLUSI NEL RECESSO:', 'woocommerce-easy-withdrawal' ) . "\n";
	foreach ( $withdrawal_items as $item ) {
		echo '- ' . esc_html( $item['name'] ) . ' x ' . esc_html( $item['qty'] ) . "\n";
	}
	echo "\n";
}

if ( $withdrawal_reason ) {
	echo esc_html__( 'MOTIVO:', 'woocommerce-easy-withdrawal' ) . "\n";
	echo esc_html( $withdrawal_reason ) . "\n\n";
}

echo esc_html__( 'Ti contatteremo al più presto con le istruzioni per la restituzione.', 'woocommerce-easy-withdrawal' ) . "\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( $additional_content ) ) . "\n\n";
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) . "\n";
