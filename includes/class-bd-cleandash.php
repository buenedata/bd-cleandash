<?php
/**
 * Main BD CleanDash Plugin Class
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main BD_CleanDash class
 */
class BD_CleanDash {
    
    /**
     * Plugin version
     *
     * @var string
     */
    public static $version = BD_CLEANDASH_VERSION;
    
    /**
     * Single instance of the class
     *
     * @var BD_CleanDash
     */
    private static $instance = null;
    
    /**
     * Dashboard manager instance
     *
     * @var BD_Dashboard
     */
    public $dashboard;
    
    /**
     * Blacklist manager instance
     *
     * @var BD_Blacklist
     */
    public $blacklist;
    
    /**
     * Admin interface instance
     *
     * @var BD_Admin
     */
    public $admin;
    
    /**
     * AJAX handler instance
     *
     * @var BD_Ajax
     */
    public $ajax;
    
    /**
     * Language manager instance
     *
     * @var BD_Language
     */
    public $language;
    
    /**
     * Get single instance
     *
     * @return BD_CleanDash
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        // Set singleton instance immediately
        if (is_null(self::$instance)) {
            self::$instance = $this;
        }
        
        $this->load_dependencies();
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize language manager first
        $this->language = new BD_Language();
        
        // Handle version upgrades and migrations
        $this->handle_version_upgrade();
        
        // Check database tables first
        $this->check_database_tables();
        
        // Check database status for admin notices
        $this->check_database_status();
        
        add_action('init', array($this, 'load_textdomain'));
        add_action('admin_init', array($this, 'init_admin'));
        
        // Initialize plugin registry early
        BD_Plugin_Registry::get_instance();
        
        // Initialize components in correct order
        $this->blacklist = new BD_Blacklist();
        $this->dashboard = new BD_Dashboard();
        $this->admin = new BD_Admin();
        $this->ajax = new BD_Ajax();
        
        // Hook components after all are initialized
        $this->blacklist->init();
        $this->dashboard->init();
        $this->admin->init();
        $this->ajax->init();
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-language.php';
        require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-plugin-registry.php';
        require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-dashboard.php';
        require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-blacklist.php';
        require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-admin.php';
        require_once BD_CLEANDASH_PLUGIN_DIR . 'includes/class-bd-ajax.php';
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'bd-cleandash',
            false,
            dirname(BD_CLEANDASH_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Initialize admin functionality
     */
    public function init_admin() {
        // Check if user has required capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Admin initialization code here
    }
    
    /**
     * Handle version upgrades and migrations
     */
    private function handle_version_upgrade() {
        $current_version = get_option('bd_cleandash_version');
        
        // If this is a fresh installation or upgrade, set English as default
        // This ensures new installations start with English
        if (!$current_version || version_compare($current_version, '1.0.2', '<')) {
            // Only set language if not already set by user
            if (!get_option('bd_cleandash_language')) {
                update_option('bd_cleandash_language', 'en');
            }
        }
        
        // Update version
        update_option('bd_cleandash_version', BD_CLEANDASH_VERSION);
    }
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            wp_die(__('BD CleanDash requires WordPress 5.0 or higher.', 'bd-cleandash'));
        }
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            wp_die(__('BD CleanDash requires PHP 7.4 or higher.', 'bd-cleandash'));
        }
        
        // Create database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clear any cached data
        wp_cache_flush();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Only run if user has proper permissions
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        // Delete database tables
        self::drop_tables();
        
        // Delete options
        self::delete_options();
        
        // Clear any cached data
        wp_cache_flush();
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Blacklist table
        $blacklist_table = $wpdb->prefix . 'bd_cleandash_blacklist';
        $blacklist_sql = "CREATE TABLE $blacklist_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            element_type enum('notice','widget') NOT NULL,
            element_id varchar(255) NOT NULL,
            element_selector varchar(500) DEFAULT NULL,
            element_content text DEFAULT NULL,
            user_id int(11) DEFAULT 0,
            role varchar(50) DEFAULT '',
            is_global tinyint(1) DEFAULT 0,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_element (element_type, element_id, user_id)
        ) $charset_collate;";
        
        // Settings table
        $settings_table = $wpdb->prefix . 'bd_cleandash_settings';
        $settings_sql = "CREATE TABLE $settings_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) DEFAULT 0,
            setting_name varchar(100) NOT NULL,
            setting_value text DEFAULT NULL,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_setting (user_id, setting_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($blacklist_sql);
        dbDelta($settings_sql);
    }
    
    /**
     * Drop database tables
     */
    private static function drop_tables() {
        global $wpdb;
        
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bd_cleandash_blacklist");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bd_cleandash_settings");
    }
    
    /**
     * Set default plugin options
     */
    public static function set_default_options() {
        $defaults = array(
            'bd_cleandash_enabled' => '1',
            'bd_cleandash_show_buttons' => '1',
            'bd_cleandash_persistent_mode' => '1',
            'bd_cleandash_auto_hide_new' => '0',
            'bd_cleandash_user_override' => '1',
            'bd_cleandash_language' => 'en',
            'bd_cleandash_version' => BD_CLEANDASH_VERSION
        );
        
        foreach ($defaults as $option => $value) {
            add_option($option, $value);
        }
    }
    
    /**
     * Delete plugin options
     */
    private static function delete_options() {
        $options = array(
            'bd_cleandash_enabled',
            'bd_cleandash_show_buttons',
            'bd_cleandash_persistent_mode',
            'bd_cleandash_auto_hide_new',
            'bd_cleandash_user_override',
            'bd_cleandash_version'
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
    }
    
    /**
     * Check and create database tables if they don't exist
     */
    public function check_database_tables() {
        global $wpdb;
        
        $blacklist_table = $wpdb->prefix . 'bd_cleandash_blacklist';
        $settings_table = $wpdb->prefix . 'bd_cleandash_settings';
        
        // Check if tables exist
        $blacklist_exists = $wpdb->get_var("SHOW TABLES LIKE '$blacklist_table'") === $blacklist_table;
        $settings_exists = $wpdb->get_var("SHOW TABLES LIKE '$settings_table'") === $settings_table;
        
        if (!$blacklist_exists || !$settings_exists) {
            error_log('BD CleanDash: Database tables missing, creating them...');
            self::create_tables();
            self::set_default_options();
            error_log('BD CleanDash: Database tables created successfully');
        }
    }
    
    /**
     * Check if database is properly set up
     */
    public function check_database_status() {
        global $wpdb;
        
        $blacklist_table = $wpdb->prefix . 'bd_cleandash_blacklist';
        $blacklist_exists = $wpdb->get_var("SHOW TABLES LIKE '$blacklist_table'") === $blacklist_table;
        
        if (!$blacklist_exists && is_admin()) {
            add_action('admin_notices', array($this, 'database_missing_notice'));
        }
    }
    
    /**
     * Show admin notice if database tables are missing
     */
    public function database_missing_notice() {
        $manual_url = admin_url('admin.php?bd_manual_activate=1');
        echo '<div class="notice notice-warning bd-notice">
            <p><strong>BD CleanDash:</strong> Database-tabeller mangler. 
            <a href="' . esc_url($manual_url) . '" class="button">Opprett tabeller nå</a></p>
        </div>';
    }
}
