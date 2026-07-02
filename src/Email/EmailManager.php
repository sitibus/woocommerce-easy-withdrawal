<?php
/**
 * Gestore email — registra le classi WC_Email nel sistema WooCommerce
 * e ascolta wew_withdrawal_submitted per inviarle.
 *
 * @package WooCommerceEasyWithdrawal\Email
 */

declare( strict_types=1 );

namespace WEW\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EmailManager
 */
final class EmailManager {

	/** Registra hook. */
	public function init(): void {
		// Inietta le classi email nel sistema WooCommerce.
		add_filter( 'woocommerce_email_classes', [ $this, 'register_email_classes' ] );

		// Ascolta la richiesta di recesso inviata dallo Step 2.
		add_action( 'wew_withdrawal_submitted', [ $this, 'on_withdrawal_submitted' ], 10, 3 );

		// Ascolta il cambio stato (Approva / Respingi) dalla dashboard.
		add_action( 'wew_withdrawal_status_updated', [ $this, 'on_status_updated' ], 10, 2 );
	}

	/**
	 * Aggiunge le classi email WEW a WooCommerce.
	 *
	 * @param array<string, \WC_Email> $emails Email WC esistenti.
	 * @return array<string, \WC_Email>
	 */
	public function register_email_classes( array $emails ): array {
		$emails['WEW_Email_Customer_Withdrawal']    = new CustomerWithdrawalEmail();
		$emails['WEW_Email_Admin_Withdrawal']       = new AdminWithdrawalEmail();
		$emails['WEW_Email_Customer_Status_Update'] = new CustomerStatusUpdateEmail();
		return $emails;
	}

	/**
	 * Invia le email al momento dell'invio della richiesta.
	 *
	 * @param \WC_Order                                 $order  Ordine.
	 * @param array<int, array{name: string, qty: int}> $items  Prodotti selezionati.
	 * @param string                                    $reason Motivo dichiarato.
	 */
	public function on_withdrawal_submitted( \WC_Order $order, array $items, string $reason ): void {
		// Aggiorna lo stato ordine.
		OrderStatus::set_withdrawal_requested( $order );

		// Recupera le istanze email dal sistema WC.
		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();

		// Email al cliente.
		if ( isset( $emails['WEW_Email_Customer_Withdrawal'] ) ) {
			/** @var CustomerWithdrawalEmail $customer_email */
			$customer_email = $emails['WEW_Email_Customer_Withdrawal'];
			$customer_email->trigger( $order, $items, $reason );
		}

		// Email all'admin.
		if ( isset( $emails['WEW_Email_Admin_Withdrawal'] ) ) {
			/** @var AdminWithdrawalEmail $admin_email */
			$admin_email = $emails['WEW_Email_Admin_Withdrawal'];
			$admin_email->trigger( $order, $items, $reason );
		}
	}

	/**
	 * Invia email al cliente quando lo stato della richiesta cambia.
	 *
	 * @param \WC_Order $order  Ordine.
	 * @param string    $status Nuovo stato: accepted|rejected.
	 */
	public function on_status_updated( \WC_Order $order, string $status ): void {
		// Notifica solo per approvazione e rispinta.
		if ( ! in_array( $status, [ 'accepted', 'rejected' ], true ) ) {
			return;
		}

		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();

		if ( isset( $emails['WEW_Email_Customer_Status_Update'] ) ) {
			/** @var CustomerStatusUpdateEmail $email */
			$email = $emails['WEW_Email_Customer_Status_Update'];
			$email->trigger( $order, $status );
		}
	}
}
