<?php
/**
 * Helper per accedere alle impostazioni del plugin.
 *
 * @package WooCommerceEasyWithdrawal\Helpers
 */

declare( strict_types=1 );

namespace WEW\Helpers;

use WEW\Core\Installer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsHelper
 */
final class SettingsHelper {

	/** @var array<string, mixed>|null Cache locale. */
	private static ?array $cache = null;

	/**
	 * Ritorna tutte le impostazioni, con fallback ai default.
	 *
	 * @return array<string, mixed>
	 */
	public static function all(): array {
		if ( null === self::$cache ) {
			$saved        = get_option( 'wew_settings', [] );
			$defaults     = Installer::default_settings();
			self::$cache  = wp_parse_args( $saved, $defaults );
		}
		return self::$cache;
	}

	/**
	 * Ritorna un singolo valore.
	 *
	 * @param string $key     Chiave impostazione.
	 * @param mixed  $default Valore di fallback.
	 * @return mixed
	 */
	public static function get( string $key, mixed $default = null ): mixed {
		return self::all()[ $key ] ?? $default;
	}

	/**
	 * Numero di giorni per il recesso (default 14).
	 */
	public static function withdrawal_days(): int {
		return (int) self::get( 'withdrawal_days', 14 );
	}

	/**
	 * Testo del pulsante.
	 */
	public static function button_text(): string {
		return (string) self::get( 'button_text', __( 'Richiedi recesso', 'easy-withdrawal-for-woocommerce' ) );
	}

	/**
	 * Recesso parziale abilitato?
	 */
	public static function is_partial_enabled(): bool {
		return (bool) self::get( 'enable_partial_withdrawal', true );
	}

	/** Svuota la cache (utile nei test). */
	public static function flush(): void {
		self::$cache = null;
	}
}
