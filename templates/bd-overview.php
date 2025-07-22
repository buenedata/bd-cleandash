<?php
/**
 * BD Overview Page Template
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Helper function to render plugin card
function render_bd_plugin_card($plugin) {
    $status_class = '';
    $status_text = '';
    $button_text = '';
    $button_disabled = false;
    $button_url = '';
    
    switch ($plugin['status']) {
        case 'active':
            $status_class = 'status-active';
            $status_text = bd__('Aktiv');
            
            // Determine button text and URL based on available admin page
            if (!empty($plugin['admin_url'])) {
                $button_text = bd__('Konfigurer');
                $button_url = $plugin['admin_url'];
            } elseif (!empty($plugin['settings_url'])) {
                $button_text = bd__('Innstillinger');
                $button_url = $plugin['settings_url'];
            } elseif (!empty($plugin['docs_url'])) {
                $button_text = bd__('Dokumentasjon');
                $button_url = $plugin['docs_url'];
            } else {
                $button_text = bd__('Vis detaljer');
                $button_url = admin_url('plugins.php');
            }
            break;
        case 'inactive':
            $status_class = 'status-inactive';
            $status_text = bd__('Inaktiv');
            $button_text = bd__('Aktiver');
            
            // Create activation URL if plugin file is available
            if (!empty($plugin['plugin_file'])) {
                $button_url = wp_nonce_url(
                    admin_url('plugins.php?action=activate&plugin=' . urlencode($plugin['plugin_file'])),
                    'activate-plugin_' . $plugin['plugin_file']
                );
            } else {
                $button_text = bd__('Gå til plugins');
                $button_url = admin_url('plugins.php');
            }
            break;
        case 'not-installed':
            $status_class = 'status-not-installed';
            $status_text = bd__('Ikke installert');
            
            // Determine button based on available options
            if (!empty($plugin['download_url'])) {
                $button_text = bd__('Last ned');
                $button_url = $plugin['download_url'];
            } elseif (!empty($plugin['wordpress_org_url'])) {
                $button_text = bd__('Installer fra WordPress.org');
                $button_url = $plugin['wordpress_org_url'];
            } elseif (!empty($plugin['docs_url'])) {
                $button_text = bd__('Les mer');
                $button_url = $plugin['docs_url'];
            } else {
                $button_text = bd__('Kommer snart');
                $button_disabled = true;
                $button_url = '#';
            }
            break;
        case 'coming-soon':
        default:
            $status_class = 'status-coming-soon';
            $status_text = bd__('Kommer snart');
            $button_text = bd__('Kommer snart');
            $button_disabled = true;
            break;
    }
    
    $card_class = 'bd-plugin-card';
    if ($plugin['status'] === 'active') {
        $card_class .= ' active';
    } elseif ($plugin['status'] === 'coming-soon') {
        $card_class .= ' placeholder';
    } elseif ($plugin['status'] === 'inactive') {
        $card_class .= ' inactive';
    } elseif ($plugin['status'] === 'not-installed') {
        $card_class .= ' not-installed';
    }
    
    if (!empty($plugin['auto_detected'])) {
        $card_class .= ' auto-detected';
    }
    ?>
    <div class="<?php echo esc_attr($card_class); ?>">
        <div class="bd-plugin-icon"><?php echo esc_html($plugin['icon']); ?></div>
        <h3><?php echo esc_html($plugin['name']); ?></h3>
        <p><?php echo esc_html($plugin['description']); ?></p>
        <div class="bd-plugin-status">
            <span class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span>
            <?php if (!empty($plugin['version']) && $plugin['status'] !== 'coming-soon'): ?>
                <span class="version">v<?php echo esc_html($plugin['version']); ?></span>
            <?php elseif (!empty($plugin['estimated_release'])): ?>
                <span class="version"><?php echo esc_html($plugin['estimated_release']); ?></span>
            <?php endif; ?>
        </div>
        <?php if ($button_disabled): ?>
            <button class="button bd-button" disabled>
                <?php echo esc_html($button_text); ?>
            </button>
        <?php else: ?>
            <a href="<?php echo esc_url($button_url); ?>" class="button <?php echo $plugin['status'] === 'active' ? 'button-primary' : ''; ?> bd-button">
                <?php echo esc_html($button_text); ?>
            </a>
        <?php endif; ?>
    </div>
    <?php
}
?>

<div class="wrap bd-cleandash-admin bd-overview">
    <div class="bd-header">
        <div class="bd-logo">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 4L6 14V36H14V28H26V36H34V14L20 4Z" fill="url(#gradient1)"/>
                <defs>
                    <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <div class="bd-header-text">
            <h1 class="gradient-text"><?php bd_e('Buene Data'); ?></h1>
            <p><?php bd_e('Profesjonelle WordPress-løsninger'); ?></p>
        </div>
    </div>
    
    <div class="bd-plugins-grid">
        <?php 
        $all_plugins = array();
        
        // Add registered plugins from registry (already sorted)
        if (!empty($plugins)) {
            $all_plugins = array_merge($all_plugins, $plugins);
        }
        
        // Add auto-detected plugins (they will be sorted by status)
        if (!empty($auto_detected)) {
            foreach ($auto_detected as $plugin) {
                $all_plugins[] = $plugin;
            }
        }
        
        // Re-sort all plugins to ensure proper order: active, inactive, not-installed, coming-soon
        if (!empty($all_plugins)): 
            usort($all_plugins, function($a, $b) {
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
            
            // Render all plugins in sorted order
            $card_index = 0;
            foreach ($all_plugins as $plugin): 
                $card_index++;
                ?>
                <div class="bd-plugin-card-wrapper" data-card-index="<?php echo $card_index; ?>">
                    <?php render_bd_plugin_card($plugin); ?>
                </div>
                <?php 
            endforeach;
        else: ?>
            <!-- Fallback: Show only CleanDash if no plugins detected -->
            <div class="bd-plugin-card-wrapper" data-card-index="1">
                <div class="bd-plugin-card active">
                    <div class="bd-plugin-icon">🧹</div>
                    <h3><?php bd_e('BD CleanDash'); ?></h3>
                    <p><?php bd_e('Rydde opp i WordPress dashbordet ved å skjule uønskede varsler og widgets.'); ?></p>
                    <div class="bd-plugin-status">
                        <span class="status-active"><?php bd_e('Aktiv'); ?></span>
                        <span class="version">v<?php echo BD_CLEANDASH_VERSION; ?></span>
                    </div>
                    <a href="<?php echo admin_url('admin.php?page=bd-cleandash'); ?>" class="button button-primary bd-button">
                        <?php bd_e('Konfigurer'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bd-footer">
        <p>
            <?php printf(
                bd__('Trenger hjelp? Besøk <a href="%s" target="_blank">dokumentasjonen</a> eller <a href="%s" target="_blank">kontakt oss</a>.'),
                'https://buenedata.no/docs/',
                'https://buenedata.no/contact/'
            ); ?>
        </p>
    </div>
</div>
