<?php
/**
 * BD CleanDash Admin Page Template
 *
 * @package BD_CleanDash
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$enabled = get_option('bd_cleandash_enabled', '1');
$show_buttons = get_option('bd_cleandash_show_buttons', '1');
$persistent_mode = get_option('bd_cleandash_persistent_mode', '1');
$auto_hide_new = get_option('bd_cleandash_auto_hide_new', '0');
$user_override = get_option('bd_cleandash_user_override', '1');
$hide_admin_bar = get_option('bd_cleandash_hide_admin_bar', '0');
$hide_admin_bar_frontend = get_option('bd_cleandash_hide_admin_bar_frontend', '0');
$hide_admin_bar_roles = get_option('bd_cleandash_hide_admin_bar_roles', array());
$hide_admin_bar_frontend_roles = get_option('bd_cleandash_hide_admin_bar_frontend_roles', array());
$plugin_language = get_option('bd_cleandash_language', 'no');

// Get all user roles
global $wp_roles;
if (!isset($wp_roles)) {
    $wp_roles = new WP_Roles();
}
$all_roles = $wp_roles->get_names();

// Get statistics with safety check
$stats = array('total' => 0, 'notices' => 0, 'widgets' => 0, 'global' => 0);
$plugin_instance = BD_CleanDash::instance();
if ($plugin_instance && $plugin_instance->blacklist) {
    $stats = $plugin_instance->blacklist->get_statistics();
}
?>

<div class="wrap bd-cleandash-admin">
    <div class="bd-header">
        <div class="bd-logo">
            <span class="bd-icon">🧹</span>
        </div>
        <div class="bd-header-text">
            <h1 class="gradient-text"><?php bd_e('BD CleanDash'); ?></h1>
            <p><?php bd_e('Hold dashbordet ditt rent og ryddig'); ?></p>
        </div>
        <div class="bd-header-actions">
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="bd-language-form" style="display: inline-block; margin-right: 12px;">
                <?php wp_nonce_field('bd_cleandash_settings'); ?>
                <input type="hidden" name="action" value="bd_cleandash_save_settings">
                <input type="hidden" name="bd_cleandash_enabled" value="<?php echo $enabled; ?>">
                <input type="hidden" name="bd_cleandash_show_buttons" value="<?php echo $show_buttons; ?>">
                <input type="hidden" name="bd_cleandash_persistent_mode" value="<?php echo $persistent_mode; ?>">
                <input type="hidden" name="bd_cleandash_auto_hide_new" value="<?php echo $auto_hide_new; ?>">
                <input type="hidden" name="bd_cleandash_user_override" value="<?php echo $user_override; ?>">
                <input type="hidden" name="bd_cleandash_hide_admin_bar" value="<?php echo $hide_admin_bar; ?>">
                <input type="hidden" name="bd_cleandash_hide_admin_bar_frontend" value="<?php echo $hide_admin_bar_frontend; ?>">
                <?php foreach ($hide_admin_bar_roles as $role): ?>
                    <input type="hidden" name="bd_cleandash_hide_admin_bar_roles[]" value="<?php echo esc_attr($role); ?>">
                <?php endforeach; ?>
                <?php foreach ($hide_admin_bar_frontend_roles as $role): ?>
                    <input type="hidden" name="bd_cleandash_hide_admin_bar_frontend_roles[]" value="<?php echo esc_attr($role); ?>">
                <?php endforeach; ?>
                
                <select name="bd_cleandash_language" id="bd-language-selector" class="button bd-button" onchange="this.form.submit()">
                    <option value="no" <?php selected($plugin_language, 'no'); ?>>🇳🇴 Norsk</option>
                    <option value="en" <?php selected($plugin_language, 'en'); ?>>🇺🇸 English</option>
                </select>
            </form>
            
            <button id="bd-quick-toggle" class="button bd-button <?php echo $enabled === '1' ? 'active' : ''; ?>">
                <span class="toggle-text">
                    <?php echo $enabled === '1' ? bd__('Aktivert') : bd__('Deaktivert'); ?>
                </span>
            </button>
        </div>
    </div>

    <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated']): ?>
        <div class="notice notice-success is-dismissible bd-notice">
            <p><?php bd_e('Innstillinger lagret!'); ?></p>
        </div>
    <?php endif; ?>

    <div class="bd-admin-container">
        <div class="bd-tabs">
            <nav class="bd-tab-nav">
                <button class="bd-tab-button active" data-tab="dashboard">
                    <span class="tab-icon">📊</span>
                    <?php bd_e('Dashboard'); ?>
                </button>
                <button class="bd-tab-button" data-tab="blacklist">
                    <span class="tab-icon">🚫</span>
                    <?php bd_e('Svarteliste'); ?>
                </button>
                <button class="bd-tab-button" data-tab="users">
                    <span class="tab-icon">👥</span>
                    <?php bd_e('Brukere'); ?>
                </button>
                <button class="bd-tab-button" data-tab="tools">
                    <span class="tab-icon">🔧</span>
                    <?php bd_e('Verktøy'); ?>
                </button>
            </nav>

            <!-- Dashboard Tab -->
            <div class="bd-tab-content active" id="tab-dashboard">
                <div class="bd-grid">
                    <!-- Settings Card -->
                    <div class="bd-card">
                        <div class="bd-card-header">
                            <h3><?php bd_e('Hovedinnstillinger'); ?></h3>
                            <p><?php bd_e('Konfigurer grunnleggende oppførsel for dashboard-opprydding'); ?></p>
                        </div>
                        <div class="bd-card-body">
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                                <?php wp_nonce_field('bd_cleandash_settings'); ?>
                                <input type="hidden" name="action" value="bd_cleandash_save_settings">
                                
                                <div class="bd-form-group">
                                    <label class="bd-toggle">
                                        <input type="checkbox" name="bd_cleandash_enabled" value="1" <?php checked($enabled, '1'); ?>>
                                        <span class="bd-toggle-slider"></span>
                                        <span class="bd-toggle-label"><?php bd_e('Aktiver dashboard-opprydding'); ?></span>
                                    </label>
                                    <p class="description"><?php bd_e('Hovedbryter for å aktivere eller deaktivere alle oppryddingsfunksjoner.'); ?></p>
                                </div>

                                <div class="bd-form-group">
                                    <label class="bd-toggle">
                                        <input type="checkbox" name="bd_cleandash_show_buttons" value="1" <?php checked($show_buttons, '1'); ?>>
                                        <span class="bd-toggle-slider"></span>
                                        <span class="bd-toggle-label"><?php bd_e('Vis skjul-knapper'); ?></span>
                                    </label>
                                    <p class="description"><?php bd_e('Vis "Skjul"-knapper på varsler og widgets for enkel skjuling.'); ?></p>
                                </div>

                                <div class="bd-form-group">
                                    <label class="bd-toggle">
                                        <input type="checkbox" name="bd_cleandash_persistent_mode" value="1" <?php checked($persistent_mode, '1'); ?>>
                                        <span class="bd-toggle-slider"></span>
                                        <span class="bd-toggle-label"><?php bd_e('Vedvarende modus'); ?></span>
                                    </label>
                                    <p class="description"><?php bd_e('Husk skjulte elementer på tvers av økter.'); ?></p>
                                </div>

                                <div class="bd-form-group">
                                    <label class="bd-toggle">
                                        <input type="checkbox" name="bd_cleandash_auto_hide_new" value="1" <?php checked($auto_hide_new, '1'); ?>>
                                        <span class="bd-toggle-slider"></span>
                                        <span class="bd-toggle-label"><?php bd_e('Auto-skjul nye elementer'); ?></span>
                                    </label>
                                    <p class="description"><?php bd_e('Skjul automatisk nye elementer som matcher svarteliste-regler.'); ?></p>
                                </div>

                                <div class="bd-form-group">
                                    <label class="bd-toggle">
                                        <input type="checkbox" name="bd_cleandash_user_override" value="1" <?php checked($user_override, '1'); ?>>
                                        <span class="bd-toggle-slider"></span>
                                        <span class="bd-toggle-label"><?php bd_e('Tillat bruker-overstyring'); ?></span>
                                    </label>
                                    <p class="description"><?php bd_e('La brukere overstyre globale innstillinger med egne preferanser.'); ?></p>
                                </div>

                                <!-- Admin Bar Settings Section -->
                                <div class="bd-form-section">
                                    <h4 class="bd-section-title"><?php bd_e('Admin-bar innstillinger'); ?></h4>
                                    <p class="bd-section-description"><?php bd_e('Kontroller visning av admin-toolbar i backend og frontend'); ?></p>
                                    
                                    <div class="bd-form-group">
                                        <label class="bd-toggle">
                                            <input type="checkbox" name="bd_cleandash_hide_admin_bar" value="1" <?php checked($hide_admin_bar, '1'); ?>>
                                            <span class="bd-toggle-slider"></span>
                                            <span class="bd-toggle-label"><?php bd_e('Skjul admin-bar i backend'); ?></span>
                                        </label>
                                        <p class="description"><?php bd_e('Skjul admin-toolbar øverst på admin-sider for en renere opplevelse.'); ?></p>
                                        
                                        <div class="bd-role-selection">
                                            <p class="description bd-roles-label"><strong><?php bd_e('Gjelder for roller:'); ?></strong></p>
                                            <div class="bd-roles-grid">
                                                <?php foreach ($all_roles as $role_key => $role_name): ?>
                                                    <label class="bd-role-checkbox">
                                                        <input type="checkbox" name="bd_cleandash_hide_admin_bar_roles[]" value="<?php echo esc_attr($role_key); ?>" 
                                                            <?php checked(in_array($role_key, $hide_admin_bar_roles)); ?>>
                                                        <?php echo esc_html($role_name); ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bd-form-group">
                                        <label class="bd-toggle">
                                            <input type="checkbox" name="bd_cleandash_hide_admin_bar_frontend" value="1" <?php checked($hide_admin_bar_frontend, '1'); ?>>
                                            <span class="bd-toggle-slider"></span>
                                            <span class="bd-toggle-label"><?php bd_e('Skjul admin-bar på frontend'); ?></span>
                                        </label>
                                        <p class="description"><?php bd_e('Skjul admin-toolbar på frontend (offentlige sider) når du er innlogget.'); ?></p>
                                        
                                        <div class="bd-role-selection">
                                            <p class="description bd-roles-label"><strong><?php bd_e('Gjelder for roller:'); ?></strong></p>
                                            <div class="bd-roles-grid">
                                                <?php foreach ($all_roles as $role_key => $role_name): ?>
                                                    <label class="bd-role-checkbox">
                                                        <input type="checkbox" name="bd_cleandash_hide_admin_bar_frontend_roles[]" value="<?php echo esc_attr($role_key); ?>" 
                                                            <?php checked(in_array($role_key, $hide_admin_bar_frontend_roles)); ?>>
                                                        <?php echo esc_html($role_name); ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bd-form-actions">
                                    <button type="submit" class="button button-primary bd-button">
                                        <?php bd_e('Lagre innstillinger'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Statistics Card -->
                    <div class="bd-card">
                        <div class="bd-card-header">
                            <h3><?php bd_e('Statistikk'); ?></h3>
                            <p><?php bd_e('Oversikt over skjulte elementer'); ?></p>
                        </div>
                        <div class="bd-card-body">
                            <div class="bd-stats-grid">
                                <div class="bd-stat">
                                    <div class="bd-stat-number"><?php echo $stats['total']; ?></div>
                                    <div class="bd-stat-label"><?php bd_e('Totalt skjulte'); ?></div>
                                </div>
                                <div class="bd-stat">
                                    <div class="bd-stat-number"><?php echo $stats['notices']; ?></div>
                                    <div class="bd-stat-label"><?php bd_e('Varsler'); ?></div>
                                </div>
                                <div class="bd-stat">
                                    <div class="bd-stat-number"><?php echo $stats['widgets']; ?></div>
                                    <div class="bd-stat-label"><?php bd_e('Widgets'); ?></div>
                                </div>
                                <div class="bd-stat">
                                    <div class="bd-stat-number"><?php echo $stats['global']; ?></div>
                                    <div class="bd-stat-label"><?php bd_e('Globale'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blacklist Tab -->
            <div class="bd-tab-content" id="tab-blacklist">
                <div class="bd-card">
                    <div class="bd-card-header">
                        <h3><?php bd_e('Svarteliste-administrasjon'); ?></h3>
                        <p><?php bd_e('Administrer skjulte elementer og regler'); ?></p>
                    </div>
                    <div class="bd-card-body">
                        <div id="blacklist-table-container">
                            <table id="blacklist-table" class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php bd_e('Type'); ?></th>
                                        <th><?php bd_e('Element ID'); ?></th>
                                        <th><?php bd_e('Bruker'); ?></th>
                                        <th><?php bd_e('Opprettet'); ?></th>
                                        <th><?php bd_e('Handlinger'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="bd-loading-text"><?php bd_e('Laster svarteliste...'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="bd-tab-content" id="tab-users">
                <div class="bd-card">
                    <div class="bd-card-header">
                        <h3><?php bd_e('Bruker- og rolleinnstillinger'); ?></h3>
                        <p><?php bd_e('Konfigurer innstillinger per bruker og rolle'); ?></p>
                    </div>
                    <div class="bd-card-body">
                        <div id="users-settings-container">
                            <!-- User settings will be loaded via AJAX -->
                            <div class="bd-loading">
                                <span><?php bd_e('Laster brukerinnstillinger...'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tools Tab -->
            <div class="bd-tab-content" id="tab-tools">
                <div class="bd-grid">
                    <!-- GitHub Updates Card -->
                    <div class="bd-card">
                        <div class="bd-card-header">
                            <h3><?php bd_e('GitHub Oppdateringer'); ?></h3>
                            <p><?php bd_e('Administrer automatiske oppdateringer fra GitHub'); ?></p>
                        </div>
                        <div class="bd-card-body">
                            <div class="bd-form-group">
                                <label><?php bd_e('Nåværende versjon'); ?></label>
                                <p><strong>v<?php echo BD_CLEANDASH_VERSION; ?></strong></p>
                            </div>
                            
                            <div class="bd-form-group">
                                <label><?php bd_e('GitHub Repository'); ?></label>
                                <p><a href="https://github.com/buenedata/bd-cleandash" target="_blank">buenedata/bd-cleandash</a></p>
                            </div>
                            
                            <div class="bd-form-group">
                                <button id="check-github-updates" class="button bd-button">
                                    <?php bd_e('Sjekk etter oppdateringer'); ?>
                                </button>
                                <p class="description"><?php bd_e('Tvinger en sjekk etter nye versjoner på GitHub.'); ?></p>
                            </div>
                            
                            <div class="bd-form-group">
                                <label><?php bd_e('Status'); ?></label>
                                <div id="github-update-status">
                                    <p><?php bd_e('Klikk "Sjekk etter oppdateringer" for å se status.'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="bd-card">
                        <div class="bd-card-header">
                            <h3><?php bd_e('Import/Export'); ?></h3>
                            <p><?php bd_e('Sikkerhetskopier og gjenopprett svarteliste-konfigurasjoner'); ?></p>
                        </div>
                        <div class="bd-card-body">
                            <div class="bd-form-group">
                                <button id="export-blacklist" class="button bd-button">
                                    <?php bd_e('Eksporter svarteliste'); ?>
                                </button>
                                <p class="description"><?php bd_e('Last ned en JSON-fil med all svarteliste-data.'); ?></p>
                            </div>
                            
                            <div class="bd-form-group">
                                <label for="import-file"><?php bd_e('Importer svarteliste'); ?></label>
                                <input type="file" id="import-file" accept=".json" class="bd-file-input">
                                <button id="import-blacklist" class="button bd-button" disabled>
                                    <?php bd_e('Importer'); ?>
                                </button>
                                <p class="description"><?php bd_e('Last opp en JSON-fil for å importere svarteliste-data.'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bd-card">
                        <div class="bd-card-header">
                            <h3><?php bd_e('Tilbakestill'); ?></h3>
                            <p><?php bd_e('Tilbakestill plugin-data og innstillinger'); ?></p>
                        </div>
                        <div class="bd-card-body">
                            <div class="bd-form-group">
                                <button id="clear-user-blacklist" class="button bd-button">
                                    <?php bd_e('Tøm min svarteliste'); ?>
                                </button>
                                <p class="description"><?php bd_e('Fjern alle dine personlige skjulte elementer.'); ?></p>
                            </div>
                            
                            <?php if (current_user_can('manage_options')): ?>
                            <div class="bd-form-group">
                                <button id="clear-all-blacklists" class="button bd-button">
                                    <?php bd_e('Tøm alle svartelister'); ?>
                                </button>
                                <p class="description"><?php bd_e('Fjern alle svartelister for alle brukere. Kan ikke angres.'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Debug script to check jQuery and admin.js loading
console.log('BD CleanDash: Inline debug script running');
console.log('BD CleanDash: jQuery available?', typeof jQuery !== 'undefined');
console.log('BD CleanDash: $ available?', typeof $ !== 'undefined');

// Check if admin script is loaded
const adminScripts = document.querySelectorAll('script[src*="bd-cleandash"]');
console.log('BD CleanDash: Found admin scripts:', adminScripts.length);
adminScripts.forEach((script, index) => {
    console.log(`BD CleanDash: Script ${index + 1}:`, script.src);
    console.log(`BD CleanDash: Script ${index + 1} loaded:`, script.readyState || 'unknown');
});

// Check if bdCleanDashAdmin localized data exists
if (typeof bdCleanDashAdmin !== 'undefined') {
    console.log('BD CleanDash: Localized data found:', bdCleanDashAdmin);
} else {
    console.log('BD CleanDash: Localized data NOT found');
}

// Fallback system for blacklist loading if main admin.js fails
setTimeout(function() {
    console.log('BD CleanDash: Checking if admin object exists after delay...');
    if (typeof window.BDCleanDashAdmin !== 'undefined') {
        console.log('BD CleanDash: Admin object found!');
    } else {
        console.log('BD CleanDash: Admin object NOT found - attempting fallback');
        
        if (typeof jQuery !== 'undefined') {
            console.log('BD CleanDash: Running fallback blacklist loader');
            // Fallback: manually load blacklist data if admin.js failed
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'bd_get_blacklist',
                    nonce: '<?php echo wp_create_nonce('bd_cleandash_admin_nonce'); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    console.log('BD CleanDash: Fallback AJAX response:', response);
                    if (response.success && response.data) {
                        const blacklistTable = document.querySelector('#blacklist-table tbody');
                        if (blacklistTable) {
                            blacklistTable.innerHTML = '';
                            if (response.data.length === 0) {
                                blacklistTable.innerHTML = '<tr><td colspan="5"><?php bd_e('Ingen elementer i svartelisten'); ?></td></tr>';
                            } else {
                                response.data.forEach(function(item) {
                                    const row = document.createElement('tr');
                                    // Use user_name preferentially, then fall back to user_display_name, user_login, or default
                                    const userName = item.user_name || item.user_display_name || item.user_login || 'Bruker #' + item.user_id;
                                    console.log('BD CleanDash: Fallback using username:', userName, 'from item:', item);
                                    
                                    row.innerHTML = '<td>' + item.element_type + '</td>' +
                                                  '<td>' + item.element_id + '</td>' +
                                                  '<td>' + userName + '</td>' +
                                                  '<td>' + item.created_at + '</td>' +
                                                  '<td><button class="button button-secondary bd-button bd-button-danger" onclick="removeBlacklistItem(' + item.id + ')">🗑️ Fjern</button></td>';
                                    blacklistTable.appendChild(row);
                                });
                            }
                        }
                    } else {
                        console.log('BD CleanDash: Fallback AJAX response indicated failure');
                        const blacklistTable = document.querySelector('#blacklist-table tbody');
                        if (blacklistTable) {
                            blacklistTable.innerHTML = '<tr><td colspan="5"><?php bd_e('Feil ved lasting av svarteliste'); ?></td></tr>';
                        }
                    }
                },
                error: function() {
                    console.log('BD CleanDash: Fallback AJAX failed');
                    const blacklistTable = document.querySelector('#blacklist-table tbody');
                    if (blacklistTable) {
                        blacklistTable.innerHTML = '<tr><td colspan="5"><?php bd_e('Feil ved lasting av svarteliste'); ?></td></tr>';
                    }
                }
            });
        }
    }
}, 2000);

// Fallback function for removing blacklist items
function removeBlacklistItem(itemId) {
    if (typeof window.BDCleanDashAdmin !== 'undefined') {
        // Use the main admin object if available
        window.BDCleanDashAdmin.removeFromBlacklist(itemId);
    } else if (typeof jQuery !== 'undefined') {
        // Fallback implementation
        console.log('BD CleanDash: Fallback remove item:', itemId);
        if (confirm('Er du sikker på at du vil fjerne dette elementet?')) {
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'bd_remove_from_blacklist',
                    item_id: itemId,
                    nonce: '<?php echo wp_create_nonce('bd_cleandash_admin_nonce'); ?>'
                },
                dataType: 'json',
                success: function(response) {
                    console.log('BD CleanDash: Fallback remove response:', response);
                    if (response.success) {
                        // Reload the blacklist
                        location.reload();
                    } else {
                        alert('Feil ved fjerning: ' + (response.data || 'Ukjent feil'));
                    }
                },
                error: function() {
                    alert('Nettverksfeil');
                }
            });
        }
    }
}

// Add loading state to language selector
jQuery(document).ready(function($) {
    $('#bd-language-selector').on('change', function() {
        const selector = $(this);
        const originalHtml = selector.html();
        
        // Show loading state
        selector.html('<option>🔄 <?php echo $plugin_language === 'en' ? 'Changing...' : 'Endrer...'; ?></option>').prop('disabled', true);
        
        // Form will auto-submit via onchange attribute
    });
});
</script>
