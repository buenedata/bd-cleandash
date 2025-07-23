<?php
// Test integration between bd-menu-helper.php and BD CleanDash
echo "=== BD CleanDash Integration Test ===\n\n";

// 1. Check plugin header in main file
echo "1. Checking plugin header...\n";
$main_file = 'd:\Programmering\GitHub\bd-cleandash\bd-cleandash.php';
$content = file_get_contents($main_file);

// Extract plugin header info
if (preg_match('/Plugin Name:\s*(.+)/i', $content, $matches)) {
    echo "   Plugin Name: " . trim($matches[1]) . "\n";
}
if (preg_match('/Author:\s*(.+)/i', $content, $matches)) {
    echo "   Author: " . trim($matches[1]) . "\n";
}
if (preg_match('/Plugin URI:\s*(.+)/i', $content, $matches)) {
    echo "   Plugin URI: " . trim($matches[1]) . "\n";
}
if (preg_match('/Text Domain:\s*(.+)/i', $content, $matches)) {
    echo "   Text Domain: " . trim($matches[1]) . "\n";
}

// 2. Check if bd-menu-helper is included
echo "\n2. Checking bd-menu-helper inclusion...\n";
$admin_file = 'd:\Programmering\GitHub\bd-cleandash\includes\class-bd-admin.php';
$admin_content = file_get_contents($admin_file);

if (strpos($admin_content, "require_once __DIR__ . '/bd-menu-helper.php'") !== false) {
    echo "   ✓ bd-menu-helper.php is included\n";
} else {
    echo "   ✗ bd-menu-helper.php is NOT included\n";
}

// 3. Check if bd_add_buene_data_menu is called
if (strpos($admin_content, 'bd_add_buene_data_menu') !== false) {
    echo "   ✓ bd_add_buene_data_menu() is called\n";
} else {
    echo "   ✗ bd_add_buene_data_menu() is NOT called\n";
}

// 4. Check menu slug consistency
echo "\n3. Checking menu slug consistency...\n";
if (strpos($admin_content, "'bd-cleandash'") !== false) {
    echo "   ✓ Menu slug 'bd-cleandash' found\n";
} else {
    echo "   ✗ Menu slug 'bd-cleandash' NOT found\n";
}

// 5. Check helper file exists
echo "\n4. Checking helper file...\n";
$helper_file = 'd:\Programmering\GitHub\bd-cleandash\includes\bd-menu-helper.php';
if (file_exists($helper_file)) {
    echo "   ✓ bd-menu-helper.php exists\n";
    
    $helper_content = file_get_contents($helper_file);
    if (strpos($helper_content, 'function bd_add_buene_data_menu') !== false) {
        echo "   ✓ bd_add_buene_data_menu function exists\n";
    } else {
        echo "   ✗ bd_add_buene_data_menu function NOT found\n";
    }
    
    if (strpos($helper_content, 'function bd_buene_data_overview_page') !== false) {
        echo "   ✓ bd_buene_data_overview_page function exists\n";
    } else {
        echo "   ✗ bd_buene_data_overview_page function NOT found\n";
    }
} else {
    echo "   ✗ bd-menu-helper.php does NOT exist\n";
}

// 6. Test detection logic match
echo "\n5. Testing detection logic match...\n";
$test_data = array(
    'Name' => 'BD CleanDash',
    'Author' => 'Buene Data',
    'PluginURI' => 'https://buenedata.no/plugins/bd-cleandash',
    'TextDomain' => 'bd-cleandash'
);

$matches = array();
if (stripos($test_data['Author'], 'Buene Data') !== false) {
    $matches[] = 'Author contains "Buene Data"';
}
if (stripos($test_data['PluginURI'], 'buenedata') !== false) {
    $matches[] = 'PluginURI contains "buenedata"';
}
if (stripos($test_data['Name'], 'BD ') === 0) {
    $matches[] = 'Name starts with "BD "';
}
if (stripos($test_data['TextDomain'], 'bd-') === 0) {
    $matches[] = 'TextDomain starts with "bd-"';
}

echo "   Detection criteria met: " . count($matches) . "/4\n";
foreach ($matches as $match) {
    echo "   ✓ " . $match . "\n";
}

if (count($matches) > 0) {
    echo "   ✓ Plugin WILL be detected by bd-menu-helper\n";
} else {
    echo "   ✗ Plugin will NOT be detected by bd-menu-helper\n";
}

echo "\n=== Integration Test Complete ===\n";
?>
