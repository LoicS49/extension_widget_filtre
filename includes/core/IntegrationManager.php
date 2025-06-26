<?php
/**
 * Integration Manager for PGFE Lite
 * 
 * Manages integrations with third-party plugins while maintaining independence
 * 
 * @package PGFE_Lite\Core
 * @since 1.0.0
 */

namespace PGFE_Lite\Core;

if (!defined('ABSPATH')) {
    exit;
}

class IntegrationManager {
    
    /**
     * Available integrations
     */
    private $integrations = [
        'elementor' => [
            'class' => 'ElementorIntegration',
            'check' => 'is_elementor_active',
            'priority' => 10
        ]
    ];
    
    /**
     * Loaded integrations
     */
    private $loaded_integrations = [];
    
    /**
     * Initialize integration manager
     */
    public function __construct() {
        add_action('init', [$this, 'initializeIntegrations'], 5);
        add_action('wp_enqueue_scripts', [$this, 'enqueueIntegrationAssets'], 15);
    }
    
    /**
     * Initialize available integrations
     */
    public function initializeIntegrations() {
        foreach ($this->integrations as $key => $config) {
            if ($this->checkIntegrationAvailability($config['check'])) {
                $this->loadIntegration($key, $config);
            }
        }
    }
    
    /**
     * Check if an integration is available
     * 
     * @param string $check_method
     * @return bool
     */
    private function checkIntegrationAvailability($check_method) {
        switch ($check_method) {
            case 'is_elementor_active':
                return class_exists('\Elementor\Plugin');
                
            default:
                return false;
        }
    }
    
    /**
     * Load a specific integration
     * 
     * @param string $key
     * @param array $config
     */
    private function loadIntegration($key, $config) {
        $integration_file = PGFE_LITE_PLUGIN_DIR . 'includes/integrations/' . $config['class'] . '.php';
        
        if (file_exists($integration_file)) {
            require_once $integration_file;
            
            $class_name = 'PGFE_Lite\\Integrations\\' . $config['class'];
            
            if (class_exists($class_name)) {
                try {
                    $this->loaded_integrations[$key] = new $class_name();
                    
                    // Intégration chargée avec succès
                } catch (\Exception $e) {
                    // Erreur d'intégration gérée
                }
            }
        }
    }
    
    /**
     * Enqueue integration-specific assets
     */
    public function enqueueIntegrationAssets() {
        foreach ($this->loaded_integrations as $key => $integration) {
            if (method_exists($integration, 'enqueueAssets')) {
                $integration->enqueueAssets();
            }
        }
    }
    
    /**
     * Get loaded integration
     * 
     * @param string $key
     * @return object|null
     */
    public function getIntegration($key) {
        return isset($this->loaded_integrations[$key]) ? $this->loaded_integrations[$key] : null;
    }
    
    /**
     * Check if integration is loaded
     * 
     * @param string $key
     * @return bool
     */
    public function isIntegrationLoaded($key) {
        return isset($this->loaded_integrations[$key]);
    }
    
    /**
     * Render widget using available integrations
     * 
     * @param string $widget_type
     * @param array $settings
     * @return string
     */
    public function renderWidget($widget_type, $settings = []) {
        // Try each integration in priority order
        $integrations_by_priority = $this->getIntegrationsByPriority();
        
        foreach ($integrations_by_priority as $integration) {
            if (method_exists($integration, 'canRenderWidget') && 
                $integration->canRenderWidget($widget_type)) {
                
                return $integration->renderWidget($widget_type, $settings);
            }
        }
        
        // No fallback available - Elementor only
        return '';}
    }
    
    /**
     * Get integrations sorted by priority
     * 
     * @return array
     */
    private function getIntegrationsByPriority() {
        $sorted = [];
        
        foreach ($this->loaded_integrations as $key => $integration) {
            $priority = $this->integrations[$key]['priority'] ?? 999;
            $sorted[$priority] = $integration;
        }
        
        ksort($sorted);
        return $sorted;
    }
    
    /**
     * Register widget across all integrations
     * 
     * @param string $widget_type
     * @param array $config
     */
    public function registerWidget($widget_type, $config) {
        foreach ($this->loaded_integrations as $integration) {
            if (method_exists($integration, 'registerWidget')) {
                $integration->registerWidget($widget_type, $config);
            }
        }
    }
}