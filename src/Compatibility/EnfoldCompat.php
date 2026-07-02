<?php
/**
 * Compatibilità con il tema Enfold.
 * Previene conflitti CSS/JS senza modificare il tema.
 *
 * @package WooCommerceEasyWithdrawal\Compatibility
 */

declare( strict_types=1 );

namespace WEW\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EnfoldCompat
 */
final class EnfoldCompat {

	/** Registra hook solo se Enfold è il tema attivo. */
	public function init(): void {
		if ( ! $this->is_enfold_active() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_enfold_overrides' ], 99 );
		add_filter( 'body_class',         [ $this, 'add_body_class' ] );
	}

	/** Verifica se Enfold è il tema attivo (parent o child). */
	private function is_enfold_active(): bool {
		$theme = wp_get_theme();
		return in_array( $theme->get( 'TextDomain' ), [ 'avia_framework', 'enfold' ], true )
			|| ( $theme->parent() && in_array( $theme->parent()->get( 'TextDomain' ), [ 'avia_framework', 'enfold' ], true ) );
	}

	/** Carica CSS specifico per Enfold se siamo in pagine account. */
	public function enqueue_enfold_overrides(): void {
		if ( ! is_account_page() ) {
			return;
		}

		wp_enqueue_style(
			'wew-enfold-compat',
			WEW_URL . 'assets/css/enfold-compat.css',
			[ 'wew-frontend' ],
			WEW_VERSION
		);
	}

	/**
	 * Aggiunge classe body per targeting CSS specifico.
	 *
	 * @param string[] $classes Classi body esistenti.
	 * @return string[]
	 */
	public function add_body_class( array $classes ): array {
		if ( is_account_page() ) {
			$classes[] = 'wew-enfold';
		}
		return $classes;
	}
}
