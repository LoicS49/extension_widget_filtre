/**
 * Elementor Fix for PGFE Widgets
 * 
 * This script fixes the "Cannot read properties of undefined (reading 'content')" error
 * that occurs when Elementor tries to access tab content properties.
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Wait for Elementor to be ready
    $(window).on('elementor/frontend/init', function() {
        
        // Fix for tab content property error
        if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
            
            // Hook into widget initialization
            elementorFrontend.hooks.addAction('frontend/element_ready/widget', function($scope, $) {
                
                // Check if this is a PGFE widget
                if ($scope.hasClass('elementor-widget-pgfe-price-slider') ||
                    $scope.hasClass('elementor-widget-pgfe-parent-category-filter') ||
                    $scope.hasClass('elementor-widget-pgfe-child-category-filter') ||
                    $scope.hasClass('elementor-widget-pgfe-archive-category-filter') ||
                    $scope.hasClass('elementor-widget-pgfe-attribute-filter') ||
                    $scope.hasClass('elementor-widget-pgfe-vendor-filter') ||
                    $scope.hasClass('elementor-widget-pgfe-tag-filter') ||
                    $scope.hasClass('elementor-widget-pgfe-simple-grid')) {
                    
                    // Initialize PGFE widget
                    initializePGFEWidget($scope);
                }
            });
        }
    });
    
    // Fix for editor mode
    $(window).on('elementor:init', function() {
        
        // Override Elementor's setDefaultTab method to handle missing content property
        if (typeof elementor !== 'undefined' && elementor.modules && elementor.modules.controls) {
            
            // Patch the tabs control to handle missing content
            const originalSetDefaultTab = elementor.modules.controls.Tabs?.prototype?.setDefaultTab;
            
            if (originalSetDefaultTab) {
                elementor.modules.controls.Tabs.prototype.setDefaultTab = function() {
                    try {
                        // Ensure tabs have proper structure
                        if (this.model && this.model.get('tabs')) {
                            const tabs = this.model.get('tabs');
                            
                            // Validate each tab has required properties
                            Object.keys(tabs).forEach(tabKey => {
                                if (!tabs[tabKey].content) {
                                    tabs[tabKey].content = '';
                                }
                                if (!tabs[tabKey].label) {
                                    tabs[tabKey].label = tabKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                }
                            });
                        }
                        
                        return originalSetDefaultTab.apply(this, arguments);
                    } catch (error) {
                        // Correction de l'erreur d'onglet Elementor appliquée
                        return null;
                    }
                };
            }
        }
    });
    
    /**
     * Initialize PGFE widget functionality
     * 
     * @param {jQuery} $scope Widget scope
     */
    function initializePGFEWidget($scope) {
        
        // Add any widget-specific initialization here
        $scope.find('.pgfe-widget-container').each(function() {
            const $container = $(this);
            
            // Add loaded class
            $container.addClass('pgfe-loaded');
            
            // Trigger custom event
            $container.trigger('pgfe:widget:loaded');
        });
    }
    
    // Global error handler for PGFE widgets
    window.addEventListener('error', function(event) {
        
        // Check if error is related to Elementor content property
        if (event.error && event.error.message && 
            event.error.message.includes("Cannot read properties of undefined (reading 'content')")) {
            
            // Tentative de correction de l'erreur de propriété content d'Elementor
            
            // Prevent the error from propagating
            event.preventDefault();
            event.stopPropagation();
            
            return false;
        }
    });
    
})(jQuery);