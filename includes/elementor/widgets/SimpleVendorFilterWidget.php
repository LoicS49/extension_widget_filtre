<?php
/**
 * Simple Vendor Filter Widget
 * Version simplifiée du widget de filtre par vendeur
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

class SimpleVendorFilterWidget extends BaseFilterWidget {
    
    /**
     * Implémentation des méthodes abstraites de BaseFilterWidget
     */
    protected function get_widget_name() {
        return 'pgfe-simple-vendor-filter';
    }
    
    protected function get_widget_title() {
        return __('Simple Vendor Filter', 'pgfe-lite');
    }
    
    protected function get_widget_icon() {
        return 'eicon-person';
    }
    
    public function get_categories() {
        return ['pgfe-lite'];
    }
    
    public function get_keywords() {
        return ['vendor', 'seller', 'filter', 'simple', 'woocommerce'];
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
                'default' => __('Filter by Vendor', 'pgfe-lite'),
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
                ],
            ]
        );
        
        $this->add_control(
            'show_count',
            [
                'label' => __('Show Product Count', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'limit',
            [
                'label' => __('Limit', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 50,
                'default' => 10,
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
        
        $this->end_controls_section();
    }
    
    protected function render_widget() {
        $settings = $this->get_settings_for_display();
        $vendors = $this->get_vendors($settings);
        
        if (empty($vendors)) {
            echo '<p>' . __('No vendors found.', 'pgfe-lite') . '</p>';
            return;
        }
        
        $widget_id = 'pgfe-vendor-filter-' . $this->get_id();
        
        // CSS intégré
        echo '<style>';
        echo "#{$widget_id} { background-color: {$settings['background_color']}; color: {$settings['text_color']}; padding: 15px; border-radius: 5px; }";
        echo "#{$widget_id} .pgfe-vendor-title { font-weight: bold; margin-bottom: 10px; }";
        echo "#{$widget_id} .pgfe-vendor-list { list-style: none; padding: 0; margin: 0; }";
        echo "#{$widget_id} .pgfe-vendor-item { margin-bottom: 8px; }";
        echo "#{$widget_id} .pgfe-vendor-label { display: flex; align-items: center; cursor: pointer; }";
        echo "#{$widget_id} .pgfe-vendor-count { margin-left: 5px; opacity: 0.7; font-size: 0.9em; }";
        echo "#{$widget_id} select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }";
        echo '</style>';
        
        echo '<div id="' . $widget_id . '" class="pgfe-simple-vendor-filter" data-filter-type="vendor">';
        
        // Titre
        if (!empty($settings['title'])) {
            echo '<div class="pgfe-vendor-title">' . esc_html($settings['title']) . '</div>';
        }
        
        // Rendu selon le type d'affichage
        if ($settings['display_type'] === 'dropdown') {
            $this->render_dropdown($vendors, $settings);
        } else {
            $this->render_checkbox_list($vendors, $settings);
        }
        
        echo '</div>';
        
        // JavaScript intégré
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo '  $("#' . $widget_id . ' input, #' . $widget_id . ' select").on("change", function() {';
        echo '    $(document).trigger("pgfe:filter:changed", {';
        echo '      type: "vendor",';
        echo '      value: $(this).val(),';
        echo '      checked: $(this).is(":checked")';
        echo '    });';
        echo '  });';
        echo '});';
        echo '</script>';
    }
    
    private function render_dropdown($vendors, $settings) {
        echo '<select name="vendor_filter" class="pgfe-vendor-dropdown">';
        echo '<option value="">' . __('All Vendors', 'pgfe-lite') . '</option>';
        
        foreach ($vendors as $vendor) {
            $count_text = $settings['show_count'] === 'yes' ? ' (' . $vendor['product_count'] . ')' : '';
            echo '<option value="' . esc_attr($vendor['id']) . '">';
            echo esc_html($vendor['name']) . $count_text;
            echo '</option>';
        }
        
        echo '</select>';
    }
    
    private function render_checkbox_list($vendors, $settings) {
        echo '<ul class="pgfe-vendor-list">';
        
        foreach ($vendors as $vendor) {
            echo '<li class="pgfe-vendor-item">';
            echo '<label class="pgfe-vendor-label">';
            echo '<input type="checkbox" name="vendor_filter[]" value="' . esc_attr($vendor['id']) . '" class="pgfe-vendor-checkbox">';
            echo '<span class="pgfe-vendor-name">' . esc_html($vendor['name']) . '</span>';
            
            if ($settings['show_count'] === 'yes') {
                echo '<span class="pgfe-vendor-count">(' . $vendor['product_count'] . ')</span>';
            }
            
            echo '</label>';
            echo '</li>';
        }
        
        echo '</ul>';
    }
    
    private function get_vendors($settings) {
        global $wpdb;
        
        // Requête simplifiée pour obtenir les vendeurs
        $sql = "SELECT DISTINCT p.post_author as id, u.display_name as name, COUNT(p.ID) as product_count 
                FROM {$wpdb->posts} p 
                JOIN {$wpdb->users} u ON p.post_author = u.ID 
                WHERE p.post_type = 'product' 
                AND p.post_status = 'publish' 
                GROUP BY p.post_author, u.display_name 
                HAVING product_count > 0 
                ORDER BY u.display_name ASC";
        
        if ($settings['limit'] > 0) {
            $sql .= " LIMIT " . intval($settings['limit']);
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        // Améliorer les noms des vendeurs avec les métadonnées
        foreach ($results as &$vendor) {
            $shop_name = $this->get_vendor_shop_name($vendor['id']);
            if (!empty($shop_name)) {
                $vendor['name'] = $shop_name;
            }
        }
        
        return $results;
    }
    
    private function get_vendor_shop_name($vendor_id) {
        // Essayer différentes sources de nom de boutique
        $shop_name = get_user_meta($vendor_id, 'dokan_store_name', true);
        
        if (empty($shop_name)) {
            $shop_name = get_user_meta($vendor_id, '_wcfm_store_name', true);
        }
        
        if (empty($shop_name)) {
            $shop_name = get_user_meta($vendor_id, 'pv_shop_name', true);
        }
        
        return $shop_name;
    }
}