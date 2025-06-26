<?php
/**
 * Simple Grid Renderer - Version nettoyée pour SimpleGridWidget
 * 
 * @package PGFE_Lite\Display
 * @since 1.0.0
 */

namespace PGFE_Lite\Display;

if (!defined('ABSPATH')) {
    exit;
}

class GridRenderer {
    
    /**
     * Render simple product grid
     * 
     * @param array $products Formatted products data
     * @param array $settings Display settings
     * @return string HTML output
     */
    public function render($products, $settings = []) {
        if (empty($products)) {
            return $this->renderEmptyState();
        }
        
        $defaults = [
            'columns' => 4,
            'show_badges' => true,
            'show_rating' => true,
            'show_vendor' => true,
            'css_class' => 'pgfe-simple-grid'
        ];
        
        $settings = wp_parse_args($settings, $defaults);
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($settings['css_class']); ?>" data-columns="<?php echo esc_attr($settings['columns']); ?>">
            <?php foreach ($products as $product): ?>
                <?php echo $this->renderProductCard($product, $settings); ?>
            <?php endforeach; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render individual product card
     * 
     * @param array $product Product data
     * @param array $settings Display settings
     * @return string HTML output
     */
    private function renderProductCard($product, $settings) {
        ob_start();
        ?>
        <div class="pgfe-product-item" data-product-id="<?php echo esc_attr($product['id']); ?>">
            <?php if (!empty($product['image'])): ?>
                <div class="pgfe-product-image">
                    <?php if ($settings['show_badges'] && !empty($product['badges'])): ?>
                        <?php foreach ($product['badges'] as $badge): ?>
                            <span class="pgfe-badge pgfe-badge-<?php echo esc_attr($badge['type']); ?>">
                                <?php echo esc_html($badge['text']); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url($product['permalink']); ?>">
                        <img src="<?php echo esc_url($product['image']); ?>" 
                             alt="<?php echo esc_attr($product['title']); ?>" 
                             loading="lazy">
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="pgfe-product-content">
                <h3 class="pgfe-product-title">
                    <a href="<?php echo esc_url($product['permalink']); ?>">
                        <?php echo esc_html($product['title']); ?>
                    </a>
                </h3>
                
                <?php if ($settings['show_rating'] && !empty($product['rating'])): ?>
                    <div class="pgfe-product-rating">
                        <?php echo $this->renderRating($product['rating']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($settings['show_vendor'] && !empty($product['vendor'])): ?>
                    <div class="pgfe-product-vendor">
                        <span class="pgfe-vendor-label">Par:</span>
                        <a href="<?php echo esc_url($product['vendor']['url']); ?>" class="pgfe-vendor-name">
                            <?php echo esc_html($product['vendor']['name']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="pgfe-product-price">
                    <?php echo wp_kses_post($product['price_html']); ?>
                </div>
                
                <div class="pgfe-product-actions">
                    <a href="<?php echo esc_url($product['add_to_cart_url']); ?>" 
                       class="pgfe-add-to-cart-btn" 
                       data-product-id="<?php echo esc_attr($product['id']); ?>">
                        <?php echo esc_html($product['add_to_cart_text']); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render product rating
     * 
     * @param array $rating Rating data
     * @return string HTML output
     */
    private function renderRating($rating) {
        if (empty($rating['average']) || $rating['average'] <= 0) {
            return '';
        }
        
        $stars = '';
        $full_stars = floor($rating['average']);
        $half_star = ($rating['average'] - $full_stars) >= 0.5;
        
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $full_stars) {
                $stars .= '<span class="pgfe-star filled">★</span>';
            } elseif ($i == $full_stars + 1 && $half_star) {
                $stars .= '<span class="pgfe-star half">☆</span>';
            } else {
                $stars .= '<span class="pgfe-star empty">☆</span>';
            }
        }
        
        $output = '<div class="pgfe-rating-stars">' . $stars . '</div>';
        
        if (!empty($rating['count'])) {
            $output .= '<span class="pgfe-rating-count">(' . esc_html($rating['count']) . ')</span>';
        }
        
        return $output;
    }
    
    /**
     * Render empty state when no products found
     * 
     * @return string HTML output
     */
    private function renderEmptyState() {
        return '<div class="pgfe-no-products">Aucun produit trouvé.</div>';
    }
    
    /**
     * Get responsive columns CSS
     * 
     * @param int $columns Number of columns
     * @return string CSS rules
     */
    public function getResponsiveCSS($columns) {
        $css = "";
        
        // Base grid
        $css .= ".pgfe-simple-grid { display: grid; gap: 20px; grid-template-columns: repeat({$columns}, 1fr); }";
        
        // Responsive breakpoints
        $css .= "@media (max-width: 1200px) { .pgfe-simple-grid { grid-template-columns: repeat(" . min(4, $columns) . ", 1fr); } }";
        $css .= "@media (max-width: 992px) { .pgfe-simple-grid { grid-template-columns: repeat(" . min(3, $columns) . ", 1fr); } }";
        $css .= "@media (max-width: 768px) { .pgfe-simple-grid { grid-template-columns: repeat(2, 1fr); } }";
        $css .= "@media (max-width: 480px) { .pgfe-simple-grid { grid-template-columns: 1fr; } }";
        
        return $css;
    }
}