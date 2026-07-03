=== WooCommerce Easy Withdrawal ===
Contributors: subitis
Tags: woocommerce, withdrawal, recesso, reso, eu, direttiva
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 10.0
WC tested up to: 10.9

EU Right of Withdrawal for WooCommerce – Gestione del diritto di recesso UE conforme alla Direttiva 2011/83/UE.

== Description ==

**WooCommerce Easy Withdrawal** permette ai tuoi clienti di esercitare il diritto di recesso direttamente dal loro account WooCommerce, in modo semplice e conforme alla normativa europea (Direttiva 2011/83/UE).

Il plugin gestisce l'intero flusso: dalla richiesta del cliente, alla notifica via email, alla gestione da parte dell'amministratore, fino all'aggiornamento dello stato visibile al cliente.

= Funzionalità lato cliente =

* Pulsante "Richiedi recesso" nella pagina I miei ordini (appare solo se l'ordine è idoneo)
* Form guidato con selezione prodotti (totale o parziale) e quantità da restituire
* Pagina di conferma con tracking dello stato (In attesa / Approvato / Respinto)
* Email di conferma ricezione richiesta
* Email di notifica cambio stato (Approvato / Respinto)
* Download PDF della richiesta di recesso

= Funzionalità lato amministratore =

* Dashboard WooCommerce con tabella richieste, filtri per stato, ricerca
* Pagina dettaglio con importo da rimborsare e pulsanti Approva / Respingi
* Export CSV delle richieste
* Metabox nell'ordine con dettagli della richiesta
* Stato ordine personalizzato "Recesso richiesto"
* Note ordine automatiche con storico

= Configurazione =

* Wizard di configurazione alla prima attivazione
* Creazione automatica della pagina "Diritto di Recesso" in bozza
* Giorni di recesso configurabili (default: 14)
* Supporto recesso parziale (solo alcuni prodotti o quantità parziale)
* Compatibile con il tema Enfold

= Requisiti tecnici =

* Compatibile con HPOS (High Performance Order Storage)
* PHP 8.1 - 8.4
* WordPress 6.8+
* WooCommerce 10+
* Nessuna dipendenza esterna (no Composer richiesto)

== Installation ==

1. Vai su Plugin > Aggiungi nuovo > Carica plugin
2. Carica il file ZIP
3. Attiva il plugin
4. Segui il wizard di configurazione iniziale
5. Personalizza la pagina "Diritto di Recesso" creata automaticamente e pubblicala

== Frequently Asked Questions ==

= Il plugin e' conforme alla normativa UE? =

Si. Il flusso e' progettato per rispettare i requisiti della Direttiva 2011/83/UE e del D.Lgs. 206/2005 (Codice del Consumo italiano). Si consiglia comunque una verifica legale prima del go-live.

= Funziona con HPOS (High Performance Order Storage)? =

Si, il plugin dichiara esplicitamente la compatibilita' HPOS e non usa meta_query dirette sulle tabelle legacy.

= Posso personalizzare le email? =

Si. Le email sono integrate nel sistema WooCommerce nativo e sono configurabili da WooCommerce > Impostazioni > Email.

= Il recesso parziale e' supportato? =

Si. Il cliente puo' selezionare singoli prodotti e specificare la quantita' da restituire (es. 2 copie su 3 ordinate).

= Come viene calcolato l'importo da rimborsare? =

L'importo viene calcolato proporzionalmente alle quantita' richieste, IVA inclusa. Appare nella dashboard admin e nell'email di notifica. Il rimborso effettivo va eseguito manualmente.

= Il plugin funziona con il tema Enfold? =

Si, include override CSS mirati per la compatibilita' con Enfold senza modificare il tema.

== Screenshots ==

1. Pulsante "Richiedi recesso" nella pagina I miei ordini
2. Form di richiesta recesso con selezione prodotti e quantita'
3. Pagina di conferma con tracking stato
4. Dashboard admin con lista richieste e filtri
5. Pagina dettaglio richiesta con importo da rimborsare
6. Metabox nell'ordine WooCommerce
7. Wizard di configurazione iniziale
8. Pagina impostazioni WooCommerce Recessi

== Changelog ==

= 1.0.0 =
* Prima versione stabile
* Struttura OOP con namespace WEW\, autoload PSR-4, compatibilita' HPOS
* Pulsante recesso condizionato (completato + pagato + entro periodo + nessuna richiesta esistente)
* Form recesso con flusso UE completo (totale/parziale, quantita' parziale)
* Email HTML al cliente e admin via sistema WooCommerce nativo
* Importo da rimborsare calcolato proporzionalmente nelle email e nella dashboard
* Stato ordine personalizzato "Recesso richiesto"
* Dashboard admin con filtri, ricerca, Approva/Respingi, export CSV
* Tracking stato lato cliente
* PDF scaricabile della richiesta
* Wizard di configurazione iniziale
* Creazione automatica pagina "Diritto di Recesso" in bozza
* File .pot per traduzioni
* Compatibile PHP 8.1-8.4, WordPress 6.8+, WooCommerce 10+

== Upgrade Notice ==

= 1.0.0 =
Prima versione stabile. Nessun aggiornamento precedente.
