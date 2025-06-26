<?php
/**
 * Product Data Formatter - Basé sur l'exemple
 * 
 * @package PGFE_Lite\Core
 * @since 1.0.0
 */

namespace PGFE_Lite\Core;

if (!defined('ABSPATH')) {
    exit;
}

class ProductFormatter {
    
    /**
     * Format product data for display
     * 
     * @param \WP_Post $product Product post object
     * @return array Formatted product data
     */
    public function formatProduct($product) {
        $wc_product = wc_get_product($product->ID);
        
        if (!$wc_product) {
            return null;
        }
        
        return [
            'id' => $product->ID,
            'title' => $product->post_title,
            'permalink' => get_permalink($product->ID),
            'image' => $this->getProductImage($wc_product),
            'price' => $this->getProductPrice($wc_product),
            'rating' => $this->getProductRating($wc_product),
            'vendor' => $this->getVendorInfo($product->post_author),
            'badges' => $this->getProductBadges($wc_product),
            'is_new' => $this->isNewProduct($product->post_date)
        ];
    }
    
    /**
     * Get product image data
     * 
     * @param \WC_Product $product WooCommerce product
     * @return array Image data
     */
    private function getProductImage($product) {
        $image_id = $product->get_image_id();
        
        if ($image_id) {
            return [
                'url' => wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail'),
                'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
            ];
        }
        
        return [
            'url' => wc_placeholder_img_src('woocommerce_thumbnail'),
            'alt' => __('Placeholder', 'pgfe-lite')
        ];
    }
    
    /**
     * Get formatted price data
     * 
     * @param \WC_Product $product WooCommerce product
     * @return array Price data
     */
    private function getProductPrice($product) {
        return [
            'regular' => $product->get_regular_price(),
            'sale' => $product->get_sale_price(),
            'formatted' => $product->get_price_html(),
            'currency' => get_woocommerce_currency_symbol()
        ];
    }
    
    /**
     * Get product rating data
     * 
     * @param \WC_Product $product WooCommerce product
     * @return array Rating data
     */
    private function getProductRating($product) {
        return [
            'average' => $product->get_average_rating(),
            'count' => $product->get_review_count(),
            'stars' => round($product->get_average_rating())
        ];
    }
    
    /**
     * Get vendor information
     * 
     * @param int $author_id Product author ID
     * @return array Vendor data
     */
    private function getVendorInfo($author_id) {
        // Get vendor shop name - Try multiple sources, prioritize pv_shop_name
        $shop_name = get_user_meta($author_id, 'pv_shop_name', true);
        if (empty($shop_name)) {
            $shop_name = get_user_meta($author_id, 'dokan_store_name', true);
        }
        if (empty($shop_name)) {
            $shop_name = get_user_meta($author_id, '_wcv_store_name', true);
        }
        if (empty($shop_name)) {
            $user = get_userdata($author_id);
            $shop_name = $user ? $user->display_name : '';
        }
        
        // Get vendor shop URL - Force custom structure
        $vendor_url = home_url('/artisans/' . sanitize_title($shop_name));
        
        return [
            'id' => $author_id,
            'name' => $shop_name,
            'url' => $vendor_url
        ];
    }
    
    /**
     * Get product badges
     * 
     * @param \WC_Product $product WooCommerce product
     * @return array Badge data
     */
    private function getProductBadges($product) {
        $badges = [];
        
        if ($product->is_on_sale()) {
            $regular_price = floatval($product->get_regular_price());
            $sale_price = floatval($product->get_sale_price());
            
            if ($regular_price > 0) {
                $discount = round((($regular_price - $sale_price) / $regular_price) * 100);
                $badges['sale'] = $discount;
            }
        }
        
        return $badges;
    }
    
    /**
     * Check if product is new (less than 30 days)
     * 
     * @param string $post_date Product creation date
     * @return bool
     */
    private function isNewProduct($post_date) {
        $created_date = strtotime($post_date);
        $thirty_days_ago = strtotime('-30 days');
        
        return $created_date > $thirty_days_ago;
    }
}