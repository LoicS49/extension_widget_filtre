<?php
namespace PGFE_Lite\Core;

/**
 * Classe pour centraliser la récupération des options
 * 
 * Cette classe fournit toutes les méthodes get_*_options() utilisées
 * par les widgets pour éviter la duplication de code.
 * 
 * @since 1.0.0
 */
class OptionsProvider {
    
    /**
     * Instance singleton
     * 
     * @var OptionsProvider
     */
    private static $instance = null;
    
    /**
     * Cache des options
     * 
     * @var array
     */
    private $cache = [];
    
    /**
     * Durée du cache en secondes
     * 
     * @var int
     */
    private $cache_duration = 3600; // 1 heure
    
    /**
     * Récupère l'instance singleton
     * 
     * @return OptionsProvider
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructeur privé pour le singleton
     */
    private function __construct() {
        // Vider le cache lors de certaines actions
        add_action('save_post', [$this, 'clear_cache']);
        add_action('delete_post', [$this, 'clear_cache']);
        add_action('created_term', [$this, 'clear_cache']);
        add_action('edited_term', [$this, 'clear_cache']);
        add_action('delete_term', [$this, 'clear_cache']);
    }
    
    /**
     * Récupère les options de produits
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_product_options($args = []) {
        $cache_key = 'product_options_' . md5(serialize($args));
        
        if ($cached = $this->get_cached($cache_key)) {
            return $cached;
        }
        
        $defaults = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_visibility',
                    'value' => ['hidden', 'search'],
                    'compare' => 'NOT IN'
                ]
            ]
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $products = get_posts($args);
        $options = [];
        
        foreach ($products as $product) {
            $wc_product = wc_get_product($product->ID);
            if (!$wc_product) {
                continue;
            }
            
            $options[] = [
                'value' => $product->ID,
                'label' => $product->post_title,
                'price' => $wc_product->get_price(),
                'stock_status' => $wc_product->get_stock_status(),
                'featured' => $wc_product->is_featured(),
                'on_sale' => $wc_product->is_on_sale(),
                'count' => 1
            ];
        }
        
        $this->set_cache($cache_key, $options);
        
        return apply_filters('pgfe_product_options', $options, $args);
    }
    
    /**
     * Récupère les options de catégories
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_category_options($args = []) {
        $cache_key = 'category_options_' . md5(serialize($args));
        
        if ($cached = $this->get_cached($cache_key)) {
            return $cached;
        }
        
        $defaults = [
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'hierarchical' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $terms = get_terms($args);
        $options = [];
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[] = [
                    'value' => $term->term_id,
                    'label' => $term->name,
                    'slug' => $term->slug,
                    'count' => $term->count,
                    'parent' => $term->parent,
                    'level' => $this->get_term_level($term->term_id, $args['taxonomy'])
                ];
            }
            
            // Organiser hiérarchiquement si demandé
            if ($args['hierarchical']) {
                $options = $this->organize_hierarchical_terms($options);
            }
        }
        
        $this->set_cache($cache_key, $options);
        
        return apply_filters('pgfe_category_options', $options, $args);
    }
    
    /**
     * Récupère les options de tags
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_tag_options($args = []) {
        $cache_key = 'tag_options_' . md5(serialize($args));
        
        if ($cached = $this->get_cached($cache_key)) {
            return $cached;
        }
        
        $defaults = [
            'taxonomy' => 'product_tag',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $terms = get_terms($args);
        $options = [];
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $options[] = [
                    'value' => $term->term_id,
                    'label' => $term->name,
                    'slug' => $term->slug,
                    'count' => $term->count
                ];
            }
        }
        
        $this->set_cache($cache_key, $options);
        
        return apply_filters('pgfe_tag_options', $options, $args);
    }
    
    /**
     * Récupère les options d'attributs
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_attribute_options($args = []) {
        $cache_key = 'attribute_options_' . md5(serialize($args));
        
        if ($cached = $this->get_cached($cache_key)) {
            return $cached;
        }
        
        $attribute_name = $args['attribute'] ?? '';
        
        if (empty($attribute_name)) {
            return [];
        }
        
        $taxonomy = wc_attribute_taxonomy_name($attribute_name);
        
        $defaults = [
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];
        
        $query_args = wp_parse_args($args, $defaults);
        unset($query_args['attribute']); // Retirer le paramètre custom
        
        $terms = get_terms($query_args);
        $options = [];
        
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $option = [
                    'value' => $term->term_id,
                    'label' => $term->name,
                    'slug' => $term->slug,
                    'count' => $term->count
                ];
                
                // Ajouter des métadonnées spécifiques aux attributs
                $color = get_term_meta($term->term_id, 'color', true);
                if (!empty($color)) {
                    $option['color'] = $color;
                }
                
                $image = get_term_meta($term->term_id, 'image', true);
                if (!empty($image)) {
                    $option['image'] = wp_get_attachment_url($image);
                }
                
                $options[] = $option;
            }
        }
        
        $this->set_cache($cache_key, $options);
        
        return apply_filters('pgfe_attribute_options', $options, $args);
    }
    
    /**
     * Récupère les options de vendeurs (si plugin multi-vendor installé)
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_vendor_options($args = []) {
        $cache_key = 'vendor_options_' . md5(serialize($args));
        
        if ($cached = $this->get_cached($cache_key)) {
            return $cached;
        }
        
        $options = [];
        
        // Support pour WC Vendors
        if (class_exists('WC_Vendors')) {
            $vendors = get_users([
                'role' => 'vendor',
                'meta_key' => 'pv_shop_slug',
                'orderby' => 'display_name',
                'order' => 'ASC'
            ]);
            
            foreach ($vendors as $vendor) {
                $shop_name = get_user_meta($vendor->ID, 'pv_shop_name', true);
                $shop_name = !empty($shop_name) ? $shop_name : $vendor->display_name;
                
                $options[] = [
                    'value' => $vendor->ID,
                    'label' => $shop_name,
                    'slug' => get_user_meta($vendor->ID, 'pv_shop_slug', true),
                    'count' => $this->get_vendor_product_count($vendor->ID)
                ];
            }
        }
        
        // Support pour Dokan
        elseif (class_exists('WeDevs_Dokan')) {
            $vendors = get_users([
                'role' => 'seller',
                'orderby' => 'display_name',
                'order' => 'ASC'
            ]);
            
            foreach ($vendors as $vendor) {
                $store_info = dokan_get_store_info($vendor->ID);
                $store_name = !empty($store_info['store_name']) ? $store_info['store_name'] : $vendor->display_name;
                
                $options[] = [
                    'value' => $vendor->ID,
                    'label' => $store_name,
                    'slug' => $store_info['store_slug'] ?? '',
                    'count' => $this->get_vendor_product_count($vendor->ID)
                ];
            }
        }
        
        // Support pour WC Marketplace
        elseif (class_exists('WCMp')) {
            $vendors = get_users([
                'role' => 'dc_vendor',
                'orderby' => 'display_name',
                'order' => 'ASC'
            ]);
            
            foreach ($vendors as $vendor) {
                $vendor_info = get_user_meta($vendor->ID, '_vendor_page_title', true);
                $vendor_name = !empty($vendor_info) ? $vendor_info : $vendor->display_name;
                
                $options[] = [
                    'value' => $vendor->ID,
                    'label' => $vendor_name,
                    'slug' => get_user_meta($vendor->ID, '_vendor_page_slug', true),
                    'count' => $this->get_vendor_product_count($vendor->ID)
                ];
            }
        }
        
        $this->set_cache($cache_key, $options);
        
        return apply_filters('pgfe_vendor_options', $options, $args);
    }
    
    /**
     * Récupère les options de prix (gammes de prix)
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_price_options($args = []) {
        $cache_key = 'price_options_' . md5(serialize($args));
        
        if ($cached = $this->get_cached($cache_key)) {
            return $cached;
        }
        
        $defaults = [
            'ranges' => [
                ['min' => 0, 'max' => 25],
                ['min' => 25, 'max' => 50],
                ['min' => 50, 'max' => 100],
                ['min' => 100, 'max' => 200],
                ['min' => 200, 'max' => 500],
                ['min' => 500, 'max' => null]
            ],
            'currency_symbol' => get_woocommerce_currency_symbol()
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $options = [];
        
        foreach ($args['ranges'] as $range) {
            $min = $range['min'];
            $max = $range['max'];
            
            if ($max === null) {
                $label = sprintf(__('%s%s et plus', 'pgfe-lite'), $args['currency_symbol'], $min);
                $value = $min . '-';
            } else {
                $label = sprintf(__('%s%s - %s%s', 'pgfe-lite'), $args['currency_symbol'], $min, $args['currency_symbol'], $max);
                $value = $min . '-' . $max;
            }
            
            $options[] = [
                'value' => $value,
                'label' => $label,
                'min' => $min,
                'max' => $max,
                'count' => $this->get_price_range_count($min, $max)
            ];
        }
        
        $this->set_cache($cache_key, $options);
        
        return apply_filters('pgfe_price_options', $options, $args);
    }
    
    /**
     * Récupère les options de statut de stock
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_stock_status_options($args = []) {
        $options = [
            [
                'value' => 'instock',
                'label' => __('En stock', 'pgfe-lite'),
                'count' => $this->get_stock_status_count('instock')
            ],
            [
                'value' => 'outofstock',
                'label' => __('Rupture de stock', 'pgfe-lite'),
                'count' => $this->get_stock_status_count('outofstock')
            ],
            [
                'value' => 'onbackorder',
                'label' => __('En réapprovisionnement', 'pgfe-lite'),
                'count' => $this->get_stock_status_count('onbackorder')
            ]
        ];
        
        return apply_filters('pgfe_stock_status_options', $options, $args);
    }
    
    /**
     * Récupère les options de tri
     * 
     * @param array $args Arguments de requête
     * @return array
     */
    public function get_sort_options($args = []) {
        $options = [
            [
                'value' => 'menu_order',
                'label' => __('Tri par défaut', 'pgfe-lite')
            ],
            [
                'value' => 'popularity',
                'label' => __('Tri par popularité', 'pgfe-lite')
            ],
            [
                'value' => 'rating',
                'label' => __('Tri par note moyenne', 'pgfe-lite')
            ],
            [
                'value' => 'date',
                'label' => __('Tri par nouveauté', 'pgfe-lite')
            ],
            [
                'value' => 'price',
                'label' => __('Tri par prix croissant', 'pgfe-lite')
            ],
            [
                'value' => 'price-desc',
                'label' => __('Tri par prix décroissant', 'pgfe-lite')
            ]
        ];
        
        return apply_filters('pgfe_sort_options', $options, $args);
    }
    
