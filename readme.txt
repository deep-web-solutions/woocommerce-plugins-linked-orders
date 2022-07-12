=== Linked Orders for WooCommerce ===
Contributors: ahegyes, deepwebsolutions
Tags: linked orders, woocommerce
Requires at least: 5.5
Tested up to: 6.0.1
Requires PHP: 7.4
Stable tag: 1.2.2  
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WooCommerce extension for creating orders that are logically connected to existing ones.

== Description ==

**Linked Orders is a WooCommerce extension for marking newer orders as being logically connected to older ones.**

What that means depends on your use-case. For example, an order containing a warranty extension should probably be linked to the order containing the product that the extension is for. Or an express processing fee paid separately. Or anything else that you'd like to keep track of without having to open up all the customer's orders to figure it out.

Empty linked orders can be created using the admin interfaces (e.g., for email or phone orders), or you can link orders programmatically to fit your existing workflow.

== Installation ==

This plugin requires WooCommerce 4.5+ to run. If you're running a lower version, please update first. After you made sure that you're running a supported version of WooCommerce, you may install `Linked Orders for WooCommerce` either manually or through your site's plugins page.

### INSTALL FROM WITHIN WORDPRESS

1. Visit the plugins page withing your dashboard and select `Add New`.
1. Search for `Locked Orders for WooCommerce` and click the `Install Now` button.
1. Activate the plugin from within your `Plugins` page.

### INSTALL MANUALLY

1. Download the plugin from https://wordpress.org/plugins/ and unzip the archive.
1. Upload the `linked-orders-for-woocommerce` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the `Plugins` menu in WordPress.

### AFTER ACTIVATION

If the minimum required version of WooCommerce is present, you will find a section present in the `Advanced` tab of the WooCommerce `Settings` page. This section is called `Linked Orders`. There you will be able to:

1. Choose the maximum depth level you want to allow.
1. Choose whether to autocomplete descendant orders upon completion of a parent order.

== Frequently Asked Questions ==

= What are linked orders? =

Linked orders are ones that have a logical connection to one another. That can mean whatever you need it to. You can [read this article](https://docs.deep-web-solutions.com/plugins/linked-orders-for-woocommerce/what-is-a-linked-order/) to learn more about linked orders and how they work.

= How can I get help if I'm stuck? =

You might be able to figure some stuff out by browsing [our knowledge base](https://docs.deep-web-solutions.com/article-categories/linked-orders-for-woocommerce/) and you can open a community support ticket here at [wordpress.org](https://wordpress.org/support/plugin/linked-orders-for-woocommerce/). Our staff regularly goes through the community forum to try and help.

= I have a question that is not listed here =

There is a chance that your question might be answered [in our knowledge base](https://docs.deep-web-solutions.com/article-categories/linked-orders-for-woocommerce/). Otherwise, feel free to reach out via our [contact form](https://www.deep-web-solutions.com/contact/).

== Screenshots ==

1. Plugin settings section within the WooCommerce settings.
2. Example of how to visualize order depth on the orders list table.
3. Example of how to visualize order relations on the edit order screen.
4. Example of how to create a new linked child order from the orders list table.

== Changelog ==

= 1.2.2 (July 12th, 2022) =
* Tested up to WordPress 6.0.1.
* Tested up to WooCommerce 6.7.0.
* Updated Freemius SDK.

= 1.2.1 (March 20th, 2022) =
* Tested up to WordPress 5.9.
* Tested up to WooCommerce 6.3.
* Security: updated Freemius SDK.

= 1.2.0 (January 15th, 2022) =
* New: integration with SkyVerge's WC Sequential Order Numbers Pro.
* Better support for 3rd-party order types.
* Removed Freemius contact form functionality.
* Tweak: updated 'tested up to' headers.
* Dev: added more automated testing.
* Dev: updated DWS framework.

= 1.1.1 (November 23rd, 2021) =
* Fixed assets version.
* Dev: hook generator helpers can now be called before initialization.
* Dev: updated DWS framework.

= 1.1.0 (November 11th, 2021) =
* Changed order actions icons for less overlap with other plugins.
* Tweaked error messages for better end-user clarity.
* Dev: renamed some variables to better express 'shop_order' agnosticism.
* Dev: updated DWS framework.

= 1.0.0 (October 25th, 2021) =
* First official release.
