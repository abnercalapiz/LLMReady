<?php
/**
 * Security Check for LLM Ready Plugin
 * This file performs various security checks to ensure the plugin is secure
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('This is a WordPress plugin file and cannot be accessed directly.');
}

class LLMR_Security_Check {
    
    private $errors = array();
    private $warnings = array();
    private $passes = array();
    
    /**
     * Run all security checks
     */
    public function run_checks() {
        echo "<h1>LLM Ready Security Check Report</h1>";
        echo "<p>Running security checks...</p>";
        
        // Check 1: Direct file access prevention
        $this->check_direct_access_prevention();
        
        // Check 2: Nonce verification
        $this->check_nonce_verification();
        
        // Check 3: Capability checks
        $this->check_capability_checks();
        
        // Check 4: Data sanitization
        $this->check_data_sanitization();
        
        // Check 5: SQL injection prevention
        $this->check_sql_injection_prevention();
        
        // Check 6: XSS prevention
        $this->check_xss_prevention();
        
        // Check 7: File permissions
        $this->check_file_permissions();
        
        // Check 8: CSRF protection
        $this->check_csrf_protection();
        
        // Display results
        $this->display_results();
    }
    
    /**
     * Check if all PHP files have direct access prevention
     */
    private function check_direct_access_prevention() {
        $files = array(
            'llmready.php',
            'includes/class-llmr.php',
            'includes/class-llmr-admin.php',
            'includes/class-llmr-generator.php',
            'includes/class-llmr-seo-detector.php',
            'includes/class-llmr-dashboard-widget.php'
        );
        
        foreach ($files as $file) {
            $content = file_get_contents(LLMR_PLUGIN_DIR . $file);
            if (strpos($content, "if (!defined('ABSPATH'))") !== false) {
                $this->passes[] = "✓ Direct access prevention found in: $file";
            } else {
                $this->errors[] = "✗ Missing direct access prevention in: $file";
            }
        }
    }
    
    /**
     * Check nonce verification in AJAX handlers
     */
    private function check_nonce_verification() {
        $content = file_get_contents(LLMR_PLUGIN_DIR . 'includes/class-llmr-generator.php');
        
        if (strpos($content, 'wp_verify_nonce') !== false) {
            $this->passes[] = "✓ Nonce verification found in AJAX handlers";
        } else {
            $this->errors[] = "✗ Missing nonce verification in AJAX handlers";
        }
    }
    
    /**
     * Check capability checks
     */
    private function check_capability_checks() {
        $files = array(
            'includes/class-llmr-admin.php',
            'includes/class-llmr-generator.php',
            'includes/class-llmr-dashboard-widget.php'
        );
        
        $capability_checks_found = 0;
        foreach ($files as $file) {
            $content = file_get_contents(LLMR_PLUGIN_DIR . $file);
            if (strpos($content, 'current_user_can') !== false) {
                $capability_checks_found++;
            }
        }
        
        if ($capability_checks_found >= 2) {
            $this->passes[] = "✓ Capability checks found in admin functions";
        } else {
            $this->warnings[] = "⚠ Limited capability checks found";
        }
    }
    
    /**
     * Check data sanitization
     */
    private function check_data_sanitization() {
        $content = file_get_contents(LLMR_PLUGIN_DIR . 'includes/class-llmr-admin.php');
        
        $sanitization_functions = array(
            'sanitize_text_field',
            'esc_url_raw',
            'esc_html',
            'esc_attr',
            'wp_strip_all_tags'
        );
        
        $found = 0;
        foreach ($sanitization_functions as $func) {
            if (strpos($content, $func) !== false) {
                $found++;
            }
        }
        
        if ($found >= 3) {
            $this->passes[] = "✓ Proper data sanitization found";
        } else {
            $this->warnings[] = "⚠ Limited data sanitization functions found";
        }
    }
    
    /**
     * Check SQL injection prevention
     */
    private function check_sql_injection_prevention() {
        $all_files = glob(LLMR_PLUGIN_DIR . '**/*.php');
        $uses_direct_sql = false;
        
        foreach ($all_files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/\$wpdb->query|wpdb->get_results|wpdb->get_var/', $content)) {
                    $uses_direct_sql = true;
                    if (!preg_match('/\$wpdb->prepare/', $content)) {
                        $this->errors[] = "✗ Direct SQL without prepare() in: " . basename($file);
                    }
                }
            }
        }
        
        if (!$uses_direct_sql) {
            $this->passes[] = "✓ No direct SQL queries found (using WP APIs)";
        }
    }
    
    /**
     * Check XSS prevention
     */
    private function check_xss_prevention() {
        $content = file_get_contents(LLMR_PLUGIN_DIR . 'includes/class-llmr-admin.php');
        
        $escape_functions = array(
            'esc_html',
            'esc_attr',
            'esc_url',
            'esc_textarea'
        );
        
        $found = 0;
        foreach ($escape_functions as $func) {
            if (strpos($content, $func) !== false) {
                $found++;
            }
        }
        
        if ($found >= 3) {
            $this->passes[] = "✓ Proper output escaping found";
        } else {
            $this->errors[] = "✗ Insufficient output escaping";
        }
    }
    
    /**
     * Check file permissions
     */
    private function check_file_permissions() {
        $writable_needed = array(
            ABSPATH . 'llms.txt'
        );
        
        foreach ($writable_needed as $file) {
            if (file_exists($file) && !is_writable($file)) {
                $this->warnings[] = "⚠ File not writable: $file";
            }
        }
        
        $this->passes[] = "✓ File permission checks completed";
    }
    
    /**
     * Check CSRF protection
     */
    private function check_csrf_protection() {
        $admin_content = file_get_contents(LLMR_PLUGIN_DIR . 'includes/class-llmr-admin.php');
        
        if (strpos($admin_content, 'settings_fields') !== false) {
            $this->passes[] = "✓ CSRF protection via settings_fields found";
        } else {
            $this->errors[] = "✗ Missing CSRF protection in settings";
        }
    }
    
    /**
     * Display results
     */
    private function display_results() {
        echo "<h2>Security Check Results</h2>";
        
        // Display passes
        if (!empty($this->passes)) {
            echo "<h3 style='color: green;'>Passed Checks (" . count($this->passes) . ")</h3>";
            echo "<ul>";
            foreach ($this->passes as $pass) {
                echo "<li>$pass</li>";
            }
            echo "</ul>";
        }
        
        // Display warnings
        if (!empty($this->warnings)) {
            echo "<h3 style='color: orange;'>Warnings (" . count($this->warnings) . ")</h3>";
            echo "<ul>";
            foreach ($this->warnings as $warning) {
                echo "<li>$warning</li>";
            }
            echo "</ul>";
        }
        
        // Display errors
        if (!empty($this->errors)) {
            echo "<h3 style='color: red;'>Failed Checks (" . count($this->errors) . ")</h3>";
            echo "<ul>";
            foreach ($this->errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
        }
        
        // Summary
        $total = count($this->passes) + count($this->warnings) + count($this->errors);
        $score = (count($this->passes) / $total) * 100;
        
        echo "<h3>Summary</h3>";
        echo "<p><strong>Security Score: " . round($score) . "%</strong></p>";
        
        if ($score >= 80) {
            echo "<p style='color: green;'>✓ The plugin has good security practices in place.</p>";
        } elseif ($score >= 60) {
            echo "<p style='color: orange;'>⚠ The plugin has basic security but could be improved.</p>";
        } else {
            echo "<p style='color: red;'>✗ The plugin needs security improvements.</p>";
        }
    }
}

