# Changelog – WooCommerce Easy Withdrawal

Tutte le modifiche notevoli sono documentate in questo file.
Formato: [Keep a Changelog](https://keepachangelog.com/it/1.0.0/)

---

## [1.0.0] – 2026-07-01

### Aggiunto
- **Step 1 – Plugin base**: struttura OOP con namespace `WEW\`, autoload PSR-4, compatibilità HPOS, PHP 8.1–8.4, WordPress 6.8+, WooCommerce 10+
- **Step 1 – Impostazioni**: pagina WooCommerce → Recessi con giorni, email admin, testo pulsante, pagina condizioni, recesso parziale
- **Step 1 – Pulsante condizionato**: appare solo su ordini completati + pagati + entro periodo + senza richiesta esistente
- **Step 1 – Compatibilità Enfold**: override CSS mirati senza modificare il tema
- **Step 2 – Form recesso**: flusso UE completo (riepilogo → selezione prodotti → motivo → condizioni → invio → conferma)
- **Step 2 – Recesso parziale**: selezione checkbox per singoli prodotti, "Seleziona tutti"
- **Step 2 – Validazione**: nonce, sanitizzazione, escaping, capability check su ogni azione
- **Step 3 – Email HTML cliente**: conferma ricezione richiesta via sistema WooCommerce nativo
- **Step 3 – Email HTML admin**: notifica nuova richiesta con link diretto all'ordine
- **Step 3 – Stato ordine personalizzato**: "Recesso richiesto" nella lista ordini admin
- **Step 3 – Note ordine**: storico automatico con data, prodotti, motivo
- **Step 3 – Metabox**: pannello laterale nell'ordine WC con stato colorato e dettagli
- **Step 4 – Dashboard admin**: WooCommerce → Richieste Recesso con tabella, filtri per stato, ricerca, paginazione
- **Step 4 – Dettaglio richiesta**: pagina con dati cliente, prodotti, motivo, pulsanti Approva/Respingi
- **Step 4 – Export CSV**: con BOM UTF-8 per Excel, rispetta filtri attivi
- **Step 4 – Tracking stato cliente**: badge dinamico in "I miei ordini" (In attesa / Approvato / Respinto)
- **Step 5 – Email notifica cambio stato**: inviata al cliente quando la richiesta viene approvata o respinta
- **Step 5 – PDF recesso**: documento HTML stampabile con dati ordine, cliente, prodotti, nota legale UE
- **Step 5 – Wizard configurazione**: mostrato alla prima attivazione, configurazione guidata in un'unica schermata
- **File `.pot`**: pronto per traduzioni in qualsiasi lingua
- **Licenza GPL-2.0-or-later**

### Sicurezza
- Nonce su ogni form e azione POST/GET
- Sanitizzazione completa di tutti gli input (`sanitize_text_field`, `sanitize_email`, `absint`, `wp_kses`)
- Escaping su tutti gli output (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`)
- Capability check `manage_woocommerce` su tutte le azioni admin
- Verifica proprietà ordine su tutte le azioni frontend cliente

### Note tecniche
- Compatibile con HPOS (High Performance Order Storage) — dichiarazione esplicita
- Elaborazione ordini a batch (50 per volta) per evitare memory exhaustion su store grandi
- Nessuna dipendenza esterna (no Composer richiesto sull'hosting)
- Standard: PSR-12, WordPress Coding Standards
