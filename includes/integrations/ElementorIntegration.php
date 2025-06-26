<?php
/**
 * Elementor Integration for PGFE Lite
 * 
 * Provides robust Elementor integration while maintaining plugin independence
 * 
 * @package PGFE_Lite\Integrations
 * @since 1.0.0
 */

namespace PGFE_Lite\Integrations;

if (!defined('ABSPATH')) {
    exit;
}

class ElementorIntegration {
    
    /**
     * Widget registry
     */
    private $widget_registry = [];
    
    /**
     * Error handler
     */
    private $error_handler;
    
    /**
     * Initialize Elementor integration
     */
    public function __construct() {
        // Initialize error handler first
        $this->initializeErrorHandler();
        
        // Hook into Elementor only when it's fully loaded
        add_action('elementor/init', [$this, 'onElementorInit'], 5);
        
        // Enqueue scripts with proper error handling
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueueEditorScripts']);
        
        // Add widget categories
        add_action('elementor/elements/categories_registered', [$this, 'addWidgetCategories']);
    }
    
    /**
     * Initialize error handler for Elementor issues
     */
    private function initializeErrorHandler() {
        $this->error_handler = new class {
            public function handleTabError($error) {
                // Erreur d'onglet Elementor gérée
                return false;
            }
            
            public function handleWidgetError($widget_type, $error) {
                // Erreur de widget gérée
                return false;
            }
        };
    }
    
    /**
     * Initialize when Elementor is ready
     */
    public function onElementorInit() {
        // Verify Elementor is properly loaded
        if (!class_exists('\Elementor\Plugin') || !\Elementor\Plugin::$instance) {
            return;
        }
        
        // Register widgets with error handling
        add_action('elementor/widgets/register', [$this, 'registerWidgets'], 10);
        
        // Load widget files conditionally
        $this->loadWidgetFiles();
    }
    
    /**
     * Load widget files with error handling
     */
    private function loadWidgetFiles() {
        $widget_files = [
            'traits/StyleControlsTrait.php',
            'traits/FilterRenderTrait.php', 
            'base/BaseFilterWidget.php',

            'widgets/SimplePriceSliderWidget.php',
            'widgets/SimpleAttributeFilterWidget.php',
            'widgets/SimpleVendorFilterWidget.php',
            'widgets/ArchiveCategoryFilterWidget.php',
            'widgets/ParentCategoryFilterWidget.php',
            'widgets/ChildCategoryFilterWidget.php',
            'widgets/TagFilterWidget.php'
        ];
        
        foreach ($widget_files as $file) {
            $file_path = PGFE_LITE_PLUGIN_DIR . 'includes/elementor/' . $file;
            
            if (file_exists($file_path)) {
                try {
                    require_once $file_path;
                } catch (\Exception $e) {
                    // Échec de chargement du fichier géré
                }
            }
        }
    }
    
    /**
     * Register widgets with Elementor
     * 
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public function registerWidgets($widgets_manager) {
        $widgets = [

            'pgfe-simple-grid' => 'PGFE_Lite\\Elementor\\Widgets\\SimpleGridWidget',
            'pgfe-price-slider' => 'PGFE_Lite\\Elementor\\Widgets\\SimplePriceSliderWidget',
            'pgfe-attribute-filter' => 'PGFE_Lite\\Elementor\\Widgets\\SimpleAttributeFilterWidget',
            'pgfe-vendor-filter' => 'PGFE_Lite\\Elementor\\Widgets\\SimpleVendorFilterWidget',
            'pgfe-archive-category-filter' => 'PGFE_Lite\\Elementor\\Widgets\\ArchiveCategoryFilterWidget',
            'pgfe-parent-category-filter' => 'PGFE_Lite\\Elementor\\Widgets\\ParentCategoryFilterWidget',
            'pgfe-child-category-filter' => 'PGFE_Lite\\Elementor\\Widgets\\ChildCategoryFilterWidget',
            'pgfe-tag-filter' => 'PGFE_Lite\\Elementor\\Widgets\\TagFilterWidget'
        ];
        
        foreach ($widgets as $widget_key => $widget_class) {
            $this->registerSingleWidget($widgets_manager, $widget_key, $widget_class);
        }
    }
    
    /**
     * Register a single widget with error handling
     * 
     * @param \Elementor\Widgets_Manager $widgets_manager
     * @param string $widget_key
     * @param string $widget_class
     */
    private function registerSingleWidget($widgets_manager, $widget_key, $widget_class) {
        try {
            // Check if class exists
            if (!class_exists($widget_class)) {
                throw new \Exception("Widget class {$widget_class} not found");
            }
            
            // Check dependencies
            if (!$this->checkWidgetDependencies($widget_key)) {
                return;
            }
            
            // Create and register widget instance
            $widget_instance = new $widget_class();
            $widgets_manager->register($widget_instance);
            
            $this->widget_registry[$widget_key] = $widget_class;
            
        } catch (\Exception $e) {
            $this->error_handler->handleWidgetError($widget_key, $e->getMessage());
        }
    }
    
    /**
     * Check widget dependencies
     * 
     * @param string $widget_key
     * @return bool
     */
    private function checkWidgetDependencies($widget_key) {
        // All widgets require WooCommerce
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        // Additional checks for specific widgets
        switch ($widget_key) {
            case 'pgfe-vendor-filter':
                // Check if vendor functionality is available
                return function_exists('dokan') || class_exists('WCVendors') || class_exists('WC_Vendors');
                
            default:
                return true;
        }
    }
    
    /**
     * Add widget categories
     * 
     * @param \Elementor\Elements_Manager $elements_manager
     */
    public function addWidgetCategories($elements_manager) {
        $elements_manager->add_category(
            'pgfe-widgets',
            [
                'title' => __('PGFE Widgets', 'pgfe-lite'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueueScripts() {
        // Enhanced error handling script
        wp_enqueue_script(
            'pgfe-elementor-enhanced',
            PGFE_LITE_PLUGIN_URL . 'assets/js/elementor-enhanced.js',
            ['jquery', 'elementor-frontend'],
            PGFE_LITE_VERSION,
            true
        );
        
        // Localize script with error handling config
        wp_localize_script('pgfe-elementor-enhanced', 'pgfe_elementor_config', [
            'debug' => WP_DEBUG,
            'error_handling' => [
                'log_errors' => WP_DEBUG_LOG,
                'suppress_tab_errors' => true,
                'fallback_mode' => true
            ]
        ]);
    }
    
    /**
     * Enqueue editor scripts
     */
    public function enqueueEditorScripts() {
        wp_enqueue_script(
            'pgfe-elementor-editor',
            PGFE_LITE_PLUGIN_URL . 'assets/js/elementor-editor.js',
            ['elementor-editor'],
            PGFE_LITE_VERSION,
            true
        );
    }
    
    /**
     * Check if widget can be rendered
     * 
     * @param string $widget_type
     * @return bool
     */
    public function canRenderWidget($widget_type) {
        return isset($this->widget_registry[$widget_type]);
    }
    
    /**
     * Render widget (fallback method)
     * 
     * @param string $widget_type
     * @param array $settings
     * @return string
     */
    public function renderWidget($widget_type, $settings = []) {
        if (!$this->canRenderWidget($widget_type)) {
            return '';
        }
        
        try {
            $widget_class = $this->widget_registry[$widget_type];
            $widget = new $widget_class();
            
            // Use reflection to call protected render method if needed
            if (method_exists($widget, 'render_content')) {
                ob_start();
                $widget->render_content();
                return ob_get_clean();
            }
            
        } catch (\Exception $e) {
            $this->error_handler->handleWidgetError($widget_type, $e->getMessage());
        }
        
        return '';
    }
    
    /**
     * Register widget with this integration
     * 
     * @param string $widget_type
     * @param array $config
     */
    public function registerWidget($widget_type, $config) {
        // This method is called by IntegrationManager
        // Implementation depends on specific needs
    }
    
    /**
     * Get registered widgets
     * 
     * @return array
     */
    public function getRegisteredWidgets() {
        return $this->widget_registry;
    }
}