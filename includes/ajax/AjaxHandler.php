<?php
/**
 * AJAX Handler
 * 
 * @package PGFE_Lite\Ajax
 * @since 1.0.0
 */

namespace PGFE_Lite\Ajax;

use PGFE_Lite\Core\ProductQuery;
use PGFE_Lite\Core\ProductFormatter;
use PGFE_Lite\Display\GridRenderer;
use Exception;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

class AjaxHandler {
    
    /**
     * Initialize AJAX handlers
     */
    public function __construct() {
        add_action('wp_ajax_pgfe_filter_products', [$this, 'filterProducts']);
        add_action('wp_ajax_nopriv_pgfe_filter_products', [$this, 'filterProducts']);
        
        add_action('wp_ajax_pgfe_get_child_categories', [$this, 'getChildCategories']);
        add_action('wp_ajax_nopriv_pgfe_get_child_categories', [$this, 'getChildCategories']);
        
        add_action('wp_ajax_pgfe_get_price_range', [$this, 'getPriceRange']);
        add_action('wp_ajax_nopriv_pgfe_get_price_range', [$this, 'getPriceRange']);
        
        add_action('wp_ajax_pgfe_load_more_products', [$this, 'loadMoreProducts']);
        add_action('wp_ajax_nopriv_pgfe_load_more_products', [$this, 'loadMoreProducts']);
        
        add_action('wp_ajax_pgfe_get_product_count', [$this, 'getProductCount']);
        add_action('wp_ajax_nopriv_pgfe_get_product_count', [$this, 'getProductCount']);
        
        // JavaScript error logging
        add_action('wp_ajax_pgfe_log_js_error', [$this, 'logJavaScriptError']);
        add_action('wp_ajax_nopriv_pgfe_log_js_error', [$this, 'logJavaScriptError']);
    }
    
