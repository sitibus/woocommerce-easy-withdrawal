<?php
/**
 * Repository per interrogare le richieste di recesso su tutti gli ordini.
 * Usa elaborazione a batch per evitare memory exhaustion su store grandi.
 *
 * @package WooCommerceEasyWithdrawal\Helpers
 */

declare( strict_types=1 );

namespace WEW\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WithdrawalRepository
 */
final class WithdrawalRepository {

	/** Quanti ordini caricare per batch durante la scansione. */
	private const BATCH_SIZE = 50;

	/**
	 * Ritorna le richieste di recesso con filtri opzionali.
	 *
	 * @param array $args Filtri: status, search, paged, per_page.
	 * @return array{orders: \WC_Order[], total: int}
	 */
	public static function query( array $args = [] ): array {
		$args = wp_parse_args( $args, [
			'status'   => '',
			'search'   => '',
			'paged'    => 1,
			'per_page' => 20,
		] );

		$matching = self::scan_all( $args['status'], $args['search'] );
		$total    = count( $matching );

		$offset = ( max( 1, (int) $args['paged'] ) - 1 ) * (int) $args['per_page'];
		$paged  = array_slice( $matching, $offset, (int) $args['per_page'] );

		return [ 'orders' => $paged, 'total' => $total ];
	}

	/**
	 * Conta le richieste per stato.
	 *
	 * @return array{pending: int, accepted: int, rejected: int, total: int}
	 */
	public static function counts(): array {
		$counts = [ 'pending' => 0, 'accepted' => 0, 'rejected' => 0, 'total' => 0 ];

		foreach ( self::scan_all() as $order ) {
			$data   = self::get_data( $order );
			$status = $data['status'];
			if ( isset( $counts[ $status ] ) ) {
				++$counts[ $status ];
			}
			++$counts['total'];
		}

		return $counts;
	}

	/**
	 * Aggiorna lo stato di una richiesta di recesso.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $status   pending|accepted|rejected.
	 * @return bool
	 */
	public static function update_status( int $order_id, string $status ): bool {
		if ( ! in_array( $status, [ 'pending', 'accepted', 'rejected' ], true ) ) {
			return false;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof \WC_Order ) {
			return false;
		}

		$data           = json_decode( $order->get_meta( '_wew_withdrawal_data', true ), true ) ?: [];
		$data['status'] = $status;

		$order->update_meta_data( '_wew_withdrawal_data', wp_json_encode( $data ) );

		$labels = [
			'pending'  => __( 'In attesa', 'woocommerce-easy-withdrawal' ),
			'accepted' => __( 'Approvato', 'woocommerce-easy-withdrawal' ),
			'rejected' => __( 'Respinto', 'woocommerce-easy-withdrawal' ),
		];

		$order->add_order_note(
			sprintf(
				/* translators: %s = nuovo stato */
				__( '🔄 Stato recesso aggiornato a: %s', 'woocommerce-easy-withdrawal' ),
				$labels[ $status ]
			),
			false,
			false
		);

		$order->save();

		do_action( 'wew_withdrawal_status_updated', $order, $status );

		return true;
	}

	/**
	 * Ritorna i dati di recesso decodificati per un ordine.
	 *
	 * @param \WC_Order $order Ordine.
	 * @return array{items: array, reason: string, requested_at: string, status: string}
	 */
	public static function get_data( \WC_Order $order ): array {
		$data = json_decode( $order->get_meta( '_wew_withdrawal_data', true ), true );
		return wp_parse_args( is_array( $data ) ? $data : [], [
			'items'        => [],
			'reason'       => '',
			'requested_at' => '',
			'status'       => 'pending',
		] );
	}

	// ── Internals ─────────────────────────────────────────────────────────────

	/**
	 * Scansiona tutti gli ordini a batch e ritorna solo quelli con richiesta WEW valida.
	 * Evita di caricare migliaia di ordini in memoria in una volta sola.
	 *
	 * @param string $filter_status Filtra per stato ('' = tutti).
	 * @param string $search        Termine di ricerca.
	 * @return \WC_Order[]
	 */
	private static function scan_all( string $filter_status = '', string $search = '' ): array {
		$found  = [];
		$page   = 1;

		do {
			$batch_args = [
				'limit'   => self::BATCH_SIZE,
				'page'    => $page,
				'type'    => 'shop_order',
				'orderby' => 'date',
				'order'   => 'DESC',
				'return'  => 'objects',
			];

			if ( $search ) {
				$batch_args['s'] = $search;
			}

			$batch = wc_get_orders( $batch_args );

			if ( empty( $batch ) ) {
				break;
			}

			foreach ( $batch as $order ) {
				// Salta rimborsi e tipi non standard.
				if ( ! ( $order instanceof \WC_Order ) || $order instanceof \WC_Order_Refund ) {
					continue;
				}

				$raw = $order->get_meta( '_wew_withdrawal_data', true );
				if ( ! $raw ) {
					continue;
				}

				$data = json_decode( $raw, true );
				if ( ! is_array( $data ) || empty( $data['requested_at'] ) ) {
					continue;
				}

				// Filtro stato opzionale.
				if ( $filter_status ) {
					$status = $data['status'] ?? 'pending';
					if ( $status !== $filter_status ) {
						continue;
					}
				}

				$found[] = $order;
			}

			// Se il batch era più piccolo del previsto, siamo all'ultima pagina.
			if ( count( $batch ) < self::BATCH_SIZE ) {
				break;
			}

			++$page;

		} while ( true );

		return $found;
	}
}
