# BD CleanDash

Clean up your WordPress dashboard by hiding unwanted notices and widgets. Maintain a distraction-free admin experience with powerful blacklist management.

## Features

- **Hide Dashboard Elements**: Easily hide notices, widgets, and other dashboard elements
- **User-Specific Settings**: Individual users can customize their dashboard experience
- **Global Management**: Administrators can set global rules for all users
- **Persistent Storage**: Hidden elements remain hidden across sessions
- **Modern Interface**: Beautiful, intuitive admin interface following BD Design Guide
- **Performance Optimized**: Minimal impact on site performance
- **Automatic Updates**: Updates directly from GitHub releases

## Installation

### Via WordPress Admin (Recommended)

1. Download the latest release from [GitHub Releases](https://github.com/buenedata/bd-cleandash/releases)
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the downloaded zip file
4. Activate the plugin

### Manual Installation

1. Download the latest release
2. Extract to `/wp-content/plugins/bd-cleandash/`
3. Activate through WordPress admin

### Via Git

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/buenedata/bd-cleandash.git
```

## Usage

1. **Access Settings**: Go to WordPress Admin → Buene Data → BD CleanDash
2. **Choose Language**: Select Norwegian (🇳🇴) or English (🇺🇸) from the language dropdown
3. **Configure Settings**: 
   - Enable/disable dashboard cleanup
   - Show/hide control buttons
   - Configure persistent mode
   - Set user override permissions
   - Configure admin bar hiding per user role
4. **Hide Elements**: Use the "Hide" buttons that appear on dashboard elements
5. **Manage Blacklist**: View and manage hidden elements in the admin interface

## Features

### Language Support
- **Bilingual Interface**: Switch between Norwegian and English
- **In-Plugin Language Selector**: No need for separate plugin versions
- **Automatic Translation**: All interface elements update instantly

### User Interface
- **Modern, Clean Design**: Beautiful gradient-based interface following BD Design Guide
- **Organized Settings**: Logical grouping with clear sections and descriptions
- **Responsive Layout**: Optimized for desktop and mobile devices
- **Intuitive Controls**: Easy-to-use toggles and form elements

### Main Settings

- **Dashboard Cleanup**: Main toggle to enable/disable all features
- **Show Hide Buttons**: Display hide/show buttons on dashboard elements
- **Persistent Mode**: Remember hidden elements across sessions
- **Auto-hide New Elements**: Automatically hide new elements matching blacklist rules
- **User Override**: Allow users to override global settings

### Admin Bar Management
- **Hide Admin Bar (Backend)**: Hide the WordPress admin toolbar in the admin area
- **Hide Admin Bar (Frontend)**: Hide the WordPress admin toolbar on the public site
- **Role-Based Configuration**: Configure admin bar hiding per user role with easy checkbox selection

### Button Styles

Choose from different button styles:
- **Default**: Standard WordPress styling
- **Minimal**: Subtle, minimal appearance
- **Bold**: Prominent, eye-catching buttons

### Admin Bar Management

BD CleanDash provides options to hide the WordPress admin bar (toolbar) for a cleaner experience:

- **Backend Admin Bar**: Hide the admin toolbar in the WordPress admin area
- **Frontend Admin Bar**: Hide the admin toolbar on public pages when logged in
- **Role-Based Hiding**: Configure admin bar hiding per user role
- **Responsive Support**: Properly handles admin bar hiding on mobile devices
- **CSS Reset**: Automatically adjusts page layout when admin bar is hidden

## BD Plugin Integration System

BD CleanDash provides a unified overview page and menu system for all BD/Buene Data plugins:

### Dynamic Plugin Discovery
- **Automatic Detection**: Automatically discovers installed BD plugins on the overview page
- **Real-Time Status**: Shows current status (active/inactive/not-installed) for each plugin
- **Plugin Cards**: Beautiful cards with icons, descriptions, and action buttons
- **Coming Soon Plugins**: Displays planned BD plugins with estimated release dates

### Plugin Registry
- **Registration API**: Other BD plugins can register themselves for better integration
- **Metadata Support**: Rich plugin information including version, admin URLs, documentation links
- **Status Detection**: Automatic detection of plugin activation status
- **Menu Integration**: Unified menu structure for consistent user experience

### For Plugin Developers
BD CleanDash includes comprehensive documentation for integrating other BD plugins:

- **Integration Guide**: Step-by-step instructions in `BD-Plugin-Menu-Integration-Guide.md`
- **Example Plugin**: Complete example implementation in `example-bd-plugin.php`
- **Registry API**: Simple registration system for plugin metadata
- **Menu Standards**: Consistent menu structure and styling guidelines

#### Quick Integration Example
```php
// Register your BD plugin
add_action('bd_plugin_registry_init', function($registry) {
    $registry->register_plugin(array(
        'slug' => 'your-plugin-slug',
        'name' => 'Your Plugin Name',
        'description' => 'Brief description of your plugin.',
        'icon' => '🔧',
        'version' => YOUR_PLUGIN_VERSION,
        'status' => 'auto',
        'admin_url' => admin_url('admin.php?page=your-plugin')
    ));
});
```

## Development

### Requirements

- WordPress 5.0+
- PHP 7.4+
- Modern browser with ES6 support

### File Structure

```
bd-cleandash/
├── assets/
│   ├── css/          # Stylesheets
│   └── js/           # JavaScript files
├── includes/         # PHP classes
├── templates/        # PHP templates
├── languages/        # Translation files
├── .github/          # GitHub workflows
└── bd-cleandash.php  # Main plugin file
```

### Building

The plugin is ready to use without a build process. All assets are included.

### Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Commit changes: `git commit -am 'Add new feature'`
4. Push to branch: `git push origin feature/new-feature`
5. Submit a Pull Request

## Automatic Updates

BD CleanDash supports automatic updates directly from GitHub releases:

- **Automatic Checking**: Plugin checks for updates every 12 hours
- **WordPress Integration**: Updates appear in WordPress admin like any other plugin
- **Secure Downloads**: Updates are downloaded securely from GitHub
- **Rollback Support**: Use WordPress plugin rollback if needed

### Update Process

1. Plugin checks GitHub releases API
2. Compares current version with latest release
3. Shows update notification in WordPress admin
4. Downloads and installs update when user confirms

## API Reference

### Hooks

#### Actions

- `bd_cleandash_init`: Fired when plugin initializes
- `bd_cleandash_element_hidden`: Fired when element is hidden
- `bd_cleandash_element_shown`: Fired when element is shown

#### Filters

- `bd_cleandash_hide_selectors`: Modify selectors for hideable elements
- `bd_cleandash_button_text`: Customize hide/show button text
- `bd_cleandash_user_permissions`: Override user permission checks

### JavaScript Events

- `bd-cleandash:element-hidden`: Element successfully hidden
- `bd-cleandash:element-shown`: Element successfully shown
- `bd-cleandash:settings-updated`: Settings successfully updated

## Troubleshooting

### Common Issues

**Plugin not hiding elements?**
- Check if plugin is enabled in settings
- Verify show buttons setting is enabled
- Clear browser cache

**Updates not working?**
- Check WordPress file permissions
- Verify internet connectivity
- Clear plugin update cache

**JavaScript errors?**
- Check browser console for errors
- Verify jQuery is loaded
- Disable other plugins to test for conflicts

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Support

- **Documentation**: [https://buenedata.no/docs/](https://buenedata.no/docs/)
- **Issues**: [GitHub Issues](https://github.com/buenedata/bd-cleandash/issues)
- **Contact**: [https://buenedata.no/contact/](https://buenedata.no/contact/)

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## Changelog

### 1.0.1
- Added automatic GitHub updates
- Improved toggle label text display
- Enhanced responsive design
- Bug fixes and performance improvements

### 1.0.0
- Initial release
- Dashboard cleanup functionality
- User management interface
- Blacklist system
- Modern admin interface

---

**BD CleanDash** is developed and maintained by [Buene Data](https://buenedata.no).
