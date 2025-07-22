<?php
/**
 * BD Plugin Registry
 * 
 * Manages registration and detection of BD/Buene Data plugins
 * for the unified overview page.
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BD_Plugin_Registry {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Registered BD plugins
     */
    private $registered_plugins = array();
    
    /**
     * Planned/Coming soon BD plugins
     */
    private $planned_plugins = array();
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_planned_plugins();
        add_action('init', array($this, 'init'), 5);
    }
    
    /**
     * Initialize the registry
     */
    public function init() {
        // Allow other plugins to register themselves
        do_action('bd_plugin_registry_init', $this);
        
        // Register this plugin (CleanDash)
        $this->register_plugin(array(
            'slug' => 'bd-cleandash',
            'name' => 'BD CleanDash',
            'description' => 'Rydde opp i WordPress dashbordet ved å skjule uønskede varsler og widgets.',
            'icon' => '🧹',
            'version' => BD_CLEANDASH_VERSION,
            'status' => 'active',
            'admin_url' => admin_url('admin.php?page=bd-cleandash'),
            'plugin_file' => BD_CLEANDASH_PLUGIN_FILE
        ));
    }
    
    /**
     * Register a BD plugin
     *
     * @param array $plugin_data Plugin information
     */
    public function register_plugin($plugin_data) {
        $defaults = array(
            'slug' => '',
            'name' => '',
            'description' => '',
            'icon' => '🔌',
            'version' => '1.0.0',
            'status' => 'inactive', // active, inactive, not-installed
            'admin_url' => '',
            'settings_url' => '',
            'plugin_file' => '',
            'download_url' => '',
            'wordpress_org_url' => '',
            'docs_url' => '',
            'support_url' => ''
        );
        
        $plugin_data = wp_parse_args($plugin_data, $defaults);
        
        if (empty($plugin_data['slug']) || empty($plugin_data['name'])) {
            return false;
        }
        
        // Detect plugin status if not provided or if set to auto-detect
        if ($plugin_data['status'] === 'auto' || empty($plugin_data['status'])) {
            $plugin_data['status'] = $this->detect_plugin_status($plugin_data['plugin_file']);
        }
        
        $this->registered_plugins[$plugin_data['slug']] = $plugin_data;
        return true;
    }
    
    /**
     * Get all registered plugins
     *
     * @return array
     */
    public function get_registered_plugins() {
        return $this->registered_plugins;
    }
    
    /**
     * Get a specific registered plugin
     *
     * @param string $slug Plugin slug
     * @return array|false
     */
    public function get_plugin($slug) {
        return isset($this->registered_plugins[$slug]) ? $this->registered_plugins[$slug] : false;
    }
    
    /**
     * Get planned/coming soon plugins
     *
     * @return array
     */
    public function get_planned_plugins() {
        return $this->planned_plugins;
    }
    
    /**
     * Initialize planned plugins list
     */
    private function init_planned_plugins() {
        $this->planned_plugins = array(
            'bd-tools' => array(
                'slug' => 'bd-tools',
                'name' => 'BD Tools',
                'description' => 'Samling av nyttige verktøy for WordPress-administrasjon.',
                'icon' => '🔧',
                'status' => 'coming-soon',
                'estimated_release' => 'Q3 2025',
                'docs_url' => 'https://buenedata.no/docs/bd-tools/',
                'support_url' => 'https://buenedata.no/support/'
            ),
            'bd-security' => array(
                'slug' => 'bd-security',
                'name' => 'BD Security',
                'description' => 'Forbedre sikkerheten på WordPress-nettstedet ditt.',
                'icon' => '🔒',
                'status' => 'coming-soon',
                'estimated_release' => 'Q4 2025',
                'docs_url' => 'https://buenedata.no/docs/bd-security/',
                'support_url' => 'https://buenedata.no/support/'
            ),
            'bd-analytics' => array(
                'slug' => 'bd-analytics',
                'name' => 'BD Analytics',
                'description' => 'Detaljert analyse og rapportering for nettstedet ditt.',
                'icon' => '📊',
                'status' => 'coming-soon',
                'estimated_release' => 'Q1 2026',
                'docs_url' => 'https://buenedata.no/docs/bd-analytics/',
                'support_url' => 'https://buenedata.no/support/'
            ),
            'bd-backup' => array(
                'slug' => 'bd-backup',
                'name' => 'BD Backup',
                'description' => 'Automatisk sikkerhetskopi og gjenoppretting av WordPress-innhold.',
                'icon' => '💾',
                'status' => 'coming-soon',
                'estimated_release' => 'Q2 2026',
                'docs_url' => 'https://buenedata.no/docs/bd-backup/',
                'support_url' => 'https://buenedata.no/support/'
            ),
            'bd-performance' => array(
                'slug' => 'bd-performance',
                'name' => 'BD Performance',
                'description' => 'Optimaliser hastigheten og ytelsen til nettstedet ditt.',
                'icon' => '⚡',
                'status' => 'coming-soon',
                'estimated_release' => 'Q3 2026',
                'docs_url' => 'https://buenedata.no/docs/bd-performance/',
                'support_url' => 'https://buenedata.no/support/'
            )
        );
    }
    
    /**
     * Detect if a plugin is active, inactive, or not installed
     *
     * @param string $plugin_file Plugin file path (relative to plugins directory)
     * @return string active|inactive|not-installed
     */
    private function detect_plugin_status($plugin_file) {
        if (empty($plugin_file)) {
            return 'not-installed';
        }
        
        // Ensure plugin functions are available
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        // Check if plugin file exists
        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;
        if (!file_exists($plugin_path)) {
            return 'not-installed';
        }
        
        // Check if plugin is active
        if (is_plugin_active($plugin_file)) {
            return 'active';
        }
        
        return 'inactive';
    }
    
    /**
     * Get all plugins for overview display (registered + planned)
     *
     * @return array
     */
    public function get_overview_plugins() {
        $plugins = array();
        
        // Add registered plugins
        foreach ($this->registered_plugins as $plugin) {
            $plugins[] = $plugin;
        }
        
        // Add planned plugins
        foreach ($this->planned_plugins as $plugin) {
            $plugins[] = $plugin;
        }
        
        // Sort by status priority (active first, then inactive, not-installed, coming-soon last)
        usort($plugins, function($a, $b) {
            $statusPriority = array(
                'active' => 1,
                'inactive' => 2,
                'not-installed' => 3,
                'coming-soon' => 4
            );
            
            $aPriority = isset($statusPriority[$a['status']]) ? $statusPriority[$a['status']] : 5;
            $bPriority = isset($statusPriority[$b['status']]) ? $statusPriority[$b['status']] : 5;
            
            // If same priority, sort by name
            if ($aPriority === $bPriority) {
                return strcmp($a['name'], $b['name']);
            }
            
            return $aPriority - $bPriority;
        });
        
        return $plugins;
    }
    
    /**
     * Check if BD plugins menu exists in admin
     *
     * @return bool
     */
    public function bd_menu_exists() {
        global $menu;
        
        if (!is_array($menu)) {
            return false;
        }
        
        foreach ($menu as $menu_item) {
            if (isset($menu_item[2]) && $menu_item[2] === 'buene-data') {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Auto-detect BD plugins by scanning for specific patterns
     * This is a fallback method for plugins that haven't registered themselves
     *
     * @return array
     */
    public function auto_detect_bd_plugins() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $bd_plugins = array();
        
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            // Check if plugin name/description contains BD or Buene Data indicators
            $is_bd_plugin = false;
            
            // Check plugin name
            if (stripos($plugin_data['Name'], 'BD ') === 0 || 
                stripos($plugin_data['Name'], 'Buene Data') !== false) {
                $is_bd_plugin = true;
            }
            
            // Check plugin description
            if (!$is_bd_plugin && 
                (stripos($plugin_data['Description'], 'Buene Data') !== false ||
                 stripos($plugin_data['Description'], 'BD Plugin') !== false)) {
                $is_bd_plugin = true;
            }
            
            // Check author
            if (!$is_bd_plugin && 
                (stripos($plugin_data['Author'], 'Buene Data') !== false ||
                 stripos($plugin_data['AuthorURI'], 'buenedata.no') !== false)) {
                $is_bd_plugin = true;
            }
            
            if ($is_bd_plugin) {
                $slug = dirname($plugin_file);
                if ($slug === '.') {
                    $slug = basename($plugin_file, '.php');
                }
                
                // Don't auto-detect if already registered
                if (isset($this->registered_plugins[$slug])) {
                    continue;
                }
                
                $bd_plugins[$slug] = array(
                    'slug' => $slug,
                    'name' => $plugin_data['Name'],
                    'description' => $plugin_data['Description'],
                    'icon' => '🔌', // Default icon for auto-detected plugins
                    'version' => $plugin_data['Version'],
                    'status' => is_plugin_active($plugin_file) ? 'active' : 'inactive',
                    'plugin_file' => $plugin_file,
                    'admin_url' => '', // Will need to be determined
                    'auto_detected' => true
                );
            }
        }
        
        return $bd_plugins;
    }
}
