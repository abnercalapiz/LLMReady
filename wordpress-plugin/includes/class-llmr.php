<?php
/**
 * Main plugin class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LLMR {
    
    protected $version;
    protected $admin;
    protected $generator;
    protected $seo_detector;
    protected $dashboard_widget;
    protected $mcp_server;
    protected $mcp_admin;
    protected $stats;
    
    public function __construct() {
        $this->version = LLMR_VERSION;
        $this->load_dependencies();
    }
    
    private function load_dependencies() {
        // Initialize SEO detector first as other classes depend on it
        $this->seo_detector = new LLMR_SEO_Detector();
        
        // Initialize other classes
        $this->admin = new LLMR_Admin();
        $this->generator = new LLMR_Generator();
        $this->dashboard_widget = new LLMR_Dashboard_Widget();
        $this->mcp_server = new LLMR_MCP_Server();
        $this->mcp_admin = new LLMR_MCP_Admin();
        $this->stats = new LLMR_Stats();
    }
    
    public function run() {
        // Admin hooks
        add_action('admin_menu', array($this->admin, 'add_admin_menu'));
        add_action('admin_init', array($this->admin, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_assets'));
        
        // Generator hooks
        $settings = get_option('llmr_settings');
        if (!empty($settings['auto_regenerate'])) {
            add_action('save_post', array($this->generator, 'regenerate_on_save'), 10, 3);
            add_action('delete_post', array($this->generator, 'regenerate_on_delete'));
            add_action('transition_post_status', array($this->generator, 'regenerate_on_status_change'), 10, 3);
        }
        
        // Dashboard widget
        add_action('wp_dashboard_setup', array($this->dashboard_widget, 'add_dashboard_widget'));
        
        // AJAX handlers
        add_action('wp_ajax_llmr_regenerate', array($this->generator, 'ajax_regenerate'));
        add_action('wp_ajax_llmr_get_stats', array($this->dashboard_widget, 'ajax_get_stats'));
        
        // Add settings link to plugins page
        add_filter('plugin_action_links_' . LLMR_BASENAME, array($this, 'add_settings_link'));
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=llmr') . '">' . __('Settings', 'llmr') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}