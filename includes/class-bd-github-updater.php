<?php
/**
 * BD CleanDash GitHub Updater
 * 
 * Handles automatic updates from GitHub releases
 *
 * @package BD_CleanDash
 * @since 1.0.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * BD_GitHub_Updater class
 */
class BD_GitHub_Updater {
    
    /**
     * Plugin file path
     */
    private $plugin_file;
    
    /**
     * Plugin data
     */
    private $plugin_data;
    
    /**
     * GitHub username
     */
    private $username;
    
    /**
     * GitHub repository name
     */
    private $repository;
    
    /**
     * Plugin slug
     */
    private $plugin_slug;
    
    /**
     * GitHub access token
     */
    private $access_token;
    
    /**
     * Cache key for version check
     */
    private $cache_key;
    
    /**
     * Cache duration in seconds (12 hours)
     */
    private $cache_duration = 43200;
    
    /**
     * Constructor
     *
     * @param string $plugin_file Plugin file path
     * @param string $username GitHub username
     * @param string $repository GitHub repository name
     * @param string $access_token GitHub access token (optional)
     */
    public function __construct($plugin_file, $username = '', $repository = '', $access_token = '') {
        $this->plugin_file = $plugin_file;
        $this->plugin_data = get_plugin_data($plugin_file);
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->username = $username;
        $this->repository = $repository;
        $this->access_token = $access_token;
        $this->cache_key = 'bd_github_updater_' . md5($this->plugin_slug);
        
        // Parse GitHub URI from plugin header if not provided
        if (empty($this->username) || empty($this->repository)) {
            $this->parse_github_uri();
        }
        
        $this->init();
    }
    
    /**
     * Initialize updater
     */
    public function init() {
        if (is_admin()) {
            add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
            add_filter('plugins_api', array($this, 'plugin_info'), 10, 3);
            add_filter('upgrader_source_selection', array($this, 'upgrader_source_selection'), 10, 3);
        }
    }
    
    /**
     * Parse GitHub URI from plugin header
     */
    private function parse_github_uri() {
        if (isset($this->plugin_data['GitHub Plugin URI'])) {
            $uri_parts = explode('/', $this->plugin_data['GitHub Plugin URI']);
            if (count($uri_parts) >= 2) {
                $this->username = $uri_parts[0];
                $this->repository = $uri_parts[1];
            }
        }
    }
    
    /**
     * Check for plugin updates
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get remote version
        $remote_version = $this->get_remote_version();
        $local_version = $this->plugin_data['Version'];
        
        // Compare versions
        if (version_compare($local_version, $remote_version, '<')) {
            $download_url = $this->get_download_url();
            
            if ($download_url) {
                $transient->response[$this->plugin_slug] = (object) array(
                    'slug' => dirname($this->plugin_slug),
                    'new_version' => $remote_version,
                    'url' => $this->plugin_data['PluginURI'],
                    'package' => $download_url,
                    'tested' => $this->plugin_data['Tested up to'],
                    'requires_php' => $this->plugin_data['Requires PHP'],
                    'compatibility' => new stdClass(),
                );
            }
        }
        
        return $transient;
    }
    
    /**
     * Get plugin information for update popup
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }
        
        if (!isset($args->slug) || $args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }
        
        $remote_data = $this->get_remote_data();
        
        if (!$remote_data) {
            return $result;
        }
        
        return (object) array(
            'name' => $this->plugin_data['Name'],
            'slug' => dirname($this->plugin_slug),
            'version' => $remote_data->tag_name,
            'author' => $this->plugin_data['Author'],
            'author_profile' => $this->plugin_data['AuthorURI'],
            'homepage' => $this->plugin_data['PluginURI'],
            'short_description' => $this->plugin_data['Description'],
            'sections' => array(
                'description' => $this->get_description($remote_data),
                'changelog' => $this->get_changelog($remote_data),
            ),
            'download_link' => $this->get_download_url(),
            'requires' => $this->plugin_data['Requires at least'],
            'tested' => $this->plugin_data['Tested up to'],
            'requires_php' => $this->plugin_data['Requires PHP'],
            'last_updated' => $remote_data->published_at,
        );
    }
    
    /**
     * Get remote version from GitHub
     */
    private function get_remote_version() {
        $cached_version = get_transient($this->cache_key . '_version');
        
        if ($cached_version !== false) {
            return $cached_version;
        }
        
        $remote_data = $this->get_remote_data();
        
        if ($remote_data && isset($remote_data->tag_name)) {
            $version = ltrim($remote_data->tag_name, 'v');
            set_transient($this->cache_key . '_version', $version, $this->cache_duration);
            return $version;
        }
        
        return false;
    }
    
    /**
     * Get download URL for latest release
     */
    private function get_download_url() {
        $remote_data = $this->get_remote_data();
        
        if ($remote_data && isset($remote_data->zipball_url)) {
            return $remote_data->zipball_url;
        }
        
        return false;
    }
    
    /**
     * Get remote data from GitHub API
     */
    private function get_remote_data() {
        $cached_data = get_transient($this->cache_key . '_data');
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $api_url = "https://api.github.com/repos/{$this->username}/{$this->repository}/releases/latest";
        
        $args = array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'BD-CleanDash-Updater',
            ),
        );
        
        // Add authorization if access token is provided
        if (!empty($this->access_token)) {
            $args['headers']['Authorization'] = 'token ' . $this->access_token;
        }
        
        $response = wp_remote_get($api_url, $args);
        
        if (is_wp_error($response)) {
            error_log('BD CleanDash Updater Error: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('BD CleanDash Updater: Invalid JSON response');
            return false;
        }
        
        // Cache the response
        set_transient($this->cache_key . '_data', $data, $this->cache_duration);
        
        return $data;
    }
    
    /**
     * Get description from release data
     */
    private function get_description($remote_data) {
        if (isset($remote_data->body) && !empty($remote_data->body)) {
            return $this->markdown_to_html($remote_data->body);
        }
        
        return $this->plugin_data['Description'];
    }
    
    /**
     * Get changelog from release data
     */
    private function get_changelog($remote_data) {
        if (isset($remote_data->body) && !empty($remote_data->body)) {
            return '<h4>Version ' . $remote_data->tag_name . '</h4>' . $this->markdown_to_html($remote_data->body);
        }
        
        return '<h4>No changelog available</h4>';
    }
    
    /**
     * Simple markdown to HTML converter
     */
    private function markdown_to_html($markdown) {
        // Basic markdown conversion
        $html = $markdown;
        
        // Headers
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
        
        // Bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Line breaks
        $html = nl2br($html);
        
        return $html;
    }
    
    /**
     * Fix source selection for GitHub downloads
     */
    public function upgrader_source_selection($source, $remote_source, $upgrader) {
        if (!isset($upgrader->skin->plugin_info) || 
            $upgrader->skin->plugin_info['Name'] !== $this->plugin_data['Name']) {
            return $source;
        }
        
        // GitHub downloads come in a subfolder, move contents up one level
        $path_parts = pathinfo($source);
        $new_source = trailingslashit($path_parts['dirname']) . dirname($this->plugin_slug);
        
        if (is_dir($source)) {
            rename($source, $new_source);
            return $new_source;
        }
        
        return $source;
    }
    
    /**
     * Clear cache
     */
    public function clear_cache() {
        delete_transient($this->cache_key . '_version');
        delete_transient($this->cache_key . '_data');
    }
    
    /**
     * Force check for updates
     */
    public function force_check() {
        $this->clear_cache();
        delete_site_transient('update_plugins');
    }
}
