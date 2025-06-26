<?php
/**
 * Archive Category Filter Widget
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

namespace PGFE_Lite\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Schemes\Color;
use PGFE_Lite\Elementor\Base\BaseFilterWidget;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ArchiveCategoryFilterWidget extends BaseFilterWidget {
    
    protected function get_widget_name() {
        return 'pgfe-archive-category-filter';
    }
    
    protected function get_widget_title() {
        return __('Archive Category Filter', 'pgfe-lite');
    }
    
    protected function get_widget_icon() {
        return 'eicon-archive-posts';
    }
    
    public function get_categories() {
        return ['pgfe-widgets'];
    }
    
    public function get_keywords() {
        return ['archive', 'category', 'filter', 'woocommerce', 'products'];
    }
    
    protected function register_widget_controls() {
        
        // Content Section
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
                'default' => __('Filter by Category', 'pgfe-lite'),
                'placeholder' => __('Enter title', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'pgfe-lite'),
                'label_off' => __('Hide', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'taxonomy',
            [
                'label' => __('Taxonomy', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'product_cat',
                'options' => [
                    'product_cat' => __('Product Categories', 'pgfe-lite'),
                    'product_tag' => __('Product Tags', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'display_type',
            [
                'label' => __('Display Type', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'list',
                'options' => [
                    'list' => __('List', 'pgfe-lite'),
                    'dropdown' => __('Dropdown', 'pgfe-lite'),
                    'pills' => __('Pills', 'pgfe-lite'),
                    'grid' => __('Grid', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'show_hierarchy',
            [
                'label' => __('Show Hierarchy', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'pgfe-lite'),
                'label_off' => __('No', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'display_type' => ['list', 'grid'],
                ],
            ]
        );
        
        $this->add_control(
            'show_count',
            [
                'label' => __('Show Product Count', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'pgfe-lite'),
                'label_off' => __('Hide', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'hide_empty',
            [
                'label' => __('Hide Empty Categories', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Hide', 'pgfe-lite'),
                'label_off' => __('Show', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name' => __('Name', 'pgfe-lite'),
                    'count' => __('Product Count', 'pgfe-lite'),
                    'term_order' => __('Term Order', 'pgfe-lite'),
                    'menu_order' => __('Menu Order', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'order',
            [
                'label' => __('Order', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC' => __('Ascending', 'pgfe-lite'),
                    'DESC' => __('Descending', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'limit',
            [
                'label' => __('Limit', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'min' => -1,
                'max' => 100,
                'step' => 1,
                'default' => -1,
                'description' => __('Number of categories to show. -1 for all.', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'pgfe-lite'),
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
                'condition' => [
                    'display_type' => 'grid',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Filter Section
        $this->start_controls_section(
            'filter_section',
            [
                'label' => __('Filter Options', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'include_ids',
            [
                'label' => __('Include Categories', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_category_options(),
                'description' => __('Select specific categories to include. Leave empty to include all.', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'exclude_ids',
            [
                'label' => __('Exclude Categories', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->get_category_options(),
                'description' => __('Select categories to exclude.', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'parent_only',
            [
                'label' => __('Parent Categories Only', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'pgfe-lite'),
                'label_off' => __('No', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->end_controls_section();
        
        // Title Style
        $this->start_controls_section(
            'archive_title_style',
            [
                'label' => __('Title', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'archive_title_typography',
                'selector' => '{{WRAPPER}} .pgfe-archive-filter-title',
            ]
        );
        
        $this->add_control(
            'archive_title_color',
            [
                'label' => __('Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-archive-filter-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'archive_title_margin',
            [
                'label' => __('Margin', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-archive-filter-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Container Style
        $this->start_controls_section(
            'container_style',
            [
                'label' => __('Container', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'container_background',
            [
                'label' => __('Background Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-archive-category-filter' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'selector' => '{{WRAPPER}} .pgfe-archive-category-filter',
            ]
        );
        
        $this->add_responsive_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-archive-category-filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-archive-category-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'selector' => '{{WRAPPER}} .pgfe-archive-category-filter',
            ]
        );
        
        $this->end_controls_section();
        
        // Items Style
        $this->start_controls_section(
            'items_style',
            [
                'label' => __('Items', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'items_typography',
                'selector' => '{{WRAPPER}} .pgfe-category-item, {{WRAPPER}} .pgfe-category-pill, {{WRAPPER}} .pgfe-archive-dropdown',
            ]
        );
        
        $this->safe_start_controls_tabs('items_style_tabs');

        $this->safe_start_controls_tab(
            'items_normal_tab',
            [
                'label' => esc_html__('Normal', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'items_color',
            [
                'label' => __('Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-item a, {{WRAPPER}} .pgfe-category-pill, {{WRAPPER}} .pgfe-archive-dropdown' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'items_background',
            [
                'label' => __('Background Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-item a, {{WRAPPER}} .pgfe-category-pill, {{WRAPPER}} .pgfe-archive-dropdown' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->safe_start_controls_tab(
            'items_hover_tab',
            [
                'label' => esc_html__('Hover', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'items_hover_color',
            [
                'label' => __('Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-item a:hover, {{WRAPPER}} .pgfe-category-pill:hover, {{WRAPPER}} .pgfe-category-pill.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'items_hover_background',
            [
                'label' => __('Background Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-item a:hover, {{WRAPPER}} .pgfe-category-pill:hover, {{WRAPPER}} .pgfe-category-pill.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'items_border',
                'selector' => '{{WRAPPER}} .pgfe-category-item a, {{WRAPPER}} .pgfe-category-pill, {{WRAPPER}} .pgfe-archive-dropdown',
            ]
        );
        
        $this->add_responsive_control(
            'items_border_radius',
            [
                'label' => __('Border Radius', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-item a, {{WRAPPER}} .pgfe-category-pill, {{WRAPPER}} .pgfe-archive-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'items_padding',
            [
                'label' => __('Padding', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-item a, {{WRAPPER}} .pgfe-category-pill, {{WRAPPER}} .pgfe-archive-dropdown' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'items_margin',
            [
                'label' => __('Margin', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-item, {{WRAPPER}} .pgfe-category-pill' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'items_gap',
            [
                'label' => __('Gap', 'pgfe-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-list' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .pgfe-category-pills' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .pgfe-category-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Count Style
        $this->start_controls_section(
            'count_style',
            [
                'label' => __('Count', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'count_typography',
                'selector' => '{{WRAPPER}} .pgfe-category-count',
            ]
        );
        
        $this->add_control(
            'count_color',
            [
                'label' => __('Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-count' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'count_background',
            [
                'label' => __('Background Color', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-count' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'count_padding',
            [
                'label' => __('Padding', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-count' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'count_border_radius',
            [
                'label' => __('Border Radius', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-category-count' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render_widget() {
        $settings = $this->get_settings_for_display();
        
        // Get categories
        $args = [
            'taxonomy' => $settings['taxonomy'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'hide_empty' => $settings['hide_empty'] === 'yes',
            'number' => $settings['limit'] > 0 ? $settings['limit'] : '',
        ];
        
        if (!empty($settings['include_ids'])) {
            $args['include'] = $settings['include_ids'];
        }
        
        if (!empty($settings['exclude_ids'])) {
            $args['exclude'] = $settings['exclude_ids'];
        }
        
        if ($settings['parent_only'] === 'yes') {
            $args['parent'] = 0;
        }
        
        $categories = get_terms($args);
        
        if (is_wp_error($categories) || empty($categories)) {
            echo '<div class="pgfe-no-categories">No categories found.</div>';
            return;
        }
        
        $widget_class = 'pgfe-archive-category-filter pgfe-archive-filter-' . $settings['display_type'];
        if ($settings['display_type'] === 'grid') {
            $widget_class .= ' pgfe-grid-columns-' . $settings['columns'];
        }
        
        echo '<div class="' . esc_attr($widget_class) . '">';
        
        // Title
        if ($settings['show_title'] === 'yes' && !empty($settings['title'])) {
            echo '<h3 class="pgfe-archive-filter-title">' . esc_html($settings['title']) . '</h3>';
        }
        
        // Render based on display type
        switch ($settings['display_type']) {
            case 'dropdown':
                $this->render_dropdown($categories, $settings);
                break;
            case 'pills':
                $this->render_pills($categories, $settings);
                break;
            case 'grid':
                $this->render_grid($categories, $settings);
                break;
            default:
                $this->render_list($categories, $settings);
                break;
        }
        
        echo '</div>';
    }
    
    private function render_dropdown($categories, $settings) {
        echo '<select class="pgfe-archive-dropdown" name="archive_categories">';
        echo '<option value="">' . __('All Categories', 'pgfe-lite') . '</option>';
        
        foreach ($categories as $category) {
            $count_text = $settings['show_count'] === 'yes' ? ' (' . $category->count . ')' : '';
            $selected = is_tax($settings['taxonomy'], $category->term_id) ? ' selected' : '';
            echo '<option value="' . esc_attr($category->term_id) . '"' . $selected . '>';
            echo esc_html($category->name . $count_text);
            echo '</option>';
        }
        
        echo '</select>';
    }
    
    private function render_pills($categories, $settings) {
        echo '<div class="pgfe-category-pills">';
        
        foreach ($categories as $category) {
            $count_text = $settings['show_count'] === 'yes' ? ' <span class="pgfe-category-count">(' . $category->count . ')</span>' : '';
            $active_class = is_tax($settings['taxonomy'], $category->term_id) ? ' active' : '';
            $category_url = get_term_link($category);
            
            echo '<a href="' . esc_url($category_url) . '" class="pgfe-category-pill' . $active_class . '" data-category-id="' . esc_attr($category->term_id) . '">';
            echo esc_html($category->name) . $count_text;
            echo '</a>';
        }
        
        echo '</div>';
    }
    
    private function render_grid($categories, $settings) {
        echo '<div class="pgfe-category-grid">';
        
        foreach ($categories as $category) {
            $count_text = $settings['show_count'] === 'yes' ? ' <span class="pgfe-category-count">(' . $category->count . ')</span>' : '';
            $active_class = is_tax($settings['taxonomy'], $category->term_id) ? ' active' : '';
            $category_url = get_term_link($category);
            
            echo '<div class="pgfe-category-item' . $active_class . '">';
            echo '<a href="' . esc_url($category_url) . '">';
            echo esc_html($category->name) . $count_text;
            echo '</a>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    private function render_list($categories, $settings) {
        if ($settings['show_hierarchy'] === 'yes') {
            $this->render_hierarchical_list($categories, $settings);
        } else {
            $this->render_flat_list($categories, $settings);
        }
    }
    
    private function render_flat_list($categories, $settings) {
        echo '<ul class="pgfe-category-list">';
        
        foreach ($categories as $category) {
            $count_text = $settings['show_count'] === 'yes' ? ' <span class="pgfe-category-count">(' . $category->count . ')</span>' : '';
            $active_class = is_tax($settings['taxonomy'], $category->term_id) ? ' active' : '';
            $category_url = get_term_link($category);
            
            echo '<li class="pgfe-category-item' . $active_class . '">';
            echo '<a href="' . esc_url($category_url) . '">';
            echo esc_html($category->name) . $count_text;
            echo '</a>';
            echo '</li>';
        }
        
        echo '</ul>';
    }
    
    private function render_hierarchical_list($categories, $settings) {
        // Group categories by parent
        $category_tree = [];
        $parent_categories = [];
        
        foreach ($categories as $category) {
            if ($category->parent == 0) {
                $parent_categories[] = $category;
                $category_tree[$category->term_id] = [];
            } else {
                if (!isset($category_tree[$category->parent])) {
                    $category_tree[$category->parent] = [];
                }
                $category_tree[$category->parent][] = $category;
            }
        }
        
        echo '<ul class="pgfe-category-list pgfe-hierarchical">';
        
        foreach ($parent_categories as $parent) {
            $count_text = $settings['show_count'] === 'yes' ? ' <span class="pgfe-category-count">(' . $parent->count . ')</span>' : '';
            $active_class = is_tax($settings['taxonomy'], $parent->term_id) ? ' active' : '';
            $category_url = get_term_link($parent);
            
            echo '<li class="pgfe-category-item pgfe-parent-category' . $active_class . '">';
            echo '<a href="' . esc_url($category_url) . '">';
            echo esc_html($parent->name) . $count_text;
            echo '</a>';
            
            // Render children if any
            if (!empty($category_tree[$parent->term_id])) {
                echo '<ul class="pgfe-child-categories">';
                foreach ($category_tree[$parent->term_id] as $child) {
                    $child_count_text = $settings['show_count'] === 'yes' ? ' <span class="pgfe-category-count">(' . $child->count . ')</span>' : '';
                    $child_active_class = is_tax($settings['taxonomy'], $child->term_id) ? ' active' : '';
                    $child_category_url = get_term_link($child);
                    
                    echo '<li class="pgfe-category-item pgfe-child-category' . $child_active_class . '">';
                    echo '<a href="' . esc_url($child_category_url) . '">';
                    echo esc_html($child->name) . $child_count_text;
                    echo '</a>';
                    echo '</li>';
                }
                echo '</ul>';
            }
            
            echo '</li>';
        }
        
        echo '</ul>';
    }
    
    private function get_category_options() {
        $options = [];
        
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ]);
        
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $options[$category->term_id] = $category->name;
            }
        }
        
        return $options;
    }
}