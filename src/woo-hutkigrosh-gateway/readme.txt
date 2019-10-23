=== WooCommerce Hutkigrosh Gateway ===
Contributors: nmekh
Tags: commerce, woocommerce, hutkigrosh, shopping, gateway, erip
Stable tag: 2.5.3
Requires at least: 4.6
Tested up to: 5.1
Requires PHP: 5.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Woocommerce ERIP (Belarus) integration via Hutkigrosh Gateway

== Description ==
Hutkigrosh™ — payment service for invoicing in AIS *Raschet* (ERIP) Belarus.
After invoicing you clients will be available for payment by a plastic card and electronic money, at any of the bank branches, cash desks, ATMs, payment terminals, in the electronic money system, through Internet banking, M-banking, Internet acquiring.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/woo-hutkigrosh-gateway` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Woocommerce->Settings->Payments screen to configure the plugin

== Changelog ==
= 2.0.1 =
* Bug fix: automatic order status update

= 2.1.0 =
* Completion page is wrapped by accordion element

= 2.1.1 =
* Bug fix for php 5.6 compatibility

= 2.2.0 =
* QR-code generation support

= 2.2.2 =
* Bug fix: correct eripid validation
* New feature: instructions section + managed completion text

= 2.2.3 =
* Bug fix: order status update in sandbox mode
* New feature: external css-file support for completion page

= 2.5.0 =
* New feature: adaptive theme completion page
* New feature: auto-expandable section on completion page (if only one section is enabled)

= 2.5.1 =
* Bug fix: Escaping "&" in product name

= 2.5.2 =
* Bug fix: Escaping « » in product name

= 2.5.3 =
* Bug fix: Resolving conflict with "Custom Order Numbers for WooCommerce"

= 2.5.4 =
* Bug fix: Order status update callback now working with custom order number