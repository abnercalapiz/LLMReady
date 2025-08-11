<?php
/**
 * MCP Server implementation for LLM Ready
 * Allows AI agents to directly query the website for business information
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LLMR_MCP_Server {
    
    /**
     * Initialize MCP Server
     */
    public function __construct() {
        // Register REST API endpoints
        add_action('rest_api_init', array($this, 'register_mcp_endpoints'));
        
        // Add MCP discovery to site headers
        add_action('wp_head', array($this, 'add_mcp_discovery_meta'));
        add_action('send_headers', array($this, 'add_mcp_headers'));
    }
    
    /**
     * Register MCP REST API endpoints
     */
    public function register_mcp_endpoints() {
        // MCP Discovery endpoint
        register_rest_route('llmr/mcp/v1', '/discovery', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_discovery'),
            'permission_callback' => '__return_true'
        ));
        
        // Business information endpoint
        register_rest_route('llmr/mcp/v1', '/business', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_business_info'),
            'permission_callback' => '__return_true'
        ));
        
        // Contact details endpoint
        register_rest_route('llmr/mcp/v1', '/contact', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_contact_info'),
            'permission_callback' => '__return_true'
        ));
        
        // Services/Products endpoint
        register_rest_route('llmr/mcp/v1', '/services', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_services'),
            'permission_callback' => '__return_true'
        ));
        
        // Content search endpoint
        register_rest_route('llmr/mcp/v1', '/search', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_search'),
            'permission_callback' => '__return_true',
            'args' => array(
                'query' => array(
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10
                )
            )
        ));
        
        // Booking/Appointment endpoint (placeholder for future)
        register_rest_route('llmr/mcp/v1', '/booking', array(
            'methods' => 'GET,POST',
            'callback' => array($this, 'handle_booking'),
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Handle MCP discovery request
     */
    public function handle_discovery($request) {
        $settings = get_option('llmr_mcp_settings', array());
        
        $discovery = array(
            'mcp_version' => '1.0',
            'server_name' => get_bloginfo('name'),
            'server_description' => get_bloginfo('description'),
            'capabilities' => array(
                'business_info' => true,
                'contact_info' => true,
                'content_search' => true,
                'services_listing' => true,
                'booking' => !empty($settings['booking_enabled'])
            ),
            'endpoints' => array(
                'business' => rest_url('llmr/mcp/v1/business'),
                'contact' => rest_url('llmr/mcp/v1/contact'),
                'services' => rest_url('llmr/mcp/v1/services'),
                'search' => rest_url('llmr/mcp/v1/search'),
                'booking' => rest_url('llmr/mcp/v1/booking')
            ),
            'rate_limits' => array(
                'requests_per_minute' => 60,
                'requests_per_hour' => 1000
            ),
            'authentication' => array(
                'required' => false,
                'type' => 'api_key',
                'description' => 'Optional API key for higher rate limits'
            )
        );
        
        return new WP_REST_Response($discovery, 200);
    }
    
    /**
     * Handle business information request
     */
    public function handle_business_info($request) {
        $settings = get_option('llmr_mcp_settings', array());
        
        // Build business hours array
        $business_hours = array();
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        
        foreach ($days as $day) {
            if (!empty($settings['hours_' . $day . '_closed'])) {
                $business_hours[$day] = 'Closed';
            } else {
                $open = !empty($settings['hours_' . $day . '_open']) ? $settings['hours_' . $day . '_open'] : '9:00';
                $close = !empty($settings['hours_' . $day . '_close']) ? $settings['hours_' . $day . '_close'] : '17:00';
                $business_hours[$day] = $open . ' - ' . $close;
            }
        }
        
        $business_hours['timezone'] = !empty($settings['timezone']) ? $settings['timezone'] : get_option('timezone_string', 'UTC');
        
        $business_info = array(
            'name' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'url' => home_url(),
            'type' => !empty($settings['business_type']) ? $settings['business_type'] : 'website',
            'industry' => !empty($settings['industry']) ? $settings['industry'] : '',
            'founded' => !empty($settings['founded_year']) ? $settings['founded_year'] : '',
            'employees' => !empty($settings['employee_count']) ? $settings['employee_count'] : '',
            'services' => $this->get_services_summary(),
            'hours' => $business_hours,
            'locations' => !empty($settings['locations']) ? $settings['locations'] : array(),
            'social_media' => array(
                'facebook' => !empty($settings['facebook_url']) ? $settings['facebook_url'] : '',
                'twitter' => !empty($settings['twitter_url']) ? $settings['twitter_url'] : '',
                'linkedin' => !empty($settings['linkedin_url']) ? $settings['linkedin_url'] : '',
                'instagram' => !empty($settings['instagram_url']) ? $settings['instagram_url'] : ''
            )
        );
        
        return new WP_REST_Response($business_info, 200);
    }
    
    /**
     * Handle contact information request
     */
    public function handle_contact_info($request) {
        $settings = get_option('llmr_mcp_settings', array());
        
        $contact_info = array(
            'email' => !empty($settings['contact_email']) ? $settings['contact_email'] : get_option('admin_email'),
            'phone' => !empty($settings['phone_number']) ? $settings['phone_number'] : '',
            'address' => !empty($settings['address']) ? $settings['address'] : '',
            'city' => !empty($settings['city']) ? $settings['city'] : '',
            'state' => !empty($settings['state']) ? $settings['state'] : '',
            'country' => !empty($settings['country']) ? $settings['country'] : '',
            'postal_code' => !empty($settings['postal_code']) ? $settings['postal_code'] : '',
            'contact_form_url' => !empty($settings['contact_page']) ? get_permalink($settings['contact_page']) : '',
            'preferred_contact_method' => !empty($settings['preferred_contact']) ? $settings['preferred_contact'] : 'email',
            'response_time' => !empty($settings['response_time']) ? $settings['response_time'] : '24-48 hours'
        );
        
        return new WP_REST_Response($contact_info, 200);
    }
    
    /**
     * Handle services listing request
     */
    public function handle_services($request) {
        $settings = get_option('llmr_mcp_settings', array());
        $services = array();
        
        // Get services from custom post type if exists
        if (post_type_exists('services')) {
            $service_posts = get_posts(array(
                'post_type' => 'services',
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            foreach ($service_posts as $service) {
                $services[] = array(
                    'id' => $service->ID,
                    'name' => $service->post_title,
                    'description' => wp_strip_all_tags($service->post_excerpt ?: $service->post_content),
                    'url' => get_permalink($service->ID),
                    'price' => get_post_meta($service->ID, 'price', true),
                    'duration' => get_post_meta($service->ID, 'duration', true)
                );
            }
        }
        
        // Add manually configured services
        if (!empty($settings['manual_services'])) {
            $services = array_merge($services, $settings['manual_services']);
        }
        
        // If no services found, provide general categories
        if (empty($services)) {
            $services = $this->get_services_from_categories();
        }
        
        return new WP_REST_Response(array(
            'services' => $services,
            'total' => count($services)
        ), 200);
    }
    
    /**
     * Handle content search request
     */
    public function handle_search($request) {
        $query = $request->get_param('query');
        $limit = $request->get_param('limit');
        
        $args = array(
            's' => $query,
            'posts_per_page' => $limit,
            'post_type' => 'any',
            'post_status' => 'publish'
        );
        
        $search_query = new WP_Query($args);
        $results = array();
        
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'url' => get_permalink(),
                    'type' => get_post_type(),
                    'date' => get_the_date('c'),
                    'relevance_score' => $this->calculate_relevance(get_the_ID(), $query)
                );
            }
            wp_reset_postdata();
        }
        
        // Sort by relevance
        usort($results, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });
        
        return new WP_REST_Response(array(
            'query' => $query,
            'results' => $results,
            'total' => count($results)
        ), 200);
    }
    
    /**
     * Handle booking requests (placeholder)
     */
    public function handle_booking($request) {
        $settings = get_option('llmr_mcp_settings', array());
        
        if (empty($settings['booking_enabled'])) {
            return new WP_REST_Response(array(
                'available' => false,
                'message' => 'Booking functionality is not enabled on this site.'
            ), 200);
        }
        
        // Placeholder for future booking integration
        return new WP_REST_Response(array(
            'available' => true,
            'booking_url' => !empty($settings['booking_url']) ? $settings['booking_url'] : '',
            'booking_system' => !empty($settings['booking_system']) ? $settings['booking_system'] : '',
            'message' => 'Please visit our booking page or contact us directly to schedule an appointment.'
        ), 200);
    }
    
    /**
     * Add MCP discovery meta tags
     */
    public function add_mcp_discovery_meta() {
        $mcp_endpoint = rest_url('llmr/mcp/v1/discovery');
        echo '<link rel="mcp-server" href="' . esc_url($mcp_endpoint) . '" />' . "\n";
        echo '<meta name="mcp-version" content="1.0" />' . "\n";
    }
    
    /**
     * Add MCP headers
     */
    public function add_mcp_headers() {
        if (!headers_sent()) {
            header('X-MCP-Server: ' . rest_url('llmr/mcp/v1/discovery'));
            header('X-MCP-Version: 1.0');
        }
    }
    
    /**
     * Helper: Get services summary
     */
    private function get_services_summary() {
        $categories = get_categories(array('hide_empty' => true));
        $services = array();
        
        foreach ($categories as $category) {
            if (stripos($category->name, 'service') !== false || 
                stripos($category->name, 'product') !== false) {
                $services[] = $category->name;
            }
        }
        
        return array_slice($services, 0, 10);
    }
    
    /**
     * Helper: Get services from categories
     */
    private function get_services_from_categories() {
        $categories = get_categories(array('hide_empty' => true));
        $services = array();
        
        foreach ($categories as $category) {
            $services[] = array(
                'name' => $category->name,
                'description' => $category->description,
                'url' => get_category_link($category->term_id),
                'post_count' => $category->count
            );
        }
        
        return $services;
    }
    
    /**
     * Helper: Calculate search relevance
     */
    private function calculate_relevance($post_id, $query) {
        $score = 0;
        $post = get_post($post_id);
        $query_lower = strtolower($query);
        
        // Title match (highest weight)
        if (stripos($post->post_title, $query) !== false) {
            $score += 10;
        }
        
        // Content match
        if (stripos($post->post_content, $query) !== false) {
            $score += 5;
        }
        
        // Excerpt match
        if (stripos($post->post_excerpt, $query) !== false) {
            $score += 3;
        }
        
        // Category/tag match
        $categories = wp_get_post_categories($post_id, array('fields' => 'names'));
        foreach ($categories as $cat) {
            if (stripos($cat, $query) !== false) {
                $score += 2;
            }
        }
        
        return $score;
    }
}