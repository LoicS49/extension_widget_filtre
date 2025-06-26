<?php
/**
 * Tag Filter Widget for PGFE Lite
 * 
 * @package PGFE_Lite\Elementor\Widgets
 * @since 1.0.0
 */

namespace PGFE_Lite\Elementor\Widgets;

use PGFE_Lite\Elementor\Base\BaseFilterWidget;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}

class TagFilterWidget extends BaseFilterWidget {
    
    /**
     * Get widget name
     */
    protected function get_widget_name() {
        return 'pgfe-tag-filter';
    }
    
    /**
     * Get widget title
     */
    protected function get_widget_title() {
        return __('Filtre par Tags', 'pgfe-lite');
    }
    
    /**
     * Get widget icon
     */
    protected function get_widget_icon() {
        return 'eicon-tags';
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
        return ['tag', 'filter', 'product', 'woocommerce', 'pgfe'];
    }
    

    
    /**
     * Register widget controls (required by BaseFilterWidget)
     */
    protected function register_widget_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Contenu', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'title',
            [
                'label' => __('Titre', 'pgfe-lite'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Filtrer par Tags', 'pgfe-lite'),
                'placeholder' => __('Entrez le titre', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'display_type',
            [
                'label' => __('Type d\'affichage', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'checkbox',
                'options' => [
                    'checkbox' => __('Cases à cocher', 'pgfe-lite'),
                    'dropdown' => __('Liste déroulante', 'pgfe-lite'),
                    'pills' => __('Pilules', 'pgfe-lite'),
                    'buttons' => __('Boutons', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'multi_select',
            [
                'label' => __('Sélection multiple', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'pgfe-lite'),
                'label_off' => __('Non', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'display_type!' => 'dropdown',
                ],
            ]
        );
        
        $this->add_control(
            'show_count',
            [
                'label' => __('Afficher le nombre de produits', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'pgfe-lite'),
                'label_off' => __('Non', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'orderby',
            [
                'label' => __('Trier par', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name' => __('Nom', 'pgfe-lite'),
                    'count' => __('Nombre de produits', 'pgfe-lite'),
                    'term_order' => __('Ordre des termes', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'order',
            [
                'label' => __('Ordre', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC' => __('Croissant', 'pgfe-lite'),
                    'DESC' => __('Décroissant', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'limit',
            [
                'label' => __('Limite', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'min' => -1,
                'max' => 100,
                'step' => 1,
                'default' => -1,
                'description' => __('Nombre maximum de tags à afficher (-1 pour tous)', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'include_tags',
            [
                'label' => __('Inclure les tags', 'pgfe-lite'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => __('IDs des tags séparés par des virgules', 'pgfe-lite'),
                'description' => __('Laissez vide pour inclure tous les tags', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'exclude_tags',
            [
                'label' => __('Exclure les tags', 'pgfe-lite'),
                'type' => Controls_Manager::TEXT,
                'placeholder' => __('IDs des tags séparés par des virgules', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'hide_empty',
            [
                'label' => __('Masquer les tags vides', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'pgfe-lite'),
                'label_off' => __('Non', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Controls
        $this->register_style_controls();
    }
    
    /**
     * Register style controls
     */
    private function register_style_controls() {
        // Title Style
        $this->start_controls_section(
            'tag_title_style',
            [
                'label' => __('Style du titre', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tag_title_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .pgfe-tag-filter-title',
            ]
        );
        
        $this->add_control(
            'tag_title_color',
            [
                'label' => __('Couleur', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_PRIMARY,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-tag-filter-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'tag_title_margin',
            [
                'label' => __('Marge', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-tag-filter-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Use trait methods with prefixes to avoid conflicts
        $this->register_container_style_controls('tag_container_style');
        $this->register_items_style_controls('tag_items_style', '.pgfe-tag-item', 'tag_');
        $this->register_button_style_controls('tag_button_style', '.pgfe-tag-btn', 'tag_');
        

        
        // Count Style
        $this->start_controls_section(
            'tag_count_style',
            [
                'label' => __('Style du compteur', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'tag_count_typography',
                'selector' => '{{WRAPPER}} .pgfe-tag-count',
            ]
        );
        
        $this->add_control(
            'tag_count_color',
            [
                'label' => __('Couleur', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-tag-count' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'tag_count_background',
            [
                'label' => __('Couleur de fond', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-tag-count' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'tag_count_border_radius',
            [
                'label' => __('Rayon de bordure', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-tag-count' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output
     */
    protected function render_widget() {
        $settings = $this->get_settings_for_display();
        
        // Get product tags
        $tags = $this->get_product_tags($settings);
        
        if (empty($tags)) {
            echo '<div class="pgfe-no-tags">' . __('Aucun tag trouvé.', 'pgfe-lite') . '</div>';
            return;
        }
        
        $widget_id = 'pgfe-tag-filter-' . $this->get_id();
        
        echo '<div class="pgfe-tag-filter" id="' . esc_attr($widget_id) . '">';
        
        // Title
        if (!empty($settings['title'])) {
            echo '<h3 class="pgfe-tag-filter-title">' . esc_html($settings['title']) . '</h3>';
        }
        
        // Filter content
        $this->render_filter_content($tags, $settings);
        
        echo '</div>';
    }
    
    /**
     * Get product tags
     */
    private function get_product_tags($settings) {
        $args = [
            'taxonomy' => 'product_tag',
            'hide_empty' => $settings['hide_empty'] === 'yes',
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
        ];
        
        if ($settings['limit'] > 0) {
            $args['number'] = $settings['limit'];
        }
        
        if (!empty($settings['include_tags'])) {
            $include = array_map('trim', explode(',', $settings['include_tags']));
            $args['include'] = array_filter($include, 'is_numeric');
        }
        
        if (!empty($settings['exclude_tags'])) {
            $exclude = array_map('trim', explode(',', $settings['exclude_tags']));
            $args['exclude'] = array_filter($exclude, 'is_numeric');
        }
        
        return get_terms($args);
    }
    
    /**
     * Render filter content
     */
    private function render_filter_content($tags, $settings) {
        $display_type = $settings['display_type'];
        $show_count = $settings['show_count'] === 'yes';
        $multi_select = $settings['multi_select'] === 'yes';
        
        switch ($display_type) {
            case 'dropdown':
                $this->render_dropdown($tags, $show_count);
                break;
            case 'pills':
                $this->render_pills($tags, $show_count, $multi_select);
                break;
            case 'buttons':
                $this->render_buttons($tags, $show_count, $multi_select);
                break;
            default:
                $this->render_checkboxes($tags, $show_count, $multi_select);
                break;
        }
    }
    
    /**
     * Render checkboxes
     */
    private function render_checkboxes($tags, $show_count, $multi_select) {
        echo '<div class="pgfe-tag-checkboxes">';
        foreach ($tags as $tag) {
            $input_type = $multi_select ? 'checkbox' : 'radio';
            $name = $multi_select ? 'pgfe_tags[]' : 'pgfe_tags';
            
            echo '<label class="pgfe-tag-item">';
            echo '<input type="' . $input_type . '" name="' . $name . '" value="' . esc_attr($tag->term_id) . '" data-tag-slug="' . esc_attr($tag->slug) . '">';
            echo '<span class="pgfe-tag-label">' . esc_html($tag->name);
            if ($show_count) {
                echo ' <span class="pgfe-tag-count">(' . $tag->count . ')</span>';
            }
            echo '</span>';
            echo '</label>';
        }
        echo '</div>';
    }
    
    /**
     * Render dropdown
     */
    private function render_dropdown($tags, $show_count) {
        echo '<select class="pgfe-tag-dropdown" name="pgfe_tags">';
        echo '<option value="">' . __('Sélectionner un tag', 'pgfe-lite') . '</option>';
        foreach ($tags as $tag) {
            echo '<option value="' . esc_attr($tag->term_id) . '" data-tag-slug="' . esc_attr($tag->slug) . '">';
            echo esc_html($tag->name);
            if ($show_count) {
                echo ' (' . $tag->count . ')';
            }
            echo '</option>';
        }
        echo '</select>';
    }
    
    /**
     * Render pills
     */
    private function render_pills($tags, $show_count, $multi_select) {
        echo '<div class="pgfe-tag-pills">';
        foreach ($tags as $tag) {
            echo '<span class="pgfe-tag-pill" data-tag-id="' . esc_attr($tag->term_id) . '" data-tag-slug="' . esc_attr($tag->slug) . '" data-multi="' . ($multi_select ? '1' : '0') . '">';
            echo esc_html($tag->name);
            if ($show_count) {
                echo ' <span class="pgfe-tag-count">(' . $tag->count . ')</span>';
            }
            echo '</span>';
        }
        echo '</div>';
    }
    
    /**
     * Render buttons
     */
    private function render_buttons($tags, $show_count, $multi_select) {
        echo '<div class="pgfe-tag-buttons">';
        foreach ($tags as $tag) {
            echo '<button type="button" class="pgfe-tag-button" data-tag-id="' . esc_attr($tag->term_id) . '" data-tag-slug="' . esc_attr($tag->slug) . '" data-multi="' . ($multi_select ? '1' : '0') . '">';
            echo esc_html($tag->name);
            if ($show_count) {
                echo ' <span class="pgfe-tag-count">(' . $tag->count . ')</span>';
            }
            echo '</button>';
        }
        echo '</div>';
    }
}