<?php
/**
 * Simple Attribute Filter Widget
 * Version simplifiée du widget de filtre par attributs
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

namespace PGFE_Lite\Elementor\Widgets;

use Elementor\Controls_Manager;
use PGFE_Lite\Elementor\Base\BaseFilterWidget;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SimpleAttributeFilterWidget extends BaseFilterWidget {
    
    /**
     * Implémentation des méthodes abstraites de BaseFilterWidget
     */
    protected function get_widget_name() {
        return 'pgfe-simple-attribute-filter';
    }
    
    protected function get_widget_title() {
        return __('Simple Attribute Filter', 'pgfe-lite');
    }
    
    protected function get_widget_icon() {
        return 'eicon-filter';
    }
    
    public function get_categories() {
        return ['pgfe-lite'];
    }
    
    public function get_keywords() {
        return ['attribute', 'filter', 'simple', 'woocommerce', 'color', 'size'];
    }
    
    protected function register_widget_controls() {
        // Section de contenu principal
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'title',
            [
                'label' => __('Title', 'pgfe-lite'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Filter by Attributes', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'attribute',
            [
                'label' => __('Attribute', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'options' => $this->get_attribute_options(),
                'default' => '',
                'description' => __('Select the product attribute to filter by.', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'display_type',
            [
                'label' => __('Display Type', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'checkbox',
                'options' => [
                    'checkbox' => __('Checkbox List', 'pgfe-lite'),
                    'dropdown' => __('Dropdown', 'pgfe-lite'),
                    'color_swatches' => __('Color Swatches', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'show_count',
            [
                'label' => __('Show Product Count', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
                'condition' => [
                    'display_type!' => 'color_swatches',
                ],
            ]
        );
        
        $this->add_control(
            'limit',
            [
                'label' => __('Limit', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 50,
                'default' => 20,
            ]
        );
        
        $this->end_controls_section();
        
        // Section de style
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
            ]
        );
        
        $this->add_control(
            'background_color',
            [
                'label' => __('Background Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
            ]
        );
        
        $this->add_control(
            'border_color',
            [
                'label' => __('Border Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#dddddd',
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render_widget() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['attribute'])) {
            echo '<div class="pgfe-no-attribute">' . __('Please select an attribute.', 'pgfe-lite') . '</div>';
            return;
        }
        
        $terms = $this->get_attribute_terms($settings);
        
        if (empty($terms)) {
            echo '<div class="pgfe-no-terms">' . __('No terms found for this attribute.', 'pgfe-lite') . '</div>';
            return;
        }
        
        $widget_id = 'pgfe-attribute-filter-' . $this->get_id();
        
        // CSS intégré
        echo '<style>';
        echo "#{$widget_id} { background-color: {$settings['background_color']}; color: {$settings['text_color']}; padding: 15px; border: 1px solid {$settings['border_color']}; border-radius: 5px; }";
        echo "#{$widget_id} .pgfe-attribute-title { font-weight: bold; margin-bottom: 10px; }";
        echo "#{$widget_id} .pgfe-attribute-list { list-style: none; padding: 0; margin: 0; }";
        echo "#{$widget_id} .pgfe-attribute-item { margin-bottom: 8px; }";
        echo "#{$widget_id} .pgfe-attribute-label { display: flex; align-items: center; cursor: pointer; }";
        echo "#{$widget_id} .pgfe-attribute-count { margin-left: 5px; opacity: 0.7; font-size: 0.9em; }";
        echo "#{$widget_id} select { width: 100%; padding: 8px; border: 1px solid {$settings['border_color']}; border-radius: 3px; }";
        echo "#{$widget_id} .pgfe-color-swatches { display: flex; flex-wrap: wrap; gap: 8px; }";
        echo "#{$widget_id} .pgfe-color-swatch { width: 30px; height: 30px; border: 2px solid {$settings['border_color']}; border-radius: 50%; cursor: pointer; position: relative; }";
        echo "#{$widget_id} .pgfe-color-swatch:hover { transform: scale(1.1); }";
        echo "#{$widget_id} .pgfe-color-swatch.selected::after { content: '✓'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; text-shadow: 1px 1px 1px rgba(0,0,0,0.5); }";
        echo '</style>';
        
        echo '<div id="' . $widget_id . '" class="pgfe-simple-attribute-filter" data-filter-type="attribute" data-attribute="' . esc_attr($settings['attribute']) . '">';
        
        // Titre
        if (!empty($settings['title'])) {
            echo '<div class="pgfe-attribute-title">' . esc_html($settings['title']) . '</div>';
        }
        
        // Rendu selon le type d'affichage
        switch ($settings['display_type']) {
            case 'dropdown':
                $this->render_dropdown($terms, $settings);
                break;
            case 'color_swatches':
                $this->render_color_swatches($terms, $settings);
                break;
            default:
                $this->render_checkbox_list($terms, $settings);
                break;
        }
        
        echo '</div>';
        
        // JavaScript intégré
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo '  $("#' . $widget_id . ' input, #' . $widget_id . ' select").on("change", function() {';
        echo '    var values = [];';
        echo '    $("#' . $widget_id . ' input:checked, #' . $widget_id . ' select option:selected").each(function() {';
        echo '      if ($(this).val()) values.push($(this).val());';
        echo '    });';
        echo '    $(document).trigger("pgfe:filter:changed", {';
        echo '      type: "attribute",';
        echo '      attribute: "' . esc_js($settings['attribute']) . '",';
        echo '      values: values';
        echo '    });';
        echo '  });';
        echo '  $("#' . $widget_id . ' .pgfe-color-swatch").on("click", function() {';
        echo '    $(this).toggleClass("selected");';
        echo '    var values = [];';
        echo '    $("#' . $widget_id . ' .pgfe-color-swatch.selected").each(function() {';
        echo '      values.push($(this).data("term-slug"));';
        echo '    });';
        echo '    $(document).trigger("pgfe:filter:changed", {';
        echo '      type: "attribute",';
        echo '      attribute: "' . esc_js($settings['attribute']) . '",';
        echo '      values: values';
        echo '    });';
        echo '  });';
        echo '});';
        echo '</script>';
    }
    
    private function render_dropdown($terms, $settings) {
        echo '<select name="attribute_' . esc_attr($settings['attribute']) . '" class="pgfe-attribute-dropdown" multiple>';
        
        foreach ($terms as $term) {
            $count_text = $settings['show_count'] === 'yes' ? ' (' . $term->count . ')' : '';
            echo '<option value="' . esc_attr($term->slug) . '">';
            echo esc_html($term->name . $count_text);
            echo '</option>';
        }
        
        echo '</select>';
    }
    
    private function render_checkbox_list($terms, $settings) {
        echo '<ul class="pgfe-attribute-list">';
        
        foreach ($terms as $term) {
            echo '<li class="pgfe-attribute-item">';
            echo '<label class="pgfe-attribute-label">';
            echo '<input type="checkbox" name="attribute_' . esc_attr($settings['attribute']) . '[]" value="' . esc_attr($term->slug) . '" class="pgfe-attribute-checkbox">';
            echo '<span class="pgfe-attribute-name">' . esc_html($term->name) . '</span>';
            
            if ($settings['show_count'] === 'yes') {
                echo '<span class="pgfe-attribute-count">(' . $term->count . ')</span>';
            }
            
            echo '</label>';
            echo '</li>';
        }
        
        echo '</ul>';
    }
    
    private function render_color_swatches($terms, $settings) {
        echo '<div class="pgfe-color-swatches">';
        
        foreach ($terms as $term) {
            $color = $this->get_term_color($term);
            
            echo '<button type="button" class="pgfe-color-swatch" ';
            echo 'data-term-slug="' . esc_attr($term->slug) . '" ';
            echo 'style="background-color: ' . esc_attr($color) . ';" ';
            echo 'title="' . esc_attr($term->name) . '">';
            echo '</button>';
        }
        
        echo '</div>';
    }
    
    private function get_attribute_terms($settings) {
        $args = [
            'taxonomy' => 'pa_' . $settings['attribute'],
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => true,
        ];
        
        if ($settings['limit'] > 0) {
            $args['number'] = $settings['limit'];
        }
        
        $terms = get_terms($args);
        
        if (is_wp_error($terms)) {
            return [];
        }
        
        return $terms;
    }
    
    private function get_term_color($term) {
        // Essayer d'obtenir la couleur depuis les métadonnées du terme
        $color = get_term_meta($term->term_id, 'color', true);
        
        if (empty($color)) {
            // Générer une couleur basée sur le nom du terme
            $color = $this->generate_color_from_name($term->name);
        }
        
        return $color;
    }
    
    private function generate_color_from_name($name) {
        // Générer une couleur basée sur le nom du terme
        $hash = md5($name);
        $color = '#' . substr($hash, 0, 6);
        
        // S'assurer que la couleur n'est pas trop claire
        $rgb = sscanf($color, "#%02x%02x%02x");
        $brightness = ($rgb[0] * 299 + $rgb[1] * 587 + $rgb[2] * 114) / 1000;
        
        if ($brightness > 200) {
            // Assombrir la couleur
            $rgb[0] = max(0, $rgb[0] - 80);
            $rgb[1] = max(0, $rgb[1] - 80);
            $rgb[2] = max(0, $rgb[2] - 80);
            $color = sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
        }
        
        return $color;
    }
    
    private function get_attribute_options() {
        $options = [];
        
        if (function_exists('wc_get_attribute_taxonomies')) {
            $attributes = wc_get_attribute_taxonomies();
            
            foreach ($attributes as $attribute) {
                $options[$attribute->attribute_name] = $attribute->attribute_label;
            }
        }
        
        return $options;
    }
}