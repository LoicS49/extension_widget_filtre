<?php
/**
 * Elementor Child Category Filter Widget
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

class ChildCategoryFilterWidget extends BaseFilterWidget {
    
    /**
     * Get widget name
     */
    protected function get_widget_name() {
        return 'llda-child-category-filter';
    }
    
    /**
     * Get widget title
     */
    protected function get_widget_title() {
        return __('Child Category Filter', 'pgfe-lite');
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
        return ['category', 'filter', 'child', 'woocommerce', 'ajax', 'dynamic'];
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
            'source_type',
            [
                'label' => __('Source', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'dynamic',
                'options' => [
                    'dynamic' => __('Parent choisi dynamiquement', 'pgfe-lite'),
                    'manual' => __('ID manuel', 'pgfe-lite'),
                ],
                'description' => __('Charge dynamiquement via AJAX les enfants de la catégorie parente sélectionnée', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'parent_category_id',
            [
                'label' => __('ID de la catégorie parente', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->getParentCategoriesOptions(),
                'condition' => [
                    'source_type' => 'manual',
                ],
                'description' => __('Sélectionnez la catégorie parente dont vous voulez afficher les enfants', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'include_categories',
            [
                'label' => __('Inclure', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->getAllCategoriesOptions(),
                'description' => __('Filtre appliqué après la détection dynamique du parent', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'exclude_categories',
            [
                'label' => __('Exclure', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->getAllCategoriesOptions(),
                'description' => __('Filtre appliqué après la détection dynamique du parent', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'display_type',
            [
                'label' => __('Affichage', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'checkbox',
                'options' => [
                    'checkbox' => __('Checkbox', 'pgfe-lite'),
                    'buttons' => __('Boutons', 'pgfe-lite'),
                    'accordion' => __('Accordéon', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'multi_selection',
            [
                'label' => __('Multi-sélection', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'or',
                'options' => [
                    'or' => __('OR (au moins une)', 'pgfe-lite'),
                    'and' => __('AND (toutes)', 'pgfe-lite'),
                ],
                'description' => __('Logique de filtrage pour les sélections multiples', 'pgfe-lite'),
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
        
        $this->add_control(
            'hide_empty',
            [
                'label' => __('Masquer les vides', 'pgfe-lite'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Oui', 'pgfe-lite'),
                'label_off' => __('Non', 'pgfe-lite'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Performance Section
        $this->start_controls_section(
            'performance_section',
            [
                'label' => __('Performance', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'cache_duration',
            [
                'label' => __('Durée du cache (minutes)', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 60,
                'description' => __('Transitoire 5 min par combinaison requête-parent (Helpers/Cache.php)', 'pgfe-lite'),
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
                'selector' => '{{WRAPPER}} .pgfe-child-category-filter',
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'label' => __('Bordure', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-child-category-filter',
            ]
        );
        
        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Padding', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-child-category-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'selector' => '{{WRAPPER}} .pgfe-child-category-filter .category-item label, {{WRAPPER}} .pgfe-child-category-filter .category-button',
            ]
        );
        
        $this->add_control(
            'items_color',
            [
                'label' => __('Couleur du texte', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-child-category-filter .category-item label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .pgfe-child-category-filter .category-button' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .pgfe-child-category-filter .category-item:hover label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .pgfe-child-category-filter .category-button:hover' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .pgfe-child-category-filter .category-item.active label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .pgfe-child-category-filter .category-button.active' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .pgfe-child-category-filter .category-item.active' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .pgfe-child-category-filter .category-button.active' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'display_type!' => 'checkbox',
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
                        'max' => 30,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-child-category-filter .category-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .pgfe-child-category-filter .category-button' => 'margin-right: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Loading
        $this->start_controls_section(
            'style_loading_section',
            [
                'label' => __('Chargement', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'loading_color',
            [
                'label' => __('Couleur du loader', 'pgfe-lite'),
                'type' => Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .pgfe-child-category-filter .loading-spinner' => 'border-top-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output on the frontend
     */
    protected function render_widget() {
        $settings = $this->get_settings_for_display();
        
        $widget_id = 'pgfe-child-category-filter-' . $this->get_id();
        $display_class = 'display-' . $settings['display_type'];
        
        // Get initial categories if manual mode
        $initial_categories = [];
        if ($settings['source_type'] === 'manual' && !empty($settings['parent_category_id'])) {
            $initial_categories = $this->getChildCategories($settings['parent_category_id'], $settings);
        }
        
        ?>
        <div class="pgfe-child-category-filter <?php echo esc_attr($display_class); ?>" 
             id="<?php echo esc_attr($widget_id); ?>"
             data-source-type="<?php echo esc_attr($settings['source_type']); ?>"
             data-parent-id="<?php echo esc_attr($settings['parent_category_id'] ?? ''); ?>"
             data-multi-selection="<?php echo esc_attr($settings['multi_selection']); ?>"
             data-cache-duration="<?php echo esc_attr($settings['cache_duration']); ?>"
             data-widget-id="<?php echo esc_attr($this->get_id()); ?>">
            
            <div class="loading-container" style="display: none;">
                <div class="loading-spinner"></div>
                <span class="loading-text"><?php _e('Chargement des catégories...', 'pgfe-lite'); ?></span>
            </div>
            
            <div class="categories-container">
                <?php if (!empty($initial_categories)) : ?>
                    <?php $this->renderCategories($initial_categories, $settings); ?>
                <?php else : ?>
                    <div class="no-parent-selected">
                        <p><?php _e('Sélectionnez une catégorie parente pour voir les sous-catégories.', 'pgfe-lite'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render categories based on display type
     */
    private function renderCategories($categories, $settings) {
        if (empty($categories)) {
            echo '<p>' . __('Aucune sous-catégorie trouvée.', 'pgfe-lite') . '</p>';
            return;
        }
        
        switch ($settings['display_type']) {
            case 'checkbox':
                $this->renderCheckboxes($categories, $settings);
                break;
            case 'buttons':
                $this->renderButtons($categories, $settings);
                break;
            case 'accordion':
                $this->renderAccordion($categories, $settings);
                break;
        }
    }
    
    /**
     * Render checkbox display
     */
    private function renderCheckboxes($categories, $settings) {
        echo '<div class="category-checkboxes">';
        foreach ($categories as $category) {
            echo '<div class="category-item">';
            echo '<label>';
            echo '<input type="checkbox" name="child_categories[]" value="' . esc_attr($category->term_id) . '" data-filter="child-category">';
            echo '<span class="category-name">' . esc_html($category->name) . '</span>';
            if ($settings['show_count'] === 'yes') {
                echo ' <span class="category-count">(' . $category->count . ')</span>';
            }
            echo '</label>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * Render buttons display
     */
    private function renderButtons($categories, $settings) {
        echo '<div class="category-buttons">';
        foreach ($categories as $category) {
            echo '<button type="button" class="category-button" data-category-id="' . esc_attr($category->term_id) . '" data-filter="child-category">';
            echo '<span class="category-name">' . esc_html($category->name) . '</span>';
            if ($settings['show_count'] === 'yes') {
                echo ' <span class="category-count">(' . $category->count . ')</span>';
            }
            echo '</button>';
        }
        echo '</div>';
    }
    
    /**
     * Render accordion display
     */
    private function renderAccordion($categories, $settings) {
        echo '<div class="category-accordion">';
        foreach ($categories as $category) {
            echo '<div class="accordion-item">';
            echo '<div class="accordion-header" data-category-id="' . esc_attr($category->term_id) . '" data-filter="child-category">';
            echo '<span class="category-name">' . esc_html($category->name) . '</span>';
            if ($settings['show_count'] === 'yes') {
                echo ' <span class="category-count">(' . $category->count . ')</span>';
            }
            echo '<span class="accordion-icon">+</span>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * Get child categories for a parent
     */
    private function getChildCategories($parent_id, $settings) {
        $args = [
            'taxonomy' => 'product_cat',
            'parent' => $parent_id,
            'hide_empty' => $settings['hide_empty'] === 'yes',
            'orderby' => 'name',
            'order' => 'ASC'
        ];
        
        // Apply include/exclude filters
        if (!empty($settings['include_categories'])) {
            $args['include'] = $settings['include_categories'];
        }
        
        if (!empty($settings['exclude_categories'])) {
            $args['exclude'] = $settings['exclude_categories'];
        }
        
        $categories = get_terms($args);
        
        if (is_wp_error($categories)) {
            return [];
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
    
    /**
     * Get all categories options for select2
     */
    private function getAllCategoriesOptions() {
        $options = [];
        
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $prefix = $category->parent ? '— ' : '';
                $options[$category->term_id] = $prefix . $category->name;
            }
        }
        
        return $options;
    }
}