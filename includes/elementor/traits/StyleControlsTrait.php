<?php
/**
 * Style Controls Trait - Contrôles de style centralisés pour les widgets Elementor
 * 
 * Ce trait centralise tous les contrôles de style communs utilisés par les widgets
 * Elementor du plugin PGFE Lite.
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

namespace PGFE_Lite\Elementor\Traits;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use PGFE_Lite\Core\StyleManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait StyleControlsTrait {
    
    /**
     * Enregistre les contrôles de style du conteneur
     */
    protected function register_container_style_controls($section_id = 'container_style', $control_prefix = 'container') {
        $this->start_controls_section(
            $section_id,
            [
                'label' => __('Conteneur', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Background
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $control_prefix . '_background',
                'label' => __('Arrière-plan', 'pgfe-lite'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .pgfe-widget-container',
            ]
        );
        
        // Border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $control_prefix . '_border',
                'label' => __('Bordure', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-widget-container',
            ]
        );
        
        // Border Radius
        $this->add_responsive_control(
            $control_prefix . '_border_radius',
            [
                'label' => __('Rayon de bordure', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-widget-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => $control_prefix . '_box_shadow',
                'label' => __('Ombre', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-widget-container',
            ]
        );
        
        // Padding
        $this->add_responsive_control(
            $control_prefix . '_padding',
            [
                'label' => __('Espacement interne', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-widget-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Margin
        $this->add_responsive_control(
            $control_prefix . '_margin',
            [
                'label' => __('Espacement externe', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-widget-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Enregistre les contrôles de style du titre avec préfixe personnalisable
     */
    protected function register_title_style_controls($section_id = 'widget_title_style', $condition = [], $control_prefix = 'widget_title') {
        $this->start_controls_section(
            $section_id,
            [
                'label' => __('Titre du Widget', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => $condition,
            ]
        );
        
        // Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $control_prefix . '_typography',
                'label' => __('Typographie', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-widget-title',
            ]
        );
        
        // Color
        $this->add_control(
            $control_prefix . '_color',
            [
                'label' => __('Couleur', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-widget-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        // Text Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => $control_prefix . '_text_shadow',
                'label' => __('Ombre du texte', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-widget-title',
            ]
        );
        
        // Margin
        $this->add_responsive_control(
            $control_prefix . '_margin',
            [
                'label' => __('Espacement externe', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-widget-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Padding
        $this->add_responsive_control(
            $control_prefix . '_padding',
            [
                'label' => __('Espacement interne', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-widget-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Enregistre les contrôles de style des éléments
     * 
     * @param string $section_id ID de la section
     * @param string $selector_suffix Suffixe du sélecteur CSS
     * @param string $control_prefix Préfixe pour les noms de contrôles
     */
    protected function register_items_style_controls($section_id = 'items_style', $selector_suffix = '.pgfe-item', $control_prefix = '') {
        $this->start_controls_section(
            $section_id,
            [
                'label' => __('Éléments', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $control_prefix . 'items_typography',
                'label' => __('Typographie', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} ' . $selector_suffix,
            ]
        );
        
        // Color
        $this->add_control(
            $control_prefix . 'items_color',
            [
                'label' => __('Couleur', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'color: {{VALUE}};',
                ],
            ]
        );
        
        // Hover Color
        $this->add_control(
            $control_prefix . 'items_hover_color',
            [
                'label' => __('Couleur au survol', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix . ':hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        // Background
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $control_prefix . 'items_background',
                'label' => __('Arrière-plan', 'pgfe-lite'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} ' . $selector_suffix,
            ]
        );
        
        // Hover Background
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $control_prefix . 'items_hover_background',
                'label' => __('Arrière-plan au survol', 'pgfe-lite'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} ' . $selector_suffix . ':hover',
            ]
        );
        
        // Border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $control_prefix . 'items_border',
                'label' => __('Bordure', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} ' . $selector_suffix,
            ]
        );
        
        // Border Radius
        $this->add_responsive_control(
            $control_prefix . 'items_border_radius',
            [
                'label' => __('Rayon de bordure', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Padding
        $this->add_responsive_control(
            $control_prefix . 'items_padding',
            [
                'label' => __('Espacement interne', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Margin
        $this->add_responsive_control(
            $control_prefix . 'items_margin',
            [
                'label' => __('Espacement externe', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Spacing between items
        $this->add_responsive_control(
            $control_prefix . 'items_spacing',
            [
                'label' => __('Espacement entre éléments', 'pgfe-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix . ':not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Enregistre les contrôles de style des boutons
     * 
     * @param string $section_id ID de la section
     * @param string $selector_suffix Suffixe du sélecteur CSS
     * @param string $control_prefix Préfixe pour les noms de contrôles
     */
    protected function register_button_style_controls($section_id = 'button_style', $selector_suffix = '.pgfe-btn', $control_prefix = '') {
        $this->start_controls_section(
            $section_id,
            [
                'label' => __('Boutons', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => $control_prefix . 'button_typography',
                'label' => __('Typographie', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} ' . $selector_suffix,
            ]
        );
        
        // Normal State
        $this->safe_start_controls_tabs($control_prefix . 'button_style_tabs');
        
        $this->safe_start_controls_tab(
            $control_prefix . 'button_normal',
            [
                'label' => __('Normal', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            $control_prefix . 'button_color',
            [
                'label' => __('Couleur du texte', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $control_prefix . 'button_background',
                'label' => __('Arrière-plan', 'pgfe-lite'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} ' . $selector_suffix,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => $control_prefix . 'button_border',
                'label' => __('Bordure', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} ' . $selector_suffix,
            ]
        );
        
        $this->end_controls_tab();
        
        // Hover State
        $this->safe_start_controls_tab(
            $control_prefix . 'button_hover',
            [
                'label' => __('Survol', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            $control_prefix . 'button_hover_color',
            [
                'label' => __('Couleur du texte', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix . ':hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => $control_prefix . 'button_hover_background',
                'label' => __('Arrière-plan', 'pgfe-lite'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} ' . $selector_suffix . ':hover',
            ]
        );
        
        $this->add_control(
            $control_prefix . 'button_hover_border_color',
            [
                'label' => __('Couleur de bordure', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix . ':hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            $control_prefix . 'button_hover_transition',
            [
                'label' => __('Durée de transition', 'pgfe-lite'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'transition: all {{SIZE}}s ease;',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        // Border Radius
        $this->add_responsive_control(
            $control_prefix . 'button_border_radius',
            [
                'label' => __('Rayon de bordure', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        // Padding
        $this->add_responsive_control(
            $control_prefix . 'button_padding',
            [
                'label' => __('Espacement interne', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} ' . $selector_suffix => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Box Shadow
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => $control_prefix . 'button_box_shadow',
                'label' => __('Ombre', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} ' . $selector_suffix,
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Enregistre les contrôles de style de la grille de produits
     * 
     * @param string $section_id ID de la section
     * @param string $control_prefix Préfixe pour les noms de contrôles
     */
    protected function register_grid_style_controls($section_id = 'grid_style', $control_prefix = '') {
        $this->start_controls_section(
            $section_id,
            [
                'label' => __('Grille', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Grid Gap
        $this->add_responsive_control(
            $control_prefix . 'grid_gap',
            [
                'label' => __('Espacement entre éléments', 'pgfe-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-simple-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        // Grid Columns
        $this->add_responsive_control(
            $control_prefix . 'grid_columns',
            [
                'label' => __('Nombre de colonnes', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-simple-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Génère les styles inline pour le widget
     */
    protected function generate_widget_styles($settings) {
        $style_manager = StyleManager::get_instance();
        $widget_id = $this->get_id();
        
        // Génère les styles Elementor
        $styles = $style_manager->generate_elementor_styles($widget_id, $settings);
        
        // Ajoute les styles inline
        if (!empty($styles)) {
            $css = $style_manager->array_to_css($styles);
            $style_manager->add_inline_style(
                "widget_{$widget_id}",
                $css,
                'elementor_widget'
            );
        }
    }
    
    /**
     * Ajoute des styles personnalisés pour un sélecteur spécifique
     */
    protected function add_custom_style($selector, $properties, $context = 'widget') {
        $style_manager = StyleManager::get_instance();
        $widget_id = $this->get_id();
        
        $full_selector = "#{$widget_id} {$selector}";
        $style_manager->add_inline_style($full_selector, $properties, $context);
    }
    
    /**
     * Enregistre tous les contrôles de style communs avec préfixes uniques
     */
    protected function register_common_style_controls($widget_prefix = 'pgfe') {
        $this->register_container_style_controls(
            $widget_prefix . '_container_style',
            $widget_prefix . '_container'
        );
        $this->register_title_style_controls(
            $widget_prefix . '_widget_title_style', 
            ['show_title' => 'yes'], 
            $widget_prefix . '_widget_title'
        );
        // Note: register_items_style_controls() is called individually by each widget with specific parameters
        // to avoid control name conflicts
    }
    
    /**
     * Applique les styles au rendu du widget
     */
    protected function apply_widget_styles() {
        $settings = $this->get_settings_for_display();
        $this->generate_widget_styles($settings);
    }
}