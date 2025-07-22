<?php
/**
 * BD CleanDash Admin Interface
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * BD_Admin class
 */
class BD_Admin {
    
    /**
     * Initialize admin functionality
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_post_bd_cleandash_save_settings', array($this, 'handle_settings_submit'));
        
        // Admin bar functionality
        add_action('init', array($this, 'init_admin_bar_settings'));
        
        // Additional AJAX handlers for admin
        add_action('wp_ajax_bd_toggle_enabled', array($this, 'ajax_toggle_enabled'));
        add_action('wp_ajax_bd_get_blacklist', array($this, 'ajax_get_blacklist'));
        add_action('wp_ajax_bd_remove_blacklist_item', array($this, 'ajax_remove_blacklist_item'));
        add_action('wp_ajax_bd_check_github_updates', array($this, 'ajax_check_github_updates'));
    }
    
    /**
     * Add admin menu following BD menu integration guide
     */
public function add_admin_menu() {
public function add_admin_menu() {
        global $menu;
        $bd_menu_exists = false;
        foreach ($menu as $menu_item) {
            // Sjekk om noen meny har slug 'buene-data'
            if (isset($menu_item[2]) && $menu_item[2] === 'buene-data') {
                $bd_menu_exists = true;
                break;
            }
        }

        if (!$bd_menu_exists) {
            // Hvis ingen "Buene Data" meny finnes, opprett den
            add_menu_page(
                __('Buene Data', 'bd-plugin'), // NB: endre text domain om nødvendig
                __('Buene Data', 'bd-plugin'),
                'manage_options',
                'buene-data', // SLUGGEN MÅ VÆRE IDENTISK I ALLE PLUGINS
                '', // Ingen callback på hovedmenyen (kan evt. ha en oversiktsside her)
                'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 2L3 7V18H7V14H13V18H17V7L10 2Z" fill="currentColor"/></svg>'),
                58.5
            );
        }

        // Legg til din plugin som undermeny under Buene Data
        add_submenu_page(
            'buene-data',
            __('BD CleanDash', 'bd-plugin'),
            __('BD CleanDash', 'bd-plugin'),
            'manage_options',
            'bd-clean-dash',
            array($this, 'render_main_page')
        );
    }
    
    /**
     * Render BD overview page
     */
    public function render_bd_overview() {
        // Get plugin registry instance
        $registry = BD_Plugin_Registry::get_instance();
        
        // Get all plugins for overview
        $overview_plugins = $registry->get_overview_plugins();
        
        // Also get auto-detected plugins as fallback
        $auto_detected = $registry->auto_detect_bd_plugins();
        
        // Pass data to template
        $template_data = array(
            'plugins' => $overview_plugins,
            'auto_detected' => $auto_detected,
            'registry' => $registry
        );
        
        // Extract variables for template
        extract($template_data);
        
        include BD_CLEANDASH_PLUGIN_DIR . 'templates/bd-overview.php';
    }
    
