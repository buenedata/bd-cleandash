=== BD CleanDash ===
Contributors: buenedata
Tags: dashboard, cleanup, admin, notices, widgets, hide
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clean up your WordPress dashboard by hiding unwanted notices and widgets. Maintain a distraction-free admin experience.

== Description ==

BD CleanDash is a powerful WordPress plugin designed to help you maintain a clean, organized, and distraction-free admin dashboard. Say goodbye to cluttered admin screens filled with promotional notices, unnecessary widgets, and plugin advertisements.

= Key Features =

**Dashboard Cleanup**
* Add discrete "Hide" buttons to all dashboard notices and widgets
* Persistent hiding - remember hidden elements across sessions
* Temporary hiding - hide elements only for current session
* Bulk actions for hiding multiple elements at once

**Blacklist/Whitelist Management**
* Notice blacklist - permanently hide specific notice types
* Widget blacklist - permanently hide specific dashboard widgets
* Whitelist override - allow specific users/roles to see blacklisted items
* Import/export - backup and restore blacklist configurations
* Auto-detection - automatically detect new notices/widgets for blacklisting

**User Management**
* Role-based control - different cleanup settings per user role
* User preferences - individual users can customize their cleanup settings
* Admin override - administrators can force certain elements to be visible/hidden

**Modern Design**
* Beautiful, modern interface following BD Design Guide
* Card-based design with hover effects
* Responsive layout for all screen sizes
* Gradient styling and smooth animations

= Part of Buene Data Plugin Suite =

BD CleanDash is part of the comprehensive Buene Data plugin suite, providing professional WordPress solutions under a unified admin menu. All BD plugins share consistent design and user experience.

= Perfect For =

* WordPress developers who want a clean working environment
* Site administrators tired of plugin promotional notices
* Agencies managing multiple client websites
* Anyone who values a distraction-free admin experience

= Privacy =

BD CleanDash does not collect, store, or transmit any personal data. All settings and blacklists are stored locally in your WordPress database.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/bd-cleandash/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Buene Data > CleanDash to configure the plugin.
4. Start hiding unwanted dashboard elements by clicking the "Hide" buttons that appear.

== Frequently Asked Questions ==

= Does this plugin affect the functionality of other plugins? =

No, BD CleanDash only hides visual elements in the admin dashboard. It does not disable or remove any plugin functionality.

= Can I recover hidden elements? =

Yes, you can easily show hidden elements again by clicking the "Show" button, or by managing your blacklist in the plugin settings.

= Will my settings be preserved if I deactivate the plugin? =

Yes, all settings and blacklists are preserved in the database. If you uninstall the plugin, all data will be removed.

= Can different users have different hidden elements? =

Yes, each user can have their own personalized blacklist, and administrators can also set global rules.

= Does this work with multisite installations? =

Yes, BD CleanDash is fully compatible with WordPress multisite installations.

== Screenshots ==

1. Clean dashboard after hiding unwanted notices and widgets
2. BD CleanDash settings page with modern tabbed interface
3. Blacklist management with detailed overview
4. Hide buttons automatically added to dashboard elements
5. Buene Data overview page showing integrated plugin suite

== Changelog ==

= 1.0.0 =
* Initial release
* Dashboard cleanup functionality
* Hide/show buttons for notices and widgets
* Persistent and temporary hiding modes
* Blacklist/whitelist management
* User and role-based permissions
* Import/export functionality
* Modern BD Design Guide interface
* Buene Data menu integration
* WordPress 6.6 compatibility
* PHP 8.x compatibility

== Upgrade Notice ==

= 1.0.0 =
Initial release of BD CleanDash. Start cleaning up your WordPress dashboard today!

== Development ==

BD CleanDash is developed by Buene Data, specialists in professional WordPress solutions. 

= Requirements =
* WordPress 5.0+
* PHP 7.4+
* MySQL 5.6+

= Security =
* Nonce verification for all form submissions
* Capability checks for admin functions
* Sanitized input/output
* SQL injection prevention
* XSS protection

= Performance =
* Minimal database queries
* Cached blacklist data
* Optimized JavaScript loading
* CSS minification
* Lazy loading for admin pages

== Support ==

For support, documentation, and feature requests, please visit our website or contact us directly.

= Links =
* [Website](https://buenedata.no/)
* [Documentation](https://buenedata.no/docs/bd-cleandash/)
* [Support](https://buenedata.no/support/)

== License ==

This plugin is licensed under the GPL v2 or later.

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
