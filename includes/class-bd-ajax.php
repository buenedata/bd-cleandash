<?php
/**
 * BD CleanDash AJAX Handlers
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * BD_Ajax class
 */
class BD_Ajax {
    
    /**
     * Initialize AJAX functionality
     */
    public function init() {
        // AJAX actions for logged-in users
        add_action('wp_ajax_bd_hide_element', array($this, 'hide_element'));
        add_action('wp_ajax_bd_show_element', array($this, 'show_element'));
        add_action('wp_ajax_bd_get_hidden_elements', array($this, 'get_hidden_elements'));
        add_action('wp_ajax_bd_bulk_hide', array($this, 'bulk_hide_elements'));
        add_action('wp_ajax_bd_clear_blacklist', array($this, 'clear_blacklist'));
        add_action('wp_ajax_bd_export_blacklist', array($this, 'export_blacklist'));
        add_action('wp_ajax_bd_import_blacklist', array($this, 'import_blacklist'));
        add_action('wp_ajax_bd_get_statistics', array($this, 'get_statistics'));
    }
    
    /**
     * Hide an element (add to blacklist)
     */
    public function hide_element() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_nonce')) {
            wp_die('Security check failed');
        }
        
        // Get parameters
        $element_type = sanitize_text_field($_POST['element_type']);
        $element_id = sanitize_text_field($_POST['element_id']);
        $element_selector = sanitize_text_field($_POST['element_selector'] ?? '');
        $element_content = sanitize_textarea_field($_POST['element_content'] ?? '');
        $is_persistent = isset($_POST['is_persistent']) && $_POST['is_persistent'] === '1';
        
        // Validate element type
        if (!in_array($element_type, array('notice', 'widget'))) {
            wp_send_json_error('Invalid element type');
        }
        
        $user_id = get_current_user_id();
        
        // Add to blacklist if persistent mode
        if ($is_persistent && get_option('bd_cleandash_persistent_mode', '1') === '1') {
            $blacklist = BD_CleanDash::instance()->blacklist;
            $result = $blacklist->add_to_blacklist(
                $element_type,
                $element_id,
                $element_selector,
                $element_content,
                $user_id
            );
            
            if (!$result) {
                wp_send_json_error('Failed to add to blacklist');
            }
        }
        
        wp_send_json_success(array(
            'message' => __('Element hidden successfully', 'bd-cleandash'),
            'element_id' => $element_id,
            'is_persistent' => $is_persistent
        ));
    }
    
    /**
     * Show an element (remove from blacklist)
     */
    public function show_element() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_nonce')) {
            wp_die('Security check failed');
        }
        
        // Get parameters
        $element_type = sanitize_text_field($_POST['element_type']);
        $element_id = sanitize_text_field($_POST['element_id']);
        
        // Validate element type
        if (!in_array($element_type, array('notice', 'widget'))) {
            wp_send_json_error('Invalid element type');
        }
        
        $user_id = get_current_user_id();
        
        // Remove from blacklist
        $blacklist = BD_CleanDash::instance()->blacklist;
        $result = $blacklist->remove_from_blacklist($element_type, $element_id, $user_id);
        
        wp_send_json_success(array(
            'message' => __('Element shown successfully', 'bd-cleandash'),
            'element_id' => $element_id
        ));
    }
    
    /**
     * Get hidden elements for current user
     */
    public function get_hidden_elements() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_nonce')) {
            wp_die('Security check failed');
        }
        
        $user_id = get_current_user_id();
        $blacklist = BD_CleanDash::instance()->blacklist;
        $hidden_elements = $blacklist->get_user_blacklist($user_id);
        
        wp_send_json_success($hidden_elements);
    }
    
    /**
     * Bulk hide elements
     */
    public function bulk_hide_elements() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $elements = $_POST['elements'] ?? array();
        $is_global = isset($_POST['is_global']) && $_POST['is_global'] === '1';
        $target_role = sanitize_text_field($_POST['target_role'] ?? '');
        
        if (empty($elements) || !is_array($elements)) {
            wp_send_json_error('No elements provided');
        }
        
        $blacklist = BD_CleanDash::instance()->blacklist;
        $hidden_count = 0;
        
        foreach ($elements as $element) {
            $element_type = sanitize_text_field($element['type']);
            $element_id = sanitize_text_field($element['id']);
            $element_selector = sanitize_text_field($element['selector'] ?? '');
            $element_content = sanitize_textarea_field($element['content'] ?? '');
            
            if (in_array($element_type, array('notice', 'widget'))) {
                $result = $blacklist->add_to_blacklist(
                    $element_type,
                    $element_id,
                    $element_selector,
                    $element_content,
                    0, // user_id 0 for admin actions
                    $target_role,
                    $is_global
                );
                
                if ($result) {
                    $hidden_count++;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d elements hidden successfully', 'bd-cleandash'), $hidden_count),
            'hidden_count' => $hidden_count
        ));
    }
    
    /**
     * Clear blacklist
     */
    public function clear_blacklist() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $scope = sanitize_text_field($_POST['scope'] ?? 'user');
        $user_id = intval($_POST['user_id'] ?? get_current_user_id());
        
        $blacklist = BD_CleanDash::instance()->blacklist;
        
        if ($scope === 'all' && current_user_can('manage_options')) {
            $result = $blacklist->clear_all_blacklists();
            $message = __('All blacklists cleared successfully', 'bd-cleandash');
        } else {
            $result = $blacklist->clear_user_blacklist($user_id);
            $message = __('User blacklist cleared successfully', 'bd-cleandash');
        }
        
        if ($result !== false) {
            wp_send_json_success(array('message' => $message));
        } else {
            wp_send_json_error('Failed to clear blacklist');
        }
    }
    
    /**
     * Export blacklist
     */
    public function export_blacklist() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $scope = sanitize_text_field($_POST['scope'] ?? 'user');
        $user_id = ($scope === 'user') ? get_current_user_id() : null;
        
        $blacklist = BD_CleanDash::instance()->blacklist;
        $export_data = $blacklist->export_blacklist($user_id);
        
        wp_send_json_success(array(
            'data' => $export_data,
            'filename' => 'bd-cleandash-blacklist-' . date('Y-m-d-H-i-s') . '.json'
        ));
    }
    
    /**
     * Import blacklist
     */
    public function import_blacklist() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $import_data = $_POST['import_data'] ?? '';
        $user_id = intval($_POST['user_id'] ?? 0);
        
        if (empty($import_data)) {
            wp_send_json_error('No import data provided');
        }
        
        $blacklist = BD_CleanDash::instance()->blacklist;
        $imported_count = $blacklist->import_blacklist($import_data, $user_id);
        
        if ($imported_count === false) {
            wp_send_json_error('Invalid import data format');
        }
        
        wp_send_json_success(array(
            'message' => sprintf(__('%d items imported successfully', 'bd-cleandash'), $imported_count),
            'imported_count' => $imported_count
        ));
    }
    
    /**
     * Get blacklist statistics
     */
    public function get_statistics() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'bd_cleandash_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $blacklist = BD_CleanDash::instance()->blacklist;
        $stats = $blacklist->get_statistics();
        
        wp_send_json_success($stats);
    }
}
