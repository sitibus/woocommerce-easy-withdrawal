<?php
/**
 * Email all'admin: notifica nuova richiesta di recesso.
 *
 * @package WooCommerceEasyWithdrawal\Email
 */

declare( strict_types=1 );

namespace WEW\Email;

use WEW\Helpers\SettingsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminWithdrawalEmail
 */
class AdminWithdrawalEmail extends \WC_Email {

	/** @var array<int, array{name: string, qty: int}> */
	public array $withdrawal_items = [];

	public string $withdrawal_reason = '';

	public float $refund_total = 0.0;

	public string $refund_formatted = '';

	public function __construct() {
		$this->id             = 'wew_admin_withdrawal';
		$this->customer_email = false;
		$this->title          = __( 'WEW – Nuova richiesta recesso (admin)', 'easy-withdrawal-for-woocommerce' );
		$this->description    = __( 'Inviata all\'amministratore quando un cliente invia una richiesta di recesso.', 'easy-withdrawal-for-woocommerce' );
		$this->template_html  = 'emails/admin-withdrawal.php';
		$this->template_plain = 'emails/plain/admin-withdrawal.php';
		$this->template_base  = WEW_DIR . 'templates/';
		$this->placeholders   = [
			'{order_number}'    => '',
			'{customer_name}'   => '',
		];

		parent::__construct();
	}

	public function get_default_subject(): string {
		return __( '[Recesso] Nuova richiesta – Ordine #{order_number} da {customer_name}', 'easy-withdrawal-for-woocommerce' );
	}

	public function get_default_heading(): string {
		return __( 'Nuova richiesta di recesso', 'easy-withdrawal-for-woocommerce' );
	}

	/**
	 * Destinatario: email admin configurata nelle impostazioni WEW, con fallback a WC.
	 *
	 * @return string
	 */
	public function get_default_recipient(): string {
		$wew_email = SettingsHelper::get( 'admin_email', '' );
		return $wew_email ?: get_option( 'woocommerce_email_from_address', get_option( 'admin_email' ) );
	}

	/**
	 * Invia l'email.
	 *
	 * @param \WC_Order                                 $order  Ordine.
	 * @param array<int, array{name: string, qty: int}> $items  Prodotti selezionati.
	 * @param string                                    $reason Motivo.
	 */
	public function trigger( \WC_Order $order, array $items, string $reason ): void {
		$this->setup_locale();

		$this->object            = $order;
		$this->withdrawal_items  = $items;
		$this->withdrawal_reason = $reason;
		$this->recipient         = $this->get_default_recipient();

		// Calcola il totale da rimborsare.
		// Usa i dati passati dall'hook oppure quelli già salvati nel meta ordine.
		$refund_total = 0.0;
		$items_to_use = ! empty( $items ) ? $items : [];

		// Se items è vuoto, prova a leggerlo dal meta salvato.
		if ( empty( $items_to_use ) ) {
			$saved = json_decode( $order->get_meta( '_wew_withdrawal_data', true ), true );
			$items_to_use = $saved['items'] ?? [];
		}

		foreach ( $order->get_items() as $item_id => $order_item ) {
			// Cerca per item_id (chiave array) oppure per nome prodotto (fallback).
			$item_data = $items_to_use[ $item_id ] ?? null;
			if ( null === $item_data ) {
				// Fallback: cerca per nome.
				foreach ( $items_to_use as $i ) {
					if ( isset( $i['name'] ) && $i['name'] === $order_item->get_name() ) {
						$item_data = $i;
						break;
					}
				}
			}
			if ( null === $item_data ) {
				continue;
			}

			$qty_requested = (int) ( $item_data['qty']         ?? $order_item->get_quantity() );
			$qty_ordered   = (int) ( $item_data['qty_ordered'] ?? $order_item->get_quantity() );
			$line_total    = (float) $order->get_line_total( $order_item, true, true );
			$unit_price    = $qty_ordered > 0 ? $line_total / $qty_ordered : $line_total;
			$refund_total += $unit_price * $qty_requested;
		}

		// Fallback: se non abbiamo calcolato nulla usa il totale ordine.
		if ( $refund_total <= 0 ) {
			$refund_total = (float) $order->get_total();
		}

		$this->refund_total     = $refund_total;
		$this->refund_formatted = wc_price( $refund_total, [ 'currency' => $order->get_currency() ] );

		$this->placeholders['{order_number}']  = $order->get_order_number();
		$this->placeholders['{customer_name}'] = $order->get_formatted_billing_full_name();

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		$this->restore_locale();
	}

	public function get_content_html(): string {
		return wc_get_template_html(
			$this->template_html,
			[
				'order'             => $this->object,
				'withdrawal_items'  => $this->withdrawal_items,
				'withdrawal_reason' => $this->withdrawal_reason,
				'refund_formatted'  => $this->refund_formatted,
				'email_heading'     => $this->get_heading(),
				'additional_content'=> $this->get_additional_content(),
				'sent_to_admin'     => true,
				'plain_text'        => false,
				'email'             => $this,
			],
			'easy-withdrawal-for-woocommerce/',
			$this->template_base
		);
	}

	public function get_content_plain(): string {
		return wc_get_template_html(
			$this->template_plain,
			[
				'order'             => $this->object,
				'withdrawal_items'  => $this->withdrawal_items,
				'withdrawal_reason' => $this->withdrawal_reason,
				'refund_formatted'  => $this->refund_formatted,
				'email_heading'     => $this->get_heading(),
				'additional_content'=> $this->get_additional_content(),
				'sent_to_admin'     => true,
				'plain_text'        => true,
				'email'             => $this,
			],
			'easy-withdrawal-for-woocommerce/',
			$this->template_base
		);
	}
}
