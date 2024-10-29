=== Auto Parts 4 Less Marketplace Sync ===
Contributors: autopart4less
Tags: Auto Parts 4 Less Marketplace Sync, Auto Parts 4 Less Connector, Connector for Auto Parts 4 Less, Auto Parts 4 Less, AP4L, Auto Parts, Connector, AutoParts, autoparts4less, WooCommerce, Auto Parts 4 Less Sync, Marketplace, Sync
Requires at least: 6.0
Tested up to: 6.0
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

This plugin will list your WooCommerce products to Auto Parts 4 Less marketplace.

== Description ==
This plugin will list your *WooCommerce* products to [Auto Parts 4 Less](https://www.autoparts4less.com/) marketplace.

== Installation ==
= Installing the plugin =
* This plugin depends on the *WooCommerce* plugin, so if you do not have *WooCommerce* plugin installed, then first install *WooCommerce* plugin and setup your products.
* In your WordPress admin panel, go to Plugins > Add New, search for **Auto Parts 4 Less Marketplace Sync** and click *Install now*.
* Alternatively, download the plugin and upload the contents of ap4l.zip to your plugins directory, which usually is /wp-content/plugins/.
* Activate the plugin.
= Configuration =
* All the sections of the plugin have comments for how to use the plugin, however you can go through below steps to configure the plugin.
* You need to create your seller account on [Auto Parts 4 Less - Seller Registration](https://seller.autoparts4less.com/auth/login) and add your email and auth_token, you got from the seller panel, in Accounts section.
* Map you WooCommerce Categories to AP4L Categories in Categories section.
* Create your policies. i.e. Shipping, Selling, Synchronization
* Create your listings and add your products to the listing.
* *Bingo!* your products will be listed on [Auto Parts 4 Less](https://www.autoparts4less.com/).

== Frequently Asked Questions ==
= Is there any plugin dependency? =
Yes, plugin is dependent on *WooCommerce* plugin.

= Is it required create a seller account on [Auto Parts 4 Less](https://www.autoparts4less.com/)? =
Yes, the very first step to sell your products on [Auto Parts 4 Less](https://www.autoparts4less.com/) is to create a seller account.

= How the plugin will work? =
We have APIs set in cron/scripts, which will executes in background and list your products to AP4L and fetch your orders from AP4L marketplace.

= Are there any logs for listings and orders? =
Yes, we are storing logs to identify API messages and errors, if any.

= How many logs will be stored? =
By default we are storing logs for last 60 days, which is recommended to investigate any issue you face ragarding listings and orders. You are free to modify that to any other number of days in *Log Settings* section.

== Screenshots ==
1. Accounts section
2. Policies section
3. Categories section
4. Listings section
5. Listing Logs section
6. Orders section
7. Order Logs section
8. Log Settings section

== Changelog ==
= 1.0.0 =
First version of the plugin released.

== Upgrade Notice ==
NA
