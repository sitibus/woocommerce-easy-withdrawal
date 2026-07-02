<?php
/**
 * Wizard di configurazione iniziale — mostrato una sola volta all'attivazione.
 *
 * @package WooCommerceEasyWithdrawal\Admin
 */

declare( strict_types=1 );

namespace WEW\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SetupWizard
 */
final class SetupWizard {

	/** Option key per sapere se il wizard è già stato completato. */
	private const COMPLETED_KEY = 'wew_wizard_completed';

	/** Registra hook. */
	public function init(): void {
		add_action( 'admin_init', [ $this, 'maybe_redirect_to_wizard' ] );
		add_action( 'admin_menu', [ $this, 'register_wizard_page' ] );
		add_action( 'admin_post_wew_wizard_save', [ $this, 'handle_save' ] );
		add_action( 'admin_post_wew_wizard_skip', [ $this, 'handle_skip' ] );
	}

	/** Redirect al wizard dopo la prima attivazione. */
	public function maybe_redirect_to_wizard(): void {
		if ( ! get_option( 'wew_redirect_to_wizard' ) ) {
			return;
		}

		if ( get_option( self::COMPLETED_KEY ) ) {
			delete_option( 'wew_redirect_to_wizard' );
			return;
		}

		// Esegui redirect solo se non siamo già in un processo bulk o AJAX.
		if ( wp_doing_ajax() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		delete_option( 'wew_redirect_to_wizard' );

		wp_safe_redirect( admin_url( 'admin.php?page=wew-wizard' ) );
		exit;
	}

	/** Registra la pagina nascosta del wizard (non appare nel menu). */
	public function register_wizard_page(): void {
		add_submenu_page(
			null, // Nessun parent — pagina nascosta.
			__( 'Configurazione Easy Withdrawal', 'woocommerce-easy-withdrawal' ),
			__( 'Wizard', 'woocommerce-easy-withdrawal' ),
			'manage_woocommerce',
			'wew-wizard',
			[ $this, 'render' ]
		);
	}

	/** Render del wizard. */
	public function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'woocommerce-easy-withdrawal' ) );
		}

		$defaults = \WEW\Core\Installer::default_settings();
		$settings = get_option( 'wew_settings', $defaults );
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width,initial-scale=1">
			<title><?php esc_html_e( 'Configurazione WooCommerce Easy Withdrawal', 'woocommerce-easy-withdrawal' ); ?></title>
			<?php wp_print_styles( 'dashicons' ); ?>
			<style>
				* { box-sizing: border-box; }
				body { background: #f0f0f1; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 40px 20px; }
				.wew-wizard-wrap { max-width: 640px; margin: 0 auto; }
				.wew-wizard-header { text-align: center; margin-bottom: 32px; }
				.wew-wizard-header h1 { font-size: 24px; color: #1f3864; margin: 16px 0 6px; }
				.wew-wizard-header p { color: #666; margin: 0; }
				.wew-wizard-logo { font-size: 40px; }
				.wew-wizard-card { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; padding: 28px 32px; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
				.wew-wizard-card h2 { font-size: 16px; color: #1f3864; margin: 0 0 20px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f1; }
				.wew-field { margin-bottom: 20px; }
				.wew-field label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #333; }
				.wew-field input[type="text"],
				.wew-field input[type="email"],
				.wew-field input[type="number"],
				.wew-field select { width: 100%; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
				.wew-field input:focus, .wew-field select:focus { border-color: #2e75b6; outline: none; box-shadow: 0 0 0 2px rgba(46,117,182,.15); }
				.wew-field .description { font-size: 12px; color: #888; margin-top: 4px; }
				.wew-checkbox-row { display: flex; align-items: center; gap: 10px; }
				.wew-checkbox-row label { margin-bottom: 0; font-weight: 400; }
				.wew-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f0f0f1; }
				.wew-btn-primary { background: #1f3864; color: #fff; border: none; padding: 10px 24px; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; }
				.wew-btn-primary:hover { background: #162a4a; }
				.wew-btn-skip { background: none; border: none; color: #888; font-size: 13px; cursor: pointer; padding: 10px; text-decoration: underline; }
				.wew-step-indicator { display: flex; justify-content: center; gap: 6px; margin-bottom: 24px; }
				.wew-step { width: 8px; height: 8px; border-radius: 50%; background: #ddd; }
				.wew-step.active { background: #1f3864; }
			</style>
		</head>
		<body>
		<div class="wew-wizard-wrap">

			<div class="wew-wizard-header">
				<div class="wew-wizard-logo">⚖️</div>
				<h1><?php esc_html_e( 'Benvenuto in Easy Withdrawal', 'woocommerce-easy-withdrawal' ); ?></h1>
				<p><?php esc_html_e( 'Configura il plugin in pochi secondi per iniziare a gestire i recessi UE.', 'woocommerce-easy-withdrawal' ); ?></p>
			</div>

			<div class="wew-step-indicator">
				<div class="wew-step active"></div>
				<div class="wew-step"></div>
				<div class="wew-step"></div>
			</div>

			<div class="wew-wizard-card">
				<h2><?php esc_html_e( 'Impostazioni principali', 'woocommerce-easy-withdrawal' ); ?></h2>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="wew_wizard_save">
					<?php wp_nonce_field( 'wew_wizard_save' ); ?>

					<div class="wew-field">
						<label for="wew_withdrawal_days"><?php esc_html_e( 'Giorni per il recesso', 'woocommerce-easy-withdrawal' ); ?></label>
						<input type="number" id="wew_withdrawal_days" name="withdrawal_days"
							   value="<?php echo esc_attr( $settings['withdrawal_days'] ); ?>" min="1" max="365">
						<p class="description"><?php esc_html_e( 'La Direttiva UE prevede un minimo di 14 giorni.', 'woocommerce-easy-withdrawal' ); ?></p>
					</div>

					<div class="wew-field">
						<label for="wew_admin_email"><?php esc_html_e( 'Email per le notifiche admin', 'woocommerce-easy-withdrawal' ); ?></label>
						<input type="email" id="wew_admin_email" name="admin_email"
							   value="<?php echo esc_attr( $settings['admin_email'] ); ?>">
						<p class="description"><?php esc_html_e( 'Riceverà una notifica ad ogni nuova richiesta di recesso.', 'woocommerce-easy-withdrawal' ); ?></p>
					</div>

					<div class="wew-field">
						<label for="wew_button_text"><?php esc_html_e( 'Testo del pulsante recesso', 'woocommerce-easy-withdrawal' ); ?></label>
						<input type="text" id="wew_button_text" name="button_text"
							   value="<?php echo esc_attr( $settings['button_text'] ); ?>">
					</div>

					<div class="wew-field">
						<label for="wew_conditions_page"><?php esc_html_e( 'Pagina condizioni di recesso', 'woocommerce-easy-withdrawal' ); ?></label>
						<?php
						wp_dropdown_pages( [
							'name'              => 'conditions_page_id',
							'id'                => 'wew_conditions_page',
							'show_option_none'  => __( '— Nessuna —', 'woocommerce-easy-withdrawal' ),
							'option_none_value' => '0',
							'selected'          => $settings['conditions_page_id'],
						] );
						?>
					</div>

					<div class="wew-field">
						<div class="wew-checkbox-row">
							<input type="checkbox" id="wew_partial" name="enable_partial_withdrawal" value="1"
								<?php checked( $settings['enable_partial_withdrawal'], true ); ?>>
							<label for="wew_partial"><?php esc_html_e( 'Abilita recesso parziale (solo alcuni prodotti)', 'woocommerce-easy-withdrawal' ); ?></label>
						</div>
					</div>

					<div class="wew-actions">
						<button type="submit" name="wew_wizard_skip" formaction="<?php echo esc_url( admin_url( 'admin-post.php?action=wew_wizard_skip' ) ); ?>" class="wew-btn-skip">
							<?php esc_html_e( 'Salta, configurerò dopo', 'woocommerce-easy-withdrawal' ); ?>
						</button>
						<button type="submit" class="wew-btn-primary">
							<?php esc_html_e( 'Salva e inizia →', 'woocommerce-easy-withdrawal' ); ?>
						</button>
					</div>
				</form>
			</div>
		</div>
		</body>
		</html>
		<?php
	}

	/** Salva le impostazioni del wizard. */
	public function handle_save(): void {
		if ( ! current_user_can( 'manage_woocommerce' )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) ), 'wew_wizard_save' )
		) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'woocommerce-easy-withdrawal' ) );
		}

		$settings = [
			'withdrawal_days'           => max( 1, absint( $_POST['withdrawal_days'] ?? 14 ) ),
			'admin_email'               => sanitize_email( $_POST['admin_email'] ?? '' ),
			'button_text'               => sanitize_text_field( wp_unslash( $_POST['button_text'] ?? '' ) ),
			'conditions_page_id'        => absint( $_POST['conditions_page_id'] ?? 0 ),
			'enable_partial_withdrawal' => ! empty( $_POST['enable_partial_withdrawal'] ),
		];

		update_option( 'wew_settings', $settings );
		update_option( self::COMPLETED_KEY, true );
		\WEW\Helpers\SettingsHelper::flush();

		wp_safe_redirect( admin_url( 'admin.php?page=wew-dashboard&wew_msg=wizard_done' ) );
		exit;
	}

	/** Salta il wizard senza salvare. */
	public function handle_skip(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'woocommerce-easy-withdrawal' ) );
		}

		update_option( self::COMPLETED_KEY, true );

		wp_safe_redirect( admin_url( 'admin.php?page=wew-dashboard' ) );
		exit;
	}
}