    /**
     * Render main admin page
     */
    public function render_admin_page() {
        include BD_CLEANDASH_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook_suffix) {
        // Debug logging flag
        $bd_cleandash_debug = defined('BD_CLEANDASH_DEBUG') && BD_CLEANDASH_DEBUG;

        if ($bd_cleandash_debug) {
            error_log("BD CleanDash: enqueue_admin_scripts called with hook: $hook_suffix");
        }
        
        // Always load dashboard scripts if cleanup is enabled - on ALL admin pages
        if (get_option('bd_cleandash_enabled', '1') === '1') {
            if ($bd_cleandash_debug) {
                error_log("BD CleanDash: Plugin enabled, enqueuing dashboard scripts");
            }
            $this->enqueue_dashboard_scripts();
        }
        
        // Force admin scripts on BD pages - simplified check
        $current_page = isset($_GET['page']) ? $_GET['page'] : '';
        if ($bd_cleandash_debug) {
            error_log("BD CleanDash: Checking admin page - Hook: $hook_suffix, Page: $current_page, Is BD page: " . ($current_page === 'bd-cleandash' || $current_page === 'buene-data' || strpos($hook_suffix, 'bd-cleandash') !== false || strpos($hook_suffix, 'buene-data') !== false ? 'yes' : 'no'));
        }
        
        if ($current_page === 'bd-cleandash' || $current_page === 'buene-data' || strpos($hook_suffix, 'bd-cleandash') !== false || strpos($hook_suffix, 'buene-data') !== false) {
            if ($bd_cleandash_debug) {
                error_log("BD CleanDash: FORCED admin script loading for page: $current_page, hook: $hook_suffix");
            }
            
            // Ensure jQuery is loaded
            wp_enqueue_script('jquery');
            
            // Enqueue admin CSS
            wp_enqueue_style(
            wp_enqueue_script(
                'bd-cleandash-admin',
                $script_url,
                array('jquery'),
                BD_CLEANDASH_VERSION, // Use only plugin version for caching
                true
            );
            $script_url = BD_CLEANDASH_PLUGIN_URL . 'assets/js/admin.js';
            if ($bd_cleandash_debug) {
                error_log("BD CleanDash: Script URL: $script_url");
            }
            
            // Enqueue admin JS with explicit jQuery dependency and cache busting
            wp_enqueue_script(
                'bd-cleandash-admin',
                $script_url,
                array('jquery'),
                BD_CLEANDASH_VERSION . '-' . time(), // Add timestamp to avoid caching
                true
            );
            
            // Localize script
            wp_localize_script('bd-cleandash-admin', 'bdCleanDashAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bd_cleandash_admin_nonce'),
                'strings' => array(
                    'confirm_remove' => bd__('Er du sikker på at du vil fjerne dette elementet?'),
                    'confirm_clear' => bd__('Er du sikker på at du vil tømme svartelisten?'),
                    'error_generic' => bd__('En feil oppstod'),
                    'user_statistics' => bd__('Brukerstatistikk'),
                    'no_user_data' => bd__('Ingen brukerdata tilgjengelig ennå.'),
                    'user' => bd__('Bruker'),
                    'total_hidden' => bd__('Totalt skjulte'),
                    'notices' => bd__('Varsler'),
                    'widgets' => bd__('Widgets'),
                    'remove' => bd__('Fjern'),
                    'confirm_remove_item' => bd__('Er du sikker på at du vil fjerne dette elementet fra svartelisten?'),
                    'could_not_remove' => bd__('Kunne ikke fjerne elementet'),
                    'unknown_error' => bd__('Ukjent feil'),
                    'network_error' => bd__('Nettverksfeil'),
                    'confirm_clear_personal' => bd__('Er du sikker på at du vil tømme din personlige svarteliste?'),
                    'could_not_clear' => bd__('Kunne ikke tømme svarteliste'),
                    'confirm_clear_all' => bd__('Er du sikker på at du vil tømme ALLE svartelister? Dette kan ikke angres.'),
                    'could_not_clear_all' => bd__('Kunne ikke tømme svartelister'),
                    'enabled' => bd__('Aktivert'),
                    'disabled' => bd__('Deaktivert'),
                    'loading_blacklist' => bd__('Laster svarteliste...'),
                    'loading_user_settings' => bd__('Laster brukerinnstillinger...'),
                    'could_not_load_blacklist' => bd__('Kunne ikke laste svarteliste'),
                    'could_not_load_user_data' => bd__('Kunne ikke laste brukerdata'),
                    'checking_updates' => bd__('Sjekker...'),
                    'check_updates' => bd__('Sjekk etter oppdateringer'),
                    'checking_for_updates' => bd__('Sjekker etter oppdateringer...'),
                    'update_check_error' => bd__('Feil'),
                    'update_network_error' => bd__('Nettverksfeil ved sjekking av oppdateringer.'),
                    'could_not_export' => bd__('Kunne ikke eksportere svarteliste'),
                    'select_file_first' => bd__('Velg en fil først'),
                    'import_failed' => bd__('Import feilet'),
                    'invalid_json' => bd__('Ugyldig JSON-fil'),
                    'no_blacklist_items' => bd__('Ingen elementer i svartelisten ennå.'),
                    'blacklist_instructions' => bd__('Gå til dashbordet og klikk "Skjul" på elementer du vil skjule.'),
                    'notice' => bd__('Varsel'),
                    'widget' => bd__('Widget'),
                    'global' => bd__('Global'),
                    'user_prefix' => bd__('Bruker #'),
                    'type' => bd__('Type'),
                    'element_id' => bd__('Element ID'),
                    'created' => bd__('Opprettet'),
                    'actions' => bd__('Handlinger')
                )
            ));
            
            if ($bd_cleandash_debug) {
                error_log("BD CleanDash: Admin scripts enqueued successfully");
            }
            return;
        }
        
