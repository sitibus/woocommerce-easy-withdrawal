<?php
/**
 * Pagina impostazioni admin: WooCommerce → Recessi → Impostazioni.
 *
 * @package WooCommerceEasyWithdrawal\Admin
 */

declare( strict_types=1 );

namespace WEW\Admin;

use WEW\Helpers\SettingsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 */
final class Settings {

	/** Slug della pagina. */
	private const PAGE_SLUG = 'wew-settings';

	/** Slug del gruppo opzioni. */
	private const OPTION_GROUP = 'wew_settings_group';

	/** Nome dell'opzione nel DB. */
	private const OPTION_NAME = 'wew_settings';

	/** Registra hook WordPress. */
	public function init(): void {
		add_action( 'admin_menu',    [ $this, 'register_menu' ] );
		add_action( 'admin_init',    [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/** Aggiunge la voce di menu sotto WooCommerce. */
	public function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Recessi', 'easy-withdrawal-for-woocommerce' ),
			__( 'Recessi', 'easy-withdrawal-for-woocommerce' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	/** Registra il settings group e i campi. */
	public function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'sanitize_callback' => [ $this, 'sanitize' ],
				'default'           => \WEW\Core\Installer::default_settings(),
			]
		);

		// Sezione generale.
		add_settings_section(
			'wew_general',
			__( 'Impostazioni generali', 'easy-withdrawal-for-woocommerce' ),
			'__return_false',
			self::PAGE_SLUG
		);

		// Campi.
		$fields = [
			[
				'id'    => 'withdrawal_days',
				'label' => __( 'Giorni di recesso', 'easy-withdrawal-for-woocommerce' ),
				'cb'    => [ $this, 'field_withdrawal_days' ],
			],
			[
				'id'    => 'admin_email',
				'label' => __( 'Email amministratore', 'easy-withdrawal-for-woocommerce' ),
				'cb'    => [ $this, 'field_admin_email' ],
			],
			[
				'id'    => 'button_text',
				'label' => __( 'Testo del pulsante', 'easy-withdrawal-for-woocommerce' ),
				'cb'    => [ $this, 'field_button_text' ],
			],
			[
				'id'    => 'conditions_page_id',
				'label' => __( 'Pagina condizioni di recesso', 'easy-withdrawal-for-woocommerce' ),
				'cb'    => [ $this, 'field_conditions_page' ],
			],
			[
				'id'    => 'enable_partial_withdrawal',
				'label' => __( 'Abilita recesso parziale', 'easy-withdrawal-for-woocommerce' ),
				'cb'    => [ $this, 'field_partial_withdrawal' ],
			],
		];

		foreach ( $fields as $field ) {
			add_settings_field(
				'wew_' . $field['id'],
				$field['label'],
				$field['cb'],
				self::PAGE_SLUG,
				'wew_general'
			);
		}
	}

	/** Sanitizza i valori prima del salvataggio. */
	public function sanitize( mixed $input ): array {
		if ( ! is_array( $input ) ) {
			$input = [];
		}

		$defaults = \WEW\Core\Installer::default_settings();
		$output   = [];

		$output['withdrawal_days'] = isset( $input['withdrawal_days'] )
			? max( 1, absint( $input['withdrawal_days'] ) )
			: $defaults['withdrawal_days'];

		$output['admin_email'] = isset( $input['admin_email'] )
			? sanitize_email( $input['admin_email'] )
			: $defaults['admin_email'];

		$output['button_text'] = isset( $input['button_text'] )
			? sanitize_text_field( $input['button_text'] )
			: $defaults['button_text'];

		$output['conditions_page_id'] = isset( $input['conditions_page_id'] )
			? absint( $input['conditions_page_id'] )
			: 0;

		$output['enable_partial_withdrawal'] = ! empty( $input['enable_partial_withdrawal'] );

		// Invalida la cache dell'helper.
		SettingsHelper::flush();

		return $output;
	}

	// ── Render campi ─────────────────────────────────────────────────────────

	public function field_withdrawal_days(): void {
		$val = SettingsHelper::withdrawal_days();
		printf(
			'<input type="number" name="%s[withdrawal_days]" value="%d" min="1" max="365" class="small-text" />
			<p class="description">%s</p>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $val ),
			esc_html__( 'Numero di giorni entro cui il cliente può richiedere il recesso (default: 14).', 'easy-withdrawal-for-woocommerce' )
		);
	}

	public function field_admin_email(): void {
		$val = SettingsHelper::get( 'admin_email', get_option( 'admin_email' ) );
		printf(
			'<input type="email" name="%s[admin_email]" value="%s" class="regular-text" />
			<p class="description">%s</p>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $val ),
			esc_html__( 'Email che riceve la notifica di ogni richiesta di recesso.', 'easy-withdrawal-for-woocommerce' )
		);
	}

	public function field_button_text(): void {
		$val = SettingsHelper::button_text();
		printf(
			'<input type="text" name="%s[button_text]" value="%s" class="regular-text" />
			<p class="description">%s</p>',
			esc_attr( self::OPTION_NAME ),
			esc_attr( $val ),
			esc_html__( 'Testo mostrato nel pulsante nell\'area "I miei ordini".', 'easy-withdrawal-for-woocommerce' )
		);
	}

	public function field_conditions_page(): void {
		$page_id = (int) SettingsHelper::get( 'conditions_page_id', 0 );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_dropdown_pages is a trusted WP function
	wp_dropdown_pages( [
			'name'              => esc_attr( self::OPTION_NAME ) . '[conditions_page_id]',
			'show_option_none'  => esc_html__( '— Nessuna pagina —', 'easy-withdrawal-for-woocommerce' ),
			'option_none_value' => '0',
			'selected'          => absint( $page_id ),
			'echo'              => 1,
		] );
		echo '<p class="description">' . esc_html__( 'Pagina con le condizioni di recesso (verrà linkata nel form).', 'easy-withdrawal-for-woocommerce' ) . '</p>';
	}

	public function field_partial_withdrawal(): void {
		$enabled = SettingsHelper::is_partial_enabled();
		printf(
			'<label>
				<input type="checkbox" name="%s[enable_partial_withdrawal]" value="1" %s />
				%s
			</label>
			<p class="description">%s</p>',
			esc_attr( self::OPTION_NAME ),
			checked( $enabled, true, false ),
			esc_html__( 'Abilita', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Se abilitato, il cliente può selezionare solo alcuni prodotti dell\'ordine per il recesso.', 'easy-withdrawal-for-woocommerce' )
		);
	}

	/** Render pagina impostazioni. */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'easy-withdrawal-for-woocommerce' ) );
		}
		?>
		<div class="wrap wew-settings-page">
			<h1>
				<?php esc_html_e( 'WooCommerce Easy Withdrawal', 'easy-withdrawal-for-woocommerce' ); ?>
				<span class="wew-version">v<?php echo esc_html( WEW_VERSION ); ?></span>
			</h1>

			<?php settings_errors( self::OPTION_GROUP ); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button( __( 'Salva impostazioni', 'easy-withdrawal-for-woocommerce' ) );
				?>
			</form>
		</div>
		<?php
	}

	/** Carica CSS admin solo nella nostra pagina. */
	public function enqueue_assets( string $hook ): void {
		if ( false === strpos( $hook, self::PAGE_SLUG ) ) {
			return;
		}
		wp_enqueue_style(
			'wew-admin',
			WEW_URL . 'assets/css/admin.css',
			[],
			WEW_VERSION
		);
	}
}
