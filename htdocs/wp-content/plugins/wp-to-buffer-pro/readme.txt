=== WP to Buffer Pro ===
Contributors: n7studios,wpcube
Donate link: http://www.wpcube.co.uk/plugins/wp-to-buffer-pro
Tags: buffer,bufferapp,schedule,twitter,facebook,linkedin,google,social,media,sharing,post
Requires at least: 3.6
Tested up to: 4.3
Stable tag: trunk

Send WordPress Pages, Posts or Custom Post Types to your Buffer (bufferapp.com) account for scheduled publishing to social networks.

== Description ==

WP to Buffer is a plugin for WordPress that sends updates to your Buffer (bufferapp.com) account  for scheduled publishing to social networks when you publish and/or update WordPress Pages, Posts and/or Custom Post Types.

Plugin settings allow granular control over choosing:
- Sending updates to Buffer for Posts, Pages and/or any Custom Post Types
- Sending updates when any of the above are published, updated or both or neither
- Text format to use when sending an update on publish or update events, with support for tags including site name, Post title, excerpt, categories, date, URL and author
- Which social media accounts connected to your Buffer account to publish updates to (Facebook, Twitter or LinkedIn)

When creating or editing a Page, Post or Custom Post Type, sending the update to Buffer can be overridden for that specific content item.

= Support =

Please email support@wpcube.co.uk, with your license key.

= WP Cube =
We produce free and premium WordPress Plugins that supercharge your site, by increasing user engagement, boost site visitor numbers
and keep your WordPress web sites secure.

Find out more about us:

* <a href="http://www.wpcube.co.uk">Our Plugins</a>
* <a href="http://www.facebook.com/wpcube">Facebook</a>
* <a href="http://twitter.com/wp_cube">Twitter</a>
* <a href="https://plus.google.com/b/110192203343779769233/110192203343779769233/posts?rel=author">Google+</a>

== Installation ==

1. Upload the `wp-to-buffer-pro` folder to the `/wp-content/plugins/` directory
2. Active the WP to Buffer Pro through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the `WP to Buffer Pro` menu that appears in your admin menu

== Frequently Asked Questions ==



== Screenshots ==

1. Settings Panel when plugin is first installed.
2. Settings Panel when Buffer Access Token is entered.
3. Settings Panel showing available options for Posts, Pages and any Custom Post Types when the plugin is authenticated with Buffer.
4. Post level settings meta box.

== Changelog ==

= 3.0.3 =
* Fix: Post submitted successfully message no longer displays when there are no status(es) to send to Buffer.
* Fix: Status(es) not sent to Buffer when WP-CRON setting enabled.
* Fix: Invalid argument supplied for foreach() error on publish.php::169 when a Post Type has no settings.
* Fix: View Details on plugin updates now display changelog.

= 3.0.2 =
* Fix: Scheduled Posts not Buffering (removed is_admin() checks)

= 3.0.1 =
* Fix: empty() checks for PHP 5.4 and older compat

= 3.0 =
* Added: Revised UI
* Added: Unlimited statuses per Post Type, Social Profile and Action
* Added: Pinterest support

= 2.3.9 =
* Fix: Scheduled Posts now honour Manual Override Settings

= 2.3.8 =
* Fix: &hellip; HTML character code appearing on Facebook + Google+ status updates when no excerpt defined on a Post

= 2.3.7 =
* Added: Author Field support on status updates
* Added: Author Field Meta / Custom Field support on status updates

= 2.3.6 =
* Added: Custom Field support on status updates
* Added: Post Override option to NOT post to Buffer for a specified post, regardless of plugin settings
* Added: Import + Export Settings, allowing users to copy settings to other plugin installations
* Added: Support Panel

= 2.3.5 =
* Fix: Transients for license key validation

= 2.3.4 =
* Fix: Force license key check method to beat aggressive server caching
* Added: Support menu with debug information

