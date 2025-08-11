<?php
/**
 * Admin settings page class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LLMR_Admin {
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('LLM Ready Settings', 'llmr'),
            __('LLM Ready', 'llmr'),
            'manage_options',
            'llmr',
            array($this, 'settings_page'),
            'dashicons-chart-line',
            100
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('llmr_settings', 'llmr_settings', array($this, 'sanitize_settings'));
        
        // General Settings Section
        add_settings_section(
            'llmr_general',
            __('General Settings', 'llmr'),
            array($this, 'general_section_callback'),
            'llmr_settings'
        );
        
        // Enable/Disable
        add_settings_field(
            'enabled',
            __('Enable Plugin', 'llmr'),
            array($this, 'enabled_field_callback'),
            'llmr_settings',
            'llmr_general'
        );
        
        // Auto-regenerate
        add_settings_field(
            'auto_regenerate',
            __('Auto-regenerate on Update', 'llmr'),
            array($this, 'auto_regenerate_field_callback'),
            'llmr_settings',
            'llmr_general'
        );
        
        // Post Types
        add_settings_field(
            'post_types',
            __('Post Types to Include', 'llmr'),
            array($this, 'post_types_field_callback'),
            'llmr_settings',
            'llmr_general'
        );
        
        // Metadata Settings Section
        add_settings_section(
            'llmr_metadata',
            __('Metadata Settings', 'llmr'),
            array($this, 'metadata_section_callback'),
            'llmr_settings'
        );
        
        // Include Author
        add_settings_field(
            'include_author',
            __('Include Author', 'llmr'),
            array($this, 'include_author_field_callback'),
            'llmr_settings',
            'llmr_metadata'
        );
        
        // Include License
        add_settings_field(
            'include_license',
            __('Include License', 'llmr'),
            array($this, 'include_license_field_callback'),
            'llmr_settings',
            'llmr_metadata'
        );
        
        // Include Canonical
        add_settings_field(
            'include_canonical',
            __('Include Canonical URL', 'llmr'),
            array($this, 'include_canonical_field_callback'),
            'llmr_settings',
            'llmr_metadata'
        );
        
        // URL Management Section
        add_settings_section(
            'llmr_urls',
            __('URL Management', 'llmr'),
            array($this, 'urls_section_callback'),
            'llmr_settings'
        );
        
        // Excluded URLs
        add_settings_field(
            'excluded_urls',
            __('Excluded URLs', 'llmr'),
            array($this, 'excluded_urls_field_callback'),
            'llmr_settings',
            'llmr_urls'
        );
        
        // Included URLs
        add_settings_field(
            'included_urls',
            __('Additional URLs', 'llmr'),
            array($this, 'included_urls_field_callback'),
            'llmr_settings',
            'llmr_urls'
        );
    }
    
    /**
     * Settings page output
     */
    public function settings_page() {
        $settings = get_option('llmr_settings');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="llmr-header">
                <p><?php _e('Improve your website\'s visibility in AI tools like ChatGPT, Perplexity, and Claude.', 'llmr'); ?></p>
                
                <?php if (!empty($settings['last_generated'])): ?>
                    <div class="notice notice-info inline">
                        <p>
                            <?php 
                            printf(
                                __('Last generated: %s | Total URLs: %d', 'llmr'),
                                human_time_diff(strtotime($settings['last_generated']), current_time('timestamp')) . ' ago',
                                $settings['total_urls']
                            ); 
                            ?>
                            <a href="<?php echo site_url('/llms.txt'); ?>" target="_blank" class="button button-small">
                                <?php _e('View llms.txt', 'llmr'); ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('llmr_settings');
                do_settings_sections('llmr_settings');
                submit_button();
                ?>
            </form>
            
            <div class="llmr-actions">
                <h2><?php _e('Actions', 'llmr'); ?></h2>
                <button type="button" class="button button-primary" id="llmr-regenerate">
                    <?php _e('Regenerate llms.txt Now', 'llmr'); ?>
                </button>
                <span class="spinner"></span>
                <div id="llmr-message"></div>
            </div>
            
            <div class="llmr-info">
                <h2><?php _e('Detected SEO Plugins', 'llmr'); ?></h2>
                <?php
                $seo_detector = new LLMR_SEO_Detector();
                $detected = array();
                
                if ($seo_detector->is_yoast_active()) {
                    $detected[] = 'Yoast SEO';
                }
                if ($seo_detector->is_rankmath_active()) {
                    $detected[] = 'Rank Math';
                }
                if ($seo_detector->is_aioseo_active()) {
                    $detected[] = 'All in One SEO';
                }
                
                if (empty($detected)) {
                    echo '<p>' . __('No SEO plugins detected. LLM Ready will include all published posts and pages.', 'llmr') . '</p>';
                } else {
                    echo '<p>' . sprintf(__('Detected: %s. Posts/pages marked as noindex will be excluded.', 'llmr'), implode(', ', $detected)) . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Section callbacks
     */
    public function general_section_callback() {
        echo '<p>' . __('Configure general plugin settings.', 'llmr') . '</p>';
    }
    
    public function metadata_section_callback() {
        echo '<p>' . __('Choose which metadata to include in the llms.txt file.', 'llmr') . '</p>';
    }
    
    public function urls_section_callback() {
        echo '<p>' . __('Manage which URLs are included or excluded from the llms.txt file.', 'llmr') . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public function enabled_field_callback() {
        $settings = get_option('llmr_settings');
        ?>
        <label>
            <input type="checkbox" name="llmr_settings[enabled]" value="1" <?php checked(1, $settings['enabled'], true); ?> />
            <?php _e('Enable LLM Ready plugin', 'llmr'); ?>
        </label>
        <?php
    }
    
    public function auto_regenerate_field_callback() {
        $settings = get_option('llmr_settings');
        ?>
        <label>
            <input type="checkbox" name="llmr_settings[auto_regenerate]" value="1" <?php checked(1, $settings['auto_regenerate'], true); ?> />
            <?php _e('Automatically regenerate llms.txt when posts are published or updated', 'llmr'); ?>
        </label>
        <?php
    }
    
    public function post_types_field_callback() {
        $settings = get_option('llmr_settings');
        $post_types = get_post_types(array('public' => true), 'objects');
        
        foreach ($post_types as $post_type) {
            ?>
            <label style="display: block; margin-bottom: 5px;">
                <input type="checkbox" 
                       name="llmr_settings[post_types][]" 
                       value="<?php echo esc_attr($post_type->name); ?>"
                       <?php checked(in_array($post_type->name, (array)$settings['post_types']), true); ?> />
                <?php echo esc_html($post_type->label); ?>
            </label>
            <?php
        }
    }
    
    public function include_author_field_callback() {
        $settings = get_option('llmr_settings');
        ?>
        <label>
            <input type="checkbox" name="llmr_settings[include_author]" value="1" <?php checked(1, $settings['include_author'], true); ?> />
            <?php _e('Include author information', 'llmr'); ?>
        </label>
        <?php
    }
    
    public function include_license_field_callback() {
        $settings = get_option('llmr_settings');
        ?>
        <label>
            <input type="checkbox" name="llmr_settings[include_license]" value="1" <?php checked(1, $settings['include_license'], true); ?> />
            <?php _e('Include license information (defaults to CC BY 4.0)', 'llmr'); ?>
        </label>
        <?php
    }
    
    public function include_canonical_field_callback() {
        $settings = get_option('llmr_settings');
        ?>
        <label>
            <input type="checkbox" name="llmr_settings[include_canonical]" value="1" <?php checked(1, $settings['include_canonical'], true); ?> />
            <?php _e('Include canonical URL', 'llmr'); ?>
        </label>
        <?php
    }
    
    public function excluded_urls_field_callback() {
        $settings = get_option('llmr_settings');
        $excluded = isset($settings['excluded_urls']) ? implode("\n", $settings['excluded_urls']) : '';
        ?>
        <textarea name="llmr_settings[excluded_urls]" rows="5" cols="50" class="large-text"><?php echo esc_textarea($excluded); ?></textarea>
        <p class="description"><?php _e('Enter one URL per line. These URLs will be excluded from llms.txt even if they match other criteria.', 'llmr'); ?></p>
        <?php
    }
    
    public function included_urls_field_callback() {
        $settings = get_option('llmr_settings');
        $included = isset($settings['included_urls']) ? implode("\n", $settings['included_urls']) : '';
        ?>
        <textarea name="llmr_settings[included_urls]" rows="5" cols="50" class="large-text"><?php echo esc_textarea($included); ?></textarea>
        <p class="description"><?php _e('Enter one URL per line. These URLs will be added to llms.txt regardless of other settings.', 'llmr'); ?></p>
        <?php
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Boolean fields
        $sanitized['enabled'] = !empty($input['enabled']) ? 1 : 0;
        $sanitized['auto_regenerate'] = !empty($input['auto_regenerate']) ? 1 : 0;
        $sanitized['include_author'] = !empty($input['include_author']) ? 1 : 0;
        $sanitized['include_license'] = !empty($input['include_license']) ? 1 : 0;
        $sanitized['include_canonical'] = !empty($input['include_canonical']) ? 1 : 0;
        
        // Post types
        if (isset($input['post_types']) && is_array($input['post_types'])) {
            $sanitized['post_types'] = array_map('sanitize_text_field', $input['post_types']);
        } else {
            $sanitized['post_types'] = array();
        }
        
        // URLs
        if (isset($input['excluded_urls'])) {
            // Check if it's already an array (from programmatic update) or string (from form)
            if (is_array($input['excluded_urls'])) {
                $sanitized['excluded_urls'] = array_filter(array_map('esc_url_raw', $input['excluded_urls']));
            } else {
                $urls = explode("\n", $input['excluded_urls']);
                $sanitized['excluded_urls'] = array_filter(array_map('esc_url_raw', array_map('trim', $urls)));
            }
        } else {
            $sanitized['excluded_urls'] = array();
        }
        
        if (isset($input['included_urls'])) {
            // Check if it's already an array (from programmatic update) or string (from form)
            if (is_array($input['included_urls'])) {
                $sanitized['included_urls'] = array_filter(array_map('esc_url_raw', $input['included_urls']));
            } else {
                $urls = explode("\n", $input['included_urls']);
                $sanitized['included_urls'] = array_filter(array_map('esc_url_raw', array_map('trim', $urls)));
            }
        } else {
            $sanitized['included_urls'] = array();
        }
        
        // Preserve existing values
        $existing = get_option('llmr_settings');
        $sanitized['last_generated'] = isset($existing['last_generated']) ? $existing['last_generated'] : '';
        $sanitized['total_urls'] = isset($existing['total_urls']) ? $existing['total_urls'] : 0;
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_llmr' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'llmr-admin',
            LLMR_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            LLMR_VERSION
        );
        
        wp_enqueue_script(
            'llmr-admin',
            LLMR_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            LLMR_VERSION,
            true
        );
        
        wp_localize_script('llmr-admin', 'llmr_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('llmr_regenerate')
        ));
    }
}