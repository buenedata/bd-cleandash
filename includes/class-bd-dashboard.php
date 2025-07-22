<?php
/**
 * BD CleanDash Dashboard Management
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * BD_Dashboard class
 */
class BD_Dashboard {
    
    /**
     * Initialize dashboard functionality
     */
    public function init() {
        // Only run on admin pages
        if (!is_admin()) {
            return;
        }
        
        // Check if cleanup is enabled
        if (get_option('bd_cleandash_enabled', '1') !== '1') {
            return;
        }
        
        add_action('admin_footer', array($this, 'add_hide_buttons'));
        add_action('wp_dashboard_setup', array($this, 'modify_dashboard_widgets'));
        add_action('admin_notices', array($this, 'filter_admin_notices'), 1);
    }
    
    /**
     * Add hide buttons to dashboard elements
     */
    public function add_hide_buttons() {
        // Only show buttons if option is enabled
        if (get_option('bd_cleandash_show_buttons', '1') !== '1') {
            return;
        }
        
        // Get current screen
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }
        
        // Only add buttons on dashboard and other admin pages
        $allowed_screens = array('dashboard', 'plugins', 'themes', 'users', 'tools', 'settings');
        if (!in_array($screen->base, $allowed_screens)) {
            return;
        }
        
        echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                bdCleanDashboard.addHideButtons();
            });
        </script>';
    }
    
    /**
     * Modify dashboard widgets based on blacklist
     */
    public function modify_dashboard_widgets() {
        global $wp_meta_boxes;
        
        // Get blacklisted widgets for current user - with safety check
        $plugin_instance = BD_CleanDash::instance();
        if (!$plugin_instance || !$plugin_instance->blacklist) {
            error_log('BD CleanDash: Plugin instance or blacklist not available in modify_dashboard_widgets');
            return;
        }
        
        try {
            $hidden_widgets = $plugin_instance->blacklist->get_hidden_elements('widget', get_current_user_id());
        } catch (Exception $e) {
            error_log('BD CleanDash: Error getting hidden widgets: ' . $e->getMessage());
            return;
        }
        
        if (empty($hidden_widgets)) {
            return;
        }
        
        // Remove blacklisted widgets
        foreach ($hidden_widgets as $widget) {
            $this->remove_dashboard_widget($widget['element_id']);
        }
    }
    
    /**
     * Remove specific dashboard widget
     */
    private function remove_dashboard_widget($widget_id) {
        global $wp_meta_boxes;
        
        $contexts = array('normal', 'side', 'column3', 'column4');
        $priorities = array('high', 'core', 'default', 'low');
        
        foreach ($contexts as $context) {
            foreach ($priorities as $priority) {
                if (isset($wp_meta_boxes['dashboard'][$context][$priority][$widget_id])) {
                    unset($wp_meta_boxes['dashboard'][$context][$priority][$widget_id]);
                }
            }
        }
    }
    
    /**
     * Filter admin notices based on blacklist
     */
    public function filter_admin_notices() {
        // Get blacklisted notices for current user - with safety check
        $plugin_instance = BD_CleanDash::instance();
        if (!$plugin_instance || !$plugin_instance->blacklist) {
            return;
        }
        
        $hidden_notices = $plugin_instance->blacklist->get_hidden_elements('notice', get_current_user_id());
        
        if (empty($hidden_notices)) {
            return;
        }
        
        // Start output buffering to capture and filter notices
        ob_start(array($this, 'filter_notice_output'));
    }
    
    /**
     * Filter notice output buffer
     */
    public function filter_notice_output($buffer) {
        // Get blacklisted notices - with safety check
        $plugin_instance = BD_CleanDash::instance();
        if (!$plugin_instance || !$plugin_instance->blacklist) {
            return $buffer;
        }
        
        $hidden_notices = $plugin_instance->blacklist->get_hidden_elements('notice', get_current_user_id());
        
        if (empty($hidden_notices)) {
            return $buffer;
        }
        
        // Apply filters to hide blacklisted notices
        foreach ($hidden_notices as $notice) {
            if (!empty($notice['element_selector'])) {
                // Use CSS to hide elements with specific selectors
                $buffer = $this->inject_hide_css($buffer, $notice['element_selector']);
            } elseif (!empty($notice['element_content'])) {
                // Hide notices with specific content
                $buffer = $this->hide_notice_by_content($buffer, $notice['element_content']);
            }
        }
        
        return $buffer;
    }
    
    /**
     * Inject CSS to hide elements
     */
    private function inject_hide_css($buffer, $selector) {
        $css = "<style>{$selector} { display: none !important; }</style>";
        
        // Insert CSS before closing head tag or at the beginning of body
        if (strpos($buffer, '</head>') !== false) {
            $buffer = str_replace('</head>', $css . '</head>', $buffer);
        } else {
            $buffer = $css . $buffer;
        }
        
        return $buffer;
    }
    
    /**
     * Hide notice by content matching
     */
    private function hide_notice_by_content($buffer, $content) {
        // Simple content matching - can be enhanced with regex
        if (strpos($buffer, $content) !== false) {
            // Find the notice div containing this content and hide it
            $pattern = '/<div[^>]*class="[^"]*notice[^"]*"[^>]*>.*?' . preg_quote($content, '/') . '.*?<\/div>/s';
            $replacement = '<div style="display: none !important;"><!-- Notice hidden by BD CleanDash --></div>';
            $buffer = preg_replace($pattern, $replacement, $buffer);
        }
        
        return $buffer;
    }
    
    /**
     * Get dashboard widget information
     */
    public function get_dashboard_widgets() {
        global $wp_meta_boxes;
        
        $widgets = array();
        
        if (!isset($wp_meta_boxes['dashboard'])) {
            return $widgets;
        }
        
        foreach ($wp_meta_boxes['dashboard'] as $context => $priorities) {
            foreach ($priorities as $priority => $widgets_data) {
                if (is_array($widgets_data)) {
                    foreach ($widgets_data as $widget_id => $widget_data) {
                        $widgets[$widget_id] = array(
                            'id' => $widget_id,
                            'title' => $widget_data['title'] ?? $widget_id,
                            'context' => $context,
                            'priority' => $priority
                        );
                    }
                }
            }
        }
        
        return $widgets;
    }
    
    /**
     * Get current page notices (for AJAX detection)
     */
    public function get_current_notices() {
        // Capture current notices
        ob_start();
        do_action('admin_notices');
        $notices_html = ob_get_clean();
        
        // Parse notices and return structured data
        $notices = array();
        
        // Use DOMDocument to parse HTML
        if (!empty($notices_html)) {
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $notices_html);
            libxml_clear_errors();
            
            $notice_elements = $dom->getElementsByTagName('div');
            foreach ($notice_elements as $element) {
                if (strpos($element->getAttribute('class'), 'notice') !== false) {
                    $notices[] = array(
                        'html' => $dom->saveHTML($element),
                        'content' => trim($element->textContent),
                        'classes' => $element->getAttribute('class'),
                        'id' => $element->getAttribute('id')
                    );
                }
            }
        }
        
        return $notices;
    }
    
    /**
     * Check if element should be hidden
     */
    public function should_hide_element($element_type, $element_id, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $plugin_instance = BD_CleanDash::instance();
        if (!$plugin_instance || !$plugin_instance->blacklist) {
            return false;
        }
        
        return $plugin_instance->blacklist->is_blacklisted($element_type, $element_id, $user_id);
    }
}
