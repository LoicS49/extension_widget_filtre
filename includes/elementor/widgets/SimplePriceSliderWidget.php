<?php
/**
 * Simple Price Slider Widget - Version simplifiée
 * 
 * @package PGFE_Lite\Elementor\Widgets
 * @since 2.0.0
 */

namespace PGFE_Lite\Elementor\Widgets;

use Elementor\Controls_Manager;
use PGFE_Lite\Elementor\Base\BaseFilterWidget;

if (!defined('ABSPATH')) {
    exit;
}

class SimplePriceSliderWidget extends BaseFilterWidget {
    
    /**
     * Get widget name
     */
    protected function get_widget_name() {
        return 'llda-simple-price-slider';
    }
    
    /**
     * Get widget title
     */
    protected function get_widget_title() {
        return __('Price Slider (Simple)', 'pgfe-lite');
    }
    
    /**
     * Get widget icon
     */
    protected function get_widget_icon() {
        return 'eicon-price-list';
    }
    
    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['pgfe-lite'];
    }
    
    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['price', 'slider', 'filter', 'simple', 'woocommerce'];
    }
    
    /**
     * Register widget controls - Version simplifiée
     */
    protected function register_widget_controls() {
        
        // Section Contenu Simplifiée
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Configuration', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'title',
            [
                'label' => __('Titre', 'pgfe-lite'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Prix', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'min_price',
            [
                'label' => __('Prix minimum', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
            ]
        );
        
        $this->add_control(
            'max_price',
            [
                'label' => __('Prix maximum', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'default' => 1000,
                'min' => 1,
            ]
        );
        
        $this->add_control(
            'step',
            [
                'label' => __('Pas', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'default' => 10,
                'min' => 1,
            ]
        );
        
        $this->add_control(
            'show_inputs',
            [
                'label' => __('Afficher les champs numériques', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'pgfe-lite'),
                'label_off' => __('Non', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style Simplifiée
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'slider_color',
            [
                'label' => __('Couleur du slider', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-simple-price-slider input[type="range"]::-webkit-slider-thumb' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pgfe-simple-price-slider input[type="range"]::-moz-range-thumb' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'track_color',
            [
                'label' => __('Couleur de la piste', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#e0e0e0',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-simple-price-slider input[type="range"]::-webkit-slider-track' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pgfe-simple-price-slider input[type="range"]::-moz-range-track' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output - Version simplifiée
     */
    protected function render_widget() {
        $settings = $this->get_settings_for_display();
        
        // Valeurs par défaut ou depuis l'URL
        $min_price = isset($_GET['min_price']) ? (int) $_GET['min_price'] : $settings['min_price'];
        $max_price = isset($_GET['max_price']) ? (int) $_GET['max_price'] : $settings['max_price'];
        
        // S'assurer que les valeurs sont dans la plage
        $min_price = max($settings['min_price'], min($min_price, $settings['max_price']));
        $max_price = max($settings['min_price'], min($max_price, $settings['max_price']));
        
        $widget_id = 'simple-price-slider-' . $this->get_id();
        
        ?>
        <div class="pgfe-filter-widget pgfe-price-slider-widget pgfe-simple-price-slider" 
             data-filter-type="price" id="<?php echo esc_attr($widget_id); ?>">
            
            <?php if (!empty($settings['title'])) : ?>
                <h3 class="pgfe-widget-title"><?php echo esc_html($settings['title']); ?></h3>
            <?php endif; ?>
            
            <div class="pgfe-price-range-container">
                
                <!-- Sliders HTML5 natifs -->
                <div class="pgfe-dual-slider">
                    <input type="range" 
                           class="pgfe-price-min" 
                           data-type="min"
                           min="<?php echo esc_attr($settings['min_price']); ?>"
                           max="<?php echo esc_attr($settings['max_price']); ?>"
                           step="<?php echo esc_attr($settings['step']); ?>"
                           value="<?php echo esc_attr($min_price); ?>">
                    
                    <input type="range" 
                           class="pgfe-price-max" 
                           data-type="max"
                           min="<?php echo esc_attr($settings['min_price']); ?>"
                           max="<?php echo esc_attr($settings['max_price']); ?>"
                           step="<?php echo esc_attr($settings['step']); ?>"
                           value="<?php echo esc_attr($max_price); ?>">
                </div>
                
                <!-- Affichage des valeurs courantes -->
                <div class="pgfe-price-display">
                    <span class="pgfe-current-min"><?php echo wc_price($min_price); ?></span>
                    <span class="pgfe-price-separator"> - </span>
                    <span class="pgfe-current-max"><?php echo wc_price($max_price); ?></span>
                </div>
                
                <?php if ($settings['show_inputs'] === 'yes') : ?>
                <!-- Champs numériques optionnels -->
                <div class="pgfe-price-inputs">
                    <div class="pgfe-input-group">
                        <label><?php _e('Min:', 'pgfe-lite'); ?></label>
                        <input type="number" 
                               class="pgfe-price-input-min" 
                               min="<?php echo esc_attr($settings['min_price']); ?>"
                               max="<?php echo esc_attr($settings['max_price']); ?>"
                               step="<?php echo esc_attr($settings['step']); ?>"
                               value="<?php echo esc_attr($min_price); ?>">
                    </div>
                    <div class="pgfe-input-group">
                        <label><?php _e('Max:', 'pgfe-lite'); ?></label>
                        <input type="number" 
                               class="pgfe-price-input-max" 
                               min="<?php echo esc_attr($settings['min_price']); ?>"
                               max="<?php echo esc_attr($settings['max_price']); ?>"
                               step="<?php echo esc_attr($settings['step']); ?>"
                               value="<?php echo esc_attr($max_price); ?>">
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Labels des limites -->
                <div class="pgfe-price-limits">
                    <span class="pgfe-limit-min"><?php echo wc_price($settings['min_price']); ?></span>
                    <span class="pgfe-limit-max"><?php echo wc_price($settings['max_price']); ?></span>
                </div>
                
            </div>
        </div>
        
        <style>
        /* Styles CSS intégrés pour simplifier */
        .pgfe-simple-price-slider {
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background: #fff;
        }
        
        .pgfe-widget-title {
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .pgfe-dual-slider {
            position: relative;
            margin: 20px 0;
        }
        
        .pgfe-dual-slider input[type="range"] {
            position: absolute;
            width: 100%;
            height: 5px;
            background: transparent;
            outline: none;
            -webkit-appearance: none;
        }
        
        .pgfe-dual-slider input[type="range"]::-webkit-slider-track {
            height: 5px;
            background: #e0e0e0;
            border-radius: 3px;
        }
        
        .pgfe-dual-slider input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            background: #007cba;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .pgfe-dual-slider input[type="range"]::-moz-range-track {
            height: 5px;
            background: #e0e0e0;
            border-radius: 3px;
            border: none;
        }
        
        .pgfe-dual-slider input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background: #007cba;
            border-radius: 50%;
            cursor: pointer;
            border: none;
        }
        
        .pgfe-price-display {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            font-size: 16px;
        }
        
        .pgfe-price-inputs {
            display: flex;
            gap: 15px;
            margin: 15px 0;
        }
        
        .pgfe-input-group {
            flex: 1;
        }
        
        .pgfe-input-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .pgfe-input-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .pgfe-price-limits {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        </style>
        
        <script>
        // JavaScript intégré pour simplifier
        (function($) {
            $(document).ready(function() {
                const widget = $('#<?php echo esc_js($widget_id); ?>');
                const minSlider = widget.find('.pgfe-price-min');
                const maxSlider = widget.find('.pgfe-price-max');
                const minInput = widget.find('.pgfe-price-input-min');
                const maxInput = widget.find('.pgfe-price-input-max');
                const minDisplay = widget.find('.pgfe-current-min');
                const maxDisplay = widget.find('.pgfe-current-max');
                
                // Fonction de mise à jour des affichages
                function updateDisplay() {
                    const minVal = parseInt(minSlider.val());
                    const maxVal = parseInt(maxSlider.val());
                    
                    // S'assurer que min <= max
                    if (minVal >= maxVal) {
                        minSlider.val(maxVal - <?php echo $settings['step']; ?>);
                    }
                    if (maxVal <= minVal) {
                        maxSlider.val(minVal + <?php echo $settings['step']; ?>);
                    }
                    
                    const finalMin = parseInt(minSlider.val());
                    const finalMax = parseInt(maxSlider.val());
                    
                    // Mettre à jour les affichages
                    minDisplay.text(finalMin + '<?php echo get_woocommerce_currency_symbol(); ?>');
                    maxDisplay.text(finalMax + '<?php echo get_woocommerce_currency_symbol(); ?>');
                    
                    if (minInput.length) minInput.val(finalMin);
                    if (maxInput.length) maxInput.val(finalMax);
                }
                
                // Événements des sliders
                minSlider.on('input change', updateDisplay);
                maxSlider.on('input change', updateDisplay);
                
                // Événements des inputs numériques
                if (minInput.length) {
                    minInput.on('change', function() {
                        minSlider.val($(this).val()).trigger('change');
                    });
                }
                
                if (maxInput.length) {
                    maxInput.on('change', function() {
                        maxSlider.val($(this).val()).trigger('change');
                    });
                }
                
                // Initialisation
                updateDisplay();
            });
        })(jQuery);
        </script>
        <?php
    }
}