<?php
/**
 * Simple Grid Widget for PGFE Lite
 * Widget simplifié pour affichage de grilles de produits avec CSS moderne
 * 
 * @package PGFE_Lite\Elementor\Widgets
 * @since 2.0.0
 */

namespace PGFE_Lite\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if (!defined('ABSPATH')) {
    exit;
}

class SimpleGridWidget extends Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'pgfe-simple-grid';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Grille Simple PGFE', 'pgfe-lite');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-gallery-grid';
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
        return ['grid', 'products', 'woocommerce', 'shop', 'pgfe'];
    }
    
    /**
     * Register widget controls
     */
    protected function register_controls() {
        
        // Section Contenu
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Configuration', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'columns_desktop',
            [
                'label' => __('Colonnes (Desktop)', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => '4',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
            ]
        );
        
        $this->add_control(
            'columns_tablet',
            [
                'label' => __('Colonnes (Tablette)', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => '2',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ],
            ]
        );
        
        $this->add_control(
            'columns_mobile',
            [
                'label' => __('Colonnes (Mobile)', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                ],
            ]
        );
        
        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Nombre de produits', 'pgfe-lite'),
                'type' => Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
            ]
        );
        
        $this->add_control(
            'gap',
            [
                'label' => __('Espacement', 'pgfe-lite'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-simple-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Section Style
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'item_border',
                'label' => __('Bordure des éléments', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-grid-item',
            ]
        );
        
        $this->add_control(
            'item_border_radius',
            [
                'label' => __('Rayon de bordure', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-grid-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_box_shadow',
                'label' => __('Ombre des éléments', 'pgfe-lite'),
                'selector' => '{{WRAPPER}} .pgfe-grid-item',
            ]
        );
        
        $this->add_control(
            'item_padding',
            [
                'label' => __('Padding des éléments', 'pgfe-lite'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .pgfe-grid-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $columns_desktop = $settings['columns_desktop'] ?? '4';
        $columns_tablet = $settings['columns_tablet'] ?? '2';
        $columns_mobile = $settings['columns_mobile'] ?? '1';
        $posts_per_page = $settings['posts_per_page'] ?? 12;
        
        // Classes CSS responsives
        $grid_classes = [
            'pgfe-simple-grid',
            'pgfe-cols-desktop-' . $columns_desktop,
            'pgfe-cols-tablet-' . $columns_tablet,
            'pgfe-cols-mobile-' . $columns_mobile
        ];
        
        ?>
        <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>" 
             data-columns="<?php echo esc_attr($columns_desktop); ?>"
             data-posts-per-page="<?php echo esc_attr($posts_per_page); ?>"
             data-widget-id="<?php echo esc_attr($this->get_id()); ?>">
             
            <!-- Loader pour les filtres -->
            <div class="pgfe-grid-loader" style="display: none;">
                <div class="pgfe-loader-spinner"></div>
                <p><?php _e('Chargement des produits...', 'pgfe-lite'); ?></p>
            </div>
            
            <?php
            // Récupérer les produits WooCommerce
            $products = $this->get_products($posts_per_page);
            
            if ($products->have_posts()) {
                while ($products->have_posts()) {
                    $products->the_post();
                    global $product;
                    
                    if (!$product || !$product->is_visible()) {
                        continue;
                    }
                    
                    $this->render_product_item($product);
                }
                wp_reset_postdata();
            } else {
                echo '<div class="pgfe-no-products">' . __('Aucun produit trouvé.', 'pgfe-lite') . '</div>';
            }
            ?>
        </div>
        
        <?php
        // Ajouter les styles CSS inline
        $this->add_inline_styles($columns_desktop, $columns_tablet, $columns_mobile);
    }
    
    /**
     * Récupérer les produits WooCommerce
     */
    private function get_products($posts_per_page) {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'meta_query' => [
                [
                    'key' => '_visibility',
                    'value' => ['exclude-from-search', 'exclude-from-catalog'],
                    'compare' => 'NOT IN'
                ]
            ]
        ];
        
        return new \WP_Query($args);
    }
    
    /**
     * Rendu d'un élément produit
     */
    private function render_product_item($product) {
        $product_id = $product->get_id();
        $title = $product->get_name();
        $price = $product->get_price_html();
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
        $link = get_permalink($product_id);
        
        ?>
        <div class="pgfe-grid-item" data-product-id="<?php echo esc_attr($product_id); ?>">
            <a href="<?php echo esc_url($link); ?>" class="pgfe-product-link">
                
                <?php if ($image): ?>
                <div class="pgfe-product-image">
                    <img src="<?php echo esc_url($image[0]); ?>" 
                         alt="<?php echo esc_attr($title); ?>"
                         loading="lazy">
                </div>
                <?php endif; ?>
                
                <div class="pgfe-product-content">
                    <h3 class="pgfe-product-title"><?php echo esc_html($title); ?></h3>
                    <div class="pgfe-product-price"><?php echo $price; ?></div>
                </div>
                
            </a>
        </div>
        <?php
    }
    
    /**
     * Ajouter les styles CSS inline
     */
    private function add_inline_styles($desktop, $tablet, $mobile) {
        $widget_id = $this->get_id();
        
        ?>
        <style>
        .elementor-element-<?php echo $widget_id; ?> .pgfe-simple-grid {
            display: grid;
            grid-template-columns: repeat(<?php echo $desktop; ?>, 1fr);
            gap: 20px;
            width: 100%;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-grid-item {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-grid-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-product-link {
            display: block;
            text-decoration: none;
            color: inherit;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-product-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 1;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-grid-item:hover .pgfe-product-image img {
            transform: scale(1.05);
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-product-content {
            padding: 15px;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-product-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 10px 0;
            line-height: 1.4;
            color: #333;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-product-price {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 16px;
        }
        
        /* Loader styles */
        .elementor-element-<?php echo $widget_id; ?> .pgfe-grid-loader {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-loader-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #e74c3c;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: pgfe-spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes pgfe-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* États de filtrage */
        .elementor-element-<?php echo $widget_id; ?> .pgfe-simple-grid.pgfe-filtering {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .elementor-element-<?php echo $widget_id; ?> .pgfe-simple-grid.pgfe-filtering .pgfe-grid-loader {
            display: block !important;
        }
        
        /* Responsive Tablet */
        @media (max-width: 1024px) {
            .elementor-element-<?php echo $widget_id; ?> .pgfe-simple-grid {
                grid-template-columns: repeat(<?php echo $tablet; ?>, 1fr);
            }
        }
        
        /* Responsive Mobile */
        @media (max-width: 767px) {
            .elementor-element-<?php echo $widget_id; ?> .pgfe-simple-grid {
                grid-template-columns: repeat(<?php echo $mobile; ?>, 1fr);
                gap: 15px;
            }
            
            .elementor-element-<?php echo $widget_id; ?> .pgfe-product-content {
                padding: 12px;
            }
            
            .elementor-element-<?php echo $widget_id; ?> .pgfe-product-title {
                font-size: 14px;
            }
            
            .elementor-element-<?php echo $widget_id; ?> .pgfe-product-price {
                font-size: 16px;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="pgfe-simple-grid pgfe-editor-preview">
            <div class="pgfe-grid-item">
                <div class="pgfe-product-image">
                    <div style="background: #f0f0f0; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; color: #999;">
                        Image
                    </div>
                </div>
                <div class="pgfe-product-content">
                    <h3 class="pgfe-product-title">Nom du produit</h3>
                    <div class="pgfe-product-price">€29.99</div>
                </div>
            </div>
            <div class="pgfe-grid-item">
                <div class="pgfe-product-image">
                    <div style="background: #f0f0f0; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; color: #999;">
                        Image
                    </div>
                </div>
                <div class="pgfe-product-content">
                    <h3 class="pgfe-product-title">Autre produit</h3>
                    <div class="pgfe-product-price">€45.00</div>
                </div>
            </div>
        </div>
        
        <style>
        .pgfe-editor-preview {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .pgfe-editor-preview .pgfe-grid-item {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .pgfe-editor-preview .pgfe-product-content {
            padding: 15px;
        }
        .pgfe-editor-preview .pgfe-product-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 10px 0;
            color: #333;
        }
        .pgfe-editor-preview .pgfe-product-price {
            font-size: 18px;
            font-weight: bold;
            color: #e74c3c;
        }
        </style>
        <?php
    }
}