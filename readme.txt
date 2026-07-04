=== Easy Withdrawal for WooCommerce ===
Contributors: subitis
Tags: woocommerce, withdrawal, return, eu, refund
Requires at least: 6.8
Tested up to: 6.8.2
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
WC requires at least: 10.0
WC tested up to: 10.9

EU Right of Withdrawal for WooCommerce. Manage customer withdrawal requests compliant with EU Directive 2011/83/EU.

== Description ==

**Easy Withdrawal for WooCommerce** allows your customers to exercise their right of withdrawal directly from their WooCommerce account, simply and in compliance with EU Directive 2011/83/EU.

The plugin manages the entire flow: from the customer request, to email notification, to admin management, to status update visible to the customer.

= Customer Features =

* "Request withdrawal" button in the My Orders page (appears only if the order is eligible)
* Guided form with product selection (total or partial) and quantity to return
* Confirmation page with status tracking (Pending / Approved / Rejected)
* Confirmation email on request submission
* Status change notification email (Approved / Rejected)
* PDF download of the withdrawal request

= Admin Features =

* Dashboard under WooCommerce with request table, status filters, search
* Detail page with refund amount and Approve / Reject buttons
* CSV export of requests
* Metabox in the order with request details
* Custom order status "Withdrawal requested"
* Automatic order notes with history

= Configuration =

* Setup wizard on first activation
* Automatic creation of "Right of Withdrawal" page as draft
* Configurable withdrawal days (default: 14)
* Partial withdrawal support (select specific products or partial quantity)
* Compatible with Enfold theme

= Technical Requirements =

* Compatible with HPOS (High Performance Order Storage)
* PHP 8.1 - 8.4
* WordPress 6.8+
* WooCommerce 10+
* No external dependencies required

== Installation ==

1. Go to Plugins > Add New > Upload Plugin
2. Upload the ZIP file
3. Activate the plugin
4. Follow the setup wizard
5. Customize the "Right of Withdrawal" page automatically created as draft and publish it

== Frequently Asked Questions ==

= Is the plugin compliant with EU regulations? =

Yes. The flow is designed to comply with EU Directive 2011/83/EU requirements. A legal review before go-live is always recommended.

= Does it work with HPOS (High Performance Order Storage)? =

Yes, the plugin explicitly declares HPOS compatibility and does not use direct meta_query on legacy tables.

= Can I customize the emails? =

Yes. Emails are integrated into the native WooCommerce email system and can be configured under WooCommerce > Settings > Emails.

= Is partial withdrawal supported? =

Yes. Customers can select individual products and specify the quantity to return (e.g. 2 copies out of 3 ordered).

= How is the refund amount calculated? =

The amount is calculated proportionally to the requested quantities, including VAT. It appears in the admin dashboard and notification email. The actual refund must be processed manually.

= Does it work with the Enfold theme? =

Yes, it includes targeted CSS overrides for Enfold compatibility without modifying the theme.

== Screenshots ==

1. "Request withdrawal" button in the My Orders page
2. Withdrawal request form with product and quantity selection
3. Confirmation page with status tracking
4. Admin dashboard with request list and filters
5. Request detail page with refund amount

== Changelog ==

= 1.0.0 =
* Initial stable release
* OOP structure with WEW\ namespace, PSR-4 autoload, HPOS compatibility
* Conditional withdrawal button (completed + paid + within period + no existing request)
* Withdrawal form with full EU flow (total/partial, partial quantity)
* HTML emails to customer and admin via native WooCommerce email system
* Refund amount calculated proportionally in emails and dashboard
* Custom order status "Withdrawal requested"
* Admin dashboard with filters, search, Approve/Reject, CSV export
* Customer-side status tracking
* Downloadable PDF of the request
* First-activation setup wizard
* Automatic creation of "Right of Withdrawal" page as draft
* .pot file for translations
* Compatible with PHP 8.1-8.4, WordPress 6.8+, WooCommerce 10+

== Upgrade Notice ==

= 1.0.0 =
Initial stable release.