= 2.3.3 =
* Dropped html_entity_decode and apply_filters on Post Title - causing too many issues.

= 2.3.2 =
* Added translation support and .pot file 

= 2.3.1 =
* Fix: Issue with characters in the title being HTML encoded

= 2.3 =
* New: Settings available on a per-account, per-post type basis

= 2.2.3 =
* Fix: Prevent double posting when Posts with category filtering are enabled, and a Post is added via third party apps using the XML RPC API
* Fix: Pages can be posted to Buffer via XML RPC API

= 2.2.2 =
* Newline / multiline support for status updates
* Added possible fix for preventing duplicate Buffer statuses on publish

= 2.2.1 =
* Better license key transient check / refresh to prevent frontend functionality from not working

= 2.2 =
* Fix: Twitter Images attached to tweets
* Fix: Featured Images on Facebook

= 2.1.9 =
* Pro: Added Include Featured Image option
* Pro: Added Number of Times to Buffer option
* Pro: Added Post, Page + Custom Post Type Meta Box for overriding status updates
* Pro: Added `wp_to_buffer_pro` hook for publish action

= 2.1.8 =
* Pro Fix: LinkedIn titles + URLs no longer removed

= 2.1.7 =
* Pro: Improved success and error messages when posting to Buffer
* Pro Fix: PHP Warning: Invalid argument supplied for foreach() on line 482 fixed

= 2.1.6 =
* Pro Fix: Changed callback and notice URLs to point to correct settings screen
* Pro: Post Type Taxonomies can now be used as tags in status updates
* Pro: Better UI for adding tags to status updates
* Pro: Quick Update panel on Dashboard

= 2.1.5 =
* Pro: Publish immediately option
* Pro: Filter by Taxonomies and Taxonomy Terms
* Fix: Don't show success message when Post/Page not posted to Buffer
* Fix: Removed Post to Buffer meta box, which wasn't honouring settings / causing double postings
* Settings: changed to tabbed interface

= 2.1.4 =
* Fix: Dashboard: PHP fatal error

= 2.1.3 =
* Fix: Posts with an image no longer show the image link, but instead show the Page / Post URL

= 2.1.2 =
* Fix: Donation Form

= 2.1.1 =
* Fix: Some assets missing from SVN checkin on 2.1

= 2.1 =
* Fix: 'Creating default object from empty value' warning
* Fix: {excerpt} tag working on Pages and Custom Post Types that do not have an Excerpt field
* Fix: Capabilities for add_menu_page
* Fix: Check for page $_GET variable

= 2.0.1 =
* Fix: Removed console.log messages
* Fix: Added Google+ icon for Buffer accounts linked to Google+ Pages

= 2.0 =
* Fix: admin_enqueue_scripts used to prevent 3.6+ JS errors
* Fix: Force older versions of WP to Buffer to upgrade to 2.x branch.
* Fix: Check for Buffer accounts before outputting settings (avoids invalid argument errors).
* Enhancement: Validation of access token to prevent several errors.
* Enhancement: Add callback URL value (not required, but avoids user confusion).
* Enhancement: Check the access token pasted into the settings field is potentially valid (avoids questions asking why the plugin doesn't work,
because the user hasn't carefully checked the access token).

= 1.1 =
* Enhancement: Removed spaces from categories in hashtags (thanks, Douglas!)
* Fix: "Error creating default object from empty value" message.
* Enhancement: Added Featured Image when posting to Buffer, if available.
* Fix: Simplified authentication process using Access Token. Fixes many common oAuth issues.

= 1.03 =
* Fix: Publish hooks now based on settings instead of registered post types, to ensure they hook early enough to work on custom post types.

= 1.02 =
* Fix: Scheduled Posts now post to Buffer on scheduled publication.

= 1.01 =
* SSL verification fix for Buffer API authentication.

= 1.0 =
* First release.

== Upgrade Notice ==
