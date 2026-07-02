<?php
/**
 * Classe principale del plugin — Singleton.
 *
 * @package WooCommerceEasyWithdrawal\Core
 */

declare( strict_types=1 );

namespace WEW\Core;

use WEW\Admin\Settings;
use WEW\Admin\WithdrawalMetabox;
use WEW\Admin\Dashboard;
use WEW\Admin\SetupWizard;
use WEW\PDF\PdfGenerator;
use WEW\Email\EmailManager;
use WEW\Email\OrderStatus;
use WEW\Frontend\WithdrawalButton;
use WEW\Frontend\WithdrawalForm;
use WEW\Frontend\WithdrawalConfirmation;
use WEW\Compatibility\EnfoldCompat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Plugin
 */
final class Plugin {

	/** @var self|null Istanza singleton. */
	private static ?self $instance = null;

	/** Costruttore privato — usare ::instance(). */
	private function __construct() {}

	/** Ritorna l'istanza singleton. */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Inizializza tutti i moduli del plugin. */
	public function init(): void {
		$this->load_textdomain();

		// Admin.
		if ( is_admin() ) {
			( new Settings() )->init();
			( new WithdrawalMetabox() )->init();
			( new Dashboard() )->init();
			( new SetupWizard() )->init();
		}

		// PDF.
		( new PdfGenerator() )->init();

		// Email & stato ordine.
		( new OrderStatus() )->init();
		( new EmailManager() )->init();

		// Frontend.
		( new WithdrawalButton() )->init();
		( new WithdrawalForm() )->init();
		( new WithdrawalConfirmation() )->init();

		// Compatibilità tema.
		( new EnfoldCompat() )->init();
	}

	/** Carica le traduzioni. */
	private function load_textdomain(): void {
		load_plugin_textdomain(
			WEW_TEXT_DOMAIN,
			false,
			dirname( plugin_basename( WEW_FILE ) ) . '/languages'
		);
	}
}
