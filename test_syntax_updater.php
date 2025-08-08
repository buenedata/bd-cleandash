<?php
// Simple syntax checker for BD Updater
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing PHP syntax for class-bd-updater.php...\n";

// Read the file content
$file_content = file_get_contents('includes/class-bd-updater.php');

if ($file_content === false) {
    echo "ERROR: Could not read file\n";
    exit(1);
}

// Check for basic syntax issues
$open_braces = substr_count($file_content, '{');
$close_braces = substr_count($file_content, '}');

echo "Open braces: $open_braces\n";
echo "Close braces: $close_braces\n";

if ($open_braces !== $close_braces) {
    echo "ERROR: Mismatched braces! Open: $open_braces, Close: $close_braces\n";
} else {
    echo "SUCCESS: Braces are balanced\n";
}

// Check for duplicate function definitions
if (preg_match_all('/public function\s+(\w+)\s*\(/', $file_content, $matches)) {
    $functions = $matches[1];
    $duplicates = array_diff_assoc($functions, array_unique($functions));
    
    if (!empty($duplicates)) {
        echo "ERROR: Duplicate function definitions found: " . implode(', ', array_unique($duplicates)) . "\n";
    } else {
        echo "SUCCESS: No duplicate function definitions\n";
    }
}

// Check for class definition
if (preg_match('/class\s+BD_CleanDash_Updater/', $file_content)) {
    echo "SUCCESS: BD_CleanDash_Updater class found\n";
} else {
    echo "ERROR: BD_CleanDash_Updater class not found\n";
}

// Check for required methods
$required_methods = ['__construct', 'check_for_update', 'get_remote_version', 'plugin_info'];
foreach ($required_methods as $method) {
    if (preg_match('/public function\s+' . preg_quote($method) . '\s*\(/', $file_content)) {
        echo "SUCCESS: Method $method found\n";
    } else {
        echo "ERROR: Method $method not found\n";
    }
}

echo "File content length: " . strlen($file_content) . " characters\n";
echo "Lines in file: " . substr_count($file_content, "\n") . "\n";

echo "Basic syntax check completed.\n";
?>