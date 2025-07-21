# Changelog

All notable changes to BD CleanDash will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

# Changelog

All notable changes to BD CleanDash will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.3] - 2025-07-21

### Added
- **Multi-language support (Norwegian/English)** with in-plugin language selector
- Automatic language switching without needing separate plugin versions
- Comprehensive translation system with BD_Language class
- **Dynamic BD Plugin Registry System** - automatically detects and displays all installed BD/Buene Data plugins
- **Unified BD Overview Page** - shows real-time status of all BD plugins with "coming soon" cards for planned plugins
- **BD Plugin Menu Integration Guide** - comprehensive documentation for other BD plugins to integrate with the unified system
- Plugin status detection (active/inactive/not-installed) with appropriate action buttons
- Auto-detection system for BD plugins that haven't manually registered
- Plugin registry API for BD plugins to register themselves with metadata
- Visual status indicators and enhanced plugin cards with proper styling
- Smart button logic for plugin cards based on available URLs and plugin status
- Staggered fade-in animation for plugin cards
- Enhanced CSS with modern design patterns and responsive layouts

### Changed
- **BD Overview page is now fully dynamic** - replaces static plugin cards with real-time detection system
- **Menu integration for all BD plugins** - standardized approach for consistent user experience across BD plugin suite
- **Reorganized settings page layout** - removed duplicate "Save Settings" buttons and improved grouping
- **Enhanced admin bar settings section** with proper spacing and visual separation
- Role checkboxes now use a responsive grid layout for better organization
- Improved plugin card styling with hover effects and status-based colors
- Updated plugin cards with "BD" prefix in names for better branding
- Enhanced responsive design for mobile devices
- Plugin cards are now always up to date with real-time status detection

### Fixed
- **Duplicate "Save Settings" buttons** - now only one button at the end of all settings
- **Poor spacing between settings sections** - added proper visual separation and spacing
- **Admin bar settings placement** - moved to logical grouped section with clear headings
- **Blacklist table styling** - restored styled badges and boxes for element types, user names, and remove buttons
- Text truncation issues in toggle labels under "Hovedinnstillinger"
- Role selection styling with better visual hierarchy and responsive design
- PHP syntax errors in overview template using proper alternative syntax
- CSS loading for the overview page by updating enqueue logic
- Fatal error on overview page by ensuring proper class loading

## [Unreleased]

### Added
- GitHub automatic updates system
- Admin interface for managing updates
- Version checking and update notifications
- Role-based admin bar hiding configuration
- Per-role settings for admin bar visibility in backend and frontend
- Improved broom icon size in header for better visibility
- **Multi-language support (Norwegian/English)** with in-plugin language selector
- Automatic language switching without needing separate plugin versions
- Comprehensive translation system with BD_Language class
- **Improved settings page UI/UX** with better organization and spacing
- Form sections with clear headings and descriptions for better user experience
- Enhanced role selection interface with grid layout and better styling
- **Dynamic BD Plugin Registry System** - automatically detects and displays all installed BD/Buene Data plugins
- **Unified BD Overview Page** - shows real-time status of all BD plugins with "coming soon" cards for planned plugins
- **BD Plugin Menu Integration Guide** - comprehensive documentation for other BD plugins to integrate with the unified system
- Plugin status detection (active/inactive/not-installed) with appropriate action buttons
- Auto-detection system for BD plugins that haven't manually registered
- Plugin registry API for BD plugins to register themselves with metadata
- Visual status indicators and enhanced plugin cards with proper styling

### Changed
- Improved toggle label text display
- Enhanced responsive design for mobile devices
- Updated plugin cards with "BD" prefix in names
- Admin bar hiding now supports role-based configuration
- Hide buttons no longer appear on BD CleanDash's own notices
- Larger broom icon in admin header for better visual prominence
- **Reorganized settings page layout** - removed duplicate "Save Settings" buttons and improved grouping
- **Enhanced admin bar settings section** with proper spacing and visual separation
- Role checkboxes now use a responsive grid layout for better organization
- **BD Overview page is now fully dynamic** - replaces static plugin cards with real-time detection system
- **Menu integration for all BD plugins** - standardized approach for consistent user experience across BD plugin suite

### Fixed
- Text truncation issues in toggle labels under "Hovedinnstillinger"
- **Duplicate "Save Settings" buttons** - now only one button at the end of all settings
- **Poor spacing between settings sections** - added proper visual separation and spacing
- **Admin bar settings placement** - moved to logical grouped section with clear headings
- Role selection styling with better visual hierarchy and responsive design
- **Blacklist table styling** - restored styled badges and boxes for element types, user names, and remove buttons
- **Missing CSS for blacklist management** - added comprehensive styling for table, badges, and interactive elements
- **Button consistency** - updated remove buttons to match BD CleanDash theme with proper gradients, hover effects, and icon styling
- **WordPress button integration** - ensured all button combinations (button, button-primary, button-secondary) work seamlessly with BD theme
- **Design guide compliance** - buttons now follow the BD Design Guide specifications for consistent styling across all interface elements

## [1.0.1] - 2025-01-21

### Added
- GitHub automatic updates functionality
- Enhanced admin interface with update management
- Improved error handling and logging
- Better responsive design for all screen sizes

### Changed
- Plugin names now include "BD" prefix for consistency
- Improved CSS specificity to prevent WordPress conflicts
- Enhanced toggle label text wrapping

### Fixed
- Toggle label text truncation issues
- Mobile layout problems
- CSS inheritance conflicts

## [1.0.0] - 2025-01-20

### Added
- Initial release of BD CleanDash
- Dashboard cleanup functionality
- Hide/show buttons for notices and widgets
- User-specific blacklist management
- Global admin settings
- Persistent storage across sessions
- Modern admin interface following BD Design Guide
- Multi-tab admin layout (Dashboard, Blacklist, Users, Tools)
- AJAX-powered interface
- Import/export functionality for blacklists
- User permissions and role management
- Statistics and reporting
- Responsive design for all devices

### Features
- **Dashboard Cleanup**: Hide unwanted notices and widgets
- **User Management**: Individual user preferences
- **Global Settings**: Administrator-controlled defaults
- **Blacklist System**: Persistent element hiding
- **Modern UI**: Beautiful, intuitive interface
- **Performance**: Minimal impact on site speed
- **Accessibility**: Keyboard navigation and screen reader support

### Technical
- WordPress 5.0+ compatibility
- PHP 7.4+ requirement
- Modern JavaScript (ES6+)
- CSS Grid and Flexbox layouts
- WordPress coding standards compliance
- Secure AJAX implementations
- Proper sanitization and validation

## [Pre-release] - Development

### Development History
- Initial plugin structure and architecture
- Database schema design and implementation
- Core functionality development
- Admin interface design and implementation
- JavaScript functionality and AJAX integration
- CSS styling and responsive design
- Testing and debugging
- Documentation and code comments
- Security review and hardening
- Performance optimization
- WordPress standards compliance
- User acceptance testing

---

**Note**: This changelog documents all significant changes to BD CleanDash. For detailed commit history, see the [GitHub repository](https://github.com/buenedata/bd-cleandash).

## Release Process

1. Update version numbers in `bd-cleandash.php`
2. Update this changelog with new features and fixes
3. Create and push a new git tag (e.g., `v1.0.1`)
4. GitHub Actions will automatically create a release
5. WordPress sites will be notified of the update

## Support

- **Documentation**: [Buene Data Docs](https://buenedata.no/docs/)
- **Issues**: [GitHub Issues](https://github.com/buenedata/bd-cleandash/issues)
- **Support**: [Contact Buene Data](https://buenedata.no/contact/)
