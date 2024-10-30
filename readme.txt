=== MenuPublisher 4 WP ===
Contributors: akrwp
Tags: lunch, menu, restaurant, gastronomer
Requires at least: 3.9.0
Tested up to: 4.6.1
Stable tag: 0005
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This is a plugin, that renders lunch and menus from MenuPublisher

== Description ==

This plugin can be used, to display lunch and menus, that a gastronomer
enters on MenuPublisher.de.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/menupublisher-4-wp` directory, or install the plugin through the WordPress plugins screen directly.
1. Correct the access rights of the plugin folder, the apache user must have read and write access. (You can orient yourself on the access rights of other plugins)
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->MenuPublisher 4 WP screen to configure the plugin, you will need to enter a RestaurantCode and add at least one code snippet
1. Add the code snippet on your page with the MenuPublisher 4 WP button of the rich text editor


== Frequently Asked Questions ==

= Where do I find the RestaurantCode =

You need to be the gastronomer, who enters the dishes on MenuPublisher.de. Login on
[MenuPublisher](http://www.menupublisher.de/ "MenuPublisher.de"),
then go to [Distribution -> Homepage](http://www.menupublisher.de/index.php?page=gastr_backend&spage=distr&sspage=wpPlugin#wp_inline "Distribution") and search there for **RestaurantCode**

== Screenshots ==

1. After you've installed and activated the plugin, go to **Settings -> MenuPublisher 4 WP**, enter there the **RestaurantCode**, which you can find on [MenuPublisher -> Distribution -> Homepage](http://www.menupublisher.de/index.php?page=gastr_backend&spage=distr&sspage=wpPlugin#wp_inline "Distribution"). Save then click on **"+add code snippet"**.
2. Select a name for the snippet (it must be unique among all snippets), the layout, whether the snippet should show Lunch card and/or Menu. Then save.
3. Edit the page, where the data, that you selected in the snippet setup, should be displayed and add there the code via MenuPublisher 4 WP button. **IMPORTANT**: keep in mind, that you should use only one code snippet on any page, otherwise you get an error and the output of the code is not correct.

== Changelog ==

= 1.3 =
* corrected a bug, pdf downloadlink was still a http link

= 1.2 =
* corrected a bug, plugin did not work on a https page

= 1.1 =
* corrected a bug, where info text was not displayed correctly

= 1.0 =
* Initially implemented the plugin

== Upgrade Notice ==

= 1.1 =
If you have a week info defined on your lunch card but get [object Object] output on your website, then this update corrects this problem.

= 1.0 =
none for the first version ever