    /**
     * Filter products based on AJAX request
     */
    public function filterProducts() {
        try {
            // Log pour débogage
            // Traitement de la requête de filtrage
            
            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pgfe_ajax_nonce')) {
                // Nonce invalide géré
                wp_send_json_error('Nonce invalide');
                return;
            }
            
            $filters = $this->sanitizeFilters($_POST['filters'] ?? []);
            $settings = $_POST['settings'] ?? [];
            
            // Filtres sanitisés et validés
            
            // Obtenir les produits
            $product_query = new ProductQuery();
            $products = $product_query->getProducts($filters);
            
            // Produits récupérés avec succès
            
            // Formater les produits
            $formatter = new ProductFormatter();
            $formatted_products = [];
            
            foreach ($products as $product) {
                $formatted_products[] = $formatter->formatProduct($product, $settings);
            }
            
            // Générer le HTML
            $renderer = new GridRenderer();
            $html = $renderer->renderGrid($formatted_products, $settings);
            
            // HTML généré pour la grille
            
            wp_send_json_success([
                'html' => $html,
                'count' => count($formatted_products),
                'filters_applied' => $filters
            ]);
            
        } catch (Exception $e) {
            // Erreur dans filterProducts gérée
            wp_send_json_error('Erreur lors du filtrage: ' . $e->getMessage());
        } catch (Error $e) {
            // Erreur fatale dans filterProducts gérée
            wp_send_json_error('Erreur fatale lors du filtrage: ' . $e->getMessage());
        }
    }
    
    /**
     * Get child categories for parent category
     */
    public function getChildCategories() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pgfe_ajax_nonce')) {
            wp_die(__('Security check failed', 'pgfe-lite'));
        }
        
        try {
            $parent_id = intval($_POST['parent_id'] ?? 0);
            $show_count = filter_var($_POST['show_count'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $include_ids = array_map('intval', $_POST['include_ids'] ?? []);
            $exclude_ids = array_map('intval', $_POST['exclude_ids'] ?? []);
            
            $args = [
                'taxonomy' => 'product_cat',
                'parent' => $parent_id,
                'hide_empty' => true,
                'orderby' => 'name',
                'order' => 'ASC'
            ];
            
            if (!empty($include_ids)) {
                $args['include'] = $include_ids;
            }
            
            if (!empty($exclude_ids)) {
                $args['exclude'] = $exclude_ids;
            }
            
            $categories = get_terms($args);
            
            if (is_wp_error($categories)) {
                throw new Exception($categories->get_error_message());
            }
            
            $formatted_categories = [];
            
            foreach ($categories as $category) {
                $formatted_category = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'parent' => $category->parent,
                    'link' => get_term_link($category)
                ];
                
                if ($show_count) {
                    $formatted_category['count'] = $category->count;
                }
                
                $formatted_categories[] = $formatted_category;
            }
            
            wp_send_json_success([
                'categories' => $formatted_categories,
                'parent_id' => $parent_id
            ]);
            
        } catch (Exception $e) {
            $this->logError('getChildCategories', $e, $_POST);
            wp_send_json_error([
                'message' => __('Error loading child categories', 'pgfe-lite'),
                'debug' => WP_DEBUG ? $e->getMessage() : '',
                'error_code' => 'CHILD_CATEGORIES_ERROR'
            ]);
        }
    }
    
    /**
     * Get price range for current filters
     */
    public function getPriceRange() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pgfe_ajax_nonce')) {
            wp_die(__('Security check failed', 'pgfe-lite'));
        }
        
        try {
            $filters = $this->sanitizeFilters($_POST['filters'] ?? []);
            $settings = $this->sanitizeSettings($_POST['settings'] ?? []);
            
            // Remove price filters to get full range
            unset($filters['min_price'], $filters['max_price']);
            
            $product_query = new ProductQuery();
            $query_args = $this->buildQueryArgs($filters, $settings);
            
            $price_range = $product_query->getPriceRange($query_args);
            
            wp_send_json_success([
                'min_price' => $price_range['min'],
                'max_price' => $price_range['max']
            ]);
            
        } catch (Exception $e) {
            $this->logError('getPriceRange', $e, $_POST);
            wp_send_json_error([
                'message' => __('Error getting price range', 'pgfe-lite'),
                'debug' => WP_DEBUG ? $e->getMessage() : '',
                'error_code' => 'PRICE_RANGE_ERROR'
            ]);
        }
    }
    
    /**
     * Load more products (pagination)
     */
    public function loadMoreProducts() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pgfe_ajax_nonce')) {
            wp_die(__('Security check failed', 'pgfe-lite'));
        }
        
        try {
            $filters = $this->sanitizeFilters($_POST['filters'] ?? []);
            $settings = $this->sanitizeSettings($_POST['settings'] ?? []);
            $page = intval($_POST['page'] ?? 1);
            $per_page = intval($settings['posts_per_page'] ?? 8);
            
            // Initialize classes
            $product_query = new ProductQuery();
            $product_formatter = new ProductFormatter();
            $grid_renderer = new GridRenderer();
            
            // Build query arguments
            $query_args = $this->buildQueryArgs($filters, $settings);
            $query_args['paged'] = $page;
            $query_args['posts_per_page'] = $per_page;
            
            // Get products
            $products = $product_query->getProducts($query_args);
            
            // Format products
            $formatted_products = [];
            foreach ($products as $product) {
                // Convert WC_Product to WP_Post if needed
                if ($product instanceof \WC_Product) {
                    $post = get_post($product->get_id());
                } else {
                    $post = $product;
                }
                
                $formatted_product = $product_formatter->formatProduct($post);
                if ($formatted_product) {
                    $formatted_products[] = $formatted_product;
                }
            }
            
            // Render products (without grid wrapper)
            $html = '';
            foreach ($formatted_products as $product) {
                $html .= $grid_renderer->renderProductCard($product, $settings);
            }
            
            // Check if there are more products
            $total_products = $product_query->getProductCount($query_args);
            $total_pages = ceil($total_products / $per_page);
            $has_more = $page < $total_pages;
            
            wp_send_json_success([
                'html' => $html,
                'has_more' => $has_more,
                'current_page' => $page,
                'total_pages' => $total_pages,
                'found_products' => count($formatted_products)
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Error loading more products', 'pgfe-lite'),
                'debug' => WP_DEBUG ? $e->getMessage() : ''
            ]);
        }
    }
    
    /**
     * Get product count for current filters
     */
    public function getProductCount() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pgfe_ajax_nonce')) {
            wp_die(__('Security check failed', 'pgfe-lite'));
        }
        
        try {
            $filters = $this->sanitizeFilters($_POST['filters'] ?? []);
            $settings = $this->sanitizeSettings($_POST['settings'] ?? []);
            
            $product_query = new ProductQuery();
            $query_args = $this->buildQueryArgs($filters, $settings);
            
            $count = $product_query->getProductCount($query_args);
            
            wp_send_json_success([
                'count' => $count
            ]);
            
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Error getting product count', 'pgfe-lite'),
                'debug' => WP_DEBUG ? $e->getMessage() : ''
            ]);
        }
    }
    
    /**
     * Sanitize filter parameters
     */
    private function sanitizeFilters($filters) {
        $sanitized = [];
        
        // Price filters
        if (isset($filters['min_price'])) {
            $sanitized['min_price'] = floatval($filters['min_price']);
        }
        
        if (isset($filters['max_price'])) {
            $sanitized['max_price'] = floatval($filters['max_price']);
        }
        
        // Category filters
        if (isset($filters['parent_categories'])) {
            $sanitized['parent_categories'] = array_map('intval', (array) $filters['parent_categories']);
        }
        
        if (isset($filters['child_categories'])) {
            $sanitized['child_categories'] = array_map('intval', (array) $filters['child_categories']);
        }
        
        if (isset($filters['category_filter'])) {
            $sanitized['category_filter'] = array_map('intval', (array) $filters['category_filter']);
        }
        
        // Vendor filters
        if (isset($filters['vendor_filter'])) {
            $sanitized['vendor_filter'] = array_map('intval', (array) $filters['vendor_filter']);
        }
        
        // Attribute filters
        if (isset($filters['attribute_filters']) && is_array($filters['attribute_filters'])) {
            $sanitized['attribute_filters'] = [];
            foreach ($filters['attribute_filters'] as $attribute => $values) {
                $sanitized['attribute_filters'][sanitize_key($attribute)] = array_map('sanitize_text_field', (array) $values);
            }
        }
        
        // Selection type
        if (isset($filters['selection_type'])) {
            $allowed_types = ['latest', 'best_selling', 'featured', 'top_rated', 'new_products', 'promotion', 'manual'];
            $sanitized['selection_type'] = in_array($filters['selection_type'], $allowed_types) ? $filters['selection_type'] : 'latest';
        }
        
        // Manual products
        if (isset($filters['manual_products'])) {
            $sanitized['manual_products'] = array_map('intval', (array) $filters['manual_products']);
        }
        
        // Exclusions
        if (isset($filters['exclude_products'])) {
            $sanitized['exclude_products'] = array_map('intval', (array) $filters['exclude_products']);
        }
        
        if (isset($filters['exclude_categories'])) {
            $sanitized['exclude_categories'] = array_map('intval', (array) $filters['exclude_categories']);
        }
        
        if (isset($filters['exclude_vendors'])) {
            $sanitized['exclude_vendors'] = array_map('intval', (array) $filters['exclude_vendors']);
        }
        
        // Search query
        if (isset($filters['search'])) {
            $sanitized['s'] = sanitize_text_field($filters['search']);
        }
        
        // Sorting
        if (isset($filters['orderby'])) {
            $allowed_orderby = ['date', 'title', 'menu_order', 'popularity', 'rating', 'price', 'price-desc'];
            $sanitized['orderby'] = in_array($filters['orderby'], $allowed_orderby) ? $filters['orderby'] : 'date';
        }
        
        if (isset($filters['order'])) {
            $sanitized['order'] = in_array(strtoupper($filters['order']), ['ASC', 'DESC']) ? strtoupper($filters['order']) : 'DESC';
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize settings parameters
     */
    private function sanitizeSettings($settings) {
        $sanitized = [];
        
        // Posts per page
        if (isset($settings['posts_per_page'])) {
            $sanitized['posts_per_page'] = max(1, min(100, intval($settings['posts_per_page'])));
        }
        
        // Columns
        if (isset($settings['columns'])) {
            $sanitized['columns'] = max(1, min(6, intval($settings['columns'])));
        }
        
        // Display options
        $boolean_settings = ['show_badges', 'show_rating', 'show_vendor', 'show_price', 'show_add_to_cart', 'hover_effect', 'lazy_load'];
        foreach ($boolean_settings as $setting) {
            if (isset($settings[$setting])) {
                $sanitized[$setting] = filter_var($settings[$setting], FILTER_VALIDATE_BOOLEAN);
            }
        }
        
        // Image size
        if (isset($settings['image_size'])) {
            $allowed_sizes = ['thumbnail', 'medium', 'large', 'full'];
            $sanitized['image_size'] = in_array($settings['image_size'], $allowed_sizes) ? $settings['image_size'] : 'medium';
        }
        
        // CSS values
        $css_settings = ['grid_gap', 'card_padding', 'border_radius', 'box_shadow'];
        foreach ($css_settings as $setting) {
            if (isset($settings[$setting])) {
                $sanitized[$setting] = sanitize_text_field($settings[$setting]);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Build query arguments from filters and settings
     */
    private function buildQueryArgs($filters, $settings) {
        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 8,
            'meta_query' => [],
            'tax_query' => []
        ];
        
        // Merge filters into args
        $args = array_merge($args, $filters);
        
        // Ensure WooCommerce visibility
        $args['meta_query'][] = [
            'key' => '_visibility',
            'value' => ['catalog', 'visible'],
            'compare' => 'IN'
        ];
        
        // Handle WooCommerce specific ordering
        if (isset($args['orderby'])) {
            switch ($args['orderby']) {
                case 'popularity':
                    $args['meta_key'] = 'total_sales';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                    
                case 'rating':
                    $args['meta_key'] = '_wc_average_rating';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                    
                case 'price':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;
                    
                case 'price-desc':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
            }
        }
        
        return $args;
    }
    
    /**
     * Log errors with context for debugging
     */
    private function logError($method, $error, $context = []) {
        // Logging d'erreur désactivé pour la production
        return;
    }
    
    /**
     * Sanitize context data for logging (remove sensitive information)
     */
    private function sanitizeLogContext($context) {
        if (!is_array($context)) {
            return $context;
        }
        
        // Remove sensitive data
        $sensitive_keys = ['password', 'token', 'key', 'secret', 'nonce'];
        $sanitized = $context;
        
        foreach ($sensitive_keys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '[REDACTED]';
            }
        }
        
        // Limit size to prevent huge logs
        $json = wp_json_encode($sanitized);
        if (strlen($json) > 2000) {
            return ['message' => 'Context too large for logging', 'size' => strlen($json)];
        }
        
        return $sanitized;
    }
    
    /**
     * Get user IP address safely
     */
    private function getUserIP() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
    
    /**
     * Validate AJAX request with enhanced security
     */
    private function validateAjaxRequest($action) {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pgfe_ajax_nonce')) {
            $this->logError($action, new Exception('Nonce verification failed'), [
                'action' => $action,
                'nonce_provided' => !empty($_POST['nonce']),
                'referer' => wp_get_referer()
            ]);
            wp_die(__('Security check failed', 'pgfe-lite'));
        }
        
        // Check if user can perform this action
        if (!current_user_can('read')) {
            $this->logError($action, new Exception('Insufficient permissions'), [
                'action' => $action,
                'user_id' => get_current_user_id(),
                'user_roles' => wp_get_current_user()->roles ?? []
            ]);
            wp_die(__('Insufficient permissions', 'pgfe-lite'));
        }
        
        // Rate limiting (simple implementation)
        $this->checkRateLimit($action);
        
        return true;
    }
    
    /**
     * Simple rate limiting to prevent abuse
     */
    private function checkRateLimit($action) {
        $user_id = get_current_user_id();
        $ip = $this->getUserIP();
        $key = 'pgfe_rate_limit_' . md5($action . '_' . $user_id . '_' . $ip);
        
        $requests = get_transient($key) ?: 0;
        
        // Allow 60 requests per minute
        if ($requests >= 60) {
            $this->logError($action, new Exception('Rate limit exceeded'), [
                'action' => $action,
                'requests' => $requests,
                'user_id' => $user_id,
                'ip' => $ip
            ]);
            wp_die(__('Too many requests. Please try again later.', 'pgfe-lite'));
        }
        
        set_transient($key, $requests + 1, MINUTE_IN_SECONDS);
    }
    
    /**
     * Check if server is overloaded
     */
    private function isServerOverloaded() {
        // Check memory usage
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $memory_usage = memory_get_usage(true);
        $memory_percent = ($memory_usage / $memory_limit) * 100;
        
        if ($memory_percent > 90) {
            return true;
        }
        
        // Check if too many concurrent requests
        $concurrent_requests = get_transient('pgfe_concurrent_requests') ?: 0;
        if ($concurrent_requests > 50) {
            return true;
        }
        
        // Check database connection
        global $wpdb;
        if (!$wpdb || $wpdb->last_error) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get products with fallback mechanism
     */
    private function getProductsWithFallback($product_query, $query_args) {
        try {
            return $product_query->getProducts($query_args);
        } catch (Exception $e) {
            // Log the error
            $this->logError('getProducts', $e, $query_args);
            
            // Try with simplified query
            $simplified_args = [
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => $query_args['posts_per_page'] ?? 12,
                'meta_query' => [
                    [
                        'key' => '_visibility',
                        'value' => ['catalog', 'visible'],
                        'compare' => 'IN'
                    ]
                ]
            ];
            
            try {
                return $product_query->getProducts($simplified_args);
            } catch (Exception $fallback_e) {
                // Return empty array as last resort
                $this->logError('getProductsFallback', $fallback_e, $simplified_args);
                return [];
            }
        }
    }
    
    /**
      * Get fallback data for error responses
      */
     private function getFallbackData() {
         return [
             'html' => '<div class="pgfe-error-message">' . __('Unable to load products. Please try again.', 'pgfe-lite') . '</div>',
             'total_products' => 0,
             'found_products' => 0,
             'price_range' => ['min' => 0, 'max' => 0],
             'is_fallback' => true
         ];
     }
     
     /**
      * Log JavaScript errors from frontend
      */
     public function logJavaScriptError() {
         // Logging d'erreur JS désactivé pour la production
        
        wp_send_json_success(['logged' => true]);
    }
    
    /**
     * Handle legacy filter request (compatibility)
     */
    private function handleLegacyFilterRequest($filters, $settings) {
        // Traitement de la requête legacy
        
        try {
            // Initialisation des classes nécessaires
            $product_query = new ProductQuery();
            $product_formatter = new ProductFormatter();
            $grid_renderer = new GridRenderer();
            
            // Construction des arguments de requête
            $query_args = $this->buildQueryArgs($filters, $settings);
            
            // Récupération des produits
            $products = $this->getProductsWithFallback($product_query, $query_args);
        } catch (Exception $e) {
            // Erreur de filtre legacy gérée
            throw $e;
        }
        
        // Format products
        $formatted_products = [];
        foreach ($products as $product) {
            try {
                // Convert WC_Product to WP_Post if needed
                if ($product instanceof \WC_Product) {
                    $post = get_post($product->get_id());
                } else {
                    $post = $product;
                }
                
                $formatted_product = $product_formatter->formatProduct($post);
                if ($formatted_product) {
                    $formatted_products[] = $formatted_product;
                }
            } catch (Exception $e) {
                // Log individual product formatting errors but continue
                $this->logError('formatProduct', $e, ['product_id' => $product->ID ?? 'unknown']);
                continue;
            }
        }
        
        // Render products (without grid wrapper to avoid nesting)
        $html = '';
        foreach ($formatted_products as $product) {
            try {
                $html .= $grid_renderer->renderProductCard($product, $settings);
            } catch (Exception $e) {
                // Log rendering errors but continue
                $this->logError('renderProductCard', $e, ['product' => $product]);
                continue;
            }
        }
        
        // Get additional data
        $price_range = $product_query->getPriceRange($query_args);
        $found_products_count = count($formatted_products);
        
        wp_send_json_success([
            'html' => $html,
            'total_products' => $found_products_count,
            'price_range' => $price_range,
            'found_products' => $found_products_count,
            'cache_timestamp' => time()
        ]);
    }
    
    /**
     * Render products for simple grid widget
     */
    private function renderProductsForSimpleGrid($products, $grid_id) {
        if (empty($products)) {
            return '<div class="pgfe-no-products">' . __('Aucun produit trouvé avec ces filtres.', 'pgfe-lite') . '</div>';
        }
        
        $html = '';
        
        foreach ($products as $product) {
            // Convert WC_Product to WP_Post if needed
            if ($product instanceof \WC_Product) {
                $product_id = $product->get_id();
                $title = $product->get_name();
                $price = $product->get_price_html();
                $link = get_permalink($product_id);
            } else {
                $product_id = $product->ID;
                $title = $product->post_title;
                $wc_product = wc_get_product($product_id);
                $price = $wc_product ? $wc_product->get_price_html() : '';
                $link = get_permalink($product_id);
            }
            
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
            
            $html .= '<div class="pgfe-grid-item" data-product-id="' . esc_attr($product_id) . '">';
            $html .= '<a href="' . esc_url($link) . '" class="pgfe-product-link">';
            
            if ($image) {
                $html .= '<div class="pgfe-product-image">';
                $html .= '<img src="' . esc_url($image[0]) . '" alt="' . esc_attr($title) . '" loading="lazy">';
                $html .= '</div>';
            }
            
            $html .= '<div class="pgfe-product-content">';
            $html .= '<h3 class="pgfe-product-title">' . esc_html($title) . '</h3>';
            $html .= '<div class="pgfe-product-price">' . $price . '</div>';
            $html .= '</div>';
            
            $html .= '</a>';
            $html .= '</div>';
        }
        
        return $html;
    }
}