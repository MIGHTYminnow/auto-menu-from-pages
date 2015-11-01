=== Auto Menu From Pages ===
Contributors:      McGuive7, MIGHTYminnow, Braad
Donate link:       http://wordpress.org/plugins/auto-menu-from-pages
Tags:              auto, automatic, menu, navigation, page, hierarchy
Requires at least: 3.5
Tested up to:      4.3
Stable tag:        1.3.1
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Automatically generate a navigation menu from your page hierarchy.

== Description ==

**A <a href="http://mightyminnow.com/plugin-landing-page?utm_source=auto-menu-from-pages&utm_medium=plugin-repo&utm_campaign=WordPress%20Plugins">MIGHTYminnow</a> plugin. Enjoy? Consider [leaving a 5-star review](https://wordpress.org/support/view/plugin-reviews/auto-menu-from-pages).**

Auto Menu From Pages generates a WordPress navigation menu that matches your page order and hierarchy. Simply click the **Sync Auto Menu** link in the admin bar to update the auto menu and reflect any changes you make to your pages so it is always current.

The auto menu works just like any other WordPress navigation menu, meaning that you can assign it to any of your theme's menu locations, output it using the Custom Menu widget, and do anything else that you could normally do to a navigation menu.

= Instructions =
1. Install and activate the plugin.
2. Navigate to Appearance > Menus in the WordPress admin and select the "Auto Menu From Pages" menu to edit (this should automatically happen on activation).
3. Assign the auto menu to one or more of your theme's menu locations using the normal "Theme locations" checkboxes, and save the menu.
4. Click the **Sync Auto Menu** link in the admin bar to automatically update your menu to reflect any changes you've made to your pages (title, order, etc).
5. Note: To hide a page from the auto-menu, check the "Hide from the auto menu" checkbox when editing that page.

= Enabling Auto-Syncing =
This plugin has the ability to automatically sync your menu after pages are modified, however the feature is turned off by default as it can create significant overhead. To turn it on, use the provided `amfp_auto_sync_menu` filter and set it to true, like so:

	add_filter( 'amfp_auto_sync_menu', '__return_true' );


== Installation ==

1. Install and activate the plugin.
2. Navigate to Appearance > Menus in the WordPress admin and select the "Auto Menu From Pages" menu to edit (this should automatically happen on activation).
3. Assign the auto menu to one or more of your theme's menu locations using the normal "Theme locations" checkboxes, and save the menu.
4. That's it! The menu will now auto-output to match your pages.
5. Note: To hide a page from the auto-menu, check the "Hide from the auto menu" checkbox when editing that page.

== Frequently Asked Questions ==

= How does the menu work? =

The menu takes your hierarchy of pages and creates a nav menu item for each page. Every time you make a change to the pages on your site, simply click the **Sync Auto Menu** link in the admin bar to update your menu. Alternately, you can use the provided `amfp_auto_sync_menu` filter (just return true) to turn on auto-syncing, however this can create significant overhead.

= Can I exclude pages from the auto menu? =

Yes. When editing a page, look for the "Auto Menu From Pages" metabox, and simply check the box for "Hide from the auto menu".

= Why can't I directly edit the menu? =

You'll notice that the auto menu doesn't have the same editing abilities (manually adding a menu item, drag-and-drop sorting of menu items, etc) as other menus. That's because the menu automatically updates to match your page hierarchy, so any manual edits you made to the menu would only be overwritten the next time you edited your pages.


= Can I manually add/items to the menu? =

At present, no. The menu auto generates based on your page hierarchy, and therefore isn't able to allow for manually added nav menu items.

== Screenshots ==

1. The Auto Menu From Pages menu in action. Looks the same as any other menu, just simplified!

== Changelog ==

= 1.3.1 =
* Fix: some pages not being added to menu (issue of more than one page pointing to the same menu item).

= 1.3.0 =
* Add support for installs with prefixed databases (solves issue of pages missing from menu).

= 1.2.0 =
* Add support to exclude pages from Simple Section Nav plugin.

= 1.1.1 =
* Remove testing filter that was setting auto-syncing to on by default.

= 1.1.0 =
* Switch to manual Sync Auto Menu link in the admin bar.
* Turn off auto-syncing feature by default, add `amfp_auto_sync_menu` filter to turn it on for development.

= 1.0.2 =
* Fix admin notice dismiss bug that was redirecting to the incorrect page
* Remove functionality that deletes the menu on plugin deactivation

= 1.0.1 =
* Fix bug causing admin CSS to render on non-menu pages, hiding various elements

= 1.0.0 =
* First release

== Upgrade Notice ==

= 1.3.1 =
* Fix: some pages not being added to menu (issue of more than one page pointing to the same menu item).

= 1.3.0 =
* Add support for installs with prefixed databases (solves issue of pages missing from menu).

= 1.2.0 =
* Add support to exclude pages from Simple Section Nav plugin.

= 1.1.1 =
* Remove testing filter that was setting auto-syncing to on by default.

= 1.1.0 =
* Switch to manual Sync Auto Menu link in the admin bar.
* Turn off auto-syncing feature by default, add `amfp_auto_sync_menu` filter to turn it on for development.

= 1.0.2 =
* Fix admin notice dismiss bug that was redirecting to the incorrect page
* Remove functionality that deletes the menu on plugin deactivation

= 1.0.1 =
* Fix bug causing admin CSS to render on non-menu pages, hiding various elements

= 1.0.0 =
First Release