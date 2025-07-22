<?php
/**
 * Buene Data Admin Menu Helper
 * Plasser denne filen i /includes/ i alle BD plugins.
 */

if (!function_exists('bd_add_buene_data_menu')) {
    function bd_add_buene_data_menu($submenu_name, $submenu_slug, $submenu_callback, $emoji = '🎨') {
        add_action('admin_menu', function() use ($submenu_name, $submenu_slug, $submenu_callback, $emoji) {
            global $menu;
            $bd_menu_exists = false;
            foreach ($menu as $menu_item) {
                if (isset($menu_item[2]) && $menu_item[2] === 'buene-data') {
                    $bd_menu_exists = true;
                    break;
                }
            }
            // Opprett hovedmeny hvis ikke finnes (callback til oversiktsside)
            if (!$bd_menu_exists) {
                add_menu_page(
                    __('Buene Data', 'buene-data'),
                    __('Buene Data', 'buene-data'),
                    'manage_options',
                    'buene-data',
                    'bd_buene_data_overview_page',
                    'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 2L3 7V18H7V14H13V18H17V7L10 2Z" fill="currentColor"/></svg>'),
                    58.5
                );
            }
            // Legg til undermeny for plugin
            add_submenu_page(
                'buene-data',
                $emoji . ' ' . $submenu_name,
                $emoji . ' ' . $submenu_name,
                'manage_options',
                $submenu_slug,
                $submenu_callback
            );
        });
    }
}

// Denne callbacken lager oversiktssiden (bare én plugin trenger å ha denne!)
if (!function_exists('bd_buene_data_overview_page')) {
    function bd_buene_data_overview_page() {
        // Finn alle BD-plugins i plugins-mappen
        $plugins = get_plugins();
        $bd_plugins = [];
        foreach ($plugins as $plugin_file => $data) {
            if (
                isset($data['Author']) &&
                (stripos($data['Author'], 'Buene Data') !== false || stripos($data['PluginURI'], 'buenedata') !== false)
            ) {
                $is_active = is_plugin_active($plugin_file);
                $bd_plugins[] = [
                    'Name'        => $data['Name'],
                    'Description' => $data['Description'],
                    'Version'     => $data['Version'],
                    'PluginURI'   => $data['PluginURI'],
                    'File'        => $plugin_file,
                    'Active'      => $is_active,
                    'Title'       => $data['Title'] ?? $data['Name'],
                    'Emoji'       => (
                        stripos($data['Name'], 'CleanDash') !== false ? '🧹' :
                        (stripos($data['Name'], 'Client Suite') !== false ? '🎨' : '🔧')
                    ),
                    'LastUpdated' => file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)
                        ? date("d.m.Y H:i", filemtime(WP_PLUGIN_DIR . '/' . $plugin_file))
                        : '',
                ];
            }
        }
        // BD Branding
        $bd_contact = '<div class="bd-contact" style="text-align:center; margin-top:40px;">
            <strong>Buene Data</strong> &nbsp;|&nbsp; <a href="https://buenedata.no" target="_blank">buenedata.no</a> &nbsp;|&nbsp; <a href="mailto:support@buenedata.no">support@buenedata.no</a>
        </div>';

        // Output oversiktsside
        echo '<div class="wrap bd-overview">';
        echo '<div class="bd-overview-header" style="margin-bottom:30px;">
                <div>
                    <h1 class="gradient-text" style="margin-bottom:12px;font-size:2.4em; font-weight:700;">Buene Data Plugin Suite</h1>
                    <p style="font-size:16px;color:#374151;">Profesjonelle WordPress-verktøy for moderne byrå og nettsteder</p>
                </div>
                <div><img src="https://buenedata.no/wp-content/uploads/2023/11/logo-buene-data-dark.svg" alt="Buene Data" height="56"></div>
            </div>';
        // GRID
        echo '<div class="bd-settings-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:32px;">';
        foreach ($bd_plugins as $plugin) {
            $card_gradient = $plugin['Emoji'] === '🧹'
                ? 'linear-gradient(135deg,#667eea 0%,#764ba2 100%)'
                : ($plugin['Emoji'] === '🎨'
                    ? 'linear-gradient(135deg,#5a67d8 0%,#6b46c1 100%)'
                    : 'linear-gradient(135deg,#f59e0b 0%,#d97706 100%)');

            echo '<div class="bd-plugin-card bd-hover-lift" style="background:#fff;border-radius:20px;box-shadow:0 8px 30px rgba(0,0,0,0.08);border:1px solid #e2e8f0;position:relative;overflow:hidden;padding:38px 30px;transition:all .3s;">
                    <div style="font-size:38px;margin-bottom:12px;">' . $plugin['Emoji'] . '</div>
                    <h2 class="gradient-text" style="font-size:1.4em;font-weight:700; margin-bottom:8px;">' . esc_html($plugin['Name']) . '</h2>
                    <p style="color:#64748b;font-size:14px; min-height:38px; margin-bottom:15px;">' . esc_html($plugin['Description']) . '</p>
                    <div style="margin-bottom:12px;">
                        <span class="bd-label" style="background:' . ($plugin['Active'] ? '#10b981' : '#e5e7eb') . ';color:' . ($plugin['Active'] ? 'white' : '#6b7280') . ';padding:3px 14px;border-radius:14px; font-size:12px;font-weight:600;letter-spacing:.5px;">' . ($plugin['Active'] ? 'Aktiv' : 'Ikke aktiv') . '</span>
                        <span style="color:#64748b;font-size:12px; margin-left:10px;">Versjon ' . esc_html($plugin['Version']) . '</span>
                    </div>
                    <div style="margin-bottom:14px;"><span style="color:#64748b;font-size:12px;">Sist oppdatert: ' . esc_html($plugin['LastUpdated']) . '</span></div>';
            if ($plugin['Active']) {
                $slug = explode('/', $plugin['File'])[0];
                $url = admin_url('admin.php?page=' . $slug);
                echo '<a href="' . esc_url($url) . '" class="button button-primary" style="background:' . $card_gradient . ';color:white;border:none;border-radius:7px;padding:8px 24px;font-weight:600;font-size:14px;box-shadow:0 1px 3px rgba(14,165,233,0.12);transition:all .2s;">Åpne innstillinger</a>';
            } else {
                echo '<span class="button" style="background:#f1f5f9;color:#a0aec0;border:none;border-radius:7px;padding:8px 24px;font-weight:600;font-size:14px;">Ikke aktivert</span>';
            }
            if (!empty($plugin['PluginURI'])) {
                echo '<div style="margin-top:18px;"><a href="' . esc_url($plugin['PluginURI']) . '" target="_blank" style="font-size:13px;color:#0ea5e9;">Se dokumentasjon &rarr;</a></div>';
            }
            echo '</div>';
        }
        echo '</div>'; // grid
        echo $bd_contact;
        echo '</div>'; // wrap

        // Inline CSS – kan evt flyttes til egen .css-fil for større prosjekt
        ?>
        <style>
        .bd-overview { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); padding: 32px 0; min-height: 100vh;}
        .gradient-text { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
        .bd-plugin-card:hover { transform: translateY(-8px); box-shadow: 0 15px 50px rgba(0,0,0,0.12);}
        @media (max-width: 800px) { .bd-settings-grid { grid-template-columns:1fr !important; } .bd-plugin-card { padding:24px 12px;}}
        .bd-label {display: inline-block; padding: 4px 10px; border-radius: 14px; font-size: 12px; font-weight: 500;}
        .button-primary:focus { outline: 2px solid #0ea5e9; }
        </style>
        <?php
    }
}