    /**
     * Méthodes utilitaires privées
     */
    
    /**
     * Récupère le niveau d'un terme dans la hiérarchie
     * 
     * @param int $term_id
     * @param string $taxonomy
     * @return int
     */
    private function get_term_level($term_id, $taxonomy) {
        $level = 0;
        $term = get_term($term_id, $taxonomy);
        
        while ($term && $term->parent > 0) {
            $level++;
            $term = get_term($term->parent, $taxonomy);
        }
        
        return $level;
    }
    
    /**
     * Organise les termes de manière hiérarchique
     * 
     * @param array $terms
     * @return array
     */
    private function organize_hierarchical_terms($terms) {
        $organized = [];
        $children = [];
        
        // Séparer les parents et les enfants
        foreach ($terms as $term) {
            if ($term['parent'] == 0) {
                $organized[] = $term;
            } else {
                $children[$term['parent']][] = $term;
            }
        }
        
        // Ajouter les enfants après leurs parents
        $result = [];
        foreach ($organized as $parent) {
            $result[] = $parent;
            if (isset($children[$parent['value']])) {
                $result = array_merge($result, $children[$parent['value']]);
            }
        }
        
        return $result;
    }
    
    /**
     * Compte les produits d'un vendeur
     * 
     * @param int $vendor_id
     * @return int
     */
    private function get_vendor_product_count($vendor_id) {
        $count = get_posts([
            'post_type' => 'product',
            'post_status' => 'publish',
            'author' => $vendor_id,
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        
        return count($count);
    }
    
    /**
     * Compte les produits dans une gamme de prix
     * 
     * @param float $min
     * @param float|null $max
     * @return int
     */
    private function get_price_range_count($min, $max = null) {
        global $wpdb;
        
        $sql = "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = 'product' 
                AND p.post_status = 'publish' 
                AND pm.meta_key = '_price' 
                AND CAST(pm.meta_value AS DECIMAL(10,2)) >= %f";
        
        $params = [$min];
        
        if ($max !== null) {
            $sql .= " AND CAST(pm.meta_value AS DECIMAL(10,2)) <= %f";
            $params[] = $max;
        }
        
        return (int) $wpdb->get_var($wpdb->prepare($sql, $params));
    }
    
    /**
     * Compte les produits par statut de stock
     * 
     * @param string $status
     * @return int
     */
    private function get_stock_status_count($status) {
        global $wpdb;
        
        $sql = "SELECT COUNT(DISTINCT p.ID) FROM {$wpdb->posts} p 
                INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
                WHERE p.post_type = 'product' 
                AND p.post_status = 'publish' 
                AND pm.meta_key = '_stock_status' 
                AND pm.meta_value = %s";
        
        return (int) $wpdb->get_var($wpdb->prepare($sql, $status));
    }
    
    /**
     * Méthodes de cache
     */
    
    /**
     * Récupère une valeur du cache
     * 
     * @param string $key
     * @return mixed|false
     */
    private function get_cached($key) {
        $cache_key = 'pgfe_options_' . $key;
        return get_transient($cache_key);
    }
    
    /**
     * Définit une valeur dans le cache
     * 
     * @param string $key
     * @param mixed $value
     */
    private function set_cache($key, $value) {
        $cache_key = 'pgfe_options_' . $key;
        set_transient($cache_key, $value, $this->cache_duration);
    }
    
    /**
     * Vide le cache
     */
    public function clear_cache() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_pgfe_options_%' 
             OR option_name LIKE '_transient_timeout_pgfe_options_%'"
        );
    }
    
    /**
     * Définit la durée du cache
     * 
     * @param int $duration Durée en secondes
     */
    public function set_cache_duration($duration) {
        $this->cache_duration = (int) $duration;
    }
}