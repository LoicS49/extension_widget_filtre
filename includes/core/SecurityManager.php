<?php

namespace PGFE_Lite\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Security Manager for centralized validation and sanitization
 * 
 * Handles input validation, sanitization, nonce verification, and security checks
 */
class SecurityManager {
    
    /**
     * Rate limiting data
     */
    private static $rate_limits = [];
    
    /**
     * Allowed HTML tags for sanitization
     */
    private static $allowed_html = [
        'a' => [
            'href' => [],
            'title' => [],
            'class' => [],
            'id' => [],
            'target' => []
        ],
        'span' => [
            'class' => [],
            'id' => []
        ],
        'div' => [
            'class' => [],
            'id' => []
        ],
        'strong' => [],
        'em' => [],
        'br' => [],
        'p' => [
            'class' => [],
            'id' => []
        ]
    ];
    
    /**
     * Validate and sanitize AJAX request
     * 
     * @param array $required_fields
     * @param string $nonce_action
     * @return array|WP_Error
     */
    public static function validateAjaxRequest($required_fields = [], $nonce_action = 'pgfe_nonce') {
        // Check if it's an AJAX request
        if (!wp_doing_ajax()) {
            return new \WP_Error('invalid_request', 'Not an AJAX request');
        }
        
        // Verify nonce
        if (!self::verifyNonce($nonce_action)) {
            return new \WP_Error('invalid_nonce', 'Security check failed');
        }
        
        // Check rate limiting
        if (!self::checkRateLimit()) {
            return new \WP_Error('rate_limit_exceeded', 'Too many requests');
        }
        
        // Validate required fields
        $sanitized_data = [];
        foreach ($required_fields as $field => $type) {
            if (!isset($_POST[$field])) {
                return new \WP_Error('missing_field', "Required field '{$field}' is missing");
            }
            
            $sanitized_data[$field] = self::sanitizeInput($_POST[$field], $type);
            
            if ($sanitized_data[$field] === false) {
                return new \WP_Error('invalid_field', "Invalid value for field '{$field}'");
            }
        }
        
        return $sanitized_data;
    }
    
    /**
     * Verify nonce
     * 
     * @param string $action
     * @param string $nonce_field
     * @return bool
     */
    public static function verifyNonce($action = 'pgfe_nonce', $nonce_field = 'nonce') {
        $nonce = $_POST[$nonce_field] ?? $_GET[$nonce_field] ?? '';
        return wp_verify_nonce($nonce, $action);
    }
    
    /**
     * Check rate limiting
     * 
     * @param int $max_requests
     * @param int $time_window
     * @return bool
     */
    public static function checkRateLimit($max_requests = 60, $time_window = 60) {
        $user_ip = self::getUserIP();
        $cache_key = 'pgfe_rate_limit_' . md5($user_ip);
        
        $requests = get_transient($cache_key);
        
        if ($requests === false) {
            set_transient($cache_key, 1, $time_window);
            return true;
        }
        
        if ($requests >= $max_requests) {
            return false;
        }
        
        set_transient($cache_key, $requests + 1, $time_window);
        return true;
    }
    
    /**
     * Sanitize input based on type
     * 
     * @param mixed $input
     * @param string $type
     * @return mixed|false
     */
    public static function sanitizeInput($input, $type) {
        switch ($type) {
            case 'string':
                return sanitize_text_field($input);
                
            case 'email':
                return sanitize_email($input);
                
            case 'url':
                return esc_url_raw($input);
                
            case 'int':
            case 'integer':
                return filter_var($input, FILTER_VALIDATE_INT);
                
            case 'float':
            case 'number':
                return filter_var($input, FILTER_VALIDATE_FLOAT);
                
            case 'bool':
            case 'boolean':
                return filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                
            case 'array':
                if (!is_array($input)) {
                    return false;
                }
                return array_map('sanitize_text_field', $input);
                
            case 'array_int':
                if (!is_array($input)) {
                    return false;
                }
                return array_map('intval', $input);
                
            case 'html':
                return wp_kses($input, self::$allowed_html);
                
            case 'textarea':
                return sanitize_textarea_field($input);
                
            case 'slug':
                return sanitize_title($input);
                
            case 'key':
                return sanitize_key($input);
                
            case 'css_class':
                return self::sanitizeCssClass($input);
                
            case 'css_value':
                return self::sanitizeCssValue($input);
                
            case 'json':
                return self::sanitizeJson($input);
                
            default:
                return sanitize_text_field($input);
        }
    }
    
    /**
     * Sanitize CSS class names
     * 
     * @param string $class
     * @return string
     */
    public static function sanitizeCssClass($class) {
        // Remove any characters that aren't alphanumeric, hyphens, or underscores
        $class = preg_replace('/[^a-zA-Z0-9_-]/', '', $class);
        
        // Ensure it doesn't start with a number
        if (preg_match('/^[0-9]/', $class)) {
            $class = 'class-' . $class;
        }
        
        return $class;
    }
    
