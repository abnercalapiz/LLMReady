<?php
/**
 * Debug activation issues for LLM Ready plugin
 * 
 * This file helps identify what's causing the fatal error during activation
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n\n";

// Define ABSPATH if not defined (for standalone testing)
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}

// Test loading each file individually
$plugin_dir = dirname(__FILE__) . '/';
$files_to_test = array(
    'includes/class-llmr-seo-detector.php',
    'includes/class-llmr-admin.php',
    'includes/class-llmr-generator.php',
    'includes/class-llmr-dashboard-widget.php',
    'includes/class-llmr-mcp-server.php',
    'includes/class-llmr-mcp-admin.php',
    'includes/class-llmr.php'
);

echo "Testing file loading:\n";
echo "====================\n\n";

foreach ($files_to_test as $file) {
    $file_path = $plugin_dir . $file;
    echo "Testing: $file ... ";
    
    if (file_exists($file_path)) {
        try {
            // Test syntax without actually including
            $output = shell_exec("php -l \"$file_path\" 2>&1");
            if (strpos($output, 'No syntax errors') !== false) {
                echo "✓ Syntax OK\n";
            } else {
                echo "✗ Syntax Error:\n$output\n";
            }
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ File not found\n";
    }
}

echo "\n\nTesting class definitions:\n";
echo "=========================\n\n";

// Actually include the files and check classes
foreach ($files_to_test as $file) {
    $file_path = $plugin_dir . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

$expected_classes = array(
    'LLMR_SEO_Detector',
    'LLMR_Admin',
    'LLMR_Generator',
    'LLMR_Dashboard_Widget',
    'LLMR_MCP_Server',
    'LLMR_MCP_Admin',
    'LLMR'
);

foreach ($expected_classes as $class) {
    echo "Class $class: ";
    if (class_exists($class)) {
        echo "✓ Found\n";
    } else {
        echo "✗ Not found\n";
    }
}

echo "\n\nTesting constants:\n";
echo "=================\n\n";

// Define constants if not defined
if (!defined('LLMR_VERSION')) define('LLMR_VERSION', '1.0.0');
if (!defined('LLMR_PLUGIN_DIR')) define('LLMR_PLUGIN_DIR', $plugin_dir);
if (!defined('LLMR_PLUGIN_URL')) define('LLMR_PLUGIN_URL', 'http://example.com/');
if (!defined('LLMR_BASENAME')) define('LLMR_BASENAME', 'llmr/llmready.php');

$constants = array('LLMR_VERSION', 'LLMR_PLUGIN_DIR', 'LLMR_PLUGIN_URL', 'LLMR_BASENAME');
foreach ($constants as $constant) {
    echo "$constant: ";
    if (defined($constant)) {
        echo "✓ Defined\n";
    } else {
        echo "✗ Not defined\n";
    }
}

echo "\n\nDone!\n";