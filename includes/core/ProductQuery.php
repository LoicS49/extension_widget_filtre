<?php
/**
 * Product Query Handler
 * 
 * @package PGFE_Lite\Core
 * @since 1.0.0
 */

namespace PGFE_Lite\Core;

if (!defined('ABSPATH')) {
    exit;
}

class ProductQuery {
    
    /**
     * Cache duration in seconds (15 minutes)
     */
    private $cache_duration = 900;
    
    /**
     * Cache group for WordPress object cache
     */
    private $cache_group = 'pgfe_lite_products';
    
    /**
     * Enable/disable caching
     */
    private $cache_enabled = true;
    
    /**
     * Get products based on query arguments
     * 
     * @param array $args Query arguments
     * @return array Array of WC_Product objects
     * @throws Exception When database errors occur
     */
    public function getProducts($args = []) {
        try {
            // Validate input arguments
            if (!is_array($args)) {
                throw new \InvalidArgumentException('Arguments must be an array');
            }
            
            // Check if WooCommerce is active
            if (!function_exists('wc_get_product')) {
                throw new \Exception('WooCommerce is not active');
            }
            
            // Check database connection
            global $wpdb;
            if (!$wpdb || $wpdb->last_error) {
                throw new \Exception('Database connection error: ' . ($wpdb->last_error ?: 'Unknown error'));
            }
            
            // Check cache first
            if ($this->cache_enabled) {
                $cache_key = $this->generateCacheKey('products', $args);
                $cached_products = wp_cache_get($cache_key, $this->cache_group);
                
                if ($cached_products !== false && is_array($cached_products)) {
                    return $cached_products;
                }
            }
        $defaults = [
            'posts_per_page' => 8,
            'post_type' => 'product',
            'post_status' => 'publish',
            'meta_query' => [],
            'tax_query' => [],
            'selection_type' => 'latest'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Handle different selection types
        $this->handleSelectionType($args);
        
        // Handle category filters
        $this->handleCategoryFilters($args);
        
        // Handle vendor filters
        $this->handleVendorFilters($args);
        
        // Handle attribute filters
        $this->handleAttributeFilters($args);
        
        // Handle price filters
        $this->handlePriceFilters($args);
        
        // Handle manual product selection
        $this->handleManualSelection($args);
        
        // Handle exclusions
        $this->handleExclusions($args);
        
            // Sanitize and validate query arguments
            $args = $this->sanitizeQueryArgs($args);
            
            // Execute query with error handling
            $query = new \WP_Query($args);
            
            // Check for query errors
            if (is_wp_error($query)) {
                throw new \Exception('Query error: ' . $query->get_error_message());
            }
            
            $products = [];
            $failed_products = 0;
            
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    
                    try {
                        $product_id = get_the_ID();
                        if (!$product_id) {
                            $failed_products++;
                            continue;
                        }
                        
                        $product = wc_get_product($product_id);
                        if ($product && $product->is_visible()) {
                            $products[] = $product;
                        } else {
                            $failed_products++;
                        }
                    } catch (\Exception $e) {
                        $failed_products++;
                        // Log individual product errors but continue
                        // Échec de chargement du produit géré
                    }
                }
            }
            
            wp_reset_postdata();
            
            // Log if many products failed to load
            // Produits échoués gérés silencieusement
            
            // Validate results
            if (!is_array($products)) {
                $products = [];
            }
            
            // Cache the results
            if ($this->cache_enabled && !empty($products)) {
                wp_cache_set($cache_key, $products, $this->cache_group, $this->cache_duration);
            }
            
            return $products;
            
        } catch (\Exception $e) {
            // Log the error
            // Erreur dans getProducts gérée
            
            // Reset post data in case of error
            wp_reset_postdata();
            
            // Re-throw the exception for handling by calling code
            throw $e;
        }
    }
    
    /**
     * Handle selection type
     */
    private function handleSelectionType(&$args) {
        switch ($args['selection_type']) {
            case 'best_selling':
                $args['meta_key'] = 'total_sales';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
                
            case 'featured':
                $args['meta_query'][] = [
                    'key' => '_featured',
                    'value' => 'yes',
                    'compare' => '='
                ];
                break;
                
            case 'top_rated':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
                
            case 'new_products':
                $args['date_query'] = [
                    [
                        'after' => '30 days ago',
                        'inclusive' => true,
                    ],
                ];
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
                
            case 'promotion':
                $args['meta_query'][] = [
                    'relation' => 'OR',
                    [
                        'key' => '_sale_price',
                        'value' => '',
                        'compare' => '!='
                    ],
                    [
                        'key' => '_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'NUMERIC'
                    ]
                ];
                break;
                
            case 'latest':
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
    }
    
    /**
     * Handle category filters
     */
    private function handleCategoryFilters(&$args) {
        $tax_queries = [];
        
        // Parent categories
        if (!empty($args['parent_categories'])) {
            $tax_queries[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $args['parent_categories'],
                'operator' => 'IN'
            ];
        }
        
        // Child categories
        if (!empty($args['child_categories'])) {
            $operator = isset($args['child_categories_logic']) && $args['child_categories_logic'] === 'and' ? 'AND' : 'IN';
            $tax_queries[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $args['child_categories'],
                'operator' => $operator
            ];
        }
        
        // Category filter (general)
        if (!empty($args['category_filter'])) {
            $tax_queries[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $args['category_filter'],
                'operator' => 'IN'
            ];
        }
        
        if (!empty($tax_queries)) {
            if (count($tax_queries) > 1) {
                $tax_queries['relation'] = 'AND';
            }
            $args['tax_query'] = array_merge($args['tax_query'], $tax_queries);
        }
    }
    
    /**
     * Handle vendor filters
     */
    private function handleVendorFilters(&$args) {
        if (!empty($args['vendor_filter'])) {
            $args['meta_query'][] = [
                'key' => '_wcv_vendor_id',
                'value' => $args['vendor_filter'],
                'compare' => 'IN'
            ];
        }
    }
    
    /**
     * Handle attribute filters
     */
    private function handleAttributeFilters(&$args) {
        if (!empty($args['attribute_filters'])) {
            $tax_queries = [];
            
            foreach ($args['attribute_filters'] as $attribute => $values) {
                if (!empty($values)) {
                    $taxonomy = 'pa_' . $attribute;
                    $operator = isset($args['attribute_logic'][$attribute]) && $args['attribute_logic'][$attribute] === 'and' ? 'AND' : 'IN';
                    
                    $tax_queries[] = [
                        'taxonomy' => $taxonomy,
                        'field' => 'slug',
                        'terms' => $values,
                        'operator' => $operator
                    ];
                }
            }
            
            if (!empty($tax_queries)) {
                if (count($tax_queries) > 1) {
                    $tax_queries['relation'] = 'AND';
                }
                $args['tax_query'] = array_merge($args['tax_query'], $tax_queries);
            }
        }
    }
    
    /**
     * Handle price filters
     */
    private function handlePriceFilters(&$args) {
        $price_meta_query = [];
        
        if (isset($args['min_price']) && $args['min_price'] !== '') {
            $price_meta_query[] = [
                'key' => '_price',
                'value' => floatval($args['min_price']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];
        }
        
        if (isset($args['max_price']) && $args['max_price'] !== '') {
            $price_meta_query[] = [
                'key' => '_price',
                'value' => floatval($args['max_price']),
                'compare' => '<=',
                'type' => 'NUMERIC'
            ];
        }
        
        if (!empty($price_meta_query)) {
            if (count($price_meta_query) > 1) {
                $price_meta_query['relation'] = 'AND';
            }
            $args['meta_query'] = array_merge($args['meta_query'], $price_meta_query);
        }
    }
    
    /**
     * Handle manual product selection
     */
    private function handleManualSelection(&$args) {
        if (!empty($args['manual_products'])) {
            $args['post__in'] = $args['manual_products'];
            $args['orderby'] = 'post__in';
        }
    }
    
    /**
     * Handle exclusions
     */
    private function handleExclusions(&$args) {
        $exclude_posts = [];
        
        // Exclude specific products
        if (!empty($args['exclude_products'])) {
            $exclude_posts = array_merge($exclude_posts, $args['exclude_products']);
        }
        
        // Exclude categories
        if (!empty($args['exclude_categories'])) {
            $excluded_products = get_posts([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => [
                    [
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => $args['exclude_categories'],
                        'operator' => 'IN'
                    ]
                ]
            ]);
            $exclude_posts = array_merge($exclude_posts, $excluded_products);
        }
        
        // Exclude vendors
        if (!empty($args['exclude_vendors'])) {
            $excluded_products = get_posts([
                'post_type' => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_query' => [
                    [
                        'key' => '_wcv_vendor_id',
                        'value' => $args['exclude_vendors'],
                        'compare' => 'IN'
                    ]
                ]
            ]);
            $exclude_posts = array_merge($exclude_posts, $excluded_products);
        }
        
        if (!empty($exclude_posts)) {
            $args['post__not_in'] = array_unique($exclude_posts);
        }
    }
    
    /**
     * Get product count for current query
     */
    public function getProductCount($args = []) {
        $args['posts_per_page'] = -1;
        $args['fields'] = 'ids';
        
        $query = new \WP_Query($args);
        return $query->found_posts;
    }
    
    /**
     * Get price range for current query
     */
    public function getPriceRange($args = []) {
        // Check cache first
        if ($this->cache_enabled) {
            $cache_key = $this->generateCacheKey('price_range', $args);
            $cached_range = wp_cache_get($cache_key, $this->cache_group);
            
            if ($cached_range !== false) {
                return $cached_range;
            }
        }
        
        global $wpdb;
        
        $args['posts_per_page'] = -1;
        $args['fields'] = 'ids';
        
        $query = new \WP_Query($args);
        $product_ids = $query->posts;
        
        if (empty($product_ids)) {
            return ['min' => 0, 'max' => 0];
        }
        
        $ids_string = implode(',', array_map('intval', $product_ids));
        
        $sql = $wpdb->prepare(
            "SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price, 
                    MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price 
             FROM {$wpdb->postmeta} 
             WHERE meta_key = '_price' 
             AND post_id IN ({$ids_string})
             AND meta_value != '' 
             AND meta_value > 0"
        );
        
        $results = $wpdb->get_row($sql);
        
        $price_range = [
            'min' => $results ? floatval($results->min_price) : 0,
            'max' => $results ? floatval($results->max_price) : 0
        ];
        
        // Cache the results
        if ($this->cache_enabled) {
            wp_cache_set($cache_key, $price_range, $this->cache_group, $this->cache_duration);
        }
        
        return $price_range;
    }
    
    /**
     * Generate cache key based on method and arguments
     */
    private function generateCacheKey($method, $args) {
        // Remove sensitive or non-cacheable data
        $cache_args = $this->sanitizeArgsForCache($args);
        
        // Create a hash of the arguments
        $args_hash = md5(serialize($cache_args));
        
        // Include current user ID for user-specific caching if needed
        $user_id = get_current_user_id();
        
        return "pgfe_{$method}_{$args_hash}_{$user_id}";
    }
    
    /**
     * Sanitize arguments for caching (remove non-deterministic values)
     */
    private function sanitizeArgsForCache($args) {
        // Remove or normalize values that shouldn't affect cache
        $sanitized = $args;
        
        // Remove pagination-specific args for certain cache keys
        unset($sanitized['paged']);
        unset($sanitized['offset']);
        
        // Sort arrays to ensure consistent cache keys
        if (isset($sanitized['meta_query']) && is_array($sanitized['meta_query'])) {
            ksort($sanitized['meta_query']);
        }
        
        if (isset($sanitized['tax_query']) && is_array($sanitized['tax_query'])) {
            ksort($sanitized['tax_query']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize and validate query arguments
     * 
     * @param array $args Query arguments
     * @return array Sanitized arguments
     */
    private function sanitizeQueryArgs($args) {
        // Ensure required fields are set
        if (!isset($args['post_type'])) {
            $args['post_type'] = 'product';
        }
        
        if (!isset($args['post_status'])) {
            $args['post_status'] = 'publish';
        }
        
        // Validate and sanitize posts_per_page
        if (isset($args['posts_per_page'])) {
            $args['posts_per_page'] = max(1, min(100, intval($args['posts_per_page'])));
        }
        
        // Validate and sanitize paged
        if (isset($args['paged'])) {
            $args['paged'] = max(1, intval($args['paged']));
        }
        
        // Validate meta_query structure
        if (isset($args['meta_query']) && !is_array($args['meta_query'])) {
            $args['meta_query'] = [];
        }
        
        // Validate tax_query structure
        if (isset($args['tax_query']) && !is_array($args['tax_query'])) {
            $args['tax_query'] = [];
        }
        
        // Sanitize orderby
        if (isset($args['orderby'])) {
            $allowed_orderby = ['date', 'title', 'menu_order', 'rand', 'meta_value', 'meta_value_num', 'price'];
            if (!in_array($args['orderby'], $allowed_orderby)) {
                $args['orderby'] = 'date';
            }
        }
        
        // Sanitize order
        if (isset($args['order'])) {
            $args['order'] = in_array(strtoupper($args['order']), ['ASC', 'DESC']) ? strtoupper($args['order']) : 'DESC';
        }
        
        // Validate post__in and post__not_in
        if (isset($args['post__in']) && !empty($args['post__in'])) {
            $args['post__in'] = array_map('intval', (array) $args['post__in']);
            $args['post__in'] = array_filter($args['post__in']);
        }
        
        if (isset($args['post__not_in']) && !empty($args['post__not_in'])) {
            $args['post__not_in'] = array_map('intval', (array) $args['post__not_in']);
            $args['post__not_in'] = array_filter($args['post__not_in']);
        }
        
        return $args;
    }
    
    /**
     * Clear cache for specific patterns or all cache
     */
    public function clearCache($pattern = null) {
        if ($pattern) {
            // WordPress doesn't support pattern-based cache clearing by default
            // This would require a custom cache implementation or Redis
            // For now, we'll clear all cache for this group
            wp_cache_flush_group($this->cache_group);
        } else {
            wp_cache_flush_group($this->cache_group);
        }
    }
    
    /**
     * Enable or disable caching
     */
    public function setCacheEnabled($enabled) {
        $this->cache_enabled = (bool) $enabled;
    }
    
    /**
     * Get cache statistics (if supported by cache backend)
     */
    public function getCacheStats() {
        // This would depend on the cache backend
        // For basic WordPress cache, we can't get detailed stats
        return [
            'enabled' => $this->cache_enabled,
            'duration' => $this->cache_duration,
            'group' => $this->cache_group
        ];
    }
}