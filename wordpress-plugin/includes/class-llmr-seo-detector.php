<?php
/**
 * SEO plugin detector class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class LLMR_SEO_Detector {
    
    /**
     * Check if Yoast SEO is active
     */
    public function is_yoast_active() {
        return defined('WPSEO_VERSION') || class_exists('WPSEO_Options');
    }
    
    /**
     * Check if Rank Math is active
     */
    public function is_rankmath_active() {
        return defined('RANK_MATH_VERSION') || class_exists('RankMath');
    }
    
    /**
     * Check if All in One SEO is active
     */
    public function is_aioseo_active() {
        return defined('AIOSEO_VERSION') || function_exists('aioseo');
    }
    
    /**
     * Get active SEO plugins
     */
    public function get_active_seo_plugins() {
        $active_plugins = array();
        
        if ($this->is_yoast_active()) {
            $active_plugins[] = 'yoast';
        }
        
        if ($this->is_rankmath_active()) {
            $active_plugins[] = 'rankmath';
        }
        
        if ($this->is_aioseo_active()) {
            $active_plugins[] = 'aioseo';
        }
        
        return $active_plugins;
    }
    
    /**
     * Check if any SEO plugin is active
     */
    public function has_seo_plugin() {
        return !empty($this->get_active_seo_plugins());
    }
}