<?php
/**
 * Plugin Name: PGFE - Lite
 * Plugin URI: https://le-local-des-artisans.com
 * Version: 1.0.0
 * Author: Le Local des Artisans
 * Text Domain: pgfe-lite
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * Woo: 8.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PGFE_LITE_VERSION', '1.0.0');
define('PGFE_LITE_PLUGIN_FILE', __FILE__);
define('PGFE_LITE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PGFE_LITE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PGFE_LITE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Performance monitoring (only in debug mode)
define('PGFE_PERFORMANCE_MONITORING', WP_DEBUG && (defined('PGFE_ENABLE_MONITORING') && PGFE_ENABLE_MONITORING));

// Security constants
define('PGFE_RATE_LIMIT_REQUESTS', 60);
define('PGFE_RATE_LIMIT_WINDOW', 60);
define('PGFE_CACHE_DURATION', 3600); // 1 hour

/**
 * Main Plugin Class
 */
class PGFE_Lite {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Dependencies check result cache
     */
    private $dependencies_checked = false;
    private $dependencies_met = false;
    
    /**
     * Get single instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        
        // Declare HPOS compatibility
        add_action('before_woocommerce_init', [$this, 'declareHposCompatibility']);
        
        // Check dependencies once and initialize accordingly
        add_action('plugins_loaded', [$this, 'checkAndInitialize'], 20);
        
        // Initialize Elementor integration
        add_action('elementor/init', [$this, 'initElementor']);
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize performance monitoring
        if (PGFE_PERFORMANCE_MONITORING) {
            \PGFE_Lite\Core\PerformanceMonitor::init();
            \PGFE_Lite\Core\PerformanceMonitor::startTimer('plugin_init');
        }
        
        // Load required files
        $this->loadIncludes();
        
        // Initialize core systems
        $this->initCoreSystems();
        
        // Initialize AJAX handlers
        $this->initAjax();
        
        if (PGFE_PERFORMANCE_MONITORING) {
            \PGFE_Lite\Core\PerformanceMonitor::stopTimer('plugin_init');
        }
    }
    
    /**
     * Initialize core systems
     */
    private function initCoreSystems() {
        // Initialize Asset Manager after 'wp' hook to ensure conditional tags work
        add_action('wp', function() {
            if (class_exists('\PGFE_Lite\Core\AssetManager')) {
                new \PGFE_Lite\Core\AssetManager();
            }
        }, 5);
        
        // Initialize Security Manager (static class, no instantiation needed)
        // \PGFE_Lite\Core\SecurityManager is ready to use
        
        // Initialize Performance Monitor (already initialized in init method)
    }
    