        // Only load admin-specific styles on our admin pages
        if (!$this->is_bd_admin_page($hook_suffix)) {
            if ($bd_cleandash_debug) {
                error_log("BD CleanDash: Not a BD admin page, skipping admin scripts");
            }
            return;
        }
    }
    
    /**
     * Enqueue dashboard cleanup scripts
     */
    public function enqueue_dashboard_scripts() {
        // Debug logging flag
        $bd_cleandash_debug = defined('BD_CLEANDASH_DEBUG') && BD_CLEANDASH_DEBUG;

        if ($bd_cleandash_debug) {
            error_log("BD CleanDash: enqueue_dashboard_scripts called");
        }
        
        wp_enqueue_style(
            'bd-cleandash-dashboard',
            BD_CLEANDASH_PLUGIN_URL . 'assets/css/dashboard.css',
            array(),
            BD_CLEANDASH_VERSION
        );

        wp_enqueue_script(
            'bd-cleandash-dashboard',
            BD_CLEANDASH_PLUGIN_URL . 'assets/js/dashboard.js',
            array('jquery'),
            BD_CLEANDASH_VERSION,
            true
        );

        // Get current user's blacklisted items - with safety check
        $hidden_elements = array();
        $plugin_instance = BD_CleanDash::instance();
        if ($plugin_instance && $plugin_instance->blacklist) {
            $hidden_elements = $plugin_instance->blacklist->get_user_blacklist(get_current_user_id());
        }

        wp_localize_script('bd-cleandash-dashboard', 'bdCleanDash', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bd_cleandash_nonce'),
            'hiddenElements' => $hidden_elements,
            'showButtons' => get_option('bd_cleandash_show_buttons', '1'),
            'persistentMode' => get_option('bd_cleandash_persistent_mode', '1'),
            'strings' => array(
                'hide' => bd__('Skjul'),
                'show' => bd__('Vis'),
                'hidden' => bd__('Skjult'),
                'hide_tooltip' => bd__('Skjul dette elementet'),
                'show_tooltip' => bd__('Vis dette elementet')
            )
        ));
        
        if ($bd_cleandash_debug) {
            error_log("BD CleanDash: Dashboard scripts enqueued with " . count($hidden_elements) . " hidden elements");
        }
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('bd_cleandash_settings', 'bd_cleandash_enabled');
        register_setting('bd_cleandash_settings', 'bd_cleandash_show_buttons');
        register_setting('bd_cleandash_settings', 'bd_cleandash_persistent_mode');
        register_setting('bd_cleandash_settings', 'bd_cleandash_auto_hide_new');
        register_setting('bd_cleandash_settings', 'bd_cleandash_user_override');
    }
    
    /**
     * Check if current page is a BD admin page
     */
    private function is_bd_admin_page($hook_suffix) {
        $bd_pages = array(
            'toplevel_page_buene-data',
            'buene-data_page_bd-cleandash'
        );
        
        $is_bd_page = in_array($hook_suffix, $bd_pages) || 
                     (isset($_GET['page']) && (strpos($_GET['page'], 'bd-') === 0 || $_GET['page'] === 'buene-data'));
        
        // Debug logging flag
        $bd_cleandash_debug = defined('BD_CLEANDASH_DEBUG') && BD_CLEANDASH_DEBUG;

        if ($bd_cleandash_debug) {
            error_log("BD CleanDash: Checking admin page - Hook: $hook_suffix, Page: " . (isset($_GET['page']) ? $_GET['page'] : 'none') . ", Is BD page: " . ($is_bd_page ? 'yes' : 'no'));
        }
        
        return $is_bd_page;
    }
    
    /**
     * Handle settings form submission
     */
    public function handle_settings_submit() {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'bd_cleandash_settings')) {
            wp_die(__('Security check failed.', 'bd-cleandash'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'bd-cleandash'));
        }
        
        // Sanitize and save settings
        $settings = array(
            'bd_cleandash_enabled' => sanitize_text_field($_POST['bd_cleandash_enabled'] ?? '0'),
            'bd_cleandash_show_buttons' => sanitize_text_field($_POST['bd_cleandash_show_buttons'] ?? '0'),
            'bd_cleandash_persistent_mode' => sanitize_text_field($_POST['bd_cleandash_persistent_mode'] ?? '0'),
            'bd_cleandash_auto_hide_new' => sanitize_text_field($_POST['bd_cleandash_auto_hide_new'] ?? '0'),
            'bd_cleandash_user_override' => sanitize_text_field($_POST['bd_cleandash_user_override'] ?? '0'),
            'bd_cleandash_hide_admin_bar' => sanitize_text_field($_POST['bd_cleandash_hide_admin_bar'] ?? '0'),
            'bd_cleandash_hide_admin_bar_frontend' => sanitize_text_field($_POST['bd_cleandash_hide_admin_bar_frontend'] ?? '0'),
            'bd_cleandash_language' => sanitize_text_field($_POST['bd_cleandash_language'] ?? 'no')
        );
        
        // Handle role arrays for admin bar settings
        $role_settings = array(
            'bd_cleandash_hide_admin_bar_roles' => array(),
            'bd_cleandash_hide_admin_bar_frontend_roles' => array()
        );
        
        if (isset($_POST['bd_cleandash_hide_admin_bar_roles']) && is_array($_POST['bd_cleandash_hide_admin_bar_roles'])) {
            $role_settings['bd_cleandash_hide_admin_bar_roles'] = array_map('sanitize_text_field', $_POST['bd_cleandash_hide_admin_bar_roles']);
        }
        
        if (isset($_POST['bd_cleandash_hide_admin_bar_frontend_roles']) && is_array($_POST['bd_cleandash_hide_admin_bar_frontend_roles'])) {
            $role_settings['bd_cleandash_hide_admin_bar_frontend_roles'] = array_map('sanitize_text_field', $_POST['bd_cleandash_hide_admin_bar_frontend_roles']);
        }
        
        // Save all settings
        foreach ($settings as $option => $value) {
            update_option($option, $value);
        }
        
        foreach ($role_settings as $option => $value) {
            update_option($option, $value);
        }
        
        // Redirect with success message
        wp_redirect(add_query_arg(array(
            'page' => 'bd-cleandash',
            'settings-updated' => 'true'
        ), admin_url('admin.php')));
        exit;
    }
    
    /**
     * AJAX handler for toggling enabled status
     */
    public function ajax_toggle_enabled() {
        // Verify nonce
        if (!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $enabled = sanitize_text_field(isset($_POST['enabled']) ? $_POST['enabled'] : '');
        update_option('bd_cleandash_enabled', $enabled);
        
        $message = $enabled === '1' ? 
            bd__('BD CleanDash aktivert') : 
            bd__('BD CleanDash deaktivert');
        
        wp_send_json_success(array('message' => $message));
    }
    
    /**
     * AJAX handler for getting blacklist data
     */
    public function ajax_get_blacklist() {
        // Verify nonce
        if (!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $plugin_instance = BD_CleanDash::instance();
        if (!$plugin_instance || !$plugin_instance->blacklist) {
            wp_send_json_error('Plugin not properly initialized');
            return;
        }
        
        $data = $plugin_instance->blacklist->get_all_blacklisted();
        
        // Debug logging flag
        $bd_cleandash_debug = defined('BD_CLEANDASH_DEBUG') && BD_CLEANDASH_DEBUG;

        // Debug: Log the actual data being returned
        if ($bd_cleandash_debug) {
            error_log('BD CleanDash AJAX: Returning blacklist data: ' . json_encode($data));
        }
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX handler for removing blacklist item
     */
    public function ajax_remove_blacklist_item() {
        // Verify nonce
        if (!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $item_id = intval(isset($_POST['item_id']) ? $_POST['item_id'] : 0);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bd_cleandash_blacklist';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $item_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => bd__('Element fjernet')));
        } else {
            wp_send_json_error('Failed to remove item');
        }
    }
    
    /**
     * AJAX handler for checking GitHub updates
     */
    public function ajax_check_github_updates() {
        // Verify nonce
        if (!isset($_POST['nonce']) || empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        // Force check for updates
        $plugin_instance = BD_CleanDash::instance();
        if ($plugin_instance && isset($GLOBALS['bd_github_updater'])) {
            $GLOBALS['bd_github_updater']->force_check();
            wp_send_json_success(array('message' => bd__('Sjekket etter oppdateringer')));
        } else {
            wp_send_json_error('GitHub updater not available');
        }
    }
    
    /**
     * Initialize admin bar settings
     */
    public function init_admin_bar_settings() {
        // Check if admin bar should be hidden
        $hide_admin_bar = get_option('bd_cleandash_hide_admin_bar', '0');
        $hide_admin_bar_frontend = get_option('bd_cleandash_hide_admin_bar_frontend', '0');
        $hide_admin_bar_roles = get_option('bd_cleandash_hide_admin_bar_roles', array());
        $hide_admin_bar_frontend_roles = get_option('bd_cleandash_hide_admin_bar_frontend_roles', array());
        
        // Get current user
        $current_user = wp_get_current_user();
        
        // Hide admin bar in backend
        if ($hide_admin_bar === '1' && is_admin()) {
            // Check if user has one of the selected roles or if no roles are selected (applies to all)
            if (empty($hide_admin_bar_roles) || $this->user_has_role($current_user, $hide_admin_bar_roles)) {
                add_filter('show_admin_bar', '__return_false');
                add_action('admin_head', array($this, 'hide_admin_bar_css'));
                add_action('admin_body_class', array($this, 'add_hide_admin_bar_class'));
            }
        }
        
        // Hide admin bar on frontend
        if ($hide_admin_bar_frontend === '1' && !is_admin()) {
            // Check if user has one of the selected roles or if no roles are selected (applies to all)
            if (empty($hide_admin_bar_frontend_roles) || $this->user_has_role($current_user, $hide_admin_bar_frontend_roles)) {
                add_filter('show_admin_bar', '__return_false');
                add_action('wp_head', array($this, 'hide_admin_bar_css'));
                add_action('body_class', array($this, 'add_hide_admin_bar_class'));
            }
        }
    }
    
    /**
     * Check if user has any of the specified roles
     */
    private function user_has_role($user, $roles) {
        if (!$user || !is_array($roles) || empty($roles)) {
            return false;
        }
        
        foreach ($roles as $role) {
            if (in_array($role, $user->roles)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Add CSS to hide admin bar completely
     */
    public function hide_admin_bar_css() {
        echo '<style type="text/css">
            #wpadminbar { 
                display: none !important; 
            }
            html.wp-toolbar { 
                padding-top: 0 !important; 
            }
            body.admin-bar { 
                margin-top: 0 !important; 
            }
            #wpwrap { 
                margin-top: 0 !important; 
            }
            @media screen and (max-width: 782px) {
                html.wp-toolbar { 
                    padding-top: 0 !important; 
                }
            }
        </style>';
    }
    
    /**
     * Add class to body for admin bar hiding
     */
    public function add_hide_admin_bar_class($classes) {
        if (is_array($classes)) {
            $classes[] = 'bd-hide-admin-bar';
        } else {
            $classes .= ' bd-hide-admin-bar';
        }
        return $classes;
    }
}
