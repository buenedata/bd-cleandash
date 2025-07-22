<?php
/**
 * BD CleanDash Blacklist Management
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * BD_Blacklist class
 */
class BD_Blacklist {
    
    /**
     * Database table name for blacklist
     *
     * @var string
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'bd_cleandash_blacklist';
    }
    
    /**
     * Initialize blacklist functionality
     */
    public function init() {
        // Hook into WordPress
    }
    
    /**
     * Add element to blacklist
     */
    public function add_to_blacklist($element_type, $element_id, $element_selector = '', $element_content = '', $user_id = 0, $role = '', $is_global = false) {
        global $wpdb;
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
        if (!$table_exists) {
            error_log('BD CleanDash: Blacklist table does not exist, cannot add to blacklist');
            return false;
        }
        
        // Sanitize inputs
        $element_type = sanitize_text_field($element_type);
        $element_id = sanitize_text_field($element_id);
        $element_selector = sanitize_text_field($element_selector);
        $element_content = sanitize_textarea_field($element_content);
        $user_id = intval($user_id);
        $role = sanitize_text_field($role);
        $is_global = $is_global ? 1 : 0;
        
        // Validate element type
        if (!in_array($element_type, array('notice', 'widget'))) {
            return false;
        }
        
        try {
            // Insert or update blacklist entry
            $result = $wpdb->replace(
                $this->table_name,
                array(
                    'element_type' => $element_type,
                    'element_id' => $element_id,
                    'element_selector' => $element_selector,
                    'element_content' => $element_content,
                    'user_id' => $user_id,
                    'role' => $role,
                    'is_global' => $is_global,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s')
            );
            
            return $result !== false;
        } catch (Exception $e) {
            error_log('BD CleanDash: Error adding to blacklist: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove element from blacklist
     */
    public function remove_from_blacklist($element_type, $element_id, $user_id = 0) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $this->table_name,
            array(
                'element_type' => sanitize_text_field($element_type),
                'element_id' => sanitize_text_field($element_id),
                'user_id' => intval($user_id)
            ),
            array('%s', '%s', '%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Check if element is blacklisted
     */
    public function is_blacklisted($element_type, $element_id, $user_id = null) {
        global $wpdb;
        
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $user = get_userdata($user_id);
        $user_roles = $user ? $user->roles : array();
        
        // Check for user-specific blacklist
        $user_blacklist = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} 
                WHERE element_type = %s AND element_id = %s AND user_id = %d",
                $element_type, $element_id, $user_id
            )
        );
        
        if ($user_blacklist > 0) {
            return true;
        }
        
        // Check for role-based blacklist
        if (!empty($user_roles)) {
            $role_placeholders = implode(',', array_fill(0, count($user_roles), '%s'));
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} 
                WHERE element_type = %s AND element_id = %s AND role IN ($role_placeholders)",
                array_merge(array($element_type, $element_id), $user_roles)
            );
            
            $role_blacklist = $wpdb->get_var($query);
            if ($role_blacklist > 0) {
                return true;
            }
        }
        
        // Check for global blacklist
        $global_blacklist = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} 
                WHERE element_type = %s AND element_id = %s AND is_global = 1",
                $element_type, $element_id
            )
        );
        
        return $global_blacklist > 0;
    }
    
    /**
     * Get user's blacklisted elements
     */
    public function get_user_blacklist($user_id = null) {
        global $wpdb;
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
        if (!$table_exists) {
            error_log('BD CleanDash: Blacklist table does not exist, returning empty array');
            return array();
        }
        
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $user = get_userdata($user_id);
        $user_roles = $user ? $user->roles : array();
        
        $where_conditions = array();
        $where_values = array();
        
        // User-specific items
        $where_conditions[] = "user_id = %d";
        $where_values[] = $user_id;
        
        // Role-based items
        if (!empty($user_roles)) {
            $role_placeholders = implode(',', array_fill(0, count($user_roles), '%s'));
            $where_conditions[] = "role IN ($role_placeholders)";
            $where_values = array_merge($where_values, $user_roles);
        }
        
        // Global items
        $where_conditions[] = "is_global = 1";
        
        $where_clause = "(" . implode(") OR (", $where_conditions) . ")";
        
        $query = "SELECT * FROM {$this->table_name} WHERE $where_clause ORDER BY created_at DESC";
        
        try {
            $prepared_query = $wpdb->prepare($query, $where_values);
            return $wpdb->get_results($prepared_query, ARRAY_A);
        } catch (Exception $e) {
            error_log('BD CleanDash: Error getting user blacklist: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get hidden elements by type
     */
    public function get_hidden_elements($element_type, $user_id = null) {
        $blacklist = $this->get_user_blacklist($user_id);
        
        return array_filter($blacklist, function($item) use ($element_type) {
            return $item['element_type'] === $element_type;
        });
    }
    
    /**
     * Get all blacklisted elements (admin view)
     */
    public function get_all_blacklisted($element_type = null, $limit = 100, $offset = 0) {
        global $wpdb;
        
        $where_clause = '';
        $where_values = array();
        
        if ($element_type) {
            $where_clause = "WHERE b.element_type = %s";
            $where_values[] = $element_type;
        }
        
        // Join with users table to get user display names
        $query = "SELECT b.*, u.display_name as user_display_name, u.user_login as user_login 
                  FROM {$this->table_name} b 
                  LEFT JOIN {$wpdb->users} u ON b.user_id = u.ID 
                  $where_clause 
                  ORDER BY b.created_at DESC 
                  LIMIT %d OFFSET %d";
        $where_values[] = $limit;
        $where_values[] = $offset;
        
        $results = $wpdb->get_results(
            $wpdb->prepare($query, $where_values),
            ARRAY_A
        );
        
        // Process results to add proper user names
        if ($results) {
            foreach ($results as &$item) {
                error_log("BD CleanDash: Processing blacklist item - user_id: {$item['user_id']}, display_name: " . ($item['user_display_name'] ?? 'null') . ", login: " . ($item['user_login'] ?? 'null'));
                
                if ($item['user_id'] && !empty($item['user_display_name'])) {
                    $item['user_name'] = $item['user_display_name'];
                } elseif ($item['user_id'] && !empty($item['user_login'])) {
                    $item['user_name'] = $item['user_login'];
                } else {
                    $item['user_name'] = 'Ukjent bruker';
                }
                
                error_log("BD CleanDash: Set user_name to: " . $item['user_name']);
            }
        }
        
        return $results;
    }
    
    /**
     * Clear user's blacklist
     */
    public function clear_user_blacklist($user_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('user_id' => intval($user_id)),
            array('%d')
        );
    }
    
    /**
     * Clear all blacklists
     */
    public function clear_all_blacklists() {
        global $wpdb;
        
        return $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }
    
    /**
     * Export blacklist data
     */
    public function export_blacklist($user_id = null) {
        if ($user_id) {
            $data = $this->get_user_blacklist($user_id);
        } else {
            $data = $this->get_all_blacklisted();
        }
        
        return json_encode($data, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import blacklist data
     */
    public function import_blacklist($json_data, $user_id = 0) {
        $data = json_decode($json_data, true);
        
        if (!is_array($data)) {
            return false;
        }
        
        $imported = 0;
        foreach ($data as $item) {
            if (isset($item['element_type']) && isset($item['element_id'])) {
                $result = $this->add_to_blacklist(
                    $item['element_type'],
                    $item['element_id'],
                    $item['element_selector'] ?? '',
                    $item['element_content'] ?? '',
                    $user_id ?: ($item['user_id'] ?? 0),
                    $item['role'] ?? '',
                    $item['is_global'] ?? false
                );
                
                if ($result) {
                    $imported++;
                }
            }
        }
        
        return $imported;
    }
    
    /**
     * Get blacklist statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $stats = array();
        
        // Total blacklisted items
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        // By type
        $stats['notices'] = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE element_type = %s", 'notice')
        );
        $stats['widgets'] = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name} WHERE element_type = %s", 'widget')
        );
        
        // By scope
        $stats['user_specific'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE user_id > 0");
        $stats['role_based'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE role != ''");
        $stats['global'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE is_global = 1");
        
        return $stats;
    }
}
