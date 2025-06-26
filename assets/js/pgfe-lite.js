/**
 * PGFE Lite JavaScript
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Global PGFE object
    window.PGFE = window.PGFE || {};
    
    // Error handling utilities
    PGFE.ErrorHandler = {
        log: function(error, context) {
            // Gestion d'erreur simplifiée pour la production
        if (window.pgfe_ajax && window.pgfe_ajax.debug) {
                this.reportError(error, context);
            }
        },
        
        reportError: function(error, context) {
            try {
                $.ajax({
                    url: window.pgfe_ajax?.ajax_url || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'pgfe_log_js_error',
                        error: error.toString(),
                        context: JSON.stringify(context || {}),
                        url: window.location.href,
                        user_agent: navigator.userAgent,
                        nonce: window.pgfe_ajax?.nonce
                    },
                    timeout: 5000
                }).fail(function() {
                    // Silently fail - don't create infinite error loops
                });
            } catch (e) {
                // Silently fail
            }
        },
        
        wrap: function(fn, context) {
            const self = this;
            return function() {
                try {
                    return fn.apply(this, arguments);
                } catch (error) {
                    self.log(error, context);
                    return null;
                }
            };
        }
    };
    
    /**
     * Price Slider Component
     */
    PGFE.PriceSlider = {
        init: function() {
            $('.pgfe-price-slider-widget').each(function() {
                PGFE.PriceSlider.initSlider($(this));
            });
        },
        
        initSlider: function($widget) {
            try {
                const $slider = $widget.find('.pgfe-price-slider');
                const $minInput = $widget.find('input[name="min_price"]');
                const $maxInput = $widget.find('input[name="max_price"]');
                
                if (!$slider.length) {
                    PGFE.ErrorHandler.log(new Error('Price slider element not found'), {
                        widget: $widget.attr('class'),
                        html: $widget.html().substring(0, 200)
                    });
                    return;
                }
                
                const widget_id = $slider.attr('id') || 'slider-' + Math.random().toString(36).substr(2, 9);
            
            const minPrice = parseFloat($slider.data('min')) || 0;
            const maxPrice = parseFloat($slider.data('max')) || 1000;
            const currentMin = parseFloat($slider.data('current-min')) || minPrice;
            const currentMax = parseFloat($slider.data('current-max')) || maxPrice;
            const isLogarithmic = $slider.data('scale') === 'logarithmic';
            
            // Create slider HTML in the slider container
            const $sliderContainer = $slider; // Use the main slider element as container
            const sliderHtml = `
                <div class="pgfe-price-slider-inner">
                    <div class="slider-track"></div>
                    <div class="slider-handle slider-handle-min" data-handle="min"></div>
                    <div class="slider-handle slider-handle-max" data-handle="max"></div>
                </div>
            `;
            $sliderContainer.html(sliderHtml);
            
            // Set initial values for inputs
            $minInput.val(currentMin);
            $maxInput.val(currentMax);
            
            const $track = $sliderContainer.find('.slider-track');
            const $minHandle = $sliderContainer.find('.slider-handle-min');
            const $maxHandle = $sliderContainer.find('.slider-handle-max');
            
            // Update slider position
            function updateSlider() {
                const minVal = parseFloat($minInput.val()) || minPrice;
                const maxVal = parseFloat($maxInput.val()) || maxPrice;
                
                let minPercent, maxPercent;
                
                if (isLogarithmic) {
                    const logMin = Math.log(minPrice);
                    const logMax = Math.log(maxPrice);
                    const logMinVal = Math.log(Math.max(minVal, 1));
                    const logMaxVal = Math.log(Math.max(maxVal, 1));
                    
                    minPercent = ((logMinVal - logMin) / (logMax - logMin)) * 100;
                    maxPercent = ((logMaxVal - logMin) / (logMax - logMin)) * 100;
                } else {
                    minPercent = ((minVal - minPrice) / (maxPrice - minPrice)) * 100;
                    maxPercent = ((maxVal - minPrice) / (maxPrice - minPrice)) * 100;
                }
                
                minPercent = Math.max(0, Math.min(100, minPercent));
                maxPercent = Math.max(0, Math.min(100, maxPercent));
                
                $minHandle.css('left', minPercent + '%');
                $maxHandle.css('left', maxPercent + '%');
                
                $track.css({
                    'left': minPercent + '%',
                    'width': (maxPercent - minPercent) + '%'
                });
            }
            
            // Convert percentage to value
            function percentToValue(percent) {
                if (isLogarithmic) {
                    const logMin = Math.log(minPrice);
                    const logMax = Math.log(maxPrice);
                    return Math.exp(logMin + (percent / 100) * (logMax - logMin));
                } else {
                    return minPrice + (percent / 100) * (maxPrice - minPrice);
                }
            }
            
            // Handle dragging
            let isDragging = false;
            let currentHandle = null;
            
            // Define event names for namespacing
            const moveEventName = 'mousemove.priceSlider' + widget_id + ' touchmove.priceSlider' + widget_id;
            const endEventName = 'mouseup.priceSlider' + widget_id + ' touchend.priceSlider' + widget_id;

            // Handle mouse and touch events
            function startDrag(e, $handle) {
                if ($handle.closest('.pgfe-price-slider-widget')[0] === $widget[0]) {
                    isDragging = true;
                    currentHandle = $handle.data('handle');
                    $handle.addClass('active');
                    attachMoveEvents();
                    e.preventDefault();
                    e.stopPropagation();
                }
            }

            // Mouse events - use namespaced events to avoid conflicts
            $sliderContainer.on('mousedown.priceSlider', '.slider-handle', function(e) {
                startDrag(e, $(this));
            });

            // Touch events for mobile
            $sliderContainer.on('touchstart.priceSlider', '.slider-handle', function(e) {
                startDrag(e, $(this));
            });
            
            function handleMove(e) {
                if (!isDragging || !currentHandle) return;
                
                const $sliderInner = $sliderContainer.find('.pgfe-price-slider-inner');
                if (!$sliderInner.length) return;
                
                // Get coordinates from mouse or touch event
                const clientX = e.type.indexOf('touch') === 0 ? e.originalEvent.touches[0].clientX : e.clientX;
                
                const sliderOffset = $sliderInner.offset();
                const sliderWidth = $sliderInner.width();
                const mouseX = clientX - sliderOffset.left;
                const percent = Math.max(0, Math.min(100, (mouseX / sliderWidth) * 100));
                const value = Math.round(percentToValue(percent));
                
                if (currentHandle === 'min') {
                    const maxVal = parseFloat($maxInput.val());
                    if (value <= maxVal) {
                        $minInput.val(value);
                    }
                } else if (currentHandle === 'max') {
                    const minVal = parseFloat($minInput.val());
                    if (value >= minVal) {
                        $maxInput.val(value);
                    }
                }
                
                updateSlider();
                e.preventDefault();
            }
            
            // Mouse and touch move events - use namespaced events
            function attachMoveEvents() {
                $(document).on(moveEventName, handleMove);
                $(document).on(endEventName, endDrag);
            }
            
            function detachMoveEvents() {
                $(document).off(moveEventName);
                $(document).off(endEventName);
            }
            
            // Initial slider update
            updateSlider();
            
            function endDrag(e) {
                if (isDragging) {
                    isDragging = false;
                    currentHandle = null;
                    $sliderContainer.find('.slider-handle').removeClass('active');
                    detachMoveEvents();
                    
                    // Trigger filter after a short delay to ensure values are updated
                    setTimeout(function() {
                        if (typeof PGFE.FilterManager !== 'undefined' && PGFE.FilterManager.triggerFilter) {
                            PGFE.FilterManager.triggerFilter();
                        }
                    }, 50);
                }
            }
            
            // Handle input changes
            $minInput.add($maxInput).on('input change', function() {
                let minVal = parseFloat($minInput.val());
                let maxVal = parseFloat($maxInput.val());
                
                // Validate and constrain values
                if (isNaN(minVal) || minVal < minPrice) minVal = minPrice;
                if (isNaN(maxVal) || maxVal > maxPrice) maxVal = maxPrice;
                if (minVal > maxPrice) minVal = maxPrice;
                if (maxVal < minPrice) maxVal = minPrice;
                
                // Ensure min <= max
                if (minVal > maxVal) {
                    if ($(this).attr('name') === 'min_price') {
                        maxVal = minVal;
                        $maxInput.val(maxVal);
                    } else {
                        minVal = maxVal;
                        $minInput.val(minVal);
                    }
                }
                
                updateSlider();
                
                // Trigger filter with debounce
                clearTimeout(this.inputTimeout);
                this.inputTimeout = setTimeout(function() {
                    if (typeof PGFE.FilterManager !== 'undefined' && PGFE.FilterManager.triggerFilter) {
                        PGFE.FilterManager.triggerFilter();
                    }
                }, 300);
            });
            
            // Cleanup function to prevent memory leaks
            function cleanup() {
                detachMoveEvents();
                $sliderContainer.off('.priceSlider');
                $minInput.add($maxInput).off('input change');
            }
            
            // Store cleanup function for potential future use
            $widget.data('cleanup', cleanup);
            
            // Initialize
            $minInput.val(currentMin);
            $maxInput.val(currentMax);
            updateSlider();
            
            } catch (error) {
                PGFE.ErrorHandler.log(error, {
                    method: 'initSlider',
                    widget_id: widget_id || 'unknown',
                    widget_class: $widget.attr('class')
                });
            }
        }
    };
    
    /**
     * Category Filter Component
     */
    PGFE.CategoryFilter = {
        init: function() {
            this.initParentFilters();
            this.initChildFilters();
            this.initAccordions();
            this.initPills();
        },
        
        initParentFilters: function() {
            $('.pgfe-parent-category-filter select, .pgfe-parent-category-filter input').on('change', function() {
                PGFE.CategoryFilter.loadChildCategories($(this).closest('.pgfe-parent-category-filter'));
                PGFE.FilterManager.triggerFilter();
            });
        },
        
        initChildFilters: function() {
            $('.pgfe-child-category-filter').each(function() {
                const $widget = $(this);
                const parentSource = $widget.data('parent-source');
                
                if (parentSource === 'dynamic') {
                    PGFE.CategoryFilter.loadChildCategories($widget);
                }
            });
            
            $(document).on('change', '.pgfe-child-category-filter input', function() {
                PGFE.FilterManager.triggerFilter();
            });
        },
        
        loadChildCategories: function($widget) {
            const $container = $widget.find('.pgfe-child-categories');
            const parentId = $widget.find('select').val() || $widget.data('parent-id');
            const showCount = $widget.data('show-count') || false;
            const includeIds = $widget.data('include-ids') || [];
            const excludeIds = $widget.data('exclude-ids') || [];
            
            if (!parentId) {
                $container.empty();
                return;
            }
            
            $container.html('<div class="pgfe-child-category-loading">Loading categories...</div>');
            
            $.ajax({
                url: pgfe_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pgfe_get_child_categories',
                    nonce: pgfe_ajax.nonce,
                    parent_id: parentId,
                    show_count: showCount,
                    include_ids: includeIds,
                    exclude_ids: excludeIds
                },
                success: function(response) {
                    if (response.success) {
                        PGFE.CategoryFilter.renderChildCategories($container, response.data.categories, $widget.data('display-type'));
                    } else {
                        $container.html('<div class="pgfe-error">Error loading categories</div>');
                    }
                },
                error: function() {
                    $container.html('<div class="pgfe-error">Error loading categories</div>');
                }
            });
        },
        
        renderChildCategories: function($container, categories, displayType) {
            let html = '';
            
            switch (displayType) {
                case 'checkbox':
                    html = '<ul class="pgfe-category-list">';
                    categories.forEach(function(category) {
                        html += `
                            <li>
                                <label>
                                    <input type="checkbox" name="child_categories[]" value="${category.id}" />
                                    ${category.name}
                                    ${category.count ? `<span class="pgfe-category-count">(${category.count})</span>` : ''}
                                </label>
                            </li>
                        `;
                    });
                    html += '</ul>';
                    break;
                    
                case 'buttons':
                    html = '<div class="pgfe-category-pills">';
                    categories.forEach(function(category) {
                        html += `
                            <button type="button" class="pgfe-category-pill" data-value="${category.id}">
                                ${category.name}
                                ${category.count ? ` (${category.count})` : ''}
                            </button>
                        `;
                    });
                    html += '</div>';
                    break;
                    
                case 'accordion':
                    html = '<div class="pgfe-category-accordion">';
                    categories.forEach(function(category) {
                        html += `
                            <div class="pgfe-accordion-item">
                                <div class="pgfe-accordion-header" data-category="${category.id}">
                                    <span>${category.name} ${category.count ? `(${category.count})` : ''}</span>
                                    <span class="pgfe-accordion-icon">▼</span>
                                </div>
                                <div class="pgfe-accordion-content">
                                    <label>
                                        <input type="checkbox" name="child_categories[]" value="${category.id}" />
                                        Select ${category.name}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    break;
            }
            
            $container.html(html);
        },
        
        initAccordions: function() {
            $(document).on('click', '.pgfe-accordion-header', function() {
                const $header = $(this);
                const $content = $header.next('.pgfe-accordion-content');
                const $icon = $header.find('.pgfe-accordion-icon');
                
                $header.toggleClass('active');
                $content.toggleClass('active');
                
                if ($content.hasClass('active')) {
                    $content.slideDown(200);
                } else {
                    $content.slideUp(200);
                }
            });
        },
        
        initPills: function() {
            $(document).on('click', '.pgfe-category-pill', function() {
                const $pill = $(this);
                const value = $pill.data('value');
                const $widget = $pill.closest('.pgfe-category-filter-widget');
                const multiSelect = $widget.data('multi-select') !== false;
                
                if (multiSelect) {
                    $pill.toggleClass('active');
                } else {
                    $widget.find('.pgfe-category-pill').removeClass('active');
                    $pill.addClass('active');
                }
                
                PGFE.CategoryFilter.updateHiddenInputs($widget);
                PGFE.FilterManager.triggerFilter();
            });
        },
        
        updateHiddenInputs: function($widget) {
            const values = [];
            $widget.find('.pgfe-category-pill.active').each(function() {
                values.push($(this).data('value'));
            });
            
            // Update or create hidden inputs
            $widget.find('input[name="categories[]"]').remove();
            values.forEach(function(value) {
                $widget.append(`<input type="hidden" name="categories[]" value="${value}" />`);
            });
        }
    };
    
    /**
     * Filter Manager
     */
    PGFE.FilterManager = {
        debounceTimer: null,
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Form submission (if any form exists)
            $(document).on('submit', '.pgfe-filter-form', function(e) {
                e.preventDefault();
                PGFE.FilterManager.applyFilters.call(PGFE.FilterManager);
            });
            
            // Reset filters
            $(document).on('click', '.pgfe-filter-reset', function(e) {
                e.preventDefault();
                PGFE.FilterManager.resetFilters();
            });
            
            // Auto-filter on input change (debounced) - listen to all filter widgets
            $(document).on('change input', '.pgfe-price-slider-widget input, .pgfe-vendor-dropdown, .pgfe-tag-filter input, .pgfe-parent-category-filter select, .pgfe-child-category-filter input, .pgfe-attribute-filter input', function() {
                PGFE.FilterManager.triggerFilter();
            });
            
            // Handle category buttons and other clickable filters
            $(document).on('click', '[data-filter]', function() {
                $(this).toggleClass('active');
                PGFE.FilterManager.triggerFilter();
            });
        },
        
        triggerFilter: function() {
            clearTimeout(this.debounceTimer);
            const self = this;
            this.debounceTimer = setTimeout(function() {
                self.applyFilters.call(self);
            }, 300);
        },
        
        applyFilters: function(retryCount = 0) {
            const $grid = $('.pgfe-simple-grid');
            
            if (!$grid.length) {
                PGFE.ErrorHandler.log(new Error('Product grid not found'), {
                    method: 'applyFilters',
                    selector: '.pgfe-simple-grid'
                });
                return;
            }
            
            const filters = this.collectFilters();
            const settings = this.collectSettings($grid);
            const maxRetries = 3;
            
            $grid.addClass('pgfe-loading');
            
            // Show loading indicator
            PGFE.Utils.showLoadingState($grid);
            
            // Requête AJAX envoyée
            
            $.ajax({
                url: pgfe_ajax?.ajax_url || '/wp-admin/admin-ajax.php',
                type: 'POST',
                timeout: 30000, // 30 second timeout
                data: {
                    action: 'pgfe_filter_products',
                    nonce: pgfe_ajax?.nonce,
                    filters: filters,
                    settings: settings
                },
                success: function(response) {
                    // Réponse AJAX reçue
                    try {
                        if (response && response.success) {
                            PGFE.Utils.handleSuccessResponse(response, $grid);
                        } else {
                            PGFE.Utils.handleErrorResponse(response, $grid, retryCount, maxRetries, filters, settings);
                        }
                    } catch (error) {
                        PGFE.ErrorHandler.log(error, {
                            method: 'applyFilters.success',
                            response: response
                        });
                        PGFE.Utils.showErrorMessage($grid, 'Error processing response');
                    }
                },
                error: function(xhr, status, error) {
                    // Erreur AJAX gérée
                    PGFE.Utils.handleAjaxError(xhr, status, error, $grid, retryCount, maxRetries, filters, settings);
                },
                complete: function() {
                    // Requête AJAX terminée
                    $grid.removeClass('pgfe-loading');
                }
            });
        },
        
        collectFilters: function() {
            const filters = {};
            
            // Price filters
            const minPrice = $('input[name="min_price"]').val();
            const maxPrice = $('input[name="max_price"]').val();
            if (minPrice) filters.min_price = minPrice;
            if (maxPrice) filters.max_price = maxPrice;
            
            // Category filters
            const parentCategories = [];
            $('input[name="parent_categories[]"]:checked, select[name="parent_categories"] option:selected, .pgfe-parent-category-filter select option:selected').each(function() {
                const value = $(this).val();
                if (value) parentCategories.push(value);
            });
            // Also collect from active parent category buttons
            $('.category-button.active[data-filter="parent-category"]').each(function() {
                const categoryId = $(this).data('category-id');
                if (categoryId) parentCategories.push(categoryId);
            });
            if (parentCategories.length) filters.parent_categories = parentCategories;
            
            const childCategories = [];
            $('input[name="child_categories[]"]:checked, input[name="categories[]"]:checked, .pgfe-child-category-filter input:checked').each(function() {
                const value = $(this).val();
                if (value) childCategories.push(value);
            });
            // Also collect from active category buttons
            $('.category-button.active[data-filter="child-category"]').each(function() {
                const categoryId = $(this).data('category-id');
                if (categoryId) childCategories.push(categoryId);
            });
            if (childCategories.length) filters.child_categories = childCategories;
            
            // Vendor filters
            const vendors = [];
            $('input[name="vendor[]"]:checked, select[name="vendor"] option:selected, .pgfe-vendor-dropdown option:selected').each(function() {
                const value = $(this).val();
                if (value) vendors.push(value);
            });
            if (vendors.length) filters.vendor_filter = vendors;
            
            // Search
            const search = $('input[name="search"]').val();
            if (search) filters.search = search;
            
            // Sorting
            const orderby = $('select[name="orderby"]').val();
            const order = $('select[name="order"]').val();
            if (orderby) filters.orderby = orderby;
            if (order) filters.order = order;
            
            return filters;
        },
        
        collectSettings: function($grid) {
            // Préserver les paramètres originaux de la grille
            const columns = $grid.attr('data-columns') || $grid.data('columns') || 3;
            return {
                columns: parseInt(columns),
                posts_per_page: $grid.data('posts-per-page') || 8,
                show_badges: $grid.data('show-badges') !== false,
                show_rating: $grid.data('show-rating') !== false,
                show_vendor: $grid.data('show-vendor') !== false,
                show_price: $grid.data('show-price') !== false,
                show_add_to_cart: $grid.data('show-add-to-cart') !== false,
                image_size: $grid.data('image-size') || 'medium',
                grid_gap: $grid.data('grid-gap') || '20px',
                card_padding: $grid.data('card-padding') || '15px',
                border_radius: $grid.data('border-radius') || '8px',
                box_shadow: $grid.data('box-shadow') || '0 2px 8px rgba(0,0,0,0.1)',
                hover_effect: $grid.data('hover-effect') !== false,
                lazy_load: $grid.data('lazy-load') !== false
            };
        },
        
        updateResultsInfo: function(data) {
            const $info = $('.pgfe-results-info');
            if ($info.length) {
                $info.html(`Found ${data.found_products} products`);
            }
        },
        
        updateUrl: function(filters) {
            if (!window.history || !window.history.pushState) return;
            
            const url = new URL(window.location);
            
            // Clear existing filter params
            url.searchParams.delete('min_price');
            url.searchParams.delete('max_price');
            url.searchParams.delete('parent_categories');
            url.searchParams.delete('child_categories');
            url.searchParams.delete('vendor');
            url.searchParams.delete('search');
            url.searchParams.delete('orderby');
            url.searchParams.delete('order');
            
            // Add new filter params
            Object.keys(filters).forEach(function(key) {
                const value = filters[key];
                if (Array.isArray(value)) {
                    value.forEach(function(v) {
                        url.searchParams.append(key, v);
                    });
                } else {
                    url.searchParams.set(key, value);
                }
            });
            
            window.history.pushState({}, '', url.toString());
        },
        
        resetFilters: function() {
            // Reset all form elements
            $('.pgfe-filter-form')[0] && $('.pgfe-filter-form')[0].reset();
            
            // Reset category pills and checkboxes
            $('.pgfe-category-pill').removeClass('active');
            $('.category-button').removeClass('active');
            $('input[name="categories[]"]').remove();
            $('input[type="checkbox"]').prop('checked', false);
            $('select').prop('selectedIndex', 0);
            
            // Reset sliders
            $('.pgfe-price-slider-widget').each(function() {
                const $widget = $(this);
                const $slider = $widget.find('.pgfe-price-slider');
                const minPrice = $slider.data('min') || 0;
                const maxPrice = $slider.data('max') || 1000;
                
                $widget.find('input[name="min_price"]').val(minPrice);
                $widget.find('input[name="max_price"]').val(maxPrice);
                
                PGFE.PriceSlider.initSlider($widget);
            });
            
            this.applyFilters();
        }
    };
    
    /**
     * Load More Component
     */
    PGFE.LoadMore = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            $(document).on('click', '.pgfe-load-more', function(e) {
                e.preventDefault();
                PGFE.LoadMore.loadMore($(this));
            });
        },
        
        loadMore: function($button) {
            const $grid = $button.closest('.pgfe-simple-grid-container').find('.pgfe-simple-grid');
            const currentPage = parseInt($button.data('page')) || 1;
            const nextPage = currentPage + 1;
            
            $button.addClass('pgfe-loading').prop('disabled', true);
            
            const filters = PGFE.FilterManager.collectFilters($('.pgfe-filter-form'));
            const settings = PGFE.FilterManager.collectSettings($grid);
            
            $.ajax({
                url: pgfe_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pgfe_load_more_products',
                    nonce: pgfe_ajax.nonce,
                    filters: filters,
                    settings: settings,
                    page: nextPage
                },
                success: function(response) {
                    if (response.success) {
                        $grid.append(response.data.html);
                        $button.data('page', nextPage);
                        
                        if (!response.data.has_more) {
                            $button.hide();
                        }
                    } else {
                        // Erreur de chargement gérée
                    }
                },
                error: function() {
                    // Erreur AJAX gérée
                },
                complete: function() {
                    $button.removeClass('pgfe-loading').prop('disabled', false);
                }
            });
        }
    };
    
    /**
     * Add to Cart Enhancement
     */
    PGFE.AddToCart = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            $(document).on('click', '.pgfe-add-to-cart.ajax_add_to_cart', function(e) {
                e.preventDefault();
                PGFE.AddToCart.addToCart($(this));
            });
        },
        
        addToCart: function($button) {
            const productId = $button.data('product_id');
            const quantity = $button.data('quantity') || 1;
            
            $button.addClass('pgfe-loading').prop('disabled', true);
            
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'woocommerce_add_to_cart',
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.error) {
                        // Erreur d'ajout au panier gérée
                    } else {
                        // Update cart fragments
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                        
                        // Show success message
                        $button.text('Added to Cart!');
                        setTimeout(function() {
                            $button.text($button.data('original-text') || 'Add to Cart');
                        }, 2000);
                    }
                },
                error: function() {
                    console.error('AJAX error occurred');
                },
                complete: function() {
                    $button.removeClass('pgfe-loading').prop('disabled', false);
                }
            });
        }
    };
    
    /**
     * Utility Functions
     */
    PGFE.Utils = {
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },
        
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },
        
        showLoadingState: function($grid) {
            if (!$grid.find('.pgfe-loading-overlay').length) {
                $grid.append('<div class="pgfe-loading-overlay"><div class="pgfe-spinner"></div><div class="pgfe-loading-text">Loading PGFE Products...</div></div>');
                
                // Timeout de sécurité pour supprimer l'overlay après 30 secondes
                const timeoutId = setTimeout(() => {
                    // Timeout de chargement atteint
                    PGFE.Utils.hideLoadingState($grid);
                    PGFE.Utils.showErrorMessage($grid, 'Loading timeout - please refresh the page');
                }, 30000);
                
                // Stocker l'ID du timeout pour pouvoir l'annuler
                $grid.data('pgfe-timeout-id', timeoutId);
            }
        },
        
        hideLoadingState: function($grid) {
            // Annuler le timeout de sécurité
            const timeoutId = $grid.data('pgfe-timeout-id');
            if (timeoutId) {
                clearTimeout(timeoutId);
                $grid.removeData('pgfe-timeout-id');
            }
            
            $grid.find('.pgfe-loading-overlay').remove();
            // État de chargement supprimé
        },
        
        handleSuccessResponse: function(response, $grid) {
            try {
                // Supprimer l'overlay de chargement
                PGFE.Utils.hideLoadingState($grid);
                
                // Préserver les attributs de grille avant de remplacer le contenu
                const columns = $grid.attr('data-columns') || $grid.data('columns');
                const gridGap = $grid.data('grid-gap');
                const cardPadding = $grid.data('card-padding');
                const borderRadius = $grid.data('border-radius');
                const boxShadow = $grid.data('box-shadow');
                const hoverEffect = $grid.data('hover-effect');
                const lazyLoad = $grid.data('lazy-load');
                
                $grid.html(response.data.html || '');
                
                // Restaurer les attributs de grille
                if (columns) {
                    $grid.attr('data-columns', columns);
                    $grid.data('columns', columns);
                }
                if (gridGap) $grid.data('grid-gap', gridGap);
                if (cardPadding) $grid.data('card-padding', cardPadding);
                if (borderRadius) $grid.data('border-radius', borderRadius);
                if (boxShadow) $grid.data('box-shadow', boxShadow);
                if (hoverEffect !== undefined) $grid.data('hover-effect', hoverEffect);
                if (lazyLoad !== undefined) $grid.data('lazy-load', lazyLoad);
                
                // Le nouveau SimpleGridWidget gère ses propres styles
                if (window.PGFE && window.PGFE.SimpleGrid) {
                    setTimeout(function() {
                        window.PGFE.SimpleGrid.refreshGrids();
                    }, 100);
                }
                
                PGFE.FilterManager.updateResultsInfo(response.data);
                PGFE.FilterManager.updateUrl(PGFE.FilterManager.collectFilters());
                
            } catch (error) {
                PGFE.ErrorHandler.log(error, {
                    method: 'handleSuccessResponse',
                    response: response
                });
                PGFE.Utils.showErrorMessage($grid, 'Error updating results');
            }
        },
        
        handleErrorResponse: function(response, $grid, retryCount, maxRetries, filters, settings) {
            // Supprimer l'overlay de chargement
            PGFE.Utils.hideLoadingState($grid);
            
            const errorData = response?.data || {};
            
            // Check if we should retry
            if (retryCount < maxRetries && errorData.retry_after) {
                const retryDelay = Math.min(errorData.retry_after * 1000, 30000); // Max 30 seconds
                setTimeout(() => {
                    PGFE.FilterManager.applyFilters(retryCount + 1);
                }, retryDelay);
                return;
            }
            
            // Show fallback data if available
            if (errorData.fallback_data) {
                $grid.html(errorData.fallback_data.html || '');
            } else {
                PGFE.Utils.showErrorMessage($grid, errorData.message || 'Filter error occurred');
            }
            
            PGFE.ErrorHandler.log(new Error(errorData.message || 'Filter error'), {
                method: 'handleErrorResponse',
                response: response,
                retryCount: retryCount
            });
        },
        
        handleAjaxError: function(xhr, status, error, $grid, retryCount, maxRetries, filters, settings) {
            // Supprimer l'overlay de chargement si on ne va pas réessayer
            const shouldRetry = retryCount < maxRetries && 
                               (status === 'timeout' || xhr.status === 503 || xhr.status === 502);
            
            if (!shouldRetry) {
                 PGFE.Utils.hideLoadingState($grid);
             }
            
            if (shouldRetry) {
                const retryDelay = Math.pow(2, retryCount) * 1000; // Exponential backoff
                setTimeout(() => {
                    PGFE.FilterManager.applyFilters(retryCount + 1);
                }, retryDelay);
                return;
            }
            
            let errorMessage = 'Network error occurred';
            if (status === 'timeout') {
                errorMessage = 'Request timed out. Please try again.';
            } else if (xhr.status === 503) {
                errorMessage = 'Service temporarily unavailable. Please try again later.';
            } else if (xhr.status === 502) {
                errorMessage = 'Server error. Please try again later.';
            }
            
            PGFE.Utils.showErrorMessage($grid, errorMessage);
            
            PGFE.ErrorHandler.log(new Error(`AJAX Error: ${status} - ${error}`), {
                method: 'handleAjaxError',
                status: status,
                error: error,
                xhr_status: xhr.status,
                retryCount: retryCount
            });
        },
        
        showErrorMessage: function($grid, message) {
            // Supprimer l'overlay de chargement
             PGFE.Utils.hideLoadingState($grid);
            
            const errorHtml = `
                <div class="pgfe-error-message">
                    <p>${message}</p>
                    <button class="pgfe-retry-btn" onclick="PGFE.FilterManager.applyFilters()">Retry</button>
                </div>
            `;
            $grid.html(errorHtml);
        }
    };
    
    /**
     * Initialize everything when DOM is ready
     */
    $(document).ready(function() {
        PGFE.PriceSlider.init();
        PGFE.CategoryFilter.init();
        PGFE.FilterManager.init();
        PGFE.LoadMore.init();
        PGFE.AddToCart.init();
        
        // Le nouveau SimpleGridWidget n'a pas besoin de GridManager
        // Les styles sont intégrés directement dans le widget
        // Système de grille simplifié initialisé
        
        // Initialize from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.toString()) {
            const $form = $('.pgfe-filter-form').first();
            if ($form.length) {
                // Populate form from URL
                urlParams.forEach(function(value, key) {
                    const $input = $form.find(`[name="${key}"], [name="${key}[]"]`);
                    if ($input.length) {
                        if ($input.is(':checkbox, :radio')) {
                            $input.filter(`[value="${value}"]`).prop('checked', true);
                        } else {
                            $input.val(value);
                        }
                    }
                });
                
                // Trigger filter
                setTimeout(function() {
                    PGFE.FilterManager.applyFilters();
                }, 100);
            }
        }
    });
    
    /**
     * Handle Elementor frontend
     */
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/llda-price-slider.default', function($scope) {
            PGFE.PriceSlider.init();
        });
        
        elementorFrontend.hooks.addAction('frontend/element_ready/llda-parent-category-filter.default', function($scope) {
            PGFE.CategoryFilter.init();
        });
        
        elementorFrontend.hooks.addAction('frontend/element_ready/llda-child-category-filter.default', function($scope) {
            PGFE.CategoryFilter.init();
        });
    });
    
})(jQuery);