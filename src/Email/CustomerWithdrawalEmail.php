<?php
/**
 * Email al cliente: conferma ricezione richiesta di recesso.
 *
 * @package WooCommerceEasyWithdrawal\Email
 */

declare( strict_types=1 );

namespace WEW\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CustomerWithdrawalEmail
 */
class CustomerWithdrawalEmail extends \WC_Email {

	/** @var array<int, array{name: string, qty: int}> */
	public array $withdrawal_items = [];

	public string $withdrawal_reason = '';

	public function __construct() {
		$this->id             = 'wew_customer_withdrawal';
		$this->customer_email = true;
		$this->title          = __( 'WEW – Conferma recesso (cliente)', 'easy-withdrawal-for-woocommerce' );
		$this->description    = __( 'Inviata al cliente quando la richiesta di recesso viene ricevuta.', 'easy-withdrawal-for-woocommerce' );
		$this->template_html  = 'emails/customer-withdrawal.php';
		$this->template_plain = 'emails/plain/customer-withdrawal.php';
		$this->template_base  = WEW_DIR . 'templates/';
		$this->placeholders   = [
			'{order_number}' => '',
			'{order_date}'   => '',
		];

		// Inizializza le opzioni WC_Email.
		parent::__construct();
	}

	/**
	 * Oggetto email predefinito.
	 *
	 * @return string
	 */
	public function get_default_subject(): string {
		return __( 'Richiesta di recesso ricevuta – Ordine #{order_number}', 'easy-withdrawal-for-woocommerce' );
	}

	/**
	 * Titolo predefinito.
	 *
	 * @return string
	 */
	public function get_default_heading(): string {
		return __( 'Richiesta di recesso ricevuta', 'easy-withdrawal-for-woocommerce' );
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
		$this->recipient         = $order->get_billing_email();

		$this->placeholders['{order_number}'] = $order->get_order_number();
		$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );

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

	/** Contenuto HTML. */
	public function get_content_html(): string {
		return wc_get_template_html(
			$this->template_html,
			[
				'order'             => $this->object,
				'withdrawal_items'  => $this->withdrawal_items,
				'withdrawal_reason' => $this->withdrawal_reason,
				'email_heading'     => $this->get_heading(),
				'additional_content'=> $this->get_additional_content(),
				'sent_to_admin'     => false,
				'plain_text'        => false,
				'email'             => $this,
			],
			'easy-withdrawal-for-woocommerce/',
			$this->template_base
		);
	}

	/** Contenuto plain text. */
	public function get_content_plain(): string {
		return wc_get_template_html(
			$this->template_plain,
			[
				'order'             => $this->object,
				'withdrawal_items'  => $this->withdrawal_items,
				'withdrawal_reason' => $this->withdrawal_reason,
				'email_heading'     => $this->get_heading(),
				'additional_content'=> $this->get_additional_content(),
				'sent_to_admin'     => false,
				'plain_text'        => true,
				'email'             => $this,
			],
			'easy-withdrawal-for-woocommerce/',
			$this->template_base
		);
	}
}
