<?php
/**
 * Template email plain text — Cliente: conferma richiesta di recesso.
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

/* translators: %s = nome cliente */
printf( esc_html__( 'Gentile %s,', 'easy-withdrawal-for-woocommerce' ), esc_html( $order->get_formatted_billing_full_name() ) );
echo "\n\n";

/* translators: %s = numero ordine */
printf( esc_html__( 'abbiamo ricevuto la tua richiesta di recesso per l\'ordine #%s.', 'easy-withdrawal-for-woocommerce' ), esc_html( $order->get_order_number() ) );
echo "\n\n";

if ( ! empty( $withdrawal_items ) ) {
	echo esc_html__( 'PRODOTTI INCLUSI NEL RECESSO:', 'easy-withdrawal-for-woocommerce' ) . "\n";
	foreach ( $withdrawal_items as $item ) {
		echo '- ' . esc_html( $item['name'] ) . ' x ' . esc_html( (string) $item['qty'] ) . "\n";
	}
	echo "\n";
}

if ( $withdrawal_reason ) {
	echo esc_html__( 'MOTIVO:', 'easy-withdrawal-for-woocommerce' ) . "\n";
	echo esc_html( $withdrawal_reason ) . "\n\n";
}

echo esc_html__( 'Ti contatteremo al più presto con le istruzioni per la restituzione.', 'easy-withdrawal-for-woocommerce' ) . "\n\n";

if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( $additional_content ) ) . "\n\n";
}

echo esc_html( wp_strip_all_tags( (string) apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) . "\n";
