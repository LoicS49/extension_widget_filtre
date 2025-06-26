<?php
/**
 * Elementor Parent Category Filter Widget
 * 
 * @package PGFE_Lite\Elementor\Widgets
 * @since 1.0.0
 */

namespace PGFE_Lite\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use PGFE_Lite\Elementor\Base\BaseFilterWidget;

if (!defined('ABSPATH')) {
    exit;
}

class ParentCategoryFilterWidget extends BaseFilterWidget {
    
    /**
     * Get widget name
     */
    protected function get_widget_name() {
        return 'llda-parent-category-filter';
    }
    
    /**
     * Get widget title
     */
    protected function get_widget_title() {
        return __('Parent Category Filter', 'pgfe-lite');
    }
    
    /**
     * Get widget icon
     */
    protected function get_widget_icon() {
        return 'eicon-product-categories';
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
        return ['category', 'filter', 'parent', 'woocommerce', 'taxonomy'];
    }
    
    /**
     * Register widget controls
     */
    protected function register_widget_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Contenu', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'taxonomy',
            [
                'label' => __('Taxonomie', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'product_cat',
                'options' => [
                    'product_cat' => __('Catégories de produits', 'pgfe-lite'),
                ],
                'description' => __('Sélectionnez la taxonomie à utiliser', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'include_categories',
            [
                'label' => __('Catégories → Inclure', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->getParentCategoriesOptions(),
                'description' => __('Laisse vide = toutes les catégories parentes. Utilise le Term Autocomplete d\'Elementor pour sélectionner plusieurs IDs.', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'exclude_categories',
            [
                'label' => __('Catégories → Exclure', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->getParentCategoriesOptions(),
                'description' => __('Familles parentes à masquer même si elles répondent au reste des critères.', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'display_type',
            [
                'label' => __('Type d\'affichage', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'list',
                'options' => [
                    'dropdown' => __('Dropdown', 'pgfe-lite'),
                    'list' => __('Liste', 'pgfe-lite'),
                    'pills' => __('Pills', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'sort_by',
            [
                'label' => __('Tri', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'name',
                'options' => [
                    'name' => __('Nom', 'pgfe-lite'),
                    'count' => __('Nombre de produits', 'pgfe-lite'),
                    'menu_order' => __('Ordre du menu', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'sort_order',
            [
                'label' => __('Ordre de tri', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC' => __('Croissant', 'pgfe-lite'),
                    'DESC' => __('Décroissant', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'hide_empty',
            [
                'label' => __('Exclure vides', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'pgfe-lite'),
                'label_off' => __('Non', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Masquer les catégories sans produits', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'show_count',
            [
                'label' => __('Afficher le compteur', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'pgfe-lite'),
                'label_off' => __('Non', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Container
        $this->start_controls_section(
            'style_container_section',
            [
                'label' => __('Conteneur', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'container_background',
                'label' => __('Arrière-plan', 'pgfe-lite'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .pgfe-parent-category-filter',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'label' => __('Bordure', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-parent-category-filter',
            ]
        );
        
        $this->add_responsive_control(
            'container_border_radius',
            [
                'label' => __('Rayon de bordure', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Items
        $this->start_controls_section(
            'style_items_section',
            [
                'label' => __('Éléments', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'items_typography',
                'label' => __('Typographie', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-parent-category-filter .category-item, {{WRAPPER}} .pgfe-parent-category-filter select',
            ]
        );
        
        $this->add_control(
            'items_color',
            [
                'label' => __('Couleur du texte', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter .category-item' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .pgfe-parent-category-filter select' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'items_hover_color',
            [
                'label' => __('Couleur au survol', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter .category-item:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'items_active_color',
            [
                'label' => __('Couleur active', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter .category-item.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'items_background',
                'label' => __('Arrière-plan', 'pgfe-lite'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .pgfe-parent-category-filter .category-item',
                'condition' => [
                    'display_type!' => 'dropdown',
                ],
            ]
        );
        
        $this->add_control(
            'items_active_background',
            [
                'label' => __('Arrière-plan actif', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter .category-item.active' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'display_type!' => 'dropdown',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'items_spacing',
            [
                'label' => __('Espacement', 'pgfe-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter.display-list .category-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .pgfe-parent-category-filter.display-pills .category-item' => 'margin-right: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'display_type!' => 'dropdown',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Badge Counter
        $this->start_controls_section(
            'style_badge_section',
            [
                'label' => __('Badge compteur', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'badge_color',
            [
                'label' => __('Couleur du texte', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter .category-count' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'badge_background',
            [
                'label' => __('Couleur de fond', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-parent-category-filter .category-count' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'label' => __('Typographie', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-parent-category-filter .category-count',
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output on the frontend
     */
    protected function render_widget() {
        $settings = $this->get_settings_for_display();
        
        // Get parent categories
        $categories = $this->getParentCategories($settings);
        
        if (empty($categories)) {
            echo '<p>' . __('Aucune catégorie trouvée.', 'pgfe-lite') . '</p>';
            return;
        }
        
        $widget_id = 'pgfe-parent-category-filter-' . $this->get_id();
        $display_class = 'display-' . $settings['display_type'];
        
        ?>
        <div class="pgfe-parent-category-filter <?php echo esc_attr($display_class); ?>" id="<?php echo esc_attr($widget_id); ?>">
            <?php if ($settings['display_type'] === 'dropdown') : ?>
                <select class="category-dropdown" name="parent_category" data-filter="parent-category">
                    <option value=""><?php _e('Toutes les catégories', 'pgfe-lite'); ?></option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?php echo esc_attr($category->term_id); ?>">
                            <?php echo esc_html($category->name); ?>
                            <?php if ($settings['show_count'] === 'yes') : ?>
                                (<?php echo $category->count; ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else : ?>
                <div class="category-list">
                    <?php foreach ($categories as $category) : ?>
                        <div class="category-item" data-category-id="<?php echo esc_attr($category->term_id); ?>" data-filter="parent-category">
                            <span class="category-name"><?php echo esc_html($category->name); ?></span>
                            <?php if ($settings['show_count'] === 'yes') : ?>
                                <span class="category-count"><?php echo $category->count; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Get parent categories based on settings
     */
    private function getParentCategories($settings) {
        $args = [
            'taxonomy' => $settings['taxonomy'],
            'parent' => 0, // Only parent categories
            'hide_empty' => $settings['hide_empty'] === 'yes',
        ];
        
        // Set ordering
        switch ($settings['sort_by']) {
            case 'count':
                $args['orderby'] = 'count';
                break;
            case 'menu_order':
                $args['orderby'] = 'menu_order';
                $args['meta_key'] = 'order';
                break;
            default:
                $args['orderby'] = 'name';
                break;
        }
        
        $args['order'] = $settings['sort_order'];
        
        // Include specific categories
        if (!empty($settings['include_categories'])) {
            $args['include'] = $settings['include_categories'];
        }
        
        // Exclude specific categories
        if (!empty($settings['exclude_categories'])) {
            $args['exclude'] = $settings['exclude_categories'];
        }
        
        $categories = get_terms($args);
        
        if (is_wp_error($categories)) {
            return [];
        }
        
        // Apply exclusion priority over inclusion
        if (!empty($settings['exclude_categories']) && !empty($settings['include_categories'])) {
            $categories = array_filter($categories, function($category) use ($settings) {
                return !in_array($category->term_id, $settings['exclude_categories']);
            });
        }
        
        return $categories;
    }
    
    /**
     * Get parent categories options for select2
     */
    private function getParentCategoriesOptions() {
        $options = [];
        
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'parent' => 0,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $options[$category->term_id] = $category->name;
            }
        }
        
        return $options;
    }
}