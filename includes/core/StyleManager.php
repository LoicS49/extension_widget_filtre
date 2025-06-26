<?php
/**
 * Style Manager - Gestionnaire centralisé des styles CSS
 * 
 * Cette classe gère l'inclusion et la configuration de tous les styles CSS
 * du plugin PGFE Lite de manière centralisée.
 * 
 * @package PGFE_Lite
 * @since 1.0.0
 */

namespace PGFE_Lite\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class StyleManager {
    
    /**
     * Instance unique de la classe
     */
    private static $instance = null;
    
    /**
     * Configuration des styles
     */
    private $style_config = [];
    
    /**
     * Styles inline générés dynamiquement
     */
    private $inline_styles = [];
    
    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct() {
        $this->init_style_config();
        $this->init_hooks();
    }
    
    /**
     * Récupère l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialise la configuration des styles
     */
    private function init_style_config() {
        $this->style_config = [
            'main' => [
                'handle' => 'pgfe-centralized-styles',
                'file' => 'pgfe-centralized-styles.css',
                'deps' => [],
                'version' => PGFE_LITE_VERSION ?? '1.0.0',
                'media' => 'all'
            ]
        ];
    }
    
    /**
     * Initialise les hooks WordPress
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_elementor_styles']);
        add_action('wp_head', [$this, 'output_inline_styles'], 100);
    }
    
    /**
     * Enregistre les styles pour le frontend
     */
    public function enqueue_styles() {
        $this->enqueue_main_styles();
        $this->enqueue_conditional_styles();
    }
    
    /**
     * Enregistre les styles pour l'admin
     */
    public function enqueue_admin_styles() {
        // Styles spécifiques à l'admin si nécessaire
        $this->enqueue_main_styles();
    }
    
    /**
     * Enregistre les styles pour Elementor
     */
    public function enqueue_elementor_styles() {
        $this->enqueue_main_styles();
    }
    
    /**
     * Enregistre les styles principaux
     */
    private function enqueue_main_styles() {
        $config = $this->style_config['main'];
        
        wp_enqueue_style(
            $config['handle'],
            $this->get_style_url($config['file']),
            $config['deps'],
            $config['version'],
            $config['media']
        );
    }
    
    /**
     * Enregistre les styles conditionnels
     */
    private function enqueue_conditional_styles() {
        // Style legacy si activé
        if ($this->is_legacy_mode_enabled()) {
            $config = $this->style_config['legacy'];
            wp_enqueue_style(
                $config['handle'],
                $this->get_style_url($config['file']),
                [$this->style_config['main']['handle']],
                $config['version'],
                $config['media']
            );
        }
    }
    
    /**
     * Génère l'URL d'un fichier de style
     */
    private function get_style_url($filename) {
        // Utiliser la constante PGFE_LITE_PLUGIN_URL pour garantir le bon chemin
        return PGFE_LITE_PLUGIN_URL . 'assets/css/' . $filename;
    }
    
    /**
     * Vérifie si le mode legacy est activé
     */
    private function is_legacy_mode_enabled() {
        return get_option('pgfe_legacy_styles_enabled', false);
    }
    
    /**
     * Ajoute des styles inline
     */
    public function add_inline_style($selector, $properties, $context = 'global') {
        if (!isset($this->inline_styles[$context])) {
            $this->inline_styles[$context] = [];
        }
        
        $this->inline_styles[$context][$selector] = $properties;
    }
    
    /**
     * Génère des styles inline pour un widget Elementor
     */
    public function generate_elementor_styles($widget_id, $settings) {
        $styles = [];
        
        // Styles de conteneur
        if (!empty($settings['container_background_background'])) {
            $styles["#{$widget_id} .pgfe-widget-container"] = [
                'background' => $this->get_background_css($settings, 'container_background')
            ];
        }
        
        // Styles de typographie
        if (!empty($settings['title_typography_typography'])) {
            $styles["#{$widget_id} .pgfe-widget-title"] = 
                $this->get_typography_css($settings, 'title_typography');
        }
        
        // Styles de couleur
        if (!empty($settings['title_color'])) {
            $styles["#{$widget_id} .pgfe-widget-title"]['color'] = $settings['title_color'];
        }
        
        // Styles responsive
        $responsive_styles = $this->generate_responsive_styles($widget_id, $settings);
        
        return array_merge($styles, $responsive_styles);
    }
    
