<?php
/**
 * Dashboard admin: WooCommerce → Recessi.
 * Tabella richieste, filtri, ricerca, dettaglio, approva/respingi, export CSV.
 *
 * @package WooCommerceEasyWithdrawal\Admin
 */

declare( strict_types=1 );

namespace WEW\Admin;

use WEW\Helpers\WithdrawalRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Dashboard
 */
final class Dashboard {

	/** Slug pagina menu principale (lista). */
	public const PAGE_SLUG = 'wew-dashboard';

	/** Registra hook. */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ], 20 );
		add_action( 'admin_post_wew_update_status', [ $this, 'handle_status_update' ] );
		add_action( 'admin_post_wew_export_csv',    [ $this, 'handle_csv_export' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_notices',         [ $this, 'show_notices' ] );
		add_action( 'admin_notices',         [ $this, 'maybe_show_draft_notice' ] );
	}

	/** Aggiunge la voce di menu principale "Recessi" sotto WooCommerce. */
	public function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Richieste di Recesso', 'easy-withdrawal-for-woocommerce' ),
			__( 'Richieste Recesso', 'easy-withdrawal-for-woocommerce' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}

	/** Render della pagina (lista o dettaglio in base a query string). */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'easy-withdrawal-for-woocommerce' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only navigation params, no data modification.
		$view     = sanitize_key( $_GET['view'] ?? 'list' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order_id = absint( $_GET['order_id'] ?? 0 );

		if ( 'detail' === $view && $order_id ) {
			$this->render_detail( $order_id );
			return;
		}

		$this->render_list();
	}

	// ── LISTA ────────────────────────────────────────────────────────────────

	/** Render della tabella principale con filtri. */
	private function render_list(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Filtri di sola lettura, nessuna modifica dati.
		$status   = sanitize_key( $_GET['status'] ?? '' );
		$search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
		$paged    = max( 1, absint( $_GET['paged'] ?? 1 ) );
		$per_page = 20;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$result = WithdrawalRepository::query( [
			'status'   => $status,
			'search'   => $search,
			'paged'    => $paged,
			'per_page' => $per_page,
		] );

		$counts      = WithdrawalRepository::counts();
		$orders      = $result['orders'];
		$total       = $result['total'];
		$total_pages = (int) ceil( $total / $per_page );

		$export_nonce = wp_create_nonce( 'wew_export_csv' );

		include WEW_DIR . 'templates/admin/dashboard-list.php';
	}

	// ── DETTAGLIO ────────────────────────────────────────────────────────────

	/**
	 * Render della pagina di dettaglio singola richiesta.
	 *
	 * @param int $order_id ID ordine.
	 */
	private function render_detail( int $order_id ): void {
		$order = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order || ! $order->get_meta( '_wew_withdrawal_requested', true ) ) {
			echo '<div class="wrap"><div class="notice notice-error"><p>'
				. esc_html__( 'Richiesta non trovata.', 'easy-withdrawal-for-woocommerce' )
				. '</p></div></div>';
			return;
		}

		$data          = WithdrawalRepository::get_data( $order );
		$action_nonce  = wp_create_nonce( 'wew_update_status_' . $order_id );

		include WEW_DIR . 'templates/admin/dashboard-detail.php';
	}

	// ── AZIONI ───────────────────────────────────────────────────────────────

	/** Gestisce l'aggiornamento di stato (Approva / Respingi) via admin-post.php. */
	public function handle_status_update(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'easy-withdrawal-for-woocommerce' ) );
		}

		$order_id = absint( $_POST['order_id'] ?? 0 );
		$status   = sanitize_key( $_POST['new_status'] ?? '' );
		$nonce    = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ?? '' ) );

		if ( ! wp_verify_nonce( $nonce, 'wew_update_status_' . $order_id ) ) {
			wp_die( esc_html__( 'Richiesta non valida.', 'easy-withdrawal-for-woocommerce' ) );
		}

		$success = WithdrawalRepository::update_status( $order_id, $status );

		$redirect_url = add_query_arg(
			[
				'page'     => self::PAGE_SLUG,
				'view'     => 'detail',
				'order_id' => $order_id,
				'wew_msg'  => $success ? 'updated' : 'error',
			],
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/** Esporta le richieste filtrate in CSV. */
	public function handle_csv_export(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Accesso non autorizzato.', 'easy-withdrawal-for-woocommerce' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'wew_export_csv' ) ) {
			wp_die( esc_html__( 'Richiesta non valida.', 'easy-withdrawal-for-woocommerce' ) );
		}

		$status = sanitize_key( $_GET['status'] ?? '' );
		$search = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );

		$result = WithdrawalRepository::query( [
			'status'   => $status,
			'search'   => $search,
			'per_page' => 99999,
			'paged'    => 1,
		] );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=recessi-' . gmdate( 'Y-m-d' ) . '.csv' );

		// BOM per Excel (UTF-8) - phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$row_separator = "\n";
		$col_separator = ',';

		$headers = [
			esc_html__( 'Ordine', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Cliente', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Email', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Data richiesta', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Stato', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Prodotti', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Motivo', 'easy-withdrawal-for-woocommerce' ),
			esc_html__( 'Totale ordine', 'easy-withdrawal-for-woocommerce' ),
		];
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV raw output
		echo implode( $col_separator, array_map( static fn( $h ) => '"' . str_replace( '"', '""', $h ) . '"', $headers ) ) . $row_separator;

		$status_labels = [
			'pending'  => __( 'In attesa', 'easy-withdrawal-for-woocommerce' ),
			'accepted' => __( 'Approvato', 'easy-withdrawal-for-woocommerce' ),
			'rejected' => __( 'Respinto', 'easy-withdrawal-for-woocommerce' ),
		];

		foreach ( $result['orders'] as $order ) {
			/** @var \WC_Order $order */
			$data       = WithdrawalRepository::get_data( $order );
			$item_names = implode( '; ', array_map(
				static fn( $i ) => $i['name'] . ' x' . $i['qty'],
				$data['items']
			) );

			$row = [
				$order->get_order_number(),
				$order->get_formatted_billing_full_name(),
				$order->get_billing_email(),
				$data['requested_at'],
				$status_labels[ $data['status'] ] ?? $data['status'],
				$item_names,
				$data['reason'],
				$order->get_total(),
			];
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV raw output
			echo implode( $col_separator, array_map( static fn( $v ) => '"' . str_replace( '"', '""', (string) $v ) . '"', $row ) ) . $row_separator;
		}

		exit;
	}

	/** Mostra notice se la pagina condizioni è ancora in bozza. */
	public function maybe_show_draft_notice(): void {
		$page_id = (int) get_option( 'wew_conditions_page_created', 0 );
		if ( ! $page_id ) {
			return;
		}
		$page = get_post( $page_id );
		if ( ! $page || 'draft' !== $page->post_status ) {
			return;
		}
		$edit_url    = get_edit_post_link( $page_id );
		$preview_url = get_preview_post_link( $page_id );
		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a> | <a href="%s" target="_blank">%s</a></p></div>',
			esc_html__( '⚠️ La pagina "Diritto di Recesso" è ancora in bozza. Personalizzala con i dati della tua attività e pubblicala.', 'easy-withdrawal-for-woocommerce' ),
			esc_url( $edit_url ),
			esc_html__( 'Modifica pagina', 'easy-withdrawal-for-woocommerce' ),
			esc_url( $preview_url ),
			esc_html__( 'Anteprima', 'easy-withdrawal-for-woocommerce' )
		);
	}

	/** Mostra notice dopo update stato. */
	public function show_notices(): void {
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( $screen->id, self::PAGE_SLUG ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only notice param after redirect.
		$msg = sanitize_key( $_GET['wew_msg'] ?? '' );
		if ( 'updated' === $msg ) {
			echo '<div class="notice notice-success is-dismissible"><p>'
				. esc_html__( 'Stato aggiornato con successo.', 'easy-withdrawal-for-woocommerce' )
				. '</p></div>';
		} elseif ( 'error' === $msg ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
				. esc_html__( 'Si è verificato un errore durante l\'aggiornamento.', 'easy-withdrawal-for-woocommerce' )
				. '</p></div>';
		}
	}

	/** Carica CSS/JS solo nella nostra pagina. */
	public function enqueue_assets( string $hook ): void {
		if ( false === strpos( $hook, self::PAGE_SLUG ) ) {
			return;
		}
		wp_enqueue_style( 'wew-admin', WEW_URL . 'assets/css/admin.css', [], WEW_VERSION );
		wp_enqueue_style( 'wew-dashboard', WEW_URL . 'assets/css/dashboard.css', [], WEW_VERSION );
	}
}