// Run functionality check
class LLMR_Functionality_Check {
    
    private $results = array();
    
    public function run_checks() {
        echo "<h2>Functionality Check</h2>";
        
        // Check 1: Plugin files exist
        $this->check_required_files();
        
        // Check 2: Classes can be instantiated
        $this->check_class_instantiation();
        
        // Check 3: Hooks are registered
        $this->check_hooks();
        
        // Check 4: Settings structure
        $this->check_settings_structure();
        
        // Display results
        $this->display_results();
    }
    
    private function check_required_files() {
        $required_files = array(
            'llmready.php',
            'includes/class-llmr.php',
            'includes/class-llmr-admin.php',
            'includes/class-llmr-generator.php',
            'includes/class-llmr-seo-detector.php',
            'includes/class-llmr-dashboard-widget.php',
            'assets/css/admin.css',
            'assets/js/admin.js'
        );
        
        foreach ($required_files as $file) {
            if (file_exists(LLMR_PLUGIN_DIR . $file)) {
                $this->results[] = "✓ File exists: $file";
            } else {
                $this->results[] = "✗ Missing file: $file";
            }
        }
    }
    
    private function check_class_instantiation() {
        $classes = array(
            'LLMR',
            'LLMR_Admin',
            'LLMR_Generator',
            'LLMR_SEO_Detector',
            'LLMR_Dashboard_Widget'
        );
        
        foreach ($classes as $class) {
            if (class_exists($class)) {
                $this->results[] = "✓ Class can be loaded: $class";
            } else {
                $this->results[] = "✗ Class not found: $class";
            }
        }
    }
    
    private function check_hooks() {
        global $wp_filter;
        
        $important_hooks = array(
            'admin_menu',
            'admin_init',
            'admin_enqueue_scripts',
            'save_post',
            'wp_dashboard_setup'
        );
        
        foreach ($important_hooks as $hook) {
            if (isset($wp_filter[$hook])) {
                $this->results[] = "✓ Hook registered: $hook";
            } else {
                $this->results[] = "⚠ Hook might not be registered yet: $hook";
            }
        }
    }
    
    private function check_settings_structure() {
        $settings = get_option('llmr_settings');
        
        if ($settings) {
            $required_keys = array(
                'enabled',
                'auto_regenerate',
                'post_types',
                'excluded_urls',
                'included_urls',
                'include_author',
                'include_license',
                'include_canonical'
            );
            
            foreach ($required_keys as $key) {
                if (isset($settings[$key])) {
                    $this->results[] = "✓ Setting exists: $key";
                } else {
                    $this->results[] = "✗ Missing setting: $key";
                }
            }
        } else {
            $this->results[] = "⚠ Settings not initialized (normal if plugin not activated)";
        }
    }
    
    private function display_results() {
        echo "<ul>";
        foreach ($this->results as $result) {
            echo "<li>$result</li>";
        }
        echo "</ul>";
    }
}

// Only run if accessed directly for testing
if (defined('WP_DEBUG') && WP_DEBUG && isset($_GET['llmr_security_check'])) {
    $security_check = new LLMR_Security_Check();
    $security_check->run_checks();
    
    $functionality_check = new LLMR_Functionality_Check();
    $functionality_check->run_checks();
}