    /**
     * Génère les styles responsive
     */
    private function generate_responsive_styles($widget_id, $settings) {
        $styles = [];
        $breakpoints = [
            'mobile' => '(max-width: 767px)',
            'tablet' => '(min-width: 768px) and (max-width: 1024px)',
            'desktop' => '(min-width: 1025px)'
        ];
        
        foreach ($breakpoints as $device => $media_query) {
            $device_styles = [];
            
            // Marges responsive
            foreach (['margin', 'padding'] as $property) {
                $value = $this->get_responsive_value($settings, "container_{$property}", $device);
                if ($value) {
                    $device_styles["#{$widget_id} .pgfe-widget-container"][$property] = $value;
                }
            }
            
            if (!empty($device_styles)) {
                $styles["@media {$media_query}"] = $device_styles;
            }
        }
        
        return $styles;
    }
    
    /**
     * Récupère une valeur responsive
     */
    private function get_responsive_value($settings, $key, $device) {
        $value = $settings["{$key}_{$device}"] ?? $settings[$key] ?? null;
        
        if (is_array($value)) {
            return $this->format_spacing_value($value);
        }
        
        return $value;
    }
    
    /**
     * Formate une valeur d'espacement
     */
    private function format_spacing_value($value) {
        if (!is_array($value)) {
            return $value;
        }
        
        $unit = $value['unit'] ?? 'px';
        $values = [];
        
        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            $values[] = ($value[$side] ?? 0) . $unit;
        }
        
