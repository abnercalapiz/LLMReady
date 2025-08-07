<?php
/**
 * Dashboard widget class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LLMR_Dashboard_Widget {
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'llmr_dashboard_widget',
            __('LLM Ready - LLM Inclusion Stats', 'llmr'),
            array($this, 'render_dashboard_widget')
        );
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $settings = get_option('llmr_settings');
        ?>
        <div class="llmr-widget">
            <?php if (!empty($settings['last_generated'])): ?>
                <div class="llmr-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo esc_html($settings['total_urls']); ?></span>
                        <span class="stat-label"><?php _e('Total URLs', 'llmr'); ?></span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($settings['post_types']); ?></span>
                        <span class="stat-label"><?php _e('Post Types', 'llmr'); ?></span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($settings['excluded_urls']); ?></span>
                        <span class="stat-label"><?php _e('Excluded', 'llmr'); ?></span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($settings['included_urls']); ?></span>
                        <span class="stat-label"><?php _e('Manual', 'llmr'); ?></span>
                    </div>
                </div>
                
                <div class="llmr-meta">
                    <p>
                        <strong><?php _e('Last Generated:', 'llmr'); ?></strong> 
                        <?php echo human_time_diff(strtotime($settings['last_generated']), current_time('timestamp')) . ' ' . __('ago', 'llmr'); ?>
                    </p>
                    
                    <p>
                        <strong><?php _e('File Location:', 'llmr'); ?></strong> 
                        <a href="<?php echo site_url('/llms.txt'); ?>" target="_blank"><?php echo site_url('/llms.txt'); ?></a>
                    </p>
                    
                    <p>
                        <strong><?php _e('Auto-regenerate:', 'llmr'); ?></strong> 
                        <?php echo $settings['auto_regenerate'] ? __('Enabled', 'llmr') : __('Disabled', 'llmr'); ?>
                    </p>
                </div>
                
                <div class="llmr-actions">
                    <a href="<?php echo admin_url('admin.php?page=llmr'); ?>" class="button button-primary">
                        <?php _e('Manage Settings', 'llmr'); ?>
                    </a>
                    <button type="button" class="button" id="llmr-widget-regenerate">
                        <?php _e('Regenerate Now', 'llmr'); ?>
                    </button>
                </div>
            <?php else: ?>
                <p><?php _e('No llms.txt file generated yet.', 'llmr'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=llmr'); ?>" class="button button-primary">
                    <?php _e('Configure Plugin', 'llmr'); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <style>
        .llmr-widget {
            padding: 10px 0;
        }
        .llmr-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .stat-number {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #2271b1;
        }
        .stat-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .llmr-meta p {
            margin: 8px 0;
        }
        .llmr-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('#llmr-widget-regenerate').on('click', function() {
                var button = $(this);
                button.prop('disabled', true).text('<?php _e('Regenerating...', 'llmr'); ?>');
                
                $.post(ajaxurl, {
                    action: 'llmr_regenerate',
                    nonce: '<?php echo wp_create_nonce('llmr_regenerate'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                        button.prop('disabled', false).text('<?php _e('Regenerate Now', 'llmr'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * AJAX handler for getting stats
     */
    public function ajax_get_stats() {
        // Check capabilities
        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }
        
        $settings = get_option('llmr_settings');
        
        wp_send_json_success(array(
            'total_urls' => $settings['total_urls'],
            'last_generated' => $settings['last_generated'],
            'post_types' => count($settings['post_types']),
            'excluded' => count($settings['excluded_urls']),
            'included' => count($settings['included_urls'])
        ));
    }
}