    /**
     * Sanitize CSS values
     * 
     * @param string $value
     * @return string
     */
    public static function sanitizeCssValue($value) {
        // Allow common CSS units and values
        $allowed_pattern = '/^[0-9]+(px|em|rem|%|vh|vw|pt|pc|in|cm|mm|ex|ch|vmin|vmax)?$|^(auto|inherit|initial|unset|none|normal)$/i';
        
        if (preg_match($allowed_pattern, $value)) {
            return $value;
        }
        
        // For color values
        if (preg_match('/^#[a-fA-F0-9]{3,6}$/', $value)) {
            return $value;
        }
        
        // For rgb/rgba values
        if (preg_match('/^rgba?\([0-9,\s\.]+\)$/', $value)) {
            return $value;
        }
        
        return '';
    }
    
    /**
     * Sanitize JSON input
     * 
     * @param string $json
     * @return array|false
     */
    public static function sanitizeJson($json) {
        $decoded = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        
        return self::sanitizeArray($decoded);
    }
    
    /**
     * Recursively sanitize array
     * 
     * @param array $array
     * @return array
     */
    public static function sanitizeArray($array) {
        if (!is_array($array)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($array as $key => $value) {
            $clean_key = sanitize_key($key);
            
            if (is_array($value)) {
                $sanitized[$clean_key] = self::sanitizeArray($value);
            } elseif (is_string($value)) {
                $sanitized[$clean_key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                $sanitized[$clean_key] = $value;
            } elseif (is_bool($value)) {
                $sanitized[$clean_key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate user capabilities
     * 
     * @param string $capability
     * @return bool
     */
    public static function validateCapability($capability = 'manage_woocommerce') {
        return current_user_can($capability);
    }
    
    /**
     * Get user IP address safely
     * 
     * @return string
     */
    public static function getUserIP() {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return '127.0.0.1';
    }
    
    /**
     * Sanitize settings array
     * 
     * @param array $settings
     * @param array $schema
     * @return array
     */
    public static function sanitizeSettings($settings, $schema = []) {
        if (!is_array($settings)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach ($settings as $key => $value) {
            $clean_key = sanitize_key($key);
            $type = $schema[$key] ?? 'string';
            
            $sanitized_value = self::sanitizeInput($value, $type);
            
            if ($sanitized_value !== false) {
                $sanitized[$clean_key] = $sanitized_value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file
     * @param array $allowed_types
     * @param int $max_size
     * @return bool|WP_Error
     */
    public static function validateFileUpload($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2097152) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return new \WP_Error('invalid_file', 'Invalid file upload');
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            return new \WP_Error('file_too_large', 'File size exceeds limit');
        }
        
        // Check file type
        $file_type = wp_check_filetype($file['name']);
        
        if (!in_array($file_type['ext'], $allowed_types)) {
            return new \WP_Error('invalid_file_type', 'File type not allowed');
        }
        
        // Additional security checks
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = [
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        
        if (!in_array($mime_type, $allowed_mimes)) {
            return new \WP_Error('invalid_mime_type', 'Invalid file content');
        }
        
        return true;
    }
    
    /**
     * Generate secure nonce
     * 
     * @param string $action
     * @return string
     */
    public static function generateNonce($action = 'pgfe_nonce') {
        return wp_create_nonce($action);
    }
    
    /**
     * Log security event
     * 
     * @param string $event
     * @param array $context
     */
    public static function logSecurityEvent($event, $context = []) {
        // Logging de sécurité désactivé pour la production
        return;
    }
    
    /**
     * Check if request is from allowed origin
     * 
     * @param array $allowed_origins
     * @return bool
     */
    public static function validateOrigin($allowed_origins = []) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        
        if (empty($origin)) {
            return false;
        }
        
        $site_url = get_site_url();
        $allowed_origins[] = $site_url;
        
        foreach ($allowed_origins as $allowed_origin) {
            if (strpos($origin, $allowed_origin) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize and validate widget settings
     * 
     * @param array $settings
     * @return array
     */
    public static function sanitizeWidgetSettings($settings) {
        $schema = [
            'widget_title' => 'string',
            'posts_per_page' => 'int',
            'columns' => 'int',
            'show_count' => 'bool',
            'show_hierarchy' => 'bool',
            'hide_empty' => 'bool',
            'orderby' => 'string',
            'order' => 'string',
            'include_categories' => 'array_int',
            'exclude_categories' => 'array_int',
            'custom_css_class' => 'css_class',
            'widget_style' => 'string',
            'image_size' => 'string',
            'button_text' => 'string',
            'ajax_enabled' => 'bool'
        ];
        
        $sanitized = self::sanitizeSettings($settings, $schema);
        
        // Additional validation for specific fields
        if (isset($sanitized['widget_style'])) {
            $allowed_styles = ['default', 'modern', 'minimal', 'card'];
            if (!in_array($sanitized['widget_style'], $allowed_styles)) {
                $sanitized['widget_style'] = 'default';
            }
        }
        
        if (isset($sanitized['orderby'])) {
            $allowed_orderby = ['name', 'count', 'term_id', 'slug', 'term_group'];
            if (!in_array($sanitized['orderby'], $allowed_orderby)) {
                $sanitized['orderby'] = 'name';
            }
        }
        
        if (isset($sanitized['order'])) {
            $allowed_order = ['ASC', 'DESC'];
            if (!in_array(strtoupper($sanitized['order']), $allowed_order)) {
                $sanitized['order'] = 'ASC';
            }
        }
        
        return $sanitized;
    }
}