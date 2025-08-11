<?php
/**
 * Stats and Analytics for LLM Ready
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LLMR_Stats {
    
    /**
     * Initialize stats
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_stats_menu'), 15);
        add_action('rest_api_init', array($this, 'register_stats_tracking'));
        add_action('wp_ajax_llmr_get_stats_data', array($this, 'ajax_get_stats_data'));
        add_action('wp_ajax_llmr_reset_stats', array($this, 'ajax_reset_stats'));
        add_action('wp_ajax_llmr_export_stats', array($this, 'ajax_export_stats'));
        
        // Track MCP API calls
        add_action('rest_api_init', array($this, 'track_mcp_calls'), 1);
    }
    
    /**
     * Add stats menu
     */
    public function add_stats_menu() {
        add_submenu_page(
            'llmr',
            __('LLM Ready Stats', 'llmr'),
            __('Stats', 'llmr'),
            'manage_options',
            'llmr-stats',
            array($this, 'stats_page')
        );
    }
    
    /**
     * Track MCP API calls
     */
    public function track_mcp_calls() {
        if (strpos($_SERVER['REQUEST_URI'], '/wp-json/llmr/mcp/v1/') !== false) {
            $this->log_api_call();
        }
    }
    
    /**
     * Log API call
     */
    private function log_api_call() {
        $endpoint = str_replace('/wp-json/llmr/mcp/v1/', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $endpoint = explode('?', $endpoint)[0];
        
        // Get current stats
        $stats = get_option('llmr_api_stats', array());
        
        // Initialize if needed
        if (!isset($stats['total_calls'])) {
            $stats = array(
                'total_calls' => 0,
                'endpoints' => array(),
                'daily' => array(),
                'hourly' => array()
            );
        }
        
        // Update total calls
        $stats['total_calls']++;
        
        // Update endpoint specific stats
        if (!isset($stats['endpoints'][$endpoint])) {
            $stats['endpoints'][$endpoint] = 0;
        }
        $stats['endpoints'][$endpoint]++;
        
        // Update daily stats
        $today = date('Y-m-d');
        if (!isset($stats['daily'][$today])) {
            $stats['daily'][$today] = 0;
        }
        $stats['daily'][$today]++;
        
        // Update hourly stats for today
        $hour = date('H');
        if (!isset($stats['hourly'][$hour])) {
            $stats['hourly'][$hour] = 0;
        }
        $stats['hourly'][$hour]++;
        
        // Keep only last 30 days
        if (count($stats['daily']) > 30) {
            $stats['daily'] = array_slice($stats['daily'], -30, null, true);
        }
        
        update_option('llmr_api_stats', $stats);
    }
    
    /**
     * Stats page
     */
    public function stats_page() {
        $settings = get_option('llmr_settings');
        $stats = get_option('llmr_api_stats', array());
        
        // Get content stats
        $total_urls = isset($settings['total_urls']) ? $settings['total_urls'] : 0;
        $last_generated = isset($settings['last_generated']) ? $settings['last_generated'] : '';
        
        // Get post type stats
        $post_type_stats = $this->get_post_type_stats();
        
        // Get MCP stats
        $total_api_calls = isset($stats['total_calls']) ? $stats['total_calls'] : 0;
        $endpoint_stats = isset($stats['endpoints']) ? $stats['endpoints'] : array();
        ?>
        <div class="wrap">
            <h1><?php _e('LLM Ready Statistics', 'llmr'); ?></h1>
            
            <!-- Overview Cards -->
            <div class="llmr-stats-cards">
                <div class="stats-card">
                    <h3><?php _e('URLs in llms.txt', 'llmr'); ?></h3>
                    <div class="stats-number"><?php echo number_format($total_urls); ?></div>
                    <div class="stats-meta">
                        <?php if ($last_generated): ?>
                            <?php _e('Last updated:', 'llmr'); ?> <?php echo human_time_diff(strtotime($last_generated), current_time('timestamp')) . ' ' . __('ago', 'llmr'); ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stats-card">
                    <h3><?php _e('MCP API Calls', 'llmr'); ?></h3>
                    <div class="stats-number"><?php echo number_format($total_api_calls); ?></div>
                    <div class="stats-meta"><?php _e('Total requests', 'llmr'); ?></div>
                </div>
                
                <div class="stats-card">
                    <h3><?php _e('Most Used Endpoint', 'llmr'); ?></h3>
                    <div class="stats-number">
                        <?php 
                        if (!empty($endpoint_stats)) {
                            arsort($endpoint_stats);
                            echo '/' . key($endpoint_stats);
                        } else {
                            echo __('No data yet', 'llmr');
                        }
                        ?>
                    </div>
                    <div class="stats-meta">
                        <?php 
                        if (!empty($endpoint_stats)) {
                            echo number_format(current($endpoint_stats)) . ' ' . __('calls', 'llmr');
                        }
                        ?>
                    </div>
                </div>
                
                <div class="stats-card">
                    <h3><?php _e('Active Post Types', 'llmr'); ?></h3>
                    <div class="stats-number"><?php echo count($settings['post_types'] ?? array()); ?></div>
                    <div class="stats-meta"><?php echo implode(', ', $settings['post_types'] ?? array()); ?></div>
                </div>
            </div>
            
            <!-- Content Stats -->
            <div class="llmr-stats-section">
                <h2><?php _e('Content Statistics', 'llmr'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Post Type', 'llmr'); ?></th>
                            <th><?php _e('Total Published', 'llmr'); ?></th>
                            <th><?php _e('Included in llms.txt', 'llmr'); ?></th>
                            <th><?php _e('Excluded (noindex)', 'llmr'); ?></th>
                            <th><?php _e('Percentage', 'llmr'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($post_type_stats as $post_type => $stats): ?>
                        <tr>
                            <td><strong><?php echo esc_html(get_post_type_object($post_type)->labels->name); ?></strong></td>
                            <td><?php echo number_format($stats['total']); ?></td>
                            <td><?php echo number_format($stats['included']); ?></td>
                            <td><?php echo number_format($stats['excluded']); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $stats['percentage']; ?>%"></div>
                                    <span><?php echo $stats['percentage']; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- API Usage Stats -->
            <div class="llmr-stats-section">
                <h2><?php _e('MCP API Usage', 'llmr'); ?></h2>
                
                <?php if (!empty($endpoint_stats)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Endpoint', 'llmr'); ?></th>
                            <th><?php _e('Total Calls', 'llmr'); ?></th>
                            <th><?php _e('Percentage', 'llmr'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        arsort($endpoint_stats);
                        foreach ($endpoint_stats as $endpoint => $calls): 
                            $percentage = round(($calls / $total_api_calls) * 100, 1);
                        ?>
                        <tr>
                            <td><code>/<?php echo esc_html($endpoint); ?></code></td>
                            <td><?php echo number_format($calls); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    <span><?php echo $percentage; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p><?php _e('No API calls recorded yet. Make sure your MCP endpoints are being used.', 'llmr'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Daily Activity Chart -->
            <div class="llmr-stats-section">
                <h2><?php _e('API Activity (Last 30 Days)', 'llmr'); ?></h2>
                <div id="daily-chart" class="stats-chart">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
            
            <!-- Hourly Distribution -->
            <div class="llmr-stats-section">
                <h2><?php _e('Hourly Distribution (Today)', 'llmr'); ?></h2>
                <div id="hourly-chart" class="stats-chart">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="llmr-stats-section">
                <h2><?php _e('Actions', 'llmr'); ?></h2>
                <p>
                    <button type="button" class="button button-secondary" onclick="if(confirm('<?php _e('Are you sure you want to reset all statistics?', 'llmr'); ?>')) { llmrResetStats(); }">
                        <?php _e('Reset Statistics', 'llmr'); ?>
                    </button>
                    <button type="button" class="button button-secondary" onclick="llmrExportStats()">
                        <?php _e('Export Stats (CSV)', 'llmr'); ?>
                    </button>
                </p>
            </div>
        </div>
        
        <style>
        .llmr-stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .stats-card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
        }
        
        .stats-card h3 {
            margin: 0 0 10px 0;
            color: #555;
            font-size: 14px;
            font-weight: 600;
        }
        
        .stats-number {
            font-size: 32px;
            font-weight: 600;
            color: #0073aa;
            margin: 10px 0;
        }
        
        .stats-meta {
            color: #666;
            font-size: 13px;
        }
        
        .llmr-stats-section {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .llmr-stats-section h2 {
            margin-top: 0;
        }
        
        .progress-bar {
            position: relative;
            background: #f0f0f0;
            height: 20px;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-fill {
            background: #0073aa;
            height: 100%;
            transition: width 0.3s ease;
        }
        
        .progress-bar span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
            font-weight: 600;
        }
        
        .stats-chart {
            max-width: 100%;
            height: 300px;
            margin: 20px 0;
        }
        </style>
        
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        jQuery(document).ready(function($) {
            // Get chart data via AJAX
            $.post(ajaxurl, {
                action: 'llmr_get_stats_data'
            }, function(response) {
                if (response.success) {
                    // Daily chart
                    if (response.data.daily) {
                        var dailyCtx = document.getElementById('dailyChart').getContext('2d');
                        new Chart(dailyCtx, {
                            type: 'line',
                            data: {
                                labels: Object.keys(response.data.daily),
                                datasets: [{
                                    label: 'API Calls',
                                    data: Object.values(response.data.daily),
                                    borderColor: '#0073aa',
                                    backgroundColor: 'rgba(0, 115, 170, 0.1)',
                                    tension: 0.1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                    
                    // Hourly chart
                    if (response.data.hourly) {
                        var hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
                        new Chart(hourlyCtx, {
                            type: 'bar',
                            data: {
                                labels: Object.keys(response.data.hourly).map(h => h + ':00'),
                                datasets: [{
                                    label: 'API Calls',
                                    data: Object.values(response.data.hourly),
                                    backgroundColor: '#0073aa'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                }
            });
        });
        
        function llmrResetStats() {
            jQuery.post(ajaxurl, {
                action: 'llmr_reset_stats',
                nonce: '<?php echo wp_create_nonce('llmr_stats'); ?>'
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
        
        function llmrExportStats() {
            window.location.href = '<?php echo admin_url('admin-ajax.php?action=llmr_export_stats&nonce=' . wp_create_nonce('llmr_stats')); ?>';
        }
        </script>
        <?php
    }
    
    /**
     * Get post type statistics
     */
    private function get_post_type_stats() {
        $settings = get_option('llmr_settings');
        $post_types = isset($settings['post_types']) ? $settings['post_types'] : array('post', 'page');
        $stats = array();
        
        foreach ($post_types as $post_type) {
            $total = wp_count_posts($post_type)->publish;
            
            // Count excluded posts (this is simplified - you'd need to check actual noindex meta)
            $excluded = 0;
            
            // For now, we'll estimate included posts
            $included = $total - $excluded;
            $percentage = $total > 0 ? round(($included / $total) * 100) : 0;
            
            $stats[$post_type] = array(
                'total' => $total,
                'included' => $included,
                'excluded' => $excluded,
                'percentage' => $percentage
            );
        }
        
        return $stats;
    }
    
    /**
     * AJAX handler for getting stats data
     */
    public function ajax_get_stats_data() {
        $stats = get_option('llmr_api_stats', array());
        
        wp_send_json_success(array(
            'daily' => isset($stats['daily']) ? $stats['daily'] : array(),
            'hourly' => isset($stats['hourly']) ? $stats['hourly'] : array()
        ));
    }
    
    /**
     * Register stats tracking for REST API
     */
    public function register_stats_tracking() {
        // Additional tracking can be added here
    }
    
    /**
     * AJAX handler for resetting stats
     */
    public function ajax_reset_stats() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'llmr_stats')) {
            wp_die('Security check failed');
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        // Reset stats
        delete_option('llmr_api_stats');
        
        wp_send_json_success(array(
            'message' => __('Statistics have been reset.', 'llmr')
        ));
    }
    
    /**
     * AJAX handler for exporting stats
     */
    public function ajax_export_stats() {
        // Check nonce
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'llmr_stats')) {
            wp_die('Security check failed');
        }
        
        // Check capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $stats = get_option('llmr_api_stats', array());
        $settings = get_option('llmr_settings');
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="llm-ready-stats-' . date('Y-m-d') . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Write headers
        fputcsv($output, array('LLM Ready Statistics Export - ' . date('Y-m-d H:i:s')));
        fputcsv($output, array());
        
        // Summary stats
        fputcsv($output, array('Summary Statistics'));
        fputcsv($output, array('Total API Calls', isset($stats['total_calls']) ? $stats['total_calls'] : 0));
        fputcsv($output, array('URLs in llms.txt', isset($settings['total_urls']) ? $settings['total_urls'] : 0));
        fputcsv($output, array('Last Generated', isset($settings['last_generated']) ? $settings['last_generated'] : 'Never'));
        fputcsv($output, array());
        
        // Endpoint stats
        if (!empty($stats['endpoints'])) {
            fputcsv($output, array('Endpoint Statistics'));
            fputcsv($output, array('Endpoint', 'Total Calls'));
            foreach ($stats['endpoints'] as $endpoint => $calls) {
                fputcsv($output, array($endpoint, $calls));
            }
            fputcsv($output, array());
        }
        
        // Daily stats
        if (!empty($stats['daily'])) {
            fputcsv($output, array('Daily Statistics'));
            fputcsv($output, array('Date', 'API Calls'));
            foreach ($stats['daily'] as $date => $calls) {
                fputcsv($output, array($date, $calls));
            }
            fputcsv($output, array());
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
}