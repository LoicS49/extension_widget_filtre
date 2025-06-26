<?php
namespace PGFE_Lite\Elementor\Base;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use PGFE_Lite\Elementor\Traits\StyleControlsTrait;
use Exception;

/**
 * Base class for all PGFE filter widgets
 * 
 * This class centralizes common methods for all widgets
 * to avoid code duplication and improve maintainability.
 * 
 * @since 1.0.0
 */
abstract class BaseFilterWidget extends Widget_Base {
    
    use StyleControlsTrait;
    
    /**
     * Widget version for cache busting
     */
    protected $widget_version = '1.0.0';
    
    /**
     * CSS class prefix for consistent naming
     */
    protected $css_prefix = 'pgfe';
    
    /**
     * Default widget settings
     */
    protected $default_settings = [];
    
    /**
     * Abstract methods to be implemented by each widget
     */
    abstract protected function get_widget_name();
    abstract protected function get_widget_title();
    abstract protected function get_widget_icon();
    abstract protected function register_widget_controls();
    abstract protected function render_widget();
    
    /**
     * Méthodes communes implémentées
     */
    
    /**
     * Récupère le nom du widget
     * 
     * @return string
     */
    public function get_name() {
        return $this->get_widget_name();
    }
    
    /**
     * Récupère le titre du widget
     * 
     * @return string
     */
    public function get_title() {
        return $this->get_widget_title();
    }
    
    /**
     * Get widget icon
     * 
     * @return string
     */
    public function get_icon() {
        return $this->get_widget_icon();
    }
    
    /**
     * Get widget categories
     * 
     * @return array
     */
    public function get_categories() {
        return ['pgfe-filters'];
    }
    
    /**
     * Get widget keywords for search
     * 
     * @return array
     */
    public function get_keywords() {
        return ['filter', 'product', 'woocommerce', 'pgfe'];
    }
    
    /**
     * Get widget help URL
     * 
     * @return string
     */
    public function get_custom_help_url() {
        return 'https://le-local-des-artisans.com/docs/pgfe-lite/';
    }
    

    

    
    /**
     * Récupère les dépendances de script
     * 
     * @return array
     */
    public function get_script_depends() {
        return ['pgfe-lite'];
    }
    
    /**
     * Récupère les dépendances de style
     * 
     * @return array
     */
    public function get_style_depends() {
        return ['pgfe-centralized-styles'];
    }
    
    /**
     * Enregistre les contrôles du widget
     */
    protected function register_controls() {
        try {
            // Ensure proper widget initialization
            if (!$this->is_widget_properly_initialized()) {
                return;
            }
            
            $this->register_widget_controls();
            // Note: Les contrôles de style communs sont maintenant gérés individuellement
            // par chaque widget pour éviter les conflits de noms
        } catch (Exception $e) {
            // Erreur de contrôles de widget gérée
        }
    }
    
    /**
     * Check if widget is properly initialized
     * 
     * @return bool
     */
    private function is_widget_properly_initialized() {
        return method_exists($this, 'get_widget_name') && 
               method_exists($this, 'register_widget_controls') &&
               !empty($this->get_widget_name());
    }
    
    /**
     * Démarre un onglet de contrôles de manière sécurisée
     * 
     * @param string $tab_id
     * @param array $args
     */
    protected function safe_start_controls_tab($tab_id, $args = []) {
        try {
            // S'assurer que les propriétés requises existent
            if (!isset($args['label'])) {
                $args['label'] = ucfirst(str_replace('_', ' ', $tab_id));
            }
            
            // Ensure tab has proper structure for Elementor
            $args = wp_parse_args($args, [
                'label' => $args['label'],
            ]);
            
            $this->start_controls_tab($tab_id, $args);
        } catch (Exception $e) {
            // Erreur d'onglet gérée
        }
    }
    
    /**
     * Safe method to start controls tabs with validation
     * 
     * @param string $tabs_id
     * @param array $args
     */
    protected function safe_start_controls_tabs($tabs_id, $args = []) {
        try {
            // Validate tabs_id
            if (empty($tabs_id) || !is_string($tabs_id)) {
                // ID d'onglet invalide géré
                return;
            }
            
            // Ensure proper structure for Elementor
            $args = wp_parse_args($args, []);
            
            $this->start_controls_tabs($tabs_id, $args);
        } catch (Exception $e) {
            // Erreur d'onglets gérée
        }
    }
    
    /**
     * Rend le widget
     */
    protected function render() {
        try {
            // Ensure widget has proper data structure
            if (!$this->validate_widget_data()) {
                return;
            }
            
            $this->safe_render();
        } catch (Exception $e) {
            // Erreur de rendu gérée
        }
    }
    
