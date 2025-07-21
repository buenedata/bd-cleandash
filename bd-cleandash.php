<?php
/**
 * Plugin Name: BD CleanDash
 * Plugin URI: https://buenedata.no/plugins/bd-cleandash
 * Description: Clean up your WordPress dashboard by hiding unwanted notices and widgets. Maintain a distraction-free admin experience with powerful blacklist management.
 * Version: 1.0.3
 * Author: Buene Data
 * Author URI: https://buenedata.no
 * Text Domain: bd-cleandash
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 * GitHub Plugin URI: buenedata/bd-cleandash
 * GitHub Branch: main
 * GitHub Access Token: 
 * Requires WP: 5.0
 * Update URI: https://api.github.com/repos/buenedata/bd-cleandash/releases/latest
 *
 * @package BD_CleanDash
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BD_CLEANDASH_VERSION', '1.0.3');
define('BD_CLEANDASH_PLUGIN_FILE', __FILE__);
define('BD_CLEANDASH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BD_CLEANDASH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BD_CLEANDASH_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('BD_CLEANDASH_FILE', __FILE__);

// Require the main plugin class
require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-cleandash.php';

// Require GitHub updater
require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-github-updater.php';

// Require language management
require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-language.php';

/**
 * Initialize the plugin
 */
function bd_cleandash_init() {
    BD_CleanDash::instance()->init();
}

/**
 * Initialize GitHub updater
 */
function bd_cleandash_github_updater() {
    $GLOBALS['bd_github_updater'] = new BD_GitHub_Updater(
        BD_CLEANDASH_PLUGIN_FILE,
        'buenedata',
        'bd-cleandash'
    );
}

// Hook into WordPress
add_action('plugins_loaded', 'bd_cleandash_init');
add_action('init', 'bd_cleandash_github_updater');

/**
 * Plugin activation hook
 */
function bd_cleandash_activate() {
    BD_CleanDash::activate();
}
register_activation_hook(__FILE__, 'bd_cleandash_activate');

/**
 * Plugin deactivation hook
 */
function bd_cleandash_deactivate() {
    BD_CleanDash::deactivate();
}
register_deactivation_hook(__FILE__, 'bd_cleandash_deactivate');

/**
 * Plugin uninstall hook
 */
function bd_cleandash_uninstall() {
    BD_CleanDash::uninstall();
}
register_uninstall_hook(__FILE__, 'bd_cleandash_uninstall');

/**
 * Manual activation helper - can be called if auto-activation failed
 */
function bd_cleandash_manual_activate() {
    if (current_user_can('activate_plugins')) {
        BD_CleanDash::activate();
        wp_die('BD CleanDash tabeller er opprettet! <a href="' . admin_url() . '">Gå tilbake til admin</a>');
    }
}

// Add manual activation URL: /wp-admin/admin.php?bd_manual_activate=1
if (isset($_GET['bd_manual_activate']) && $_GET['bd_manual_activate'] == '1' && is_admin()) {
    add_action('admin_init', 'bd_cleandash_manual_activate');
}
