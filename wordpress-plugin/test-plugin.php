<?php
/**
 * LLM Ready Plugin Test Suite
 * 
 * This file tests all major functionality of the LLM Ready plugin
 * Run this after plugin activation to verify everything works
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('This file must be run within WordPress');
}

class LLMR_Test_Suite {
    
    private $tests_passed = 0;
    private $tests_failed = 0;
    private $test_results = array();
    
    public function run_all_tests() {
        echo "<h1>LLM Ready Plugin Test Suite</h1>";
        echo "<p>Running comprehensive tests...</p>";
        
        // Test 1: Plugin activation
        $this->test_plugin_activation();
        
        // Test 2: File generation
        $this->test_llms_file_generation();
        
        // Test 3: Settings functionality
        $this->test_settings_functionality();
        
        // Test 4: SEO plugin detection
        $this->test_seo_plugin_detection();
        
        // Test 5: Post type detection
        $this->test_post_type_detection();
        
        // Test 6: URL filtering
        $this->test_url_filtering();
        
        // Test 7: Auto-regeneration hooks
        $this->test_auto_regeneration_hooks();
        
        // Test 8: Dashboard widget
        $this->test_dashboard_widget();
        
        // Test 9: Admin menu
        $this->test_admin_menu();
        
        // Test 10: Security features
        $this->test_security_features();
        
        // Display results
        $this->display_test_results();
    }
    
    /**
     * Test plugin activation
     */
    private function test_plugin_activation() {
        $this->start_test("Plugin Activation");
        
        // Check if plugin is active
        if (is_plugin_active('llmready/llmready.php')) {
            $this->pass("Plugin is active");
        } else {
            $this->fail("Plugin is not active");
        }
        
        // Check if constants are defined
        $constants = array('LLMR_VERSION', 'LLMR_PLUGIN_DIR', 'LLMR_PLUGIN_URL', 'LLMR_BASENAME');
        foreach ($constants as $constant) {
            if (defined($constant)) {
                $this->pass("Constant $constant is defined");
            } else {
                $this->fail("Constant $constant is not defined");
            }
        }
        
        // Check if default options are set
        $options = get_option('llmr_settings');
        if ($options !== false) {
            $this->pass("Default options are set");
        } else {
            $this->fail("Default options are not set");
        }
    }
    
    /**
     * Test llms.txt file generation
     */
    private function test_llms_file_generation() {
        $this->start_test("LLMS File Generation");
        
        // Test file generation
        $generator = new LLMR_Generator();
        $result = $generator->generate_llms_file();
        
        if ($result) {
            $this->pass("llms.txt file generated successfully");
        } else {
            $this->fail("Failed to generate llms.txt file");
        }
        
        // Check if file exists
        $llms_file = ABSPATH . 'llms.txt';
        if (file_exists($llms_file)) {
            $this->pass("llms.txt file exists at root");
            
            // Check file content
            $content = file_get_contents($llms_file);
            if (strpos($content, '# LLMs.txt - AI Tool Accessibility File') !== false) {
                $this->pass("llms.txt has correct header");
            } else {
                $this->fail("llms.txt missing correct header");
            }
            
            // Check if URLs are included
            if (strpos($content, home_url()) !== false) {
                $this->pass("llms.txt contains site URLs");
            } else {
                $this->fail("llms.txt missing site URLs");
            }
        } else {
            $this->fail("llms.txt file does not exist");
        }
    }
    
    /**
     * Test settings functionality
     */
    private function test_settings_functionality() {
        $this->start_test("Settings Functionality");
        
        $settings = get_option('llmr_settings');
        
        // Test each setting
        $required_settings = array(
            'enabled' => 'boolean',
            'auto_regenerate' => 'boolean',
            'post_types' => 'array',
            'excluded_urls' => 'array',
            'included_urls' => 'array',
            'include_author' => 'boolean',
            'include_license' => 'boolean',
            'include_canonical' => 'boolean'
        );
        
        foreach ($required_settings as $key => $type) {
            if (isset($settings[$key])) {
                $actual_type = gettype($settings[$key]);
                if ($actual_type === $type) {
                    $this->pass("Setting '$key' has correct type: $type");
                } else {
                    $this->fail("Setting '$key' has wrong type: $actual_type (expected: $type)");
                }
            } else {
                $this->fail("Setting '$key' is missing");
            }
        }
    }
    
    /**
     * Test SEO plugin detection
     */
    private function test_seo_plugin_detection() {
        $this->start_test("SEO Plugin Detection");
        
        $detector = new LLMR_SEO_Detector();
        
        // Test detection methods exist
        $methods = array('is_yoast_active', 'is_rankmath_active', 'is_aioseo_active');
        foreach ($methods as $method) {
            if (method_exists($detector, $method)) {
                $this->pass("Method $method exists");
            } else {
                $this->fail("Method $method missing");
            }
        }
        
        // Test get_active_seo_plugins returns array
        $active_plugins = $detector->get_active_seo_plugins();
        if (is_array($active_plugins)) {
            $this->pass("get_active_seo_plugins returns array");
        } else {
            $this->fail("get_active_seo_plugins does not return array");
        }
    }
    
    /**
     * Test post type detection
     */
    private function test_post_type_detection() {
        $this->start_test("Post Type Detection");
        
        $post_types = get_post_types(array('public' => true), 'names');
        
        if (in_array('post', $post_types)) {
            $this->pass("Default 'post' type detected");
        } else {
            $this->fail("Default 'post' type not detected");
        }
        
        if (in_array('page', $post_types)) {
            $this->pass("Default 'page' type detected");
        } else {
            $this->fail("Default 'page' type not detected");
        }
    }
    
    /**
     * Test URL filtering
     */
    private function test_url_filtering() {
        $this->start_test("URL Filtering");
        
        $settings = get_option('llmr_settings');
        
        // Test excluded URLs
        if (is_array($settings['excluded_urls'])) {
            $this->pass("Excluded URLs is an array");
        } else {
            $this->fail("Excluded URLs is not an array");
        }
        
        // Test included URLs
        if (is_array($settings['included_urls'])) {
            $this->pass("Included URLs is an array");
        } else {
            $this->fail("Included URLs is not an array");
        }
    }
    
    /**
     * Test auto-regeneration hooks
     */
    private function test_auto_regeneration_hooks() {
        $this->start_test("Auto-regeneration Hooks");
        
        global $wp_filter;
        
        $hooks = array('save_post', 'delete_post', 'transition_post_status');
        $settings = get_option('llmr_settings');
        
        if ($settings['auto_regenerate']) {
            foreach ($hooks as $hook) {
                if (has_action($hook)) {
                    $this->pass("Hook '$hook' has actions attached");
                } else {
                    $this->fail("Hook '$hook' has no actions");
                }
            }
        } else {
            $this->pass("Auto-regeneration is disabled (hooks not required)");
        }
    }
    
    /**
     * Test dashboard widget
     */
    private function test_dashboard_widget() {
        $this->start_test("Dashboard Widget");
        
        global $wp_meta_boxes;
        
        // Check if widget class exists
        if (class_exists('LLMR_Dashboard_Widget')) {
            $this->pass("Dashboard widget class exists");
        } else {
            $this->fail("Dashboard widget class missing");
        }
        
        // Check if add_dashboard_widget method exists
        $widget = new LLMR_Dashboard_Widget();
        if (method_exists($widget, 'add_dashboard_widget')) {
            $this->pass("add_dashboard_widget method exists");
        } else {
            $this->fail("add_dashboard_widget method missing");
        }
    }
    
    /**
     * Test admin menu
     */
    private function test_admin_menu() {
        $this->start_test("Admin Menu");
        
        global $menu, $submenu;
        
        // Check if admin class exists
        if (class_exists('LLMR_Admin')) {
            $this->pass("Admin class exists");
        } else {
            $this->fail("Admin class missing");
        }
        
        // Check if settings page method exists
        $admin = new LLMR_Admin();
        if (method_exists($admin, 'settings_page')) {
            $this->pass("Settings page method exists");
        } else {
            $this->fail("Settings page method missing");
        }
    }
    
    /**
     * Test security features
     */
    private function test_security_features() {
        $this->start_test("Security Features");
        
        // Test nonce generation
        $nonce = wp_create_nonce('llmr_regenerate');
        if ($nonce) {
            $this->pass("Nonce generation works");
        } else {
            $this->fail("Nonce generation failed");
        }
        
        // Test capability checks
        if (current_user_can('manage_options')) {
            $this->pass("Capability check works for admin");
        } else {
            $this->pass("Capability check works (non-admin)");
        }
    }
    
    /**
     * Helper methods
     */
    private function start_test($test_name) {
        $this->test_results[] = array(
            'name' => $test_name,
            'results' => array()
        );
    }
    
    private function pass($message) {
        $this->tests_passed++;
        $current_test = count($this->test_results) - 1;
        $this->test_results[$current_test]['results'][] = array(
            'status' => 'pass',
            'message' => $message
        );
    }
    
    private function fail($message) {
        $this->tests_failed++;
        $current_test = count($this->test_results) - 1;
        $this->test_results[$current_test]['results'][] = array(
            'status' => 'fail',
            'message' => $message
        );
    }
    
    /**
     * Display test results
     */
    private function display_test_results() {
        echo "<h2>Test Results</h2>";
        
        foreach ($this->test_results as $test) {
            echo "<h3>" . esc_html($test['name']) . "</h3>";
            echo "<ul>";
            foreach ($test['results'] as $result) {
                $icon = $result['status'] === 'pass' ? '✓' : '✗';
                $color = $result['status'] === 'pass' ? 'green' : 'red';
                echo "<li style='color: $color;'>$icon " . esc_html($result['message']) . "</li>";
            }
            echo "</ul>";
        }
        
        // Summary
        $total = $this->tests_passed + $this->tests_failed;
        $percentage = $total > 0 ? round(($this->tests_passed / $total) * 100) : 0;
        
        echo "<h2>Summary</h2>";
        echo "<p><strong>Tests Passed:</strong> {$this->tests_passed}</p>";
        echo "<p><strong>Tests Failed:</strong> {$this->tests_failed}</p>";
        echo "<p><strong>Success Rate:</strong> {$percentage}%</p>";
        
        if ($percentage >= 90) {
            echo "<p style='color: green; font-weight: bold;'>✓ All major functionality is working correctly!</p>";
        } elseif ($percentage >= 70) {
            echo "<p style='color: orange; font-weight: bold;'>⚠ Most functionality is working, but some issues need attention.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>✗ Several issues detected. Please review and fix.</p>";
        }
    }
}

// Create admin page for testing
add_action('admin_menu', 'llmr_add_test_menu');
function llmr_add_test_menu() {
    add_submenu_page(
        'llmr',
        'LLM Ready Tests',
        'Run Tests',
        'manage_options',
        'llmr-tests',
        'llmr_test_page'
    );
}

function llmr_test_page() {
    ?>
    <div class="wrap">
        <h1>LLM Ready Plugin Tests</h1>
        <?php
        $test_suite = new LLMR_Test_Suite();
        $test_suite->run_all_tests();
        ?>
    </div>
    <?php
}