        return implode(' ', $values);
    }
    
    /**
     * Génère le CSS de background
     */
    private function get_background_css($settings, $prefix) {
        $background_type = $settings["{$prefix}_background"] ?? 'classic';
        
        switch ($background_type) {
            case 'classic':
                return $settings["{$prefix}_color"] ?? 'transparent';
                
            case 'gradient':
                return $this->get_gradient_css($settings, $prefix);
                
            default:
                return 'transparent';
        }
    }
    
    /**
     * Génère le CSS de gradient
     */
    private function get_gradient_css($settings, $prefix) {
        $type = $settings["{$prefix}_gradient_type"] ?? 'linear';
        $angle = $settings["{$prefix}_gradient_angle"] ?? 180;
        $color_a = $settings["{$prefix}_gradient_color_a"] ?? '#000';
        $color_b = $settings["{$prefix}_gradient_color_b"] ?? '#fff';
        $location_a = $settings["{$prefix}_gradient_color_a_stop"] ?? 0;
        $location_b = $settings["{$prefix}_gradient_color_b_stop"] ?? 100;
        
        if ($type === 'radial') {
            return "radial-gradient(circle, {$color_a} {$location_a}%, {$color_b} {$location_b}%)";
        }
        
        return "linear-gradient({$angle}deg, {$color_a} {$location_a}%, {$color_b} {$location_b}%)";
    }
    
    /**
     * Génère le CSS de typographie
     */
    private function get_typography_css($settings, $prefix) {
        $css = [];
        
        if (!empty($settings["{$prefix}_font_family"])) {
            $css['font-family'] = $settings["{$prefix}_font_family"];
        }
        
        if (!empty($settings["{$prefix}_font_size"])) {
            $css['font-size'] = $this->format_size_value($settings["{$prefix}_font_size"]);
        }
        
        if (!empty($settings["{$prefix}_font_weight"])) {
            $css['font-weight'] = $settings["{$prefix}_font_weight"];
        }
        
        if (!empty($settings["{$prefix}_line_height"])) {
            $css['line-height'] = $this->format_size_value($settings["{$prefix}_line_height"]);
        }
        
        if (!empty($settings["{$prefix}_letter_spacing"])) {
            $css['letter-spacing'] = $this->format_size_value($settings["{$prefix}_letter_spacing"]);
        }
        
        return $css;
    }
    
    /**
     * Formate une valeur de taille
     */
    private function format_size_value($value) {
        if (is_array($value)) {
            $size = $value['size'] ?? 0;
            $unit = $value['unit'] ?? 'px';
            return $size . $unit;
        }
        
        return $value;
    }
    
    /**
     * Convertit un tableau de styles en CSS
     */
    public function array_to_css($styles_array) {
        $css = '';
        
        foreach ($styles_array as $selector => $properties) {
            if (strpos($selector, '@media') === 0) {
                $css .= $selector . ' {';
                $css .= $this->array_to_css($properties);
                $css .= '}';
            } else {
                $css .= $selector . ' {';
                foreach ($properties as $property => $value) {
                    if (!empty($value)) {
                        $css .= $property . ': ' . $value . ';';
                    }
                }
                $css .= '}';
            }
        }
        
        return $css;
    }
    
    /**
     * Affiche les styles inline
     */
    public function output_inline_styles() {
        if (empty($this->inline_styles)) {
            return;
        }
        
        echo "<style id='pgfe-inline-styles'>\n";
        
        foreach ($this->inline_styles as $context => $styles) {
            if (!empty($styles)) {
                echo "/* Context: {$context} */\n";
                echo $this->array_to_css($styles);
                echo "\n";
            }
        }
        
        echo "</style>\n";
    }
    
    /**
     * Nettoie les styles inline
     */
    public function clear_inline_styles($context = null) {
        if ($context) {
            unset($this->inline_styles[$context]);
        } else {
            $this->inline_styles = [];
        }
    }
    
    /**
     * Active le mode legacy
     */
    public function enable_legacy_mode() {
        update_option('pgfe_legacy_styles_enabled', true);
    }
    
    /**
     * Désactive le mode legacy
     */
    public function disable_legacy_mode() {
        update_option('pgfe_legacy_styles_enabled', false);
    }
    
    /**
     * Génère les variables CSS personnalisées
     */
    public function generate_css_variables($settings = []) {
        $variables = [
            '--pgfe-primary-color' => $settings['primary_color'] ?? '#007cba',
            '--pgfe-secondary-color' => $settings['secondary_color'] ?? '#50575e',
            '--pgfe-accent-color' => $settings['accent_color'] ?? '#0073aa',
            '--pgfe-border-radius' => $settings['border_radius'] ?? '4px',
            '--pgfe-spacing-lg' => $settings['spacing_large'] ?? '20px',
            '--pgfe-transition-normal' => $settings['transition_speed'] ?? '0.3s ease'
        ];
        
        $css = ':root {';
        foreach ($variables as $property => $value) {
            $css .= $property . ': ' . $value . ';';
        }
        $css .= '}';
        
        return $css;
    }
    
    /**
     * Optimise et minifie le CSS
     */
    public function minify_css($css) {
        // Supprime les commentaires
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Supprime les espaces inutiles
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
        
        // Supprime les espaces autour des caractères spéciaux
        $css = str_replace([' {', '{ ', ' }', '} ', ': ', ' :', '; ', ' ;'], ['{', '{', '}', '}', ':', ':', ';', ';'], $css);
        
        return trim($css);
    }
    
    /**
     * Génère un hash pour le cache des styles
     */
    public function generate_style_hash($content) {
        return md5($content . PGFE_LITE_VERSION);
    }
    
    /**
     * Met en cache les styles générés
     */
    public function cache_styles($key, $styles, $expiration = 3600) {
        $cache_key = 'pgfe_styles_' . $key;
        set_transient($cache_key, $styles, $expiration);
    }
    
    /**
     * Récupère les styles depuis le cache
     */
    public function get_cached_styles($key) {
        $cache_key = 'pgfe_styles_' . $key;
        return get_transient($cache_key);
    }
    
    /**
     * Vide le cache des styles
     */
    public function clear_style_cache() {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pgfe_styles_%'"
        );
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pgfe_styles_%'"
        );
    }
}

// Initialisation automatique
StyleManager::get_instance();