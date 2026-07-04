# WooCommerce Easy Withdrawal

**EU Right of Withdrawal for WooCommerce** — Plugin open source per la gestione del diritto di recesso UE (Direttiva 2011/83/UE).

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-GPL--2.0-green)
![WordPress](https://img.shields.io/badge/WordPress-6.8%2B-blue)
![WooCommerce](https://img.shields.io/badge/WooCommerce-10%2B-purple)
![PHP](https://img.shields.io/badge/PHP-8.1--8.4-orange)

---

## Descrizione

WooCommerce Easy Withdrawal permette ai tuoi clienti di esercitare il diritto di recesso direttamente dal loro account WooCommerce, in modo semplice e conforme alla normativa europea.

Il plugin gestisce l'intero flusso: dalla richiesta del cliente, alla notifica via email, alla gestione da parte dell'amministratore, fino all'aggiornamento dello stato visibile al cliente.

---

## Funzionalità

### Lato cliente
- Pulsante **"Richiedi recesso"** nella pagina I miei ordini (appare solo se l'ordine è idoneo)
- Form guidato con selezione prodotti (totale o parziale), motivo facoltativo, accettazione condizioni
- Pagina di conferma con tracking dello stato (In attesa / Approvato / Respinto)
- Email di conferma ricezione richiesta
- Email di notifica cambio stato (Approvato / Respinto)
- Download PDF della richiesta di recesso

### Lato amministratore
- Dashboard **WooCommerce → Richieste Recesso** con tabella, filtri per stato, ricerca
- Pagina dettaglio con pulsanti Approva / Respingi
- Export CSV delle richieste
- Metabox nell'ordine con dettagli della richiesta
- Stato ordine personalizzato "Recesso richiesto"
- Note ordine automatiche con storico

### Configurazione
- Wizard di configurazione alla prima attivazione
- Pagina impostazioni: giorni di recesso, email admin, testo pulsante, pagina condizioni
- Creazione automatica della pagina "Diritto di Recesso" in bozza alla prima attivazione
- Supporto recesso parziale (solo alcuni prodotti)

---

## Requisiti

- WordPress 6.8+
- WooCommerce 10+
- PHP 8.1+
- HPOS (High Performance Order Storage) supportato

---

## Installazione

### Da WordPress (consigliato)
1. Vai su **Plugin → Aggiungi nuovo → Carica plugin**
2. Carica il file ZIP
3. Attiva il plugin
4. Segui il wizard di configurazione

### Da GitHub
1. Scarica il repository come ZIP
2. Carica in WordPress come sopra

---

## Configurazione

Dopo l'attivazione, il wizard ti guiderà nella configurazione iniziale. Puoi modificare le impostazioni in qualsiasi momento da **WooCommerce → Recessi**.

| Impostazione | Descrizione | Default |
|---|---|---|
| Giorni di recesso | Periodo entro cui il cliente può recedere | 14 |
| Email amministratore | Destinatario delle notifiche | Email sito |
| Testo pulsante | Label del pulsante in "I miei ordini" | Richiedi recesso |
| Pagina condizioni | Pagina con le condizioni di recesso | Auto-creata in bozza |
| Recesso parziale | Permette di selezionare solo alcuni prodotti | Abilitato |

---

## Struttura del codice

```
easy-withdrawal-for-woocommerce/
├── assets/css/
│   ├── admin.css
│   ├── dashboard.css
│   ├── frontend.css
│   └── enfold-compat.css
├── languages/
│   └── easy-withdrawal-for-woocommerce.pot
├── src/
│   ├── Admin/
│   │   ├── Dashboard.php         # Dashboard richieste + export CSV
│   │   ├── Settings.php          # Pagina impostazioni WC
│   │   ├── SetupWizard.php       # Wizard prima attivazione
│   │   └── WithdrawalMetabox.php # Metabox nell'ordine
│   ├── Compatibility/
│   │   └── EnfoldCompat.php      # Compatibilità tema Enfold
│   ├── Core/
│   │   ├── Installer.php         # Attivazione / disattivazione
│   │   └── Plugin.php            # Bootstrap singleton
│   ├── Email/
│   │   ├── AdminWithdrawalEmail.php
│   │   ├── CustomerStatusUpdateEmail.php
│   │   ├── CustomerWithdrawalEmail.php
│   │   ├── EmailManager.php
│   │   └── OrderStatus.php
│   ├── Frontend/
│   │   ├── WithdrawalButton.php
│   │   ├── WithdrawalConfirmation.php
│   │   └── WithdrawalForm.php
│   ├── Helpers/
│   │   ├── OrderHelper.php
│   │   ├── SettingsHelper.php
│   │   └── WithdrawalRepository.php
│   └── PDF/
│       └── PdfGenerator.php
├── templates/
│   ├── admin/
│   │   ├── dashboard-detail.php
│   │   └── dashboard-list.php
│   ├── emails/
│   │   ├── admin-withdrawal.php
│   │   ├── customer-status-update.php
│   │   ├── customer-withdrawal.php
│   │   └── plain/
│   └── withdrawal-form.php
│   └── withdrawal-confirmed.php
├── CHANGELOG.md
├── README.md
├── uninstall.php
└── easy-withdrawal-for-woocommerce.php
```

---

## Standard di sviluppo

- PSR-12
- WordPress Coding Standards
- OOP con namespace `WEW\`
- Autoload PSR-4 senza Composer
- Sanitizzazione e escaping su tutti gli input/output
- Nonce su ogni form e azione
- Capability check `manage_woocommerce` su tutte le azioni admin
- Compatibile HPOS con elaborazione a batch (no `meta_query`, no `limit -1`)
- Zero dipendenze esterne

---

## Roadmap PRO

Le seguenti funzionalità sono pianificate per una versione premium:

- Rimborso automatico via Stripe
- Rimborso automatico via PayPal
- Generazione etichetta GLS
- Generazione etichetta Poste Italiane
- PDF con firma digitale
- Workflow personalizzabili
- API REST
- Statistiche avanzate
- Multi Store
- Integrazione CRM

---

## Contribuire

Pull request e issue sono benvenuti. Per modifiche importanti, apri prima una issue per discutere cosa vorresti cambiare.

---

## Licenza

GPL-2.0-or-later — vedi [LICENSE](LICENSE)

---

## Autore

**Gianluca Busetto** — [github.com/sitibus](https://github.com/sitibus)