    /**
     * Validate widget data structure
     * 
     * @return bool
     */
    private function validate_widget_data() {
        // Check if widget has required methods
        if (!method_exists($this, 'render_widget')) {
            // Méthode render_widget manquante gérée
            return false;
        }
        
        // Ensure widget settings are properly initialized
        $settings = $this->get_settings_for_display();
        if (!is_array($settings)) {
            // Structure de paramètres invalide gérée
            return false;
        }
        
        return true;
    }
    
    /**
     * Safe render method with error handling
     * This method should be called by the actual render() method in child classes
     */
    protected function safe_render() {
        try {
            // Check dependencies first
            $dependency_check = $this->check_dependencies();
            if ($dependency_check !== true) {
                $this->render_error($dependency_check, 'warning');
                return;
            }
            
            // Get and validate settings
            $settings = $this->get_settings_for_display();
            
            // Add widget wrapper with consistent classes
            $wrapper_class = $this->get_css_class('widget', [$this->get_widget_name()]);
            
            echo '<div class="' . esc_attr($wrapper_class) . '" data-widget="' . esc_attr($this->get_widget_name()) . '">';
            
            // Call the actual widget render method
            $this->render_widget();
            
            echo '</div>';
            
        } catch (Exception $e) {
            // Erreur de widget gérée
            
            // Show user-friendly error message
            $this->render_error(
                __('An error occurred while rendering this widget.', 'pgfe-lite'),
                'error'
            );
        }
    }
    
