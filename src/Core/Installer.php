<?php
/**
 * Gestione attivazione, disattivazione e disinstallazione.
 *
 * @package WooCommerceEasyWithdrawal\Core
 */

declare( strict_types=1 );

namespace WEW\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Installer
 */
final class Installer {

	/** Eseguito all'attivazione del plugin. */
	public static function activate(): void {
		// Imposta opzioni di default se non esistono.
		if ( false === get_option( 'wew_settings' ) ) {
			add_option( 'wew_settings', self::default_settings() );
		}

		// Prima attivazione: crea la pagina condizioni di recesso in bozza.
		if ( ! get_option( 'wew_conditions_page_created' ) ) {
			self::create_conditions_page();
		}

		// Prima attivazione: mostra il wizard di configurazione.
		if ( ! get_option( 'wew_wizard_completed' ) ) {
			update_option( 'wew_redirect_to_wizard', true );
		}

		flush_rewrite_rules();
	}

	/**
	 * Crea la pagina "Diritto di Recesso" come bozza.
	 * L'admin la troverà in WordPress → Pagine, la personalizzerà e pubblicherà.
	 */
	private static function create_conditions_page(): void {
		$shop_name = get_bloginfo( 'name' );

		$content = <<<HTML
<!-- wp:heading -->
<h2 class="wp-block-heading">1. Diritto di recesso</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ai sensi della Direttiva Europea 2011/83/UE e del D.Lgs. 206/2005 (Codice del Consumo), il Cliente che acquista in qualità di <strong>consumatore</strong> ha il diritto di recedere dal contratto di acquisto, senza dover fornire alcuna motivazione, entro <strong>14 giorni</strong> dalla data in cui ha acquisito il possesso fisico dei beni.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">2. Come esercitare il recesso</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Per esercitare il diritto di recesso, il Cliente può:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
<li>Utilizzare il pulsante <strong>"Richiedi recesso"</strong> presente nella sezione <em>Il mio account → I miei ordini</em></li>
<li>Inviare una comunicazione scritta via email a: <strong>[INSERIRE EMAIL]</strong></li>
<li>Inviare una lettera raccomandata A/R a: <strong>[INSERIRE INDIRIZZO]</strong></li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">3. Rimborso</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>In caso di recesso, rimborseremo tutti i pagamenti ricevuti, comprese le spese di consegna standard, entro <strong>14 giorni</strong> dal giorno in cui siamo informati della decisione di recesso. Il rimborso verrà effettuato con lo stesso mezzo di pagamento usato per la transazione iniziale.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">4. Restituzione dei prodotti</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Il Cliente deve rispedire i beni entro <strong>14 giorni</strong> dalla comunicazione di recesso al seguente indirizzo:</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>[INSERIRE RAGIONE SOCIALE]</strong><br>[INSERIRE INDIRIZZO COMPLETO]</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>I costi diretti di restituzione sono a carico del Cliente. I beni devono essere restituiti in condizioni integre, nella confezione originale.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">5. Eccezioni al diritto di recesso</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ai sensi dell'art. 59 del D.Lgs. 206/2005, il diritto di recesso <strong>non si applica</strong> a:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
<li>Beni confezionati su misura o chiaramente personalizzati</li>
<li>Contenuti digitali forniti su supporto non materiale (es. ebook, corsi online), qualora l'esecuzione sia iniziata con l'accordo espresso del consumatore</li>
<li>[AGGIUNGERE EVENTUALI ALTRE ECCEZIONI SPECIFICHE PER LA PROPRIA ATTIVITÀ]</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2 class="wp-block-heading">6. Modulo di recesso</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Il Cliente può utilizzare il modulo disponibile nell'area personale del sito oppure il seguente modello:</p>
<!-- /wp:paragraph -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
<p><em>A [RAGIONE SOCIALE], [INDIRIZZO] – [EMAIL]</em></p>
<p><em>Con la presente notifico il recesso dal contratto di vendita dei seguenti beni:</em><br>
— Beni ordinati il: ____________________<br>
— Numero ordine: ____________________<br>
— Nome del consumatore: ____________________<br>
— Indirizzo: ____________________<br>
— Data: ____________________<br>
— Firma (solo se cartaceo): ____________________</p>
</blockquote>
<!-- /wp:quote -->

<!-- wp:heading -->
<h2 class="wp-block-heading">7. Risoluzione alternativa delle controversie</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Per qualsiasi controversia il consumatore può rivolgersi alla Piattaforma ODR europea: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><em>⚠️ Questa è una bozza generata automaticamente da WooCommerce Easy Withdrawal. Personalizza i campi tra parentesi quadre [  ] con i dati della tua attività prima di pubblicare la pagina.</em></p>
<!-- /wp:paragraph -->
HTML;

		$page_id = wp_insert_post( [
			'post_title'   => __( 'Diritto di Recesso', 'woocommerce-easy-withdrawal' ),
			'post_content' => $content,
			'post_status'  => 'draft', // Bozza — l'admin la pubblica quando è pronta.
			'post_type'    => 'page',
			'post_author'  => get_current_user_id() ?: 1,
			'comment_status' => 'closed',
		] );

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			// Salva l'ID nelle impostazioni così il wizard la preseleziona.
			$settings = get_option( 'wew_settings', [] );
			if ( empty( $settings['conditions_page_id'] ) ) {
				$settings['conditions_page_id'] = $page_id;
				update_option( 'wew_settings', $settings );
			}
			update_option( 'wew_conditions_page_created', $page_id );
		}
	}

	/** Eseguito alla disattivazione del plugin. */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Valori predefiniti delle impostazioni.
	 *
	 * @return array<string, mixed>
	 */
	public static function default_settings(): array {
		return [
			'withdrawal_days'          => 14,
			'admin_email'              => get_option( 'admin_email', '' ),
			'button_text'              => __( 'Richiedi recesso', 'woocommerce-easy-withdrawal' ),
			'conditions_page_id'       => 0,
			'enable_partial_withdrawal' => true,
		];
	}
}
