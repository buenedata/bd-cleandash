# BD CleanDash Language Support

This document outlines the multi-language support implementation in BD CleanDash.

## Overview

BD CleanDash now supports both Norwegian and English languages with an in-plugin language selector, eliminating the need for separate plugin versions.

## Features

### Language Selector
- Located in Main Settings (Hovedinnstillinger/Main Settings)
- Dropdown with Norwegian (🇳🇴) and English (🇺🇸) flags
- Auto-submits form when language is changed
- Provides immediate feedback during language switching

### Translation System
- **BD_Language Class**: Manages all translations and language switching
- **Helper Functions**: `bd__()`, `bd_e()`, and `bd_translate()`
- **Dynamic Strings**: JavaScript strings are also translated
- **Fallback Support**: Falls back to original text if translation missing

### Supported Languages

#### Norwegian (no)
- Default language
- Complete translation coverage
- Uses Norwegian text throughout interface

#### English (en) 
- Full English translation
- Professional terminology
- Consistent interface language

## Implementation Details

### PHP Translation Functions
```php
// Translate and return string
$text = bd__('Hovedinnstillinger');

// Translate and echo string  
bd_e('Lagre innstillinger');

// Translate with context
$text = bd_translate('Status', 'admin_context');
```

### JavaScript Integration
Dashboard scripts receive translated strings through `wp_localize_script()`:
```javascript
bdCleanDash.strings = {
    hide: 'Hide' / 'Skjul',
    show: 'Show' / 'Vis', 
    hidden: 'Hidden' / 'Skjult'
};
```

### Language Storage
- User's language preference stored in `bd_cleandash_language` option
- Persists across sessions
- Applied to all plugin interfaces

## Usage

### For Administrators
1. Go to BD CleanDash settings
2. Select preferred language from dropdown
3. Settings auto-save and page refreshes with new language
4. All subsequent visits use selected language

### For Developers
1. Use `bd__()` and `bd_e()` instead of `__()` and `_e()`
2. Add new strings to BD_Language class translations array
3. Ensure JavaScript strings are passed through localization

## File Structure
```
includes/
├── class-bd-language.php     # Main language management class
├── class-bd-cleandash.php    # Updated to initialize language manager
├── class-bd-admin.php        # Updated script localization
└── ...

templates/
└── admin-page.php            # Updated with bd_e() functions and language selector
```

## Translation Coverage

### Fully Translated Areas
- ✅ Main navigation and headers
- ✅ Settings page and form labels
- ✅ Button text and actions
- ✅ Dashboard hide/show functionality
- ✅ Admin bar configuration
- ✅ Role selection interface
- ✅ JavaScript button tooltips

### Future Improvements
- Add more languages (Swedish, Danish, German)
- Export/import translation files
- Translation management interface
- Community translation support

## Benefits

1. **Single Plugin**: No need to maintain separate language versions
2. **User Choice**: Each user can select their preferred language
3. **Instant Switching**: Language changes apply immediately
4. **Maintainable**: Centralized translation management
5. **Extensible**: Easy to add new languages

This implementation provides a professional, user-friendly multi-language experience while maintaining code simplicity and maintainability.
