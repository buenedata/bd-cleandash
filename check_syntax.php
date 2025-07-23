<?php
// Simple syntax checker
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing PHP syntax for class-bd-admin.php...\n";

// Read the file content
$file_content = file_get_contents('includes/class-bd-admin.php');

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

echo "File content length: " . strlen($file_content) . " characters\n";
echo "Lines in file: " . substr_count($file_content, "\n") . "\n";

// Check for common syntax errors
$common_errors = array(
    '/\$\w+\s*=\s*$/' => 'Incomplete assignment',
    '/\(\s*$/' => 'Unclosed parenthesis',
    '/{\s*$/' => 'Unclosed brace at end of file',
    '/public function\s+public function/' => 'Duplicate function keywords'
);

foreach ($common_errors as $pattern => $description) {
    if (preg_match($pattern, $file_content)) {
        echo "WARNING: Potential issue found - $description\n";
    }
}

echo "Basic syntax check completed.\n";
?>
