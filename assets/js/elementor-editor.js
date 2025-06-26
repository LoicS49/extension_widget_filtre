/**
 * PGFE Elementor Editor JavaScript
 * 
 * Enhances the Elementor editor experience for PGFE widgets
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * PGFE Editor Enhancement
     */
    const PGFEEditor = {
        
        /**
         * Initialize editor enhancements
         */
        init: function() {
            // Wait for Elementor editor to be ready
            if (typeof elementor !== 'undefined') {
                this.setupEditorHooks();
            } else {
                // Fallback: wait for editor load
                $(window).on('elementor:init', () => {
                    this.setupEditorHooks();
                });
            }
        },
        
        /**
         * Setup Elementor editor hooks
         */
        setupEditorHooks: function() {
            // Hook into panel opening
            if (elementor.hooks) {
                elementor.hooks.addAction('panel/open_editor/widget', (panel, model, view) => {
                    this.onWidgetPanelOpen(panel, model, view);
                });
            }
            
            // Add custom CSS for PGFE widgets in editor
            this.addEditorStyles();
        },
        
        /**
         * Handle widget panel opening
         */
        onWidgetPanelOpen: function(panel, model, view) {
            const widgetType = model.get('widgetType');
            
            // Only handle PGFE widgets
            if (widgetType && widgetType.indexOf('pgfe-') === 0) {
                this.enhancePGFEWidgetPanel(panel, model, view);
            }
        },
        
        /**
         * Enhance PGFE widget panel
         */
        enhancePGFEWidgetPanel: function(panel, model, view) {
            // Add helpful tooltips and descriptions
            setTimeout(() => {
                this.addWidgetTooltips();
                this.addWidgetDescriptions();
            }, 100);
        },
        
        /**
         * Add helpful tooltips to widget controls
         */
        addWidgetTooltips: function() {
            // Add tooltips for complex controls
            $('.elementor-control-type-select select[data-setting="filter_type"]').each(function() {
                const $control = $(this).closest('.elementor-control');
                if (!$control.find('.pgfe-tooltip').length) {
                    $control.append('<div class="pgfe-tooltip">Choisissez le type de filtre à afficher</div>');
                }
            });
        },
        
        /**
         * Add widget descriptions
         */
        addWidgetDescriptions: function() {
            // Add descriptions for PGFE widget sections
            $('.elementor-control-section_content .elementor-panel-heading-title').each(function() {
                const $title = $(this);
                const text = $title.text();
                
                if (text.includes('PGFE') && !$title.next('.pgfe-description').length) {
                    $title.after('<div class="pgfe-description">Configuration des widgets PGFE pour WooCommerce</div>');
                }
            });
        },
        
        /**
         * Add custom CSS for editor
         */
        addEditorStyles: function() {
            const css = `
                .pgfe-tooltip {
                    font-size: 11px;
                    color: #71d7f7;
                    margin-top: 5px;
                    font-style: italic;
                }
                
                .pgfe-description {
                    font-size: 12px;
                    color: #a4afb7;
                    margin: 5px 0;
                    padding: 5px;
                    background: rgba(255,255,255,0.05);
                    border-radius: 3px;
                }
                
                .elementor-control-type-pgfe_info {
                    background: #f1f3f4;
                    border-left: 3px solid #71d7f7;
                    padding: 10px;
                    margin: 10px 0;
                }
                
                .pgfe-widget-icon {
                    color: #71d7f7;
                }
            `;
            
            if (!$('#pgfe-editor-styles').length) {
                $('<style id="pgfe-editor-styles">' + css + '</style>').appendTo('head');
            }
        }
    };
    
    /**
     * Widget Preview Enhancement
     */
    const WidgetPreview = {
        
        /**
         * Initialize preview enhancements
         */
        init: function() {
            // Enhance widget previews in editor
            this.setupPreviewHooks();
        },
        
        /**
         * Setup preview hooks
         */
        setupPreviewHooks: function() {
            // Monitor for widget changes
            $(document).on('input change', '.elementor-control input, .elementor-control select', (e) => {
                this.onControlChange(e);
            });
        },
        
        /**
         * Handle control changes
         */
        onControlChange: function(event) {
            const $control = $(event.target).closest('.elementor-control');
            const controlName = $control.data('setting');
            
            // Add visual feedback for important controls
            if (controlName && this.isImportantControl(controlName)) {
                this.highlightControl($control);
            }
        },
        
        /**
         * Check if control is important
         */
        isImportantControl: function(controlName) {
            const importantControls = [
                'filter_type',
                'product_count',
                'columns',
                'show_title',
                'price_range'
            ];
            
            return importantControls.includes(controlName);
        },
        
        /**
         * Highlight control temporarily
         */
        highlightControl: function($control) {
            $control.addClass('pgfe-control-changed');
            
            setTimeout(() => {
                $control.removeClass('pgfe-control-changed');
            }, 1000);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        PGFEEditor.init();
        WidgetPreview.init();
    });
    
    // Expose for external use
    window.PGFE = window.PGFE || {};
    window.PGFE.Editor = PGFEEditor;
    window.PGFE.WidgetPreview = WidgetPreview;
    
})(jQuery);

// Additional CSS for control highlighting
jQuery(document).ready(function($) {
    const highlightCSS = `
        .pgfe-control-changed {
            background: rgba(113, 215, 247, 0.1) !important;
            border-left: 3px solid #71d7f7 !important;
            transition: all 0.3s ease;
        }
        
        .elementor-control.pgfe-control-changed .elementor-control-title {
            color: #71d7f7 !important;
        }
    `;
    
    if (!$('#pgfe-control-highlight').length) {
        $('<style id="pgfe-control-highlight">' + highlightCSS + '</style>').appendTo('head');
    }
});