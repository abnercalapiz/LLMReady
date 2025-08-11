<?php
/**
 * Plugin Name:       LLM Ready
 * Plugin URI:        https://www.jezweb.com.au/llm-ready
 * Description:       Improve your website's visibility and ranking in AI tools like ChatGPT, Perplexity, and Claude by automatically generating an llms.txt file and providing an MCP server for direct AI agent interactions.
 * Version:           1.0.5
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Jezweb
 * Author URI:        https://www.jezweb.com.au/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       llmr
 * Domain Path:       /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
if (!defined('LLMR_VERSION')) {
    define('LLMR_VERSION', '1.0.5');
}
if (!defined('LLMR_PLUGIN_DIR')) {
    define('LLMR_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('LLMR_PLUGIN_URL')) {
    define('LLMR_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('LLMR_BASENAME')) {
    define('LLMR_BASENAME', plugin_basename(__FILE__));
}

// Include required files
$required_files = array(
    'includes/class-llmr-seo-detector.php',    // Load first as others depend on it
    'includes/class-llmr-admin.php',
    'includes/class-llmr-generator.php',
    'includes/class-llmr-dashboard-widget.php',
    'includes/class-llmr-mcp-server.php',
    'includes/class-llmr-mcp-admin.php',
    'includes/class-llmr-stats.php',            // Stats and analytics
    'includes/class-llmr.php'                   // Load main class last
);

foreach ($required_files as $file) {
    $file_path = LLMR_PLUGIN_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log('LLM Ready: Required file not found: ' . $file);
    }
}

// Initialize the plugin
function llmr_init() {
    try {
        if (!class_exists('LLMR')) {
            error_log('LLM Ready: Main class not found');
            return;
        }
        
        $plugin = new LLMR();
        $plugin->run();
    } catch (Exception $e) {
        error_log('LLM Ready Error: ' . $e->getMessage());
        
        // Show admin notice if there's an error
        add_action('admin_notices', function() use ($e) {
            ?>
            <div class="notice notice-error">
                <p><?php _e('LLM Ready Error: ', 'llmr'); ?><?php echo esc_html($e->getMessage()); ?></p>
            </div>
            <?php
        });
    }
}
add_action('plugins_loaded', 'llmr_init');

// Activation hook
register_activation_hook(__FILE__, 'llmr_activate');
function llmr_activate() {
    try {
        // Set default options
        $default_options = array(
            'enabled' => true,
            'auto_regenerate' => true,
            'post_types' => array('post', 'page'),
            'excluded_urls' => array(),
            'included_urls' => array(),
            'include_author' => true,
            'include_license' => true,
            'include_canonical' => true,
            'last_generated' => '',
            'total_urls' => 0
        );
        
        if (!get_option('llmr_settings')) {
            add_option('llmr_settings', $default_options);
        }
        
        // Generate initial llms.txt file only if classes are available
        if (class_exists('LLMR_Generator')) {
            $generator = new LLMR_Generator();
            $generator->generate_llms_file();
        }
        
        // Clear any scheduled hooks
        wp_clear_scheduled_hook('llmr_regenerate_file');
    } catch (Exception $e) {
        error_log('LLM Ready Activation Error: ' . $e->getMessage());
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'llmr_deactivate');
function llmr_deactivate() {
    // Clear scheduled hooks
    wp_clear_scheduled_hook('llmr_regenerate_file');
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'llmr_uninstall');
function llmr_uninstall() {
    // Remove options
    delete_option('llmr_settings');
    
    // Remove llms.txt file
    $llms_file = ABSPATH . 'llms.txt';
    if (file_exists($llms_file)) {
        unlink($llms_file);
    }
}