/**
 * PGFE Elementor JavaScript - Version nettoyée
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Elementor frontend hooks
    $(window).on('elementor/frontend/init', function() {
        
        // Initialize PGFE widgets when Elementor loads them
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function($scope) {
            
            // Initialize SimpleGrid widgets (nouveau système)
            if ($scope.find('.pgfe-simple-grid').length) {
                const $grid = $scope.find('.pgfe-simple-grid');
                
                // Marquer comme prêt pour Elementor
                if (!$grid.hasClass('pgfe-elementor-ready')) {
                    // Le nouveau SimpleGridWidget gère ses propres styles
                    // Pas besoin d'initialisation JavaScript complexe
                    $grid.addClass('pgfe-elementor-ready');
                }
            }
            
            // Initialize Filter widgets
            if ($scope.find('.pgfe-filter-widget').length) {
                if (typeof window.PGFE !== 'undefined') {
                    // Initialize price sliders
                    if (window.PGFE.PriceSlider) {
                        window.PGFE.PriceSlider.init();
                    }
                    
                    // Initialize filter integration
                    if (window.PGFE.FilterIntegration) {
                        window.PGFE.FilterIntegration.init();
                    }
                }
            }
        });
        
        // Handle Elementor editor mode
        if (elementorFrontend.isEditMode()) {
            // Mode éditeur Elementor détecté
        }
    });
    
})(jQuery);