<?php
/**
 * Controller del form di recesso.
 * Gestisce la pagina dedicata con il flusso UE completo:
 * Riepilogo ordine → Selezione prodotti → Motivo → Accetto condizioni → Invia → Conferma
 *
 * @package WooCommerceEasyWithdrawal\Frontend
 */

declare( strict_types=1 );

namespace WEW\Frontend;

use WEW\Helpers\OrderHelper;
use WEW\Helpers\SettingsHelper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WithdrawalForm
 */
final class WithdrawalForm {

	/** Slug della query var per il form. */
	private const QV_ACTION   = 'wew_action';
	private const QV_ORDER_ID = 'wew_order_id';
	private const QV_NONCE    = 'wew_nonce';

	/** Registra hook. */
	public function init(): void {
		// Intercetta il GET per mostrare il form.
		add_action( 'template_redirect', [ $this, 'handle_form_display' ] );

		// Processa il POST (invio form).
		add_action( 'template_redirect', [ $this, 'handle_form_submit' ] );

		// Assets.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	// ── Display ──────────────────────────────────────────────────────────────

	/** Intercetta GET ?wew_action=request e mostra il form. */
	public function handle_form_display(): void {
		if ( ! is_account_page() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verificato in get_verified_order().
		$action = sanitize_key( $_GET[ self::QV_ACTION ] ?? '' );
		if ( 'request' !== $action ) {
			return;
		}

		$order = $this->get_verified_order();
		if ( ! $order ) {
			wc_add_notice(
				__( 'Ordine non trovato o non valido per il recesso.', 'easy-withdrawal-for-woocommerce' ),
				'error'
			);
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		// Mostra il form invece del contenuto normale dell'account.
		add_action( 'woocommerce_account_content', [ $this, 'render_form' ], 5 );
		// Rimuove il contenuto standard (endpoint orders).
		remove_action( 'woocommerce_account_orders_endpoint', 'woocommerce_account_orders' );
	}

	/** Render del form HTML. */
	public function render_form(): void {
		$order = $this->get_verified_order();
		if ( ! $order ) {
			return;
		}

		$items              = $order->get_items();
		$partial_enabled    = SettingsHelper::is_partial_enabled();
		$conditions_page_id = (int) SettingsHelper::get( 'conditions_page_id', 0 );
		$conditions_url     = $conditions_page_id ? get_permalink( $conditions_page_id ) : '';
		$days_remaining     = OrderHelper::days_remaining( $order );

		wc_get_template(
			'withdrawal-form.php',
			compact( 'order', 'items', 'partial_enabled', 'conditions_url', 'days_remaining' ),
			'easy-withdrawal-for-woocommerce/',
			WEW_DIR . 'templates/'
		);
	}

	// ── Submit ───────────────────────────────────────────────────────────────

	/** Processa il POST del form. */
	public function handle_form_submit(): void {
		if ( ! is_account_page() ) {
			return;
		}

		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ?? '' ) {
			return;
		}

		if ( empty( $_POST['wew_submit_withdrawal'] ) ) {
			return;
		}

		// Verifica nonce.
		$nonce    = sanitize_text_field( wp_unslash( $_POST['wew_form_nonce'] ?? '' ) );
		$order_id = absint( $_POST['wew_order_id'] ?? 0 );

		if ( ! wp_verify_nonce( $nonce, 'wew_submit_withdrawal_' . $order_id ) ) {
			wc_add_notice( __( 'Richiesta non valida. Riprova.', 'easy-withdrawal-for-woocommerce' ), 'error' );
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		// Recupera e verifica ordine.
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order || ! $this->user_owns_order( $order ) ) {
			wc_add_notice( __( 'Ordine non trovato.', 'easy-withdrawal-for-woocommerce' ), 'error' );
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		if ( ! OrderHelper::is_eligible_for_withdrawal( $order ) ) {
			wc_add_notice( __( 'Questo ordine non è più idoneo al recesso.', 'easy-withdrawal-for-woocommerce' ), 'error' );
			wp_safe_redirect( wc_get_account_endpoint_url( 'orders' ) );
			exit;
		}

		// Accettazione condizioni obbligatoria.
		if ( empty( $_POST['wew_accept_conditions'] ) ) {
			wc_add_notice( __( 'Devi accettare le condizioni di recesso per procedere.', 'easy-withdrawal-for-woocommerce' ), 'error' );
			wp_safe_redirect( $this->form_url( $order ) );
			exit;
		}

		// Prodotti selezionati.
		$partial_enabled  = SettingsHelper::is_partial_enabled();
		$selected_items   = $this->parse_selected_items( $order, $partial_enabled );

		if ( empty( $selected_items ) ) {
			wc_add_notice( __( 'Seleziona almeno un prodotto per procedere con il recesso.', 'easy-withdrawal-for-woocommerce' ), 'error' );
			wp_safe_redirect( $this->form_url( $order ) );
			exit;
		}

		// Motivo (facoltativo).
		$reason = sanitize_textarea_field( wp_unslash( $_POST['wew_reason'] ?? '' ) );

		// Salva la richiesta.
		$this->save_withdrawal_request( $order, $selected_items, $reason );

		// Redirect alla conferma.
		wp_safe_redirect( add_query_arg( [
			'wew_action'   => 'confirmed',
			'wew_order_id' => $order->get_id(),
			'wew_nonce'    => wp_create_nonce( 'wew_confirmed_' . $order->get_id() ),
		], wc_get_account_endpoint_url( 'orders' ) ) );
		exit;
	}

	// ── Helpers ──────────────────────────────────────────────────────────────

	/**
	 * Recupera e verifica l'ordine dalla query string.
	 *
	 * @return \WC_Order|null
	 */
	private function get_verified_order(): ?\WC_Order {
		$order_id = absint( $_GET[ self::QV_ORDER_ID ] ?? 0 );
		$nonce    = sanitize_text_field( wp_unslash( $_GET[ self::QV_NONCE ] ?? '' ) );

		if ( ! $order_id || ! wp_verify_nonce( $nonce, 'wew_withdrawal_' . $order_id ) ) {
			return null;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			return null;
		}

		if ( ! $this->user_owns_order( $order ) ) {
			return null;
		}

		if ( ! OrderHelper::is_eligible_for_withdrawal( $order ) ) {
			return null;
		}

		return $order;
	}

	/**
	 * L'utente loggato è il proprietario dell'ordine?
	 *
	 * @param \WC_Order $order Ordine.
	 * @return bool
	 */
	private function user_owns_order( \WC_Order $order ): bool {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		return (int) $order->get_customer_id() === get_current_user_id();
	}

	/**
	 * Analizza i prodotti selezionati dal POST.
	 *
	 * @param \WC_Order $order           Ordine.
	 * @param bool      $partial_enabled Recesso parziale abilitato?
	 * @return array<int, array{name: string, qty: int}>
	 */
	private function parse_selected_items( \WC_Order $order, bool $partial_enabled ): array {
		$result = [];

		if ( ! $partial_enabled ) {
			// Tutti i prodotti.
			foreach ( $order->get_items() as $item_id => $item ) {
				/** @var \WC_Order_Item_Product $item */
				$result[ $item_id ] = [
					'name' => $item->get_name(),
					'qty'  => $item->get_quantity(),
				];
			}
			return $result;
		}

		// Parziale: prende solo gli item selezionati con la quantità indicata.
		// Il nonce è già verificato in handle_form_submit() prima di chiamare questo metodo.
		// phpcs:disable WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput
		$raw_items = isset( $_POST['wew_items'] ) && is_array( $_POST['wew_items'] )
			? wp_unslash( $_POST['wew_items'] )
			: [];
		// phpcs:enable WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput
		$posted    = array_map(
			static function ( $item ) {
				if ( is_array( $item ) ) {
					return [
						'selected' => ! empty( $item['selected'] ) ? 1 : 0,
						'qty'      => isset( $item['qty'] ) ? absint( $item['qty'] ) : 0,
					];
				}
				return absint( $item );
			},
			$raw_items
		);

		if ( empty( $posted ) ) {
			return $result;
		}

		foreach ( $order->get_items() as $item_id => $item ) {
			/** @var \WC_Order_Item_Product $item */
			$item_data = $posted[ $item_id ] ?? null;

			// Supporta sia il nuovo formato [selected => 1, qty => N]
			// sia il vecchio formato [item_id => 1] per retrocompatibilità.
			$is_selected = is_array( $item_data )
				? ! empty( $item_data['selected'] )
				: ! empty( $item_data );

			if ( ! $is_selected ) {
				continue;
			}

			$max_qty      = $item->get_quantity();
			$requested_qty = is_array( $item_data )
				? max( 1, min( absint( $item_data['qty'] ?? $max_qty ), $max_qty ) )
				: $max_qty;

			$result[ $item_id ] = [
				'name'        => $item->get_name(),
				'qty'         => $requested_qty,
				'qty_ordered' => $max_qty,
			];
		}

		return $result;
	}

	/**
	 * Salva la richiesta di recesso nell'ordine (meta HPOS-compatibile).
	 *
	 * @param \WC_Order                              $order  Ordine.
	 * @param array<int, array{name: string, qty: int}> $items  Prodotti.
	 * @param string                                 $reason Motivo.
	 */
	private function save_withdrawal_request( \WC_Order $order, array $items, string $reason ): void {
		$data = [
			'requested_at' => current_time( 'mysql' ),
			'items'        => $items,
			'reason'       => $reason,
			'status'       => 'pending',
		];

		// Salva come meta ordine (compatibile HPOS).
		$order->update_meta_data( '_wew_withdrawal_requested', '1' );
		$order->update_meta_data( '_wew_withdrawal_data', wp_json_encode( $data ) );
		$order->save();

		/**
		 * Hook per Step 3: invio email e aggiornamento stato ordine.
		 *
		 * @param \WC_Order                              $order Ordine.
		 * @param array<int, array{name: string, qty: int}> $items Prodotti selezionati.
		 * @param string                                 $reason Motivo.
		 */
		do_action( 'wew_withdrawal_submitted', $order, $items, $reason );
	}

	/**
	 * URL del form per un ordine.
	 *
	 * @param \WC_Order $order Ordine.
	 * @return string
	 */
	private function form_url( \WC_Order $order ): string {
		return add_query_arg( [
			'wew_action'   => 'request',
			'wew_order_id' => $order->get_id(),
			'wew_nonce'    => wp_create_nonce( 'wew_withdrawal_' . $order->get_id() ),
		], wc_get_account_endpoint_url( 'orders' ) );
	}

	/** Carica CSS/JS solo nelle pagine account. */
	public function enqueue_assets(): void {
		if ( ! is_account_page() ) {
			return;
		}
		wp_enqueue_style(
			'wew-frontend',
			WEW_URL . 'assets/css/frontend.css',
			[],
			WEW_VERSION
		);
	}
}
