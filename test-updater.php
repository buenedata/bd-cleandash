<?php
/**
 * Test script for BD Plugin Updater
 * Kjør denne i WordPress admin for å teste update systemet
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo '<h2>BD CleanDash Updater Test</h2>';

// Include updater
require_once plugin_dir_path(__FILE__) . 'includes/class-bd-updater.php';

// Create updater instance
$updater = new BD_CleanDash_Updater(
    plugin_dir_path(__FILE__) . 'bd-cleandash.php',
    'buenedata',
    'bd-cleandash'
);

// Test GitHub API connection
echo '<h3>GitHub API Test</h3>';
$api_url = 'https://api.github.com/repos/buenedata/bd-cleandash/releases/latest';
$response = wp_remote_get($api_url);

if (is_wp_error($response)) {
    echo '<p style="color: red;">Error: ' . $response->get_error_message() . '</p>';
} else {
    $code = wp_remote_retrieve_response_code($response);
    echo '<p style="color: ' . ($code === 200 ? 'green' : 'red') . ';">HTTP Response Code: ' . $code . '</p>';
    
    if ($code === 200) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        echo '<p>Latest Release: ' . ($data['tag_name'] ?? 'Unknown') . '</p>';
        echo '<p>Published: ' . ($data['published_at'] ?? 'Unknown') . '</p>';
    }
}

// Clear update cache
$cache_key = 'bd_update_' . md5(plugin_basename(plugin_dir_path(__FILE__) . 'bd-cleandash.php'));
delete_transient($cache_key);
echo '<p>Update cache cleared.</p>';

// Test current plugin version
if (!function_exists('get_plugin_data')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugin_data = get_plugin_data(plugin_dir_path(__FILE__) . 'bd-cleandash.php');
echo '<h3>Current Plugin Info</h3>';
echo '<p>Plugin Name: ' . $plugin_data['Name'] . '</p>';
echo '<p>Current Version: ' . $plugin_data['Version'] . '</p>';
echo '<p>Plugin Basename: ' . plugin_basename(plugin_dir_path(__FILE__) . 'bd-cleandash.php') . '</p>';

echo '<p><a href="' . admin_url('plugins.php') . '">Go to Plugins Page</a></p>';
echo '<p><a href="' . admin_url('update-core.php') . '">Check for Updates</a></p>';