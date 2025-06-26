<?php
/**
 * Elementor Integration Manager for PGFE Lite
 * 
 * @package PGFE_Lite\Elementor
 * @since 1.0.0
 */

namespace PGFE_Lite\Elementor;

if (!defined('ABSPATH')) {
    exit;
}

class ElementorManager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('elementor/widgets/register', [$this, 'registerWidgets']);
        add_action('elementor/elements/categories_registered', [$this, 'addWidgetCategories']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueueScripts']);
    }
    
    /**
     * Register widgets
     */
    public function registerWidgets($widgets_manager) {
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Load widget files
        $this->loadWidgets();
        
        try {
            // Register Simplified Price Slider Widget
            if (class_exists('PGFE_Lite\Elementor\Widgets\SimplePriceSliderWidget')) {
                $widgets_manager->register(new Widgets\SimplePriceSliderWidget());
            }
            
            // Register Simplified Vendor Filter Widget
            if (class_exists('PGFE_Lite\Elementor\Widgets\SimpleVendorFilterWidget')) {
                $widgets_manager->register(new Widgets\SimpleVendorFilterWidget());
            }
            
            // Register Simplified Attribute Filter Widget
            if (class_exists('PGFE_Lite\Elementor\Widgets\SimpleAttributeFilterWidget')) {
                $widgets_manager->register(new Widgets\SimpleAttributeFilterWidget());
            }
            
            // Register Parent Category Filter Widget
            if (class_exists('PGFE_Lite\Elementor\Widgets\ParentCategoryFilterWidget')) {
                $widgets_manager->register(new Widgets\ParentCategoryFilterWidget());
            }
            
            // Register Child Category Filter Widget
            if (class_exists('PGFE_Lite\Elementor\Widgets\ChildCategoryFilterWidget')) {
                $widgets_manager->register(new Widgets\ChildCategoryFilterWidget());
            }
            
            // Register Archive Category Filter Widget
            if (class_exists('PGFE_Lite\Elementor\Widgets\ArchiveCategoryFilterWidget')) {
                $widgets_manager->register(new Widgets\ArchiveCategoryFilterWidget());
            }
            
            // Register Tag Filter Widget
            if (class_exists('PGFE_Lite\Elementor\Widgets\TagFilterWidget')) {
                $widgets_manager->register(new Widgets\TagFilterWidget());
            }
            
            // Nouveau widget de grille simplifié
            if (class_exists('PGFE_Lite\Elementor\Widgets\SimpleGridWidget')) {
                $widgets_manager->register(new \PGFE_Lite\Elementor\Widgets\SimpleGridWidget());
            }
            
        } catch (Exception $e) {
            // Erreur d'enregistrement des widgets gérée
        }
    }
    
    /**
     * Load widget files
     */
    private function loadWidgets() {
        $widget_files = [
            'SimplePriceSliderWidget.php',
            'SimpleVendorFilterWidget.php',
            'SimpleAttributeFilterWidget.php',
            'ParentCategoryFilterWidget.php',
            'ChildCategoryFilterWidget.php',
            'ArchiveCategoryFilterWidget.php',
            'TagFilterWidget.php'
        ];
        
        foreach ($widget_files as $file) {
            $file_path = PGFE_LITE_PLUGIN_DIR . 'includes/elementor/widgets/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
        
        // Nouveau widget de grille simplifié
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/elementor/widgets/SimpleGridWidget.php';
    }
    

    
    /**
     * Check if widget dependencies are met
     * 
     * @param array $dependencies
     * @return bool
     */
    private function checkWidgetDependencies($dependencies) {
        foreach ($dependencies as $dependency) {
            switch ($dependency) {
                case 'woocommerce':
                    if (!class_exists('WooCommerce')) {
                        return false;
                    }
                    break;
                    
                case 'elementor':
                    if (!class_exists('\Elementor\Plugin')) {
                        return false;
                    }
                    break;
            }
        }
        
        return true;
    }
    
    /**
     * Load a specific widget file
     * 
     * @param string $filename
     */
    private function loadWidgetFile($filename) {
        $widget_path = PGFE_LITE_PLUGIN_DIR . 'includes/elementor/widgets/' . $filename;
        
        if (file_exists($widget_path)) {
            require_once $widget_path;
        }
    }
    

    
    /**
     * Add custom widget categories
     * 
     * @param \Elementor\Elements_Manager $elements_manager
     */
    public function addWidgetCategories($elements_manager) {
        $elements_manager->add_category(
            'pgfe-lite',
            [
                'title' => __('PGFE - Lite', 'pgfe-lite'),
                'icon' => 'fa fa-filter',
            ]
        );
    }
    
    /**
     * Enqueue frontend styles for Elementor
     * NOTE: Cette méthode est désactivée - les styles Elementor sont maintenant
     * intégrés dans pgfe-centralized-styles.css via StyleManager
     */
    // public function enqueueStyles() {
    //     wp_enqueue_style(
    //         'pgfe-lite-elementor',
    //         PGFE_LITE_PLUGIN_URL . 'assets/css/elementor.css',
    //         [],
    //         PGFE_LITE_VERSION
    //     );
    // }
    
    /**
     * Enqueue frontend scripts for Elementor
     */
    public function enqueueScripts() {
        wp_enqueue_script(
            'pgfe-lite-elementor',
            PGFE_LITE_PLUGIN_URL . 'assets/js/elementor.js',
            ['jquery'],
            PGFE_LITE_VERSION,
            true
        );
        
        // Enqueue Elementor fix script for frontend
        wp_enqueue_script(
            'pgfe-lite-elementor-fix',
            PGFE_LITE_PLUGIN_URL . 'assets/js/elementor-fix.js',
            ['jquery', 'elementor-frontend'],
            PGFE_LITE_VERSION,
            true
        );
    }
    
    /**
     * Enqueue editor scripts for Elementor
     */
    public function enqueueEditorScripts() {
        // Enqueue Elementor fix script for editor
        wp_enqueue_script(
            'pgfe-lite-elementor-editor-fix',
            PGFE_LITE_PLUGIN_URL . 'assets/js/elementor-fix.js',
            ['jquery', 'elementor-editor'],
            PGFE_LITE_VERSION,
            true
        );
    }
    
    /**
     * Check if Elementor is active and compatible
     * 
     * @return bool
     */
    public static function isElementorActive() {
        return did_action('elementor/loaded');
    }
    
    /**
     * Get minimum Elementor version required
     * 
     * @return string
     */
    public static function getMinimumElementorVersion() {
        return '3.0.0';
    }
    
    /**
     * Check Elementor version compatibility
     * 
     * @return bool
     */
    public static function isElementorVersionCompatible() {
        if (!defined('ELEMENTOR_VERSION')) {
            return false;
        }
        
        return version_compare(ELEMENTOR_VERSION, self::getMinimumElementorVersion(), '>=');
    }
}