    /**
     * Load text domain
     */
    public function loadTextDomain() {
        load_plugin_textdomain('pgfe-lite', false, dirname(PGFE_LITE_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Declare HPOS compatibility
     */
    public function declareHposCompatibility() {
        if (class_exists('Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    }
    
    /**
     * Check and initialize plugin based on dependencies
     */
    public function checkAndInitialize() {
        if ($this->dependencies_checked) {
            return $this->dependencies_met;
        }
        
        $this->dependencies_checked = true;
        $this->dependencies_met = $this->checkDependencies();
        
        if (!$this->dependencies_met) {
            // Dependencies not met, show admin notice
            add_action('admin_notices', [$this, 'showDependencyNotice']);
            return false;
        }
        
        // Dependencies met, proceed with conditional loading
        $this->conditionalLoadIncludes();
        
        return true;
    }
    
    /**
     * Check plugin dependencies (private method)
     */
    private function checkDependencies() {
        // Check WooCommerce
        if (!class_exists('WooCommerce')) {
            return false;
        }
        
        // Check Elementor (more flexible check)
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Show dependency notice
     */
    public function showDependencyNotice() {
        $missing_plugins = [];
        
        if (!class_exists('WooCommerce')) {
            $missing_plugins[] = 'WooCommerce';
        }
        
        if (!class_exists('\Elementor\Plugin')) {
            $missing_plugins[] = 'Elementor';
        }
        
        if (!empty($missing_plugins)) {
            echo '<div class="notice notice-error"><p>';
            printf(
                __('PGFE - Lite requires the following plugins to be installed and activated: %s', 'pgfe-lite'),
                implode(', ', $missing_plugins)
            );
            echo '</p></div>';
        }
    }
    
    /**
     * Initialize Elementor integration
     */
    public function initElementor() {
        // Only initialize if dependencies were already checked and met
        if (!$this->dependencies_met) {
            return;
        }
        
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/elementor/ElementorManager.php';
        new PGFE_Lite\Elementor\ElementorManager();
    }
    
    /**
     * Load core classes conditionally
     */
    private function loadCoreClasses() {
        $core_files = [
            'ProductQuery.php',
            'ProductFormatter.php', 
            'StyleManager.php',
            'OptionsProvider.php'
        ];
        
        foreach ($core_files as $file) {
            $file_path = PGFE_LITE_PLUGIN_DIR . 'includes/core/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Load display classes
     */
    private function loadDisplayClasses() {
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/display/GridRenderer.php';
    }
    
    /**
     * Load AJAX handlers
     */
    private function loadAjaxHandlers() {
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/ajax/AjaxHandler.php';
    }
    
    /**
     * Load Elementor integration files conditionally
     */
    private function loadElementorFiles() {
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        $elementor_files = [
            'includes/elementor/traits/StyleControlsTrait.php',
            'includes/elementor/traits/FilterRenderTrait.php', 
            'includes/elementor/base/BaseFilterWidget.php'
        ];
        
        foreach ($elementor_files as $file) {
            $file_path = PGFE_LITE_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Load WooCommerce integration files conditionally
     */
    private function loadWooCommerceFiles() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // WooCommerce specific files can be loaded here
        // Currently all our files work with or without WooCommerce
    }
    
    /**
     * Conditional loading based on active plugins and context
     */
    private function conditionalLoadIncludes() {
        // Load core classes first
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/PerformanceMonitor.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/SecurityManager.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/AssetManager.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/OptionsProvider.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/StyleManager.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/ProductQuery.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/ProductFormatter.php';
        
        // Load WooCommerce dependent classes only if WooCommerce is available
        if (class_exists('WooCommerce')) {
            require_once PGFE_LITE_PLUGIN_DIR . 'includes/display/GridRenderer.php';
            require_once PGFE_LITE_PLUGIN_DIR . 'includes/ajax/AjaxHandler.php';
        }
        
        // Note: Elementor classes are loaded in initElementor() method
        // to ensure Elementor is fully initialized before loading widgets
    }
    
    /**
     * Fallback asset loading when AssetManager is not available
     */
    private function enqueueBasicAssets() {
        // Enqueue basic styles
        wp_enqueue_style(
            'pgfe-centralized-styles',
            PGFE_LITE_PLUGIN_URL . 'assets/css/pgfe-centralized-styles.css',
            [],
            PGFE_LITE_VERSION
        );
        
        // Note: Scripts are handled by enqueueScripts() method
        // to ensure proper dependency order
    }
    
    /**
     * Load required files (legacy method for backward compatibility)
     */
    private function loadIncludes() {
        // Core classes
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/ProductQuery.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/ProductFormatter.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/StyleManager.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/core/OptionsProvider.php';
        
        // Display classes
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/display/GridRenderer.php';
        
        // Elementor base classes and traits
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/elementor/traits/StyleControlsTrait.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/elementor/traits/FilterRenderTrait.php';
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/elementor/base/BaseFilterWidget.php';
        
        // AJAX handlers
        require_once PGFE_LITE_PLUGIN_DIR . 'includes/ajax/AjaxHandler.php';
    }
    
    /**
     * Initialize AJAX handlers
     */
    private function initAjax() {
        // Only initialize AJAX if dependencies are met
        if ($this->dependencies_met && class_exists('WooCommerce')) {
            new PGFE_Lite\Ajax\AjaxHandler();
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueueScripts() {
        // Note: Main styles are now handled by StyleManager
        // to avoid conflicts and provide better style management
        
        // Enqueue simple grid system (remplace le système complexe GridManager)
        wp_enqueue_script(
            'pgfe-simple-grid',
            PGFE_LITE_PLUGIN_URL . 'assets/js/pgfe-simple-grid.js',
            ['jquery'],
            PGFE_LITE_VERSION,
            true
        );
        
        // Enqueue simple filter system
        wp_enqueue_script(
            'pgfe-simple-filter',
            PGFE_LITE_PLUGIN_URL . 'assets/js/pgfe-simple-filter.js',
            ['jquery', 'pgfe-simple-grid'],
            PGFE_LITE_VERSION,
            true
        );
        
        // Localize script data (consolidated for both grid and filter)
        $localize_data = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pgfe_ajax_nonce'),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'strings' => [
                'loading' => __('Chargement...', 'pgfe-lite'),
                'error' => __('Une erreur est survenue', 'pgfe-lite'),
                'noResults' => __('Aucun résultat trouvé', 'pgfe-lite'),
                'search' => __('Rechercher...', 'pgfe-lite'),
                'ajax_error' => __('Erreur de communication avec le serveur', 'pgfe-lite'),
                'filter_error' => __('Erreur lors du filtrage des produits', 'pgfe-lite'),
                'retry' => __('Réessayer', 'pgfe-lite'),
                'no_products' => __('Aucun produit trouvé avec ces filtres', 'pgfe-lite'),
            ]
        ];
        
        // Localize only the main script to avoid duplication
        wp_localize_script('pgfe-simple-grid', 'pgfe_ajax', $localize_data);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueueAdminScripts() {
        // Admin styles can be added here if needed
        // wp_enqueue_style(
        //     'pgfe-lite-admin',
        //     PGFE_LITE_PLUGIN_URL . 'assets/css/admin.css',
        //     [],
        //     PGFE_LITE_VERSION
        // );
    }
}

// Initialize plugin
PGFE_Lite::getInstance();

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, function() {
    // Create necessary database tables or options if needed
    flush_rewrite_rules();
});

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Fichiers de debug supprimés - code nettoyé