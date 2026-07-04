<?php
/**
 * Email al cliente: notifica cambio stato richiesta (Approvato / Respinto).
 *
 * @package WooCommerceEasyWithdrawal\Email
 */

declare( strict_types=1 );

namespace WEW\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CustomerStatusUpdateEmail
 */
class CustomerStatusUpdateEmail extends \WC_Email {

	public string $new_status = '';

	public function __construct() {
		$this->id             = 'wew_customer_status_update';
		$this->customer_email = true;
		$this->title          = __( 'WEW – Aggiornamento stato recesso (cliente)', 'easy-withdrawal-for-woocommerce' );
		$this->description    = __( 'Inviata al cliente quando la richiesta viene approvata o respinta.', 'easy-withdrawal-for-woocommerce' );
		$this->template_html  = 'emails/customer-status-update.php';
		$this->template_plain = 'emails/plain/customer-status-update.php';
		$this->template_base  = WEW_DIR . 'templates/';
		$this->placeholders   = [
			'{order_number}' => '',
			'{status_label}' => '',
		];

		parent::__construct();
	}

	public function get_default_subject(): string {
		return __( 'Aggiornamento sulla tua richiesta di recesso – Ordine #{order_number}', 'easy-withdrawal-for-woocommerce' );
	}

	public function get_default_heading(): string {
		return __( 'Aggiornamento richiesta di recesso', 'easy-withdrawal-for-woocommerce' );
	}

	/**
	 * Invia la notifica di cambio stato.
	 *
	 * @param \WC_Order $order     Ordine.
	 * @param string    $status    Nuovo stato: accepted|rejected.
	 */
	public function trigger( \WC_Order $order, string $status ): void {
		$this->setup_locale();

		$this->object    = $order;
		$this->new_status = $status;
		$this->recipient  = $order->get_billing_email();

		$labels = [
			'accepted' => __( 'Approvata', 'easy-withdrawal-for-woocommerce' ),
			'rejected' => __( 'Respinta', 'easy-withdrawal-for-woocommerce' ),
		];

		$this->placeholders['{order_number}'] = $order->get_order_number();
		$this->placeholders['{status_label}'] = $labels[ $status ] ?? $status;

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
				'order'              => $this->object,
				'new_status'         => $this->new_status,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			],
			'easy-withdrawal-for-woocommerce/',
			$this->template_base
		);
	}

	public function get_content_plain(): string {
		return wc_get_template_html(
			$this->template_plain,
			[
				'order'              => $this->object,
				'new_status'         => $this->new_status,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			],
			'easy-withdrawal-for-woocommerce/',
			$this->template_base
		);
	}
}
