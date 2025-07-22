<?php
/**
 * BD CleanDash Language Management
 *
 * @package BD_CleanDash
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BD_Language {
    
    /**
     * Current language
     */
    private $current_language;
    
    /**
     * Available languages
     */
    private $languages = array(
        'no' => array(
            'name' => 'Norsk',
            'flag' => '🇳🇴',
            'locale' => 'nb_NO'
        ),
        'en' => array(
            'name' => 'English',
            'flag' => '🇺🇸',
            'locale' => 'en_US'
        )
    );
    
    /**
     * Translation strings
     */
    private $translations = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->current_language = get_option('bd_cleandash_language', 'en');
        $this->load_translations();
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'set_text_domain'));
        add_filter('bd_cleandash_translate', array($this, 'translate'), 10, 2);
    }
    
    /**
     * Set text domain based on selected language
     */
    public function set_text_domain() {
        if ($this->current_language === 'en') {
            // For English, we use the default strings without translation
            return;
        }
        
        // For Norwegian, load the standard WordPress text domain
        load_plugin_textdomain('bd-cleandash', false, dirname(plugin_basename(BD_CLEANDASH_FILE)) . '/languages/');
    }
    
    /**
     * Load all translations
     */
    private function load_translations() {
        $this->translations = array(
            'no' => array(
                // Main navigation
                'BD CleanDash' => 'BD CleanDash',
                'Hold dashbordet ditt rent og ryddig' => 'Hold dashbordet ditt rent og ryddig',
                'Dashboard' => 'Dashboard',
                'Svarteliste' => 'Svarteliste',
                'Brukere' => 'Brukere',
                'Verktøy' => 'Verktøy',
                
                // Settings
                'Hovedinnstillinger' => 'Hovedinnstillinger',
                'Konfigurer grunnleggende oppførsel for dashboard-opprydding' => 'Konfigurer grunnleggende oppførsel for dashboard-opprydding',
                'Aktiver dashboard-opprydding' => 'Aktiver dashboard-opprydding',
                'Hovedbryter for å aktivere eller deaktivere alle oppryddingsfunksjoner.' => 'Hovedbryter for å aktivere eller deaktivere alle oppryddingsfunksjoner.',
                'Vis skjul-knapper' => 'Vis skjul-knapper',
                'Vis "Skjul"-knapper på varsler og widgets for enkel skjuling.' => 'Vis "Skjul"-knapper på varsler og widgets for enkel skjuling.',
                'Vedvarende modus' => 'Vedvarende modus',
                'Husk skjulte elementer på tvers av økter.' => 'Husk skjulte elementer på tvers av økter.',
                'Auto-skjul nye elementer' => 'Auto-skjul nye elementer',
                'Skjul automatisk nye elementer som matcher svarteliste-regler.' => 'Skjul automatisk nye elementer som matcher svarteliste-regler.',
                'Tillat bruker-overstyring' => 'Tillat bruker-overstyring',
                'La brukere overstyre globale innstillinger med egne preferanser.' => 'La brukere overstyre globale innstillinger med egne preferanser.',
                'Språk / Language' => 'Språk / Language',
                'Velg språk for plugin-grensesnittet' => 'Velg språk for plugin-grensesnittet',
                
                // Admin bar
                'Skjul admin-bar i backend' => 'Skjul admin-bar i backend',
                'Skjul admin-toolbar øverst på admin-sider for en renere opplevelse.' => 'Skjul admin-toolbar øverst på admin-sider for en renere opplevelse.',
                'Skjul admin-bar på frontend' => 'Skjul admin-bar på frontend',
                'Skjul admin-toolbar på frontend (offentlige sider) når du er innlogget.' => 'Skjul admin-toolbar på frontend (offentlige sider) når du er innlogget.',
                'Gjelder for roller:' => 'Gjelder for roller:',
                
                // Statistics
                'Statistikk' => 'Statistikk',
                'Oversikt over skjulte elementer' => 'Oversikt over skjulte elementer',
                'Totalt skjulte' => 'Totalt skjulte',
                'Varsler' => 'Varsler',
                'Widgets' => 'Widgets',
                'Globale' => 'Globale',
                
                // Buttons and actions
                'Lagre innstillinger' => 'Lagre innstillinger',
                'Aktivert' => 'Aktivert',
                'Deaktivert' => 'Deaktivert',
                'Innstillinger lagret!' => 'Innstillinger lagret!',
                
                // Dashboard actions
                'Skjul' => 'Skjul',
                'Vis' => 'Vis',
                'Skjult' => 'Skjult',
                'Skjul dette elementet' => 'Skjul dette elementet',
                'Vis dette elementet' => 'Vis dette elementet',
                
                // Blacklist
                'Svarteliste-administrasjon' => 'Svarteliste-administrasjon',
                'Administrer skjulte elementer og regler' => 'Administrer skjulte elementer og regler',
                'Type' => 'Type',
                'Element ID' => 'Element ID',
                'Bruker' => 'Bruker',
                'Opprettet' => 'Opprettet',
                'Handlinger' => 'Handlinger',
                'Laster svarteliste...' => 'Laster svarteliste...',
                'Ingen elementer i svartelisten' => 'Ingen elementer i svartelisten',
                'Feil ved lasting av svarteliste' => 'Feil ved lasting av svarteliste',
                
                // Admin JavaScript strings
                'Er du sikker på at du vil fjerne dette elementet?' => 'Er du sikker på at du vil fjerne dette elementet?',
                'Er du sikker på at du vil tømme svartelisten?' => 'Er du sikker på at du vil tømme svartelisten?',
                'En feil oppstod' => 'En feil oppstod',
                'Brukerstatistikk' => 'Brukerstatistikk',
                'Ingen brukerdata tilgjengelig ennå.' => 'Ingen brukerdata tilgjengelig ennå.',
                
                // Tools
                'GitHub Oppdateringer' => 'GitHub Oppdateringer',
                'Administrer automatiske oppdateringer fra GitHub' => 'Administrer automatiske oppdateringer fra GitHub',
                'Nåværende versjon' => 'Nåværende versjon',
                'GitHub Repository' => 'GitHub Repository',
                'Sjekk etter oppdateringer' => 'Sjekk etter oppdateringer',
                'Tvinger en sjekk etter nye versjoner på GitHub.' => 'Tvinger en sjekk etter nye versjoner på GitHub.',
                'Klikk "Sjekk etter oppdateringer" for å se status.' => 'Klikk "Sjekk etter oppdateringer" for å se status.',
                'Status' => 'Status',
                'Import/Export' => 'Import/Export',
                'Sikkerhetskopier og gjenopprett svarteliste-konfigurasjoner' => 'Sikkerhetskopier og gjenopprett svarteliste-konfigurasjoner',
                'Eksporter svarteliste' => 'Eksporter svarteliste',
                'Last ned en JSON-fil med all svarteliste-data.' => 'Last ned en JSON-fil med all svarteliste-data.',
                'Importer svarteliste' => 'Importer svarteliste',
                'Importer' => 'Importer',
                'Last opp en JSON-fil for å importere svarteliste-data.' => 'Last opp en JSON-fil for å importere svarteliste-data.',
                'Tilbakestill' => 'Tilbakestill',
                'Tilbakestill plugin-data og innstillinger' => 'Tilbakestill plugin-data og innstillinger',
                'Tøm min svarteliste' => 'Tøm min svarteliste',
                'Fjern alle dine personlige skjulte elementer.' => 'Fjern alle dine personlige skjulte elementer.',
                'Tøm alle svartelister' => 'Tøm alle svartelister',
                'Fjern alle svartelister for alle brukere. Kan ikke angres.' => 'Fjern alle svartelister for alle brukere. Kan ikke angres.',
                
                // Users
                'Bruker- og rolleinnstillinger' => 'Bruker- og rolleinnstillinger',
                'Konfigurer innstillinger per bruker og rolle' => 'Konfigurer innstillinger per bruker og rolle',
                'Laster brukerinnstillinger...' => 'Laster brukerinnstillinger...',
                
                // Admin response
                'BD CleanDash er nå aktivert.' => 'BD CleanDash er nå aktivert.',
                'BD CleanDash er nå deaktivert.' => 'BD CleanDash er nå deaktivert.',
                'Elementet ble fjernet fra svartelisten.' => 'Elementet ble fjernet fra svartelisten.',
                'Svartelisten ble tømt.' => 'Svartelisten ble tømt.',
                'Innstillingene ble lagret.' => 'Innstillingene ble lagret.',
                'Handling fullført.' => 'Handling fullført.',
                'Oppdatering tilgjengelig!' => 'Oppdatering tilgjengelig!',
                'Ingen oppdateringer tilgjengelig.' => 'Ingen oppdateringer tilgjengelig.',
                'Feil under oppdatering.' => 'Feil under oppdatering.',
                'Suksess!' => 'Suksess!',
                'Advarsel!' => 'Advarsel!',
                'Informasjon' => 'Informasjon',
                'Bekreftelse' => 'Bekreftelse',
                'Feil' => 'Feil',
                'Vennligst vent...' => 'Vennligst vent...',
                'Laster...' => 'Laster...',
                'Fullført' => 'Fullført',
                'Ingen endringer ble gjort.' => 'Ingen endringer ble gjort.',
                'Ugyldig forespørsel.' => 'Ugyldig forespørsel.',
                'Tilgang nektet.' => 'Tilgang nektet.',
                'Elementet finnes ikke.' => 'Elementet finnes ikke.',
                'Operasjonen er ikke tillatt.' => 'Operasjonen er ikke tillatt.',
                'Ukjent handling.' => 'Ukjent handling.',
                'Vennligst prøv igjen.' => 'Vennligst prøv igjen.',
                'Takk for at du bruker BD CleanDash!' => 'Takk for at du bruker BD CleanDash!',
                
                // Additional JavaScript strings
                'Fjern' => 'Fjern',
                'Kunne ikke fjerne elementet' => 'Kunne ikke fjerne elementet',
                'Ukjent feil' => 'Ukjent feil',
                'Nettverksfeil' => 'Nettverksfeil',
                'Er du sikker på at du vil fjerne dette elementet fra svartelisten?' => 'Er du sikker på at du vil fjerne dette elementet fra svartelisten?',
                'Er du sikker på at du vil tømme din personlige svarteliste?' => 'Er du sikker på at du vil tømme din personlige svarteliste?',
                'Kunne ikke tømme svarteliste' => 'Kunne ikke tømme svarteliste',
                'Er du sikker på at du vil tømme ALLE svartelister? Dette kan ikke angres.' => 'Er du sikker på at du vil tømme ALLE svartelister? Dette kan ikke angres.',
                'Kunne ikke tømme svartelister' => 'Kunne ikke tømme svartelister',
                'Kunne ikke laste svarteliste' => 'Kunne ikke laste svarteliste',
                'Kunne ikke laste brukerdata' => 'Kunne ikke laste brukerdata',
                'Sjekker...' => 'Sjekker...',
                'Sjekk etter oppdateringer' => 'Sjekk etter oppdateringer',
                'Sjekker etter oppdateringer...' => 'Sjekker etter oppdateringer...',
                'Nettverksfeil ved sjekking av oppdateringer.' => 'Nettverksfeil ved sjekking av oppdateringer.',
                'Kunne ikke eksportere svarteliste' => 'Kunne ikke eksportere svarteliste',
                'Velg en fil først' => 'Velg en fil først',
                'Import feilet' => 'Import feilet',
                'Ugyldig JSON-fil' => 'Ugyldig JSON-fil',
                'Ingen elementer i svartelisten ennå.' => 'Ingen elementer i svartelisten ennå.',
                'Gå til dashbordet og klikk "Skjul" på elementer du vil skjule.' => 'Gå til dashbordet og klikk "Skjul" på elementer du vil skjule.',
                'Varsel' => 'Varsel',
                'Widget' => 'Widget',
                'Global' => 'Global',
                'Bruker #' => 'Bruker #',
                'Type' => 'Type',
                'Element ID' => 'Element ID',
                'Opprettet' => 'Opprettet',
                'Handlinger' => 'Handlinger',
                
                // Overview page
                'Buene Data' => 'Buene Data',
                'Profesjonelle WordPress-løsninger' => 'Profesjonelle WordPress-løsninger',
                'Rydde opp i WordPress dashbordet ved å skjule uønskede varsler og widgets.' => 'Rydde opp i WordPress dashbordet ved å skjule uønskede varsler og widgets.',
                'Aktiv' => 'Aktiv',
                'Konfigurer' => 'Konfigurer',
                'BD Tools' => 'BD Tools',
                'Samling av nyttige verktøy for WordPress-administrasjon.' => 'Samling av nyttige verktøy for WordPress-administrasjon.',
                'Kommer snart' => 'Kommer snart',
                'BD Security' => 'BD Security',
                'Forbedre sikkerheten på WordPress-nettstedet ditt.' => 'Forbedre sikkerheten på WordPress-nettstedet ditt.',
                'BD Analytics' => 'BD Analytics',
                'Detaljert analyse og rapportering for nettstedet ditt.' => 'Detaljert analyse og rapportering for nettstedet ditt.',
                'Trenger hjelp? Besøk <a href="%s" target="_blank">dokumentasjonen</a> eller <a href="%s" target="_blank">kontakt oss</a>.' => 'Trenger hjelp? Besøk <a href="%s" target="_blank">dokumentasjonen</a> eller <a href="%s" target="_blank">kontakt oss</a>.'
            ),
            'en' => array(
                // Main navigation
                'BD CleanDash' => 'BD CleanDash',
                'Hold dashbordet ditt rent og ryddig' => 'Keep your dashboard clean and organized',
                'Dashboard' => 'Dashboard',
                'Svarteliste' => 'Blacklist',
                'Brukere' => 'Users',
                'Verktøy' => 'Tools',
                
                // Settings
                'Hovedinnstillinger' => 'Main Settings',
                'Konfigurer grunnleggende oppførsel for dashboard-opprydding' => 'Configure basic behavior for dashboard cleanup',
                'Aktiver dashboard-opprydding' => 'Enable dashboard cleanup',
                'Hovedbryter for å aktivere eller deaktivere alle oppryddingsfunksjoner.' => 'Main switch to enable or disable all cleanup functions.',
                'Vis skjul-knapper' => 'Show hide buttons',
                'Vis "Skjul"-knapper på varsler og widgets for enkel skjuling.' => 'Show "Hide" buttons on notices and widgets for easy hiding.',
                'Vedvarende modus' => 'Persistent mode',
                'Husk skjulte elementer på tvers av økter.' => 'Remember hidden elements across sessions.',
                'Auto-skjul nye elementer' => 'Auto-hide new elements',
                'Skjul automatisk nye elementer som matcher svarteliste-regler.' => 'Automatically hide new elements that match blacklist rules.',
                'Tillat bruker-overstyring' => 'Allow user override',
                'La brukere overstyre globale innstillinger med egne preferanser.' => 'Let users override global settings with their own preferences.',
                'Språk / Language' => 'Language / Språk',
                'Velg språk for plugin-grensesnittet' => 'Select language for the plugin interface',
                
                // Admin bar
                'Skjul admin-bar i backend' => 'Hide admin bar in backend',
                'Skjul admin-toolbar øverst på admin-sider for en renere opplevelse.' => 'Hide admin toolbar at the top of admin pages for a cleaner experience.',
                'Skjul admin-bar på frontend' => 'Hide admin bar on frontend',
                'Skjul admin-toolbar på frontend (offentlige sider) når du er innlogget.' => 'Hide admin toolbar on frontend (public pages) when logged in.',
                'Gjelder for roller:' => 'Applies to roles:',
                
                // Statistics
                'Statistikk' => 'Statistics',
                'Oversikt over skjulte elementer' => 'Overview of hidden elements',
                'Totalt skjulte' => 'Total hidden',
                'Varsler' => 'Notices',
                'Widgets' => 'Widgets',
                'Globale' => 'Global',
                
                // Buttons and actions
                'Lagre innstillinger' => 'Save Settings',
                'Aktivert' => 'Enabled',
                'Deaktivert' => 'Disabled',
                'Innstillinger lagret!' => 'Settings saved!',
                
                // Dashboard actions
                'Skjul' => 'Hide',
                'Vis' => 'Show',
                'Skjult' => 'Hidden',
                'Skjul dette elementet' => 'Hide this element',
                'Vis dette elementet' => 'Show this element',
                
                // Blacklist
                'Svarteliste-administrasjon' => 'Blacklist Management',
                'Administrer skjulte elementer og regler' => 'Manage hidden elements and rules',
                'Type' => 'Type',
                'Element ID' => 'Element ID',
                'Bruker' => 'User',
                'Opprettet' => 'Created',
                'Handlinger' => 'Actions',
                'Laster svarteliste...' => 'Loading blacklist...',
                'Ingen elementer i svartelisten' => 'No elements in blacklist',
                'Feil ved lasting av svarteliste' => 'Error loading blacklist',
                
                // Admin JavaScript strings
                'Er du sikker på at du vil fjerne dette elementet?' => 'Are you sure you want to remove this element?',
                'Er du sikker på at du vil tømme svartelisten?' => 'Are you sure you want to clear the blacklist?',
                'En feil oppstod' => 'An error occurred',
                'Brukerstatistikk' => 'User Statistics',
                'Ingen brukerdata tilgjengelig ennå.' => 'No user data available yet.',
                
                // Tools
                'GitHub Oppdateringer' => 'GitHub Updates',
                'Administrer automatiske oppdateringer fra GitHub' => 'Manage automatic updates from GitHub',
                'Nåværende versjon' => 'Current version',
                'GitHub Repository' => 'GitHub Repository',
                'Sjekk etter oppdateringer' => 'Check for updates',
                'Tvinger en sjekk etter nye versjoner på GitHub.' => 'Force a check for new versions on GitHub.',
                'Klikk "Sjekk etter oppdateringer" for å se status.' => 'Click "Check for updates" to see status.',
                'Status' => 'Status',
                'Import/Export' => 'Import/Export',
                'Sikkerhetskopier og gjenopprett svarteliste-konfigurasjoner' => 'Backup and restore blacklist configurations',
                'Eksporter svarteliste' => 'Export blacklist',
                'Last ned en JSON-fil med all svarteliste-data.' => 'Download a JSON file with all blacklist data.',
                'Importer svarteliste' => 'Import blacklist',
                'Importer' => 'Import',
                'Last opp en JSON-fil for å importere svarteliste-data.' => 'Upload a JSON file to import blacklist data.',
                'Tilbakestill' => 'Reset',
                'Tilbakestill plugin-data og innstillinger' => 'Reset plugin data and settings',
                'Tøm min svarteliste' => 'Clear my blacklist',
                'Fjern alle dine personlige skjulte elementer.' => 'Remove all your personal hidden elements.',
                'Tøm alle svartelister' => 'Clear all blacklists',
                'Fjern alle svartelister for alle brukere. Kan ikke angres.' => 'Remove all blacklists for all users. Cannot be undone.',
                
                // Users
                'Bruker- og rolleinnstillinger' => 'User and role settings',
                'Konfigurer innstillinger per bruker og rolle' => 'Configure settings per user and role',
                'Laster brukerinnstillinger...' => 'Loading user settings...',
                
                // Admin response
                'BD CleanDash er nå aktivert.' => 'BD CleanDash is now enabled.',
                'BD CleanDash er nå deaktivert.' => 'BD CleanDash is now disabled.',
                'Elementet ble fjernet fra svartelisten.' => 'Element was removed from blacklist.',
                'Svartelisten ble tømt.' => 'Blacklist was cleared.',
                'Innstillingene ble lagret.' => 'Settings were saved.',
                'Handling fullført.' => 'Action completed.',
                'Oppdatering tilgjengelig!' => 'Update available!',
                'Ingen oppdateringer tilgjengelig.' => 'No updates available.',
                'Feil under oppdatering.' => 'Error during update.',
                'Suksess!' => 'Success!',
                'Advarsel!' => 'Warning!',
                'Informasjon' => 'Information',
                'Bekreftelse' => 'Confirmation',
                'Feil' => 'Error',
                'Vennligst vent...' => 'Please wait...',
                'Laster...' => 'Loading...',
                'Fullført' => 'Completed',
                'Ingen endringer ble gjort.' => 'No changes were made.',
                'Ugyldig forespørsel.' => 'Invalid request.',
                'Tilgang nektet.' => 'Access denied.',
                'Elementet finnes ikke.' => 'Element not found.',
                'Operasjonen er ikke tillatt.' => 'Operation not allowed.',
                'Ukjent handling.' => 'Unknown action.',
                'Vennligst prøv igjen.' => 'Please try again.',
                'Takk for at du bruker BD CleanDash!' => 'Thank you for using BD CleanDash!',
                
                // Additional JavaScript strings
                'Fjern' => 'Remove',
                'Kunne ikke fjerne elementet' => 'Could not remove element',
                'Ukjent feil' => 'Unknown error',
                'Nettverksfeil' => 'Network error',
                'Er du sikker på at du vil fjerne dette elementet fra svartelisten?' => 'Are you sure you want to remove this element from the blacklist?',
                'Er du sikker på at du vil tømme din personlige svarteliste?' => 'Are you sure you want to clear your personal blacklist?',
                'Kunne ikke tømme svarteliste' => 'Could not clear blacklist',
                'Er du sikker på at du vil tømme ALLE svartelister? Dette kan ikke angres.' => 'Are you sure you want to clear ALL blacklists? This cannot be undone.',
                'Kunne ikke tømme svartelister' => 'Could not clear blacklists',
                'Kunne ikke laste svarteliste' => 'Could not load blacklist',
                'Kunne ikke laste brukerdata' => 'Could not load user data',
                'Sjekker...' => 'Checking...',
                'Sjekk etter oppdateringer' => 'Check for updates',
                'Sjekker etter oppdateringer...' => 'Checking for updates...',
                'Nettverksfeil ved sjekking av oppdateringer.' => 'Network error while checking for updates.',
                'Kunne ikke eksportere svarteliste' => 'Could not export blacklist',
                'Velg en fil først' => 'Select a file first',
                'Import feilet' => 'Import failed',
                'Ugyldig JSON-fil' => 'Invalid JSON file',
                'Ingen elementer i svartelisten ennå.' => 'No elements in the blacklist yet.',
                'Gå til dashbordet og klikk "Skjul" på elementer du vil skjule.' => 'Go to the dashboard and click "Hide" on elements you want to hide.',
                'Varsel' => 'Notice',
                'Widget' => 'Widget',
                'Global' => 'Global',
                'Bruker #' => 'User #',
                'Type' => 'Type',
                'Element ID' => 'Element ID',
                'Opprettet' => 'Created',
                'Handlinger' => 'Actions',
                
                // Overview page
                'Buene Data' => 'Buene Data',
                'Profesjonelle WordPress-løsninger' => 'Professional WordPress solutions',
                'Rydde opp i WordPress dashbordet ved å skjule uønskede varsler og widgets.' => 'Clean up the WordPress dashboard by hiding unwanted notices and widgets.',
                'Aktiv' => 'Active',
                'Konfigurer' => 'Configure',
                'BD Tools' => 'BD Tools',
                'Samling av nyttige verktøy for WordPress-administrasjon.' => 'Collection of useful tools for WordPress administration.',
                'Kommer snart' => 'Coming soon',
                'BD Security' => 'BD Security',
                'Forbedre sikkerheten på WordPress-nettstedet ditt.' => 'Improve the security of your WordPress website.',
                'BD Analytics' => 'BD Analytics',
                'Detaljert analyse og rapportering for nettstedet ditt.' => 'Detailed analysis and reporting for your website.',
                'Trenger hjelp? Besøk <a href="%s" target="_blank">dokumentasjonen</a> eller <a href="%s" target="_blank">kontakt oss</a>.' => 'Need help? Visit the <a href="%s" target="_blank">documentation</a> or <a href="%s" target="_blank">contact us</a>.'
            )
        );
    }
    
    /**
     * Translate a string
     */
    public function translate($string, $context = '') {
        if (isset($this->translations[$this->current_language][$string])) {
            return $this->translations[$this->current_language][$string];
        }
        
        // Fallback to WordPress translation if available
        if ($this->current_language === 'no') {
            return __($string, 'bd-cleandash');
        }
        
        // Return original string if no translation found
        return $string;
    }
    
    /**
     * Get current language
     */
    public function get_current_language() {
        return $this->current_language;
    }
    
    /**
     * Set current language
     */
    public function set_language($language) {
        if (isset($this->languages[$language])) {
            $this->current_language = $language;
            update_option('bd_cleandash_language', $language);
            $this->load_translations();
        }
    }
    
    /**
     * Get available languages
     */
    public function get_languages() {
        return $this->languages;
    }
    
    /**
     * Get language name
     */
    public function get_language_name($language_code) {
        return isset($this->languages[$language_code]) ? $this->languages[$language_code]['name'] : $language_code;
    }
    
    /**
     * Get language flag
     */
    public function get_language_flag($language_code) {
        return isset($this->languages[$language_code]) ? $this->languages[$language_code]['flag'] : '';
    }
}

/**
 * Helper function to translate strings with BD CleanDash language system
 */
function bd_translate($string, $context = '') {
    static $language_manager = null;
    
    if ($language_manager === null) {
        $language_manager = new BD_Language();
    }
    
    return $language_manager->translate($string, $context);
}

/**
 * Shorthand function for BD translations
 */
function bd__($string, $context = '') {
    return bd_translate($string, $context);
}

/**
 * Echo translated string
 */
function bd_e($string, $context = '') {
    echo bd_translate($string, $context);
}