    /**
     * Rend le début du wrapper du widget
     * 
     * @param array $settings
     */
    protected function render_widget_wrapper_start($settings) {
        $classes = $this->get_widget_classes($settings);
        $attributes = $this->get_widget_attributes($settings);
        
        echo '<div class="' . esc_attr(implode(' ', $classes)) . '"';
        
        foreach ($attributes as $key => $value) {
            echo ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
        
        echo '>';
        
        // Titre du widget si défini
        if (!empty($settings['widget_title'])) {
            echo '<h3 class="pgfe-widget-title">' . esc_html($settings['widget_title']) . '</h3>';
        }
    }
    
    /**
     * Rend la fin du wrapper du widget
     */
    protected function render_widget_wrapper_end() {
        echo '</div>';
    }
    
    /**
     * Récupère les classes CSS du widget
     * 
     * @param array $settings
     * @return array
     */
    protected function get_widget_classes($settings) {
        $classes = [
            'pgfe-filter-widget',
            'pgfe-' . $this->get_widget_name(),
            'elementor-widget-' . $this->get_name()
        ];
        
        // Ajouter des classes conditionnelles
        if (!empty($settings['custom_css_class'])) {
            $classes[] = $settings['custom_css_class'];
        }
        
        if (!empty($settings['widget_style'])) {
            $classes[] = 'pgfe-style-' . $settings['widget_style'];
        }
        
        return apply_filters('pgfe_widget_classes', $classes, $this->get_name(), $settings);
    }
    
    /**
     * Récupère les attributs du widget
     * 
     * @param array $settings
     * @return array
     */
    protected function get_widget_attributes($settings) {
        $attributes = [
            'data-widget' => $this->get_name()
        ];
        
        // Ajouter des attributs conditionnels
        if (!empty($settings['widget_id'])) {
            $attributes['id'] = $settings['widget_id'];
        }
        
        return apply_filters('pgfe_widget_attributes', $attributes, $this->get_name(), $settings);
    }
    
    /**
     * Enregistre les contrôles de contenu communs
     */
    protected function register_common_content_controls() {
        $this->start_controls_section(
            'general_section',
            [
                'label' => __('Général', 'pgfe-lite'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'widget_title',
            [
                'label' => __('Titre du Widget', 'pgfe-lite'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Entrez le titre...', 'pgfe-lite'),
            ]
        );
        
        $this->add_control(
            'widget_style',
            [
                'label' => __('Style du Widget', 'pgfe-lite'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __('Par défaut', 'pgfe-lite'),
                    'modern' => __('Moderne', 'pgfe-lite'),
                    'minimal' => __('Minimal', 'pgfe-lite'),
                    'card' => __('Carte', 'pgfe-lite'),
                ],
            ]
        );
        
        $this->add_control(
            'custom_css_class',
            [
                'label' => __('Classe CSS Personnalisée', 'pgfe-lite'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'title' => __('Ajoutez votre classe CSS personnalisée. Séparez les classes multiples par des espaces.', 'pgfe-lite'),
            ]
        );
        
        $this->end_controls_section();
    }
    

    
    /**
     * Valide les paramètres du widget
     * 
     * @param array $settings
     * @return array
     */
    protected function validate_settings($settings) {
        // Validation de base
        $validated = [];
        
        foreach ($settings as $key => $value) {
            switch ($key) {
                case 'widget_title':
                    $validated[$key] = sanitize_text_field($value);
                    break;
                    
                case 'custom_css_class':
                    $validated[$key] = sanitize_html_class($value);
                    break;
                    
                case 'widget_style':
                    $allowed_styles = ['default', 'modern', 'minimal', 'card'];
                    $validated[$key] = in_array($value, $allowed_styles) ? $value : 'default';
                    break;
                    
                default:
                    $validated[$key] = $value;
                    break;
            }
        }
        
        return apply_filters('pgfe_validate_widget_settings', $validated, $this->get_name());
    }
    
    /**
     * Get validated settings for display
     * 
     * @return array
     */
    protected function get_validated_settings() {
        $settings = $this->get_settings_for_display();
        return $this->validate_settings($settings);
    }
    
    /**
     * Ajoute des actions et filtres spécifiques au widget
     */
    protected function add_widget_hooks() {
        // Hook pour permettre aux développeurs d'ajouter du contenu avant le widget
        add_action('pgfe_before_widget_' . $this->get_name(), [$this, 'before_widget_content']);
        
        // Hook pour permettre aux développeurs d'ajouter du contenu après le widget
        add_action('pgfe_after_widget_' . $this->get_name(), [$this, 'after_widget_content']);
    }
    
    /**
     * Contenu avant le widget (hook)
     */
    public function before_widget_content() {
        // Implémentation par défaut vide
        // Les widgets enfants peuvent surcharger cette méthode
    }
    
    /**
     * Contenu après le widget (hook)
     */
    public function after_widget_content() {
        // Implémentation par défaut vide
        // Les widgets enfants peuvent surcharger cette méthode
    }
    
    /**
     * Constructeur
     */
    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);
        $this->add_widget_hooks();
    }
    
    /**
     * Generate consistent CSS classes for widget elements
     * 
     * @param string $element Element name
     * @param array $modifiers Additional modifiers
     * @return string
     */
    protected function get_css_class($element = '', $modifiers = []) {
        $classes = [$this->css_prefix];
        
        if (!empty($element)) {
            $classes[] = $this->css_prefix . '-' . $element;
        }
        
        foreach ($modifiers as $modifier) {
            $classes[] = $this->css_prefix . '-' . $element . '--' . $modifier;
        }
        
        return implode(' ', array_filter($classes));
    }
    
    /**
     * Render error message in a consistent way
     * 
     * @param string $message Error message
     * @param string $type Error type (error, warning, info)
     */
    protected function render_error($message, $type = 'error') {
        $css_class = $this->get_css_class('message', [$type]);
        
        echo '<div class="' . esc_attr($css_class) . '">';
        echo '<p>' . esc_html($message) . '</p>';
        echo '</div>';
    }
    
    /**
     * Check if required dependencies are available
     * 
     * @return bool|string True if all dependencies are met, error message otherwise
     */
    protected function check_dependencies() {
        // Check WooCommerce
        if (!class_exists('WooCommerce')) {
            return __('WooCommerce is required for this widget to work.', 'pgfe-lite');
        }
        
        // Check if we're in the frontend and WooCommerce is properly loaded
        if (!is_admin() && !function_exists('wc_get_products')) {
            return __('WooCommerce functions are not available.', 'pgfe-lite');
        }
        
        return true;
    }
    
    /**
     * Generate unique widget ID for JavaScript targeting
     * 
     * @return string
     */
    protected function get_widget_id() {
        return 'pgfe-' . $this->get_widget_name() . '-' . $this->get_id();
    }
    
    /**
     * Enqueue widget-specific assets
     */
    protected function enqueue_widget_assets() {
        // This can be overridden by individual widgets
        // to enqueue specific CSS/JS files
    }
    
    /**
     * Get default control settings for consistency
     * 
     * @param string $control_type
     * @return array
     */
    protected function get_default_control_settings($control_type) {
        $defaults = [
            'text' => [
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'dynamic' => ['active' => true]
            ],
            'textarea' => [
                'type' => Controls_Manager::TEXTAREA,
                'label_block' => true,
                'dynamic' => ['active' => true]
            ],
            'select' => [
                'type' => Controls_Manager::SELECT,
                'label_block' => true
            ],
            'switcher' => [
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'pgfe-lite'),
                'label_off' => __('No', 'pgfe-lite'),
                'return_value' => 'yes'
            ]
        ];
        
        return $defaults[$control_type] ?? [];
    }
}