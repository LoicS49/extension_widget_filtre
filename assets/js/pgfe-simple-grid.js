/**
 * PGFE Simple Grid - Version nettoyée pour le nouveau SimpleGridWidget
 * Compatible avec le système de filtres intégré
 */

(function($) {
    'use strict';

    // Objet global pour la grille simplifiée
    window.PGFESimpleGrid = {
        initialized: false,
        
        // Initialiser les grilles SimpleGrid
        init: function() {
            $('.pgfe-simple-grid').each(function() {
                PGFESimpleGrid.setupGrid($(this));
            });
            
            // Écouter les événements de filtrage seulement une fois
            if (!this.initialized) {
                this.bindFilterEvents();
                this.initialized = true;
            }
        },
        
        // Configurer une grille SimpleGrid
        setupGrid: function($grid) {
            if ($grid.data('pgfe-simple-initialized')) {
                return;
            }
            
            // Marquer comme initialisée
            $grid.data('pgfe-simple-initialized', true);
            
            // Le SimpleGridWidget gère ses propres styles CSS
            // Pas besoin de manipulation JavaScript complexe
            $grid.addClass('pgfe-simple-ready');
            
            // Ajouter un loader pour le filtrage
            this.addGridLoader($grid);
            
            // Configuration terminée
        },
        
        // Ajouter un loader à la grille
        addGridLoader: function($grid) {
            if ($grid.find('.pgfe-grid-loader').length === 0) {
                const loader = '<div class="pgfe-grid-loader" style="display: none;"><div class="pgfe-spinner"></div><p>Filtrage en cours...</p></div>';
                $grid.prepend(loader);
            }
        },
        
        // Lier les événements de filtrage
        bindFilterEvents: function() {
            // Écouter les événements de début de filtrage
            $(document).on('pgfe:filter:start', function() {
                $('.pgfe-simple-grid').addClass('pgfe-filtering');
                $('.pgfe-grid-loader').show();
            });
            
            // Écouter les événements de fin de filtrage
            $(document).on('pgfe:filter:complete', function() {
                $('.pgfe-simple-grid').removeClass('pgfe-filtering');
                $('.pgfe-grid-loader').hide();
            });
            
            // Écouter les mises à jour de grille
            $(document).on('pgfe:grid:updated', function(event, data) {
                PGFESimpleGrid.onGridUpdated(data);
            });
        },
        
        // Gestionnaire de mise à jour de grille
        onGridUpdated: function(data) {
            // Animation des nouveaux éléments
            $('.pgfe-grid-item').each(function(index) {
                $(this).css('animation-delay', (index * 50) + 'ms').addClass('pgfe-fade-in');
            });
            
            // Déclencher un événement personnalisé
            $(document).trigger('pgfe:simple-grid:refreshed', data);
        },
        
        // Rafraîchir les grilles après filtrage
        refresh: function() {
            $('.pgfe-simple-grid').each(function() {
                const $grid = $(this);
                // Déclencher un événement de rafraîchissement si nécessaire
                $grid.trigger('pgfe:grid:refreshed');
            });
        },
        
        // Obtenir toutes les grilles actives
        getActiveGrids: function() {
            const grids = [];
            $('.pgfe-simple-grid').each(function() {
                const $grid = $(this);
                const widgetId = $grid.data('widget-id');
                if (widgetId) {
                    grids.push({
                        id: widgetId,
                        element: $grid,
                        columns: $grid.data('columns') || 4,
                        postsPerPage: $grid.data('posts-per-page') || 12
                    });
                }
            });
            return grids;
        }
    };
    
    // Auto-initialisation
    $(document).ready(function() {
        PGFESimpleGrid.init();
    });
    
    // Support Elementor
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function($scope) {
            if ($scope.find('.pgfe-simple-grid').length) {
                PGFESimpleGrid.init();
            }
        });
    });
    
})(jQuery);