<?php
/**
 * MCP Server Admin Settings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LLMR_MCP_Admin {
    
    /**
     * Initialize MCP admin
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_mcp_menu'), 20);
        add_action('admin_init', array($this, 'register_mcp_settings'));
    }
    
    /**
     * Add MCP submenu
     */
    public function add_mcp_menu() {
        add_submenu_page(
            'llmr',
            __('MCP Server Settings', 'llmr'),
            __('MCP Server', 'llmr'),
            'manage_options',
            'llmr-mcp',
            array($this, 'mcp_settings_page')
        );
    }
    
    /**
     * Register MCP settings
     */
    public function register_mcp_settings() {
        register_setting('llmr_mcp_settings', 'llmr_mcp_settings', array($this, 'sanitize_mcp_settings'));
        
        // Business Information Section
        add_settings_section(
            'llmr_mcp_business',
            __('Business Information', 'llmr'),
            array($this, 'business_section_callback'),
            'llmr_mcp_settings'
        );
        
        // Business fields
        $business_fields = array(
            'business_type' => __('Business Type', 'llmr'),
            'industry' => __('Industry', 'llmr'),
            'founded_year' => __('Founded Year', 'llmr'),
            'employee_count' => __('Number of Employees', 'llmr')
        );
        
        foreach ($business_fields as $field => $label) {
            add_settings_field(
                $field,
                $label,
                array($this, $field . '_field_callback'),
                'llmr_mcp_settings',
                'llmr_mcp_business'
            );
        }
        
        // Contact Information Section
        add_settings_section(
            'llmr_mcp_contact',
            __('Contact Information', 'llmr'),
            array($this, 'contact_section_callback'),
            'llmr_mcp_settings'
        );
        
        // Contact fields
        $contact_fields = array(
            'contact_email' => __('Contact Email', 'llmr'),
            'phone_number' => __('Phone Number', 'llmr'),
            'address' => __('Street Address', 'llmr'),
            'city' => __('City', 'llmr'),
            'state' => __('State/Province', 'llmr'),
            'country' => __('Country', 'llmr'),
            'postal_code' => __('Postal Code', 'llmr'),
            'response_time' => __('Typical Response Time', 'llmr')
        );
        
        foreach ($contact_fields as $field => $label) {
            add_settings_field(
                $field,
                $label,
                array($this, $field . '_field_callback'),
                'llmr_mcp_settings',
                'llmr_mcp_contact'
            );
        }
        
        // Social Media Section
        add_settings_section(
            'llmr_mcp_social',
            __('Social Media', 'llmr'),
            array($this, 'social_section_callback'),
            'llmr_mcp_settings'
        );
        
        // Social fields
        $social_fields = array(
            'facebook_url' => __('Facebook URL', 'llmr'),
            'twitter_url' => __('Twitter/X URL', 'llmr'),
            'linkedin_url' => __('LinkedIn URL', 'llmr'),
            'instagram_url' => __('Instagram URL', 'llmr')
        );
        
        foreach ($social_fields as $field => $label) {
            add_settings_field(
                $field,
                $label,
                array($this, $field . '_field_callback'),
                'llmr_mcp_settings',
                'llmr_mcp_social'
            );
        }
        
        // Services Section
        add_settings_section(
            'llmr_mcp_services',
            __('Services Configuration', 'llmr'),
            array($this, 'services_section_callback'),
            'llmr_mcp_settings'
        );
        
        add_settings_field(
            'manual_services',
            __('Manual Services', 'llmr'),
            array($this, 'manual_services_field_callback'),
            'llmr_mcp_settings',
            'llmr_mcp_services'
        );
        
        // Integration Section
        add_settings_section(
            'llmr_mcp_integration',
            __('Future Integrations', 'llmr'),
            array($this, 'integration_section_callback'),
            'llmr_mcp_settings'
        );
        
        add_settings_field(
            'booking_enabled',
            __('Enable Booking Integration', 'llmr'),
            array($this, 'booking_enabled_field_callback'),
            'llmr_mcp_settings',
            'llmr_mcp_integration'
        );
        
        add_settings_field(
            'booking_url',
            __('Booking Page URL', 'llmr'),
            array($this, 'booking_url_field_callback'),
            'llmr_mcp_settings',
            'llmr_mcp_integration'
        );
    }
    
    /**
     * MCP Settings page
     */
    public function mcp_settings_page() {
        $mcp_discovery_url = rest_url('llmr/mcp/v1/discovery');
        ?>
        <div class="wrap">
            <h1><?php _e('LLM Ready MCP Server Settings', 'llmr'); ?></h1>
            
            <div class="llmr-mcp-header">
                <div class="notice notice-info">
                    <p><?php _e('MCP (Model Context Protocol) Server allows AI agents to directly query your website for business information, contact details, and services.', 'llmr'); ?></p>
                    <p><strong><?php _e('MCP Discovery Endpoint:', 'llmr'); ?></strong> <code><?php echo esc_html($mcp_discovery_url); ?></code></p>
                </div>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('llmr_mcp_settings');
                do_settings_sections('llmr_mcp_settings');
                submit_button();
                ?>
            </form>
            
            <div class="llmr-mcp-test">
                <h2><?php _e('Test MCP Endpoints', 'llmr'); ?></h2>
                <p><?php _e('Test your MCP server endpoints to ensure they\'re working correctly:', 'llmr'); ?></p>
                
                <div class="mcp-test-buttons">
                    <button type="button" class="button" data-endpoint="discovery"><?php _e('Test Discovery', 'llmr'); ?></button>
                    <button type="button" class="button" data-endpoint="business"><?php _e('Test Business Info', 'llmr'); ?></button>
                    <button type="button" class="button" data-endpoint="contact"><?php _e('Test Contact Info', 'llmr'); ?></button>
                    <button type="button" class="button" data-endpoint="services"><?php _e('Test Services', 'llmr'); ?></button>
                </div>
                
                <div id="mcp-test-results" style="margin-top: 20px;"></div>
            </div>
            
            <style>
            .llmr-mcp-header {
                margin-bottom: 30px;
            }
            .mcp-test-buttons {
                margin: 20px 0;
            }
            .mcp-test-buttons .button {
                margin-right: 10px;
            }
            #mcp-test-results {
                background: #f5f5f5;
                padding: 20px;
                border-radius: 5px;
                display: none;
            }
            #mcp-test-results.active {
                display: block;
            }
            #mcp-test-results pre {
                background: #fff;
                padding: 15px;
                border: 1px solid #ddd;
                overflow-x: auto;
            }
            .service-item {
                background: #f9f9f9;
                padding: 15px;
                margin-bottom: 10px;
                border: 1px solid #ddd;
            }
            .service-item input {
                margin-bottom: 5px;
            }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                // MCP endpoint testing
                $('.mcp-test-buttons .button').on('click', function() {
                    var endpoint = $(this).data('endpoint');
                    var $results = $('#mcp-test-results');
                    var url = '<?php echo rest_url('llmr/mcp/v1/'); ?>' + endpoint;
                    
                    $results.addClass('active').html('<p>Testing ' + endpoint + ' endpoint...</p>');
                    
                    $.ajax({
                        url: url,
                        method: endpoint === 'search' ? 'POST' : 'GET',
                        data: endpoint === 'search' ? { query: 'test' } : {},
                        success: function(response) {
                            $results.html('<h3>Success!</h3><pre>' + JSON.stringify(response, null, 2) + '</pre>');
                        },
                        error: function(xhr) {
                            $results.html('<h3>Error</h3><pre>' + xhr.responseText + '</pre>');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }
    
    /**
     * Section callbacks
     */
    public function business_section_callback() {
        echo '<p>' . __('Provide information about your business that AI agents can access.', 'llmr') . '</p>';
    }
    
    public function contact_section_callback() {
        echo '<p>' . __('Contact information that AI agents can provide to users.', 'llmr') . '</p>';
    }
    
    public function social_section_callback() {
        echo '<p>' . __('Social media profiles for your business.', 'llmr') . '</p>';
    }
    
    public function services_section_callback() {
        echo '<p>' . __('Configure services that AI agents can inform users about.', 'llmr') . '</p>';
    }
    
    public function integration_section_callback() {
        echo '<p>' . __('Configure future integrations like booking systems.', 'llmr') . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public function business_type_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['business_type']) ? $settings['business_type'] : '';
        ?>
        <select name="llmr_mcp_settings[business_type]">
            <option value=""><?php _e('Select Type', 'llmr'); ?></option>
            <option value="agency" <?php selected($value, 'agency'); ?>><?php _e('Agency', 'llmr'); ?></option>
            <option value="restaurant" <?php selected($value, 'restaurant'); ?>><?php _e('Restaurant', 'llmr'); ?></option>
            <option value="retail" <?php selected($value, 'retail'); ?>><?php _e('Retail Store', 'llmr'); ?></option>
            <option value="service" <?php selected($value, 'service'); ?>><?php _e('Service Business', 'llmr'); ?></option>
            <option value="healthcare" <?php selected($value, 'healthcare'); ?>><?php _e('Healthcare', 'llmr'); ?></option>
            <option value="education" <?php selected($value, 'education'); ?>><?php _e('Education', 'llmr'); ?></option>
            <option value="nonprofit" <?php selected($value, 'nonprofit'); ?>><?php _e('Non-Profit', 'llmr'); ?></option>
            <option value="other" <?php selected($value, 'other'); ?>><?php _e('Other', 'llmr'); ?></option>
        </select>
        <?php
    }
    
    public function industry_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['industry']) ? $settings['industry'] : '';
        ?>
        <input type="text" name="llmr_mcp_settings[industry]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <p class="description"><?php _e('e.g., Web Development, Marketing, Healthcare', 'llmr'); ?></p>
        <?php
    }
    
    public function founded_year_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['founded_year']) ? $settings['founded_year'] : '';
        ?>
        <input type="number" name="llmr_mcp_settings[founded_year]" value="<?php echo esc_attr($value); ?>" min="1900" max="<?php echo date('Y'); ?>" />
        <?php
    }
    
    public function employee_count_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['employee_count']) ? $settings['employee_count'] : '';
        ?>
        <select name="llmr_mcp_settings[employee_count]">
            <option value=""><?php _e('Select Range', 'llmr'); ?></option>
            <option value="1-10" <?php selected($value, '1-10'); ?>>1-10</option>
            <option value="11-50" <?php selected($value, '11-50'); ?>>11-50</option>
            <option value="51-200" <?php selected($value, '51-200'); ?>>51-200</option>
            <option value="201-500" <?php selected($value, '201-500'); ?>>201-500</option>
            <option value="500+" <?php selected($value, '500+'); ?>>500+</option>
        </select>
        <?php
    }
    
    public function contact_email_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['contact_email']) ? $settings['contact_email'] : get_option('admin_email');
        ?>
        <input type="email" name="llmr_mcp_settings[contact_email]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }
    
    public function phone_number_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['phone_number']) ? $settings['phone_number'] : '';
        ?>
        <input type="tel" name="llmr_mcp_settings[phone_number]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }
    
    public function address_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['address']) ? $settings['address'] : '';
        ?>
        <input type="text" name="llmr_mcp_settings[address]" value="<?php echo esc_attr($value); ?>" class="large-text" />
        <?php
    }
    
    public function city_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['city']) ? $settings['city'] : '';
        ?>
        <input type="text" name="llmr_mcp_settings[city]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }
    
    public function state_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['state']) ? $settings['state'] : '';
        ?>
        <input type="text" name="llmr_mcp_settings[state]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }
    
    public function country_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['country']) ? $settings['country'] : '';
        ?>
        <input type="text" name="llmr_mcp_settings[country]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }
    
    public function postal_code_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['postal_code']) ? $settings['postal_code'] : '';
        ?>
        <input type="text" name="llmr_mcp_settings[postal_code]" value="<?php echo esc_attr($value); ?>" />
        <?php
    }
    
    public function response_time_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['response_time']) ? $settings['response_time'] : '';
        ?>
        <input type="text" name="llmr_mcp_settings[response_time]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <p class="description"><?php _e('e.g., 24 hours, 1-2 business days', 'llmr'); ?></p>
        <?php
    }
    
    public function facebook_url_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['facebook_url']) ? $settings['facebook_url'] : '';
        ?>
        <input type="url" name="llmr_mcp_settings[facebook_url]" value="<?php echo esc_attr($value); ?>" class="large-text" />
        <?php
    }
    
    public function twitter_url_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['twitter_url']) ? $settings['twitter_url'] : '';
        ?>
        <input type="url" name="llmr_mcp_settings[twitter_url]" value="<?php echo esc_attr($value); ?>" class="large-text" />
        <?php
    }
    
    public function linkedin_url_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['linkedin_url']) ? $settings['linkedin_url'] : '';
        ?>
        <input type="url" name="llmr_mcp_settings[linkedin_url]" value="<?php echo esc_attr($value); ?>" class="large-text" />
        <?php
    }
    
    public function instagram_url_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['instagram_url']) ? $settings['instagram_url'] : '';
        ?>
        <input type="url" name="llmr_mcp_settings[instagram_url]" value="<?php echo esc_attr($value); ?>" class="large-text" />
        <?php
    }
    
    public function manual_services_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $services = isset($settings['manual_services']) ? $settings['manual_services'] : array();
        ?>
        <div id="manual-services-container">
            <?php
            if (empty($services)) {
                $services = array(array('name' => '', 'description' => '', 'price' => ''));
            }
            foreach ($services as $index => $service): ?>
                <div class="service-item">
                    <input type="text" name="llmr_mcp_settings[manual_services][<?php echo $index; ?>][name]" 
                           value="<?php echo esc_attr($service['name']); ?>" placeholder="Service Name" class="regular-text" />
                    <input type="text" name="llmr_mcp_settings[manual_services][<?php echo $index; ?>][description]" 
                           value="<?php echo esc_attr($service['description']); ?>" placeholder="Description" class="large-text" />
                    <input type="text" name="llmr_mcp_settings[manual_services][<?php echo $index; ?>][price]" 
                           value="<?php echo esc_attr($service['price']); ?>" placeholder="Price" />
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button" id="add-service"><?php _e('Add Service', 'llmr'); ?></button>
        
        <script>
        jQuery(document).ready(function($) {
            $('#add-service').on('click', function() {
                var index = $('.service-item').length;
                var html = '<div class="service-item">' +
                    '<input type="text" name="llmr_mcp_settings[manual_services][' + index + '][name]" placeholder="Service Name" class="regular-text" />' +
                    '<input type="text" name="llmr_mcp_settings[manual_services][' + index + '][description]" placeholder="Description" class="large-text" />' +
                    '<input type="text" name="llmr_mcp_settings[manual_services][' + index + '][price]" placeholder="Price" />' +
                    '</div>';
                $('#manual-services-container').append(html);
            });
        });
        </script>
        <?php
    }
    
    public function booking_enabled_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['booking_enabled']) ? $settings['booking_enabled'] : false;
        ?>
        <label>
            <input type="checkbox" name="llmr_mcp_settings[booking_enabled]" value="1" <?php checked(1, $value); ?> />
            <?php _e('Enable booking integration for AI agents', 'llmr'); ?>
        </label>
        <?php
    }
    
    public function booking_url_field_callback() {
        $settings = get_option('llmr_mcp_settings');
        $value = isset($settings['booking_url']) ? $settings['booking_url'] : '';
        ?>
        <input type="url" name="llmr_mcp_settings[booking_url]" value="<?php echo esc_attr($value); ?>" class="large-text" />
        <p class="description"><?php _e('URL to your booking or appointment page', 'llmr'); ?></p>
        <?php
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_mcp_settings($input) {
        $sanitized = array();
        
        // Text fields
        $text_fields = array('business_type', 'industry', 'contact_email', 'phone_number', 
                            'address', 'city', 'state', 'country', 'postal_code', 
                            'response_time', 'employee_count');
        
        foreach ($text_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_text_field($input[$field]);
            }
        }
        
        // URL fields
        $url_fields = array('facebook_url', 'twitter_url', 'linkedin_url', 'instagram_url', 'booking_url');
        
        foreach ($url_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = esc_url_raw($input[$field]);
            }
        }
        
        // Number fields
        if (isset($input['founded_year'])) {
            $sanitized['founded_year'] = absint($input['founded_year']);
        }
        
        // Boolean fields
        $sanitized['booking_enabled'] = !empty($input['booking_enabled']) ? 1 : 0;
        
        // Services array
        if (isset($input['manual_services']) && is_array($input['manual_services'])) {
            $sanitized['manual_services'] = array();
            foreach ($input['manual_services'] as $service) {
                if (!empty($service['name'])) {
                    $sanitized['manual_services'][] = array(
                        'name' => sanitize_text_field($service['name']),
                        'description' => sanitize_text_field($service['description']),
                        'price' => sanitize_text_field($service['price'])
                    );
                }
            }
        }
        
        return $sanitized;
    }
}