<?php

namespace PGFE_Lite\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Asset Manager for optimized loading of CSS and JS files
 * 
 * Handles conditional loading, minification, and caching of assets
 */
class AssetManager {
    
    /**
     * Registered assets
     */
    private $registered_assets = [
        'styles' => [],
        'scripts' => []
    ];
    
    /**
     * Loaded assets cache
     */
    private $loaded_assets = [
        'styles' => [],
        'scripts' => []
    ];
    
    /**
     * Asset dependencies
     */
    private $dependencies = [];
    
    /**
     * Initialize asset manager
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets'], 10);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets'], 10);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueueElementorAssets'], 10);
        
        // Register default assets
        $this->registerDefaultAssets();
        
        // AssetManager initialisé
    }
    
    /**
     * Register default plugin assets
     */
    private function registerDefaultAssets() {
        // Core styles
        $this->registerStyle('pgfe-core', [
            'file' => 'css/pgfe-centralized-styles.css',
            'dependencies' => [],
            'conditions' => ['woocommerce_active']
        ]);
        
        // Note: Core scripts (pgfe-lite.js) are handled by main plugin file
        // to avoid conflicts and ensure proper dependency order
        // AssetManager focuses on widget-specific and conditional assets
        
        // Widget-specific assets - using core files since specific widget files don't exist
        // Price slider functionality is included in pgfe-lite.js
        // Category filter functionality is included in pgfe-lite.js
        
        // Admin assets
        $this->registerStyle('pgfe-admin', [
            'file' => 'css/admin.css',
            'dependencies' => [],
            'conditions' => ['is_admin']
        ]);
        
        $this->registerScript('pgfe-admin', [
            'file' => 'js/admin.js',
            'dependencies' => ['jquery'],
            'conditions' => ['is_admin']
        ]);
        
        // Elementor assets
        $this->registerStyle('pgfe-elementor', [
            'file' => 'css/elementor.css',
            'dependencies' => ['pgfe-centralized-styles'],
            'conditions' => ['elementor_active']
        ]);
        
        $this->registerScript('pgfe-elementor', [
            'file' => 'js/elementor.js',
            'dependencies' => ['jquery', 'pgfe-lite-js'],
            'conditions' => ['elementor_active']
        ]);
    }
    
    /**
     * Register a style asset
     * 
     * @param string $handle
     * @param array $args
     */
    public function registerStyle($handle, $args) {
        $this->registered_assets['styles'][$handle] = wp_parse_args($args, [
            'file' => '',
            'dependencies' => [],
            'version' => PGFE_LITE_VERSION,
            'media' => 'all',
            'conditions' => []
        ]);
    }
    
    /**
     * Register a script asset
     * 
     * @param string $handle
     * @param array $args
     */
    public function registerScript($handle, $args) {
        $this->registered_assets['scripts'][$handle] = wp_parse_args($args, [
            'file' => '',
            'dependencies' => [],
            'version' => PGFE_LITE_VERSION,
            'in_footer' => true,
            'conditions' => [],
            'localize' => null
        ]);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueueAssets() {
        if (is_admin()) {
            return;
        }
        
        $this->conditionallyEnqueueAssets();
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueueAdminAssets() {
        if (!is_admin()) {
            return;
        }
        
        $this->conditionallyEnqueueAssets();
    }
    
    /**
     * Enqueue Elementor-specific assets
     */
    public function enqueueElementorAssets() {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }
        
        $this->conditionallyEnqueueAssets();
    }
    
    /**
     * Conditionally enqueue assets based on conditions
     */
    private function conditionallyEnqueueAssets() {
        // Enqueue styles
        foreach ($this->registered_assets['styles'] as $handle => $asset) {
            if ($this->shouldEnqueueAsset($asset)) {
                $this->enqueueStyle($handle, $asset);
            }
        }
        
        // Enqueue scripts
        foreach ($this->registered_assets['scripts'] as $handle => $asset) {
            if ($this->shouldEnqueueAsset($asset)) {
                $this->enqueueScript($handle, $asset);
            }
        }
    }
    
    /**
     * Check if an asset should be enqueued based on conditions
     * 
     * @param array $asset
     * @return bool
     */
    private function shouldEnqueueAsset($asset) {
        if (empty($asset['conditions'])) {
            return true;
        }
        
        foreach ($asset['conditions'] as $condition) {
            if (!$this->checkCondition($condition)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if condition is met
     * 
     * @param string $condition
     * @return bool
     */
    private function checkCondition($condition) {
        // Ensure we don't use conditional tags before they're available
        if (!did_action('wp') && in_array($condition, ['is_page', 'is_search', 'is_home', 'is_front_page', 'is_single', 'is_archive'])) {
            return false;
        }
        
        switch ($condition) {
            case 'woocommerce_active':
                return class_exists('WooCommerce');
                
            case 'elementor_active':
                return class_exists('\Elementor\Plugin');
                
            case 'is_admin':
                return is_admin();
                
            case 'widget_price_slider':
                return $this->isWidgetUsedOnPage('llda-price-slider');
                
            case 'widget_category_filter':
                return $this->isWidgetUsedOnPage(['llda-parent-category-filter', 'llda-child-category-filter', 'llda-archive-category-filter']);
                
            default:
                return apply_filters('pgfe_asset_condition_' . $condition, false);
        }
    }
    
    /**
     * Check if a widget is used on the current page
     * 
     * @param string|array $widget_types
     * @return bool
     */
    private function isWidgetUsedOnPage($widget_types) {
        global $post;
        
        if (!$post || !class_exists('\Elementor\Plugin')) {
            return false;
        }
        
        // Get Elementor data
        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
        
        if (empty($elementor_data)) {
            return false;
        }
        
        $widget_types = (array) $widget_types;
        $used_widgets = [];
        $this->findWidgetsInData(json_decode($elementor_data, true), $used_widgets);
        
        return !empty(array_intersect($widget_types, $used_widgets));
    }
    
    /**
     * Recursively find widgets in Elementor data
     * 
     * @param array $data
     * @param array &$used_widgets
     */
    private function findWidgetsInData($data, &$used_widgets) {
        if (!is_array($data)) {
            return;
        }
        
        foreach ($data as $element) {
            if (isset($element['widgetType'])) {
                $used_widgets[] = $element['widgetType'];
            }
            
            if (isset($element['elements'])) {
                $this->findWidgetsInData($element['elements'], $used_widgets);
            }
        }
    }
    
    /**
     * Enqueue a style asset
     * 
     * @param string $handle
     * @param array $asset
     */
    private function enqueueStyle($handle, $asset) {
        if (isset($this->loaded_assets['styles'][$handle])) {
            return;
        }
        
        $url = $this->getAssetUrl($asset['file']);
        
        if ($url) {
            wp_enqueue_style(
                $handle,
                $url,
                $asset['dependencies'],
                $asset['version'],
                $asset['media']
            );
            
            $this->loaded_assets['styles'][$handle] = true;
        }
    }
    
    /**
     * Enqueue a script asset
     * 
     * @param string $handle
     * @param array $asset
     */
    private function enqueueScript($handle, $asset) {
        if (isset($this->loaded_assets['scripts'][$handle])) {
            return;
        }
        
        $url = $this->getAssetUrl($asset['file']);
        
        if ($url) {
            wp_enqueue_script(
                $handle,
                $url,
                $asset['dependencies'],
                $asset['version'],
                $asset['in_footer']
            );
            
            // Handle localization
            if (!empty($asset['localize'])) {
                wp_localize_script(
                    $handle,
                    $asset['localize']['object_name'],
                    $asset['localize']['data']
                );
            }
            
            $this->loaded_assets['scripts'][$handle] = true;
        }
    }
    
    /**
     * Get asset URL
     * 
     * @param string $file
     * @return string|false
     */
    private function getAssetUrl($file) {
        $file_path = PGFE_LITE_PLUGIN_DIR . 'assets/' . $file;
        
        if (!file_exists($file_path)) {
            // Asset non trouvé géré
            return false;
        }
        
        return PGFE_LITE_PLUGIN_URL . 'assets/' . $file;
    }
    
    /**
     * Get loaded assets for debugging
     * 
     * @return array
     */
    public function getLoadedAssets() {
        return $this->loaded_assets;
    }
    
    /**
     * Clear loaded assets cache
     */
    public function clearCache() {
        $this->loaded_assets = [
            'styles' => [],
            'scripts' => []
        ];
    }
    
    /**
     * Force enqueue an asset
     * 
     * @param string $handle
     * @param string $type 'style' or 'script'
     */
    public function forceEnqueue($handle, $type = 'style') {
        $assets = $this->registered_assets[$type . 's'] ?? [];
        
        if (isset($assets[$handle])) {
            if ($type === 'style') {
                $this->enqueueStyle($handle, $assets[$handle]);
            } else {
                $this->enqueueScript($handle, $assets[$handle]);
            }
        }
    }
}