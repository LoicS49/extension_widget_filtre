/**
 * PGFE Simple Filter - Version simplifiée du système de filtres
 * Réduit la complexité tout en gardant les fonctionnalités essentielles
 */

(function($) {
    'use strict';

    // Système de filtres simplifié
    window.PGFESimpleFilter = {
        
        // État des filtres
        filters: {},
        isFiltering: false,
        initialized: false,
        
        // Initialisation
        init: function() {
            if (!this.initialized) {
            this.bindEvents();
            this.initialized = true;
        }
        },
        
        // Liaison des événements simplifiée
        bindEvents: function() {
            // Un seul gestionnaire pour tous les filtres
            $(document).on('change input', '.pgfe-filter-widget input, .pgfe-filter-widget select', 
                this.handleFilterChange.bind(this));
            
            // Réinitialisation
            $(document).on('click', '.pgfe-filter-reset', this.resetFilters.bind(this));
        },
        
        // Gestionnaire de changement unifié
        handleFilterChange: function(event) {
            if (this.isFiltering) return;
            
            const $input = $(event.target);
            const $widget = $input.closest('.pgfe-filter-widget');
            const filterType = this.getFilterType($widget);
            const filterValue = this.getFilterValue($widget);
            
            // Mettre à jour les filtres avec les bons noms
            this.updateFilters(filterType, filterValue, $widget);
            
            // Appliquer avec un délai pour éviter les requêtes multiples
            clearTimeout(this.filterTimeout);
            this.filterTimeout = setTimeout(() => {
                this.applyFilters();
            }, 300);
        },
        
        // Détection simplifiée du type de filtre
        getFilterType: function($widget) {
            if ($widget.hasClass('pgfe-category-filter-widget')) return 'category';
            if ($widget.hasClass('pgfe-price-slider-widget')) return 'price';
            if ($widget.hasClass('pgfe-attribute-filter-widget')) return 'attribute';
            if ($widget.hasClass('pgfe-vendor-filter-widget')) return 'vendor';
            if ($widget.hasClass('pgfe-tag-filter-widget')) return 'tag';
            
            // Fallback basé sur les données
            return $widget.data('filter-type') || 'general';
        },
        
        // Extraction simplifiée de la valeur
        getFilterValue: function($widget) {
            const $inputs = $widget.find('input:checked, select option:selected, input[type="range"]');
            
            if ($inputs.length === 0) return null;
            
            // Pour les sliders de prix
            if ($widget.hasClass('pgfe-price-slider-widget')) {
                const min = $widget.find('input[data-type="min"]').val();
                const max = $widget.find('input[data-type="max"]').val();
                return { min: min, max: max };
            }
            
            // Pour les autres filtres
            const values = $inputs.map(function() {
                return $(this).val();
            }).get();
            
            return values.length === 1 ? values[0] : values;
        },
        
        // Mise à jour des filtres avec les bons noms pour le PHP
        updateFilters: function(filterType, filterValue, $widget) {
            // Nettoyer les anciens filtres de ce type
            this.clearFiltersByType(filterType);
            
            if (!filterValue || (Array.isArray(filterValue) && filterValue.length === 0)) {
                return;
            }
            
            switch (filterType) {
                case 'price':
                    if (filterValue.min && filterValue.min !== '') {
                        this.filters['min_price'] = parseFloat(filterValue.min);
                    }
                    if (filterValue.max && filterValue.max !== '') {
                        this.filters['max_price'] = parseFloat(filterValue.max);
                    }
                    break;
                    
                case 'category':
                    this.filters['category_filter'] = Array.isArray(filterValue) ? filterValue.map(v => parseInt(v)) : [parseInt(filterValue)];
                    break;
                    
                case 'vendor':
                    this.filters['vendor_filter'] = Array.isArray(filterValue) ? filterValue.map(v => parseInt(v)) : [parseInt(filterValue)];
                    break;
                    
                case 'attribute':
                    // Obtenir le nom de l'attribut depuis le widget
                    const attributeName = $widget.data('attribute-name') || $widget.find('[data-attribute-name]').data('attribute-name');
                    if (attributeName) {
                        if (!this.filters['attribute_filters']) {
                            this.filters['attribute_filters'] = {};
                        }
                        this.filters['attribute_filters'][attributeName] = Array.isArray(filterValue) ? filterValue : [filterValue];
                    }
                    break;
                    
                default:
                    // Pour les autres types, utiliser le nom tel quel
                    this.filters[filterType] = filterValue;
                    break;
            }
        },
        
        // Nettoyer les filtres par type
        clearFiltersByType: function(filterType) {
            switch (filterType) {
                case 'price':
                    delete this.filters['min_price'];
                    delete this.filters['max_price'];
                    break;
                    
                case 'category':
                    delete this.filters['category_filter'];
                    break;
                    
                case 'vendor':
                    delete this.filters['vendor_filter'];
                    break;
                    
                case 'attribute':
                    // Ne pas supprimer tous les attributs, juste celui concerné
                    // Cela sera géré dans updateFilters
                    break;
                    
                default:
                    delete this.filters[filterType];
                    break;
            }
        },
        
        // Application simplifiée des filtres
        applyFilters: function() {
            if (this.isFiltering) return;
            
            this.isFiltering = true;
            
            // Afficher le loader
            $('.pgfe-simple-grid').addClass('pgfe-filtering');
            $('.pgfe-grid-loader').show();
            
            // Log des filtres pour débogage
            // Envoi des filtres au serveur
            
            // Préparer les données avec settings par défaut
            const data = {
                action: 'pgfe_filter_products',
                nonce: window.pgfe_ajax ? pgfe_ajax.nonce : '',
                filters: this.filters,
                settings: {
                    posts_per_page: 12,
                    columns: 4,
                    show_badges: true,
                    show_rating: true,
                    show_vendor: true,
                    show_price: true,
                    show_add_to_cart: true,
                    image_size: 'medium'
                }
            };
            
            // Données AJAX préparées
            
            // Requête AJAX simplifiée
            $.ajax({
                url: window.pgfe_ajax ? pgfe_ajax.ajax_url : '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: data,
                success: this.handleSuccess.bind(this),
                error: function(xhr, status, error) {
                    this.handleError(error || status || 'Erreur inconnue', xhr);
                }.bind(this),
                complete: this.handleComplete.bind(this)
            });
        },
        
        // Gestion du succès
        handleSuccess: function(response) {
            // Réponse reçue du serveur
            
            if (response && response.success && response.data && response.data.html) {
                this.updateGrid(response.data.html);
                // Filtrage terminé avec succès
            } else {
                // Réponse invalide détectée
                this.handleError('Réponse invalide: ' + JSON.stringify(response));
            }
        },
        
        // Gestion des erreurs
        handleError: function(error, xhr) {
            // Erreur de filtrage gérée
            
            const errorHtml = `
                <div class="pgfe-filter-error">
                    <p>Erreur lors du filtrage des produits.</p>
                </div>
            `;
            
            this.updateGrid(errorHtml);
        },
        
        // Finalisation
        handleComplete: function() {
            this.isFiltering = false;
            $('.pgfe-simple-grid').removeClass('pgfe-filtering');
            $('.pgfe-grid-loader').hide();
        },
        
        // Mise à jour de la grille
        updateGrid: function(html) {
            const $grid = $('.pgfe-simple-grid').first();
            
            // Supprimer l'ancien contenu
            $grid.find('.pgfe-grid-item, .pgfe-no-products, .pgfe-filter-error').remove();
            
            // Ajouter le nouveau contenu
            $grid.append(html);
            
            // Animation simple
            $grid.find('.pgfe-grid-item').hide().fadeIn(200);
        },
        
        // Fonction de debug désactivée pour la production
        showDebugInfo: function() {
            return;
        },
        
        // Réinitialisation des filtres
        resetFilters: function() {
            this.filters = {};
            
            // Réinitialiser les inputs
            $('.pgfe-filter-widget input').prop('checked', false).val('');
            $('.pgfe-filter-widget select').prop('selectedIndex', 0);
            
            // Appliquer les filtres vides
            this.applyFilters();
        },
        
        // Obtenir les filtres actifs
        getActiveFilters: function() {
            return Object.assign({}, this.filters);
        },
        
        // Définir des filtres (usage externe)
        setFilters: function(newFilters) {
            this.filters = Object.assign({}, newFilters);
            this.applyFilters();
        }
    };
    
    // Auto-initialisation
    $(document).ready(function() {
        PGFESimpleFilter.init();
    });
    
    // Support Elementor - initialiser seulement si pas déjà fait
    if (typeof elementorFrontend !== 'undefined') {
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function($scope) {
            // Initialiser seulement si le widget contient des filtres
            if ($scope.find('.pgfe-filter-widget').length) {
                PGFESimpleFilter.init();
            }
        });
    }
    
})(jQuery);