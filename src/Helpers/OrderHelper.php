<?php
/**
 * Helper per la verifica dell'ammissibilità al recesso.
 * Supporta sia HPOS (custom_order_tables) sia tabelle legacy.
 *
 * @package WooCommerceEasyWithdrawal\Helpers
 */

declare( strict_types=1 );

namespace WEW\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OrderHelper
 */
final class OrderHelper {

	/**
	 * L'ordine è ancora entro il periodo di recesso?
	 *
	 * @param \WC_Order $order  Ordine WooCommerce.
	 * @return bool
	 */
	public static function is_within_withdrawal_period( \WC_Order $order ): bool {
		$days      = SettingsHelper::withdrawal_days();
		$date_paid = $order->get_date_paid();

		if ( ! $date_paid instanceof \WC_DateTime ) {
			// Fallback: usa la data di creazione ordine.
			$date_paid = $order->get_date_created();
		}

		if ( ! $date_paid instanceof \WC_DateTime ) {
			return false;
		}

		$now      = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );
		$paid_utc = \DateTimeImmutable::createFromMutable( $date_paid->getOffsetTimestamp()
			? $date_paid
			: new \DateTime( '@' . $date_paid->getTimestamp() ) );

		$diff = $now->getTimestamp() - $date_paid->getTimestamp();

		return $diff <= ( $days * DAY_IN_SECONDS );
	}

	/**
	 * Verifica tutti i requisiti per mostrare il pulsante di recesso.
	 *
	 * Condizioni:
	 *  1. Ordine con stato "completed".
	 *  2. Ordine pagato (date_paid non nulla).
	 *  3. Entro il periodo di recesso configurato.
	 *  4. Nessuna richiesta di recesso già presente.
	 *
	 * @param \WC_Order $order Ordine da verificare.
	 * @return bool
	 */
	public static function is_eligible_for_withdrawal( \WC_Order $order ): bool {
		// 1. Stato ordine.
		if ( 'completed' !== $order->get_status() ) {
			return false;
		}

		// 2. Pagato.
		if ( ! $order->get_date_paid() ) {
			return false;
		}

		// 3. Entro i giorni configurati.
		if ( ! self::is_within_withdrawal_period( $order ) ) {
			return false;
		}

		// 4. Nessuna richiesta esistente.
		if ( self::has_existing_withdrawal( $order ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Verifica se esiste già una richiesta di recesso per l'ordine.
	 * Usa la meta dell'ordine, compatibile con HPOS.
	 *
	 * @param \WC_Order $order Ordine.
	 * @return bool
	 */
	public static function has_existing_withdrawal( \WC_Order $order ): bool {
		return ! empty( $order->get_meta( '_wew_withdrawal_requested', true ) );
	}

	/**
	 * Ritorna il numero di giorni rimanenti per il recesso.
	 *
	 * @param \WC_Order $order Ordine.
	 * @return int Giorni rimanenti (0 se scaduto).
	 */
	public static function days_remaining( \WC_Order $order ): int {
		$days      = SettingsHelper::withdrawal_days();
		$date_paid = $order->get_date_paid() ?? $order->get_date_created();

		if ( ! $date_paid ) {
			return 0;
		}

		$elapsed  = ( time() - $date_paid->getTimestamp() ) / DAY_IN_SECONDS;
		$remaining = $days - (int) floor( $elapsed );

		return max( 0, $remaining );
	}
}
