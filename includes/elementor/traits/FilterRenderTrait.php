<?php
namespace PGFE_Lite\Elementor\Traits;

/**
 * Trait pour centraliser les méthodes de rendu des filtres
 * 
 * Ce trait contient toutes les méthodes de rendu communes utilisées
 * par les différents widgets de filtre pour éviter la duplication.
 * 
 * @since 1.0.0
 */
trait FilterRenderTrait {
    
    /**
     * Rend un filtre en format dropdown
     * 
     * @param array $options Options du filtre
     * @param array $settings Paramètres du widget
     * @param string $filter_type Type de filtre
     */
    protected function render_dropdown($options, $settings, $filter_type) {
        if (empty($options)) {
            return;
        }
        
        $placeholder = $settings['dropdown_placeholder'] ?? __('Sélectionner...', 'pgfe-lite');
        $multiple = $settings['allow_multiple'] ?? false;
        $search = $settings['enable_search'] ?? false;
        
        $classes = [
            'pgfe-filter-dropdown',
            'pgfe-' . $filter_type . '-dropdown'
        ];
        
        if ($search) {
            $classes[] = 'pgfe-searchable';
        }
        
        if ($multiple) {
            $classes[] = 'pgfe-multiple';
        }
        
        echo '<div class="pgfe-dropdown-wrapper">';
        
        echo '<select class="' . esc_attr(implode(' ', $classes)) . '"';
        echo ' data-filter-type="' . esc_attr($filter_type) . '"';
        echo ' data-placeholder="' . esc_attr($placeholder) . '"';
        
        if ($multiple) {
            echo ' multiple';
        }
        
        echo '>';
        
        // Option par défaut
        if (!$multiple) {
            echo '<option value="">' . esc_html($placeholder) . '</option>';
        }
        
        // Rendu des options
        $this->render_dropdown_options($options, $settings);
        
        echo '</select>';
        echo '</div>';
    }
    
    /**
     * Rend les options d'un dropdown
     * 
     * @param array $options
     * @param array $settings
     */
    protected function render_dropdown_options($options, $settings) {
        $show_count = $settings['show_count'] ?? false;
        $hierarchical = $settings['hierarchical'] ?? false;
        
        foreach ($options as $option) {
            $value = $option['value'] ?? '';
            $label = $option['label'] ?? '';
            $count = $option['count'] ?? 0;
            $level = $option['level'] ?? 0;
            
            if (empty($value) || empty($label)) {
                continue;
            }
            
            echo '<option value="' . esc_attr($value) . '"';
            
            if ($hierarchical && $level > 0) {
                echo ' data-level="' . esc_attr($level) . '"';
            }
            
            echo '>';
            
            // Indentation pour la hiérarchie
            if ($hierarchical && $level > 0) {
                echo str_repeat('&nbsp;&nbsp;', $level);
            }
            
            echo esc_html($label);
            
            // Affichage du nombre
            if ($show_count && $count > 0) {
                echo ' (' . esc_html($count) . ')';
            }
            
            echo '</option>';
        }
    }
    
    /**
     * Rend un filtre en format pills/tags
     * 
     * @param array $options Options du filtre
     * @param array $settings Paramètres du widget
     * @param string $filter_type Type de filtre
     */
    protected function render_pills($options, $settings, $filter_type) {
        if (empty($options)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $allow_multiple = $settings['allow_multiple'] ?? true;
        
        $classes = [
            'pgfe-filter-pills',
            'pgfe-' . $filter_type . '-pills'
        ];
        
        if ($allow_multiple) {
            $classes[] = 'pgfe-multiple';
        }
        
        echo '<div class="' . esc_attr(implode(' ', $classes)) . '" data-filter-type="' . esc_attr($filter_type) . '">';
        
        foreach ($options as $option) {
            $this->render_pill_item($option, $settings, $filter_type);
        }
        
        echo '</div>';
    }
    
    /**
     * Rend un élément pill individuel
     * 
     * @param array $option
     * @param array $settings
     * @param string $filter_type
     */
    protected function render_pill_item($option, $settings, $filter_type) {
        $value = $option['value'] ?? '';
        $label = $option['label'] ?? '';
        $count = $option['count'] ?? 0;
        $color = $option['color'] ?? '';
        
        if (empty($value) || empty($label)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        
        echo '<span class="pgfe-pill" data-value="' . esc_attr($value) . '"';
        
        if (!empty($color)) {
            echo ' style="--pill-color: ' . esc_attr($color) . '"';
        }
        
        echo '>';
        echo '<span class="pgfe-pill-label">' . esc_html($label) . '</span>';
        
        if ($show_count && $count > 0) {
            echo '<span class="pgfe-pill-count">(' . esc_html($count) . ')</span>';
        }
        
        echo '</span>';
    }
    
    /**
     * Rend un filtre en format boutons
     * 
     * @param array $options Options du filtre
     * @param array $settings Paramètres du widget
     * @param string $filter_type Type de filtre
     */
    protected function render_buttons($options, $settings, $filter_type) {
        if (empty($options)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $allow_multiple = $settings['allow_multiple'] ?? true;
        $button_style = $settings['button_style'] ?? 'default';
        
        $classes = [
            'pgfe-filter-buttons',
            'pgfe-' . $filter_type . '-buttons',
            'pgfe-button-style-' . $button_style
        ];
        
        if ($allow_multiple) {
            $classes[] = 'pgfe-multiple';
        }
        
        echo '<div class="' . esc_attr(implode(' ', $classes)) . '" data-filter-type="' . esc_attr($filter_type) . '">';
        
        foreach ($options as $option) {
            $this->render_button_item($option, $settings, $filter_type);
        }
        
        echo '</div>';
    }
    
    /**
     * Rend un bouton individuel
     * 
     * @param array $option
     * @param array $settings
     * @param string $filter_type
     */
    protected function render_button_item($option, $settings, $filter_type) {
        $value = $option['value'] ?? '';
        $label = $option['label'] ?? '';
        $count = $option['count'] ?? 0;
        $icon = $option['icon'] ?? '';
        
        if (empty($value) || empty($label)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $show_icons = $settings['show_icons'] ?? false;
        
        echo '<button type="button" class="pgfe-filter-button" data-value="' . esc_attr($value) . '">';
        
        if ($show_icons && !empty($icon)) {
            echo '<i class="' . esc_attr($icon) . '"></i>';
        }
        
        echo '<span class="pgfe-button-label">' . esc_html($label) . '</span>';
        
        if ($show_count && $count > 0) {
            echo '<span class="pgfe-button-count">(' . esc_html($count) . ')</span>';
        }
        
        echo '</button>';
    }
    
    /**
     * Rend un filtre en format checkboxes
     * 
     * @param array $options Options du filtre
     * @param array $settings Paramètres du widget
     * @param string $filter_type Type de filtre
     */
    protected function render_checkboxes($options, $settings, $filter_type) {
        if (empty($options)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $hierarchical = $settings['hierarchical'] ?? false;
        
        $classes = [
            'pgfe-filter-checkboxes',
            'pgfe-' . $filter_type . '-checkboxes'
        ];
        
        if ($hierarchical) {
            $classes[] = 'pgfe-hierarchical';
        }
        
        echo '<div class="' . esc_attr(implode(' ', $classes)) . '" data-filter-type="' . esc_attr($filter_type) . '">';
        
        foreach ($options as $option) {
            $this->render_checkbox_item($option, $settings, $filter_type);
        }
        
        echo '</div>';
    }
    
    /**
     * Rend un élément checkbox individuel
     * 
     * @param array $option
     * @param array $settings
     * @param string $filter_type
     */
    protected function render_checkbox_item($option, $settings, $filter_type) {
        $value = $option['value'] ?? '';
        $label = $option['label'] ?? '';
        $count = $option['count'] ?? 0;
        $level = $option['level'] ?? 0;
        
        if (empty($value) || empty($label)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $hierarchical = $settings['hierarchical'] ?? false;
        
        $checkbox_id = 'pgfe_' . $filter_type . '_' . $value . '_' . uniqid();
        
        echo '<div class="pgfe-checkbox-item"';
        
        if ($hierarchical && $level > 0) {
            echo ' data-level="' . esc_attr($level) . '" style="margin-left: ' . ($level * 20) . 'px;"';
        }
        
        echo '>';
        
        echo '<input type="checkbox" id="' . esc_attr($checkbox_id) . '" value="' . esc_attr($value) . '" class="pgfe-checkbox">';
        echo '<label for="' . esc_attr($checkbox_id) . '" class="pgfe-checkbox-label">';
        echo '<span class="pgfe-checkbox-text">' . esc_html($label) . '</span>';
        
        if ($show_count && $count > 0) {
            echo '<span class="pgfe-checkbox-count">(' . esc_html($count) . ')</span>';
        }
        
        echo '</label>';
        echo '</div>';
    }
    
    /**
     * Rend un filtre en format radio buttons
     * 
     * @param array $options Options du filtre
     * @param array $settings Paramètres du widget
     * @param string $filter_type Type de filtre
     */
    protected function render_radio($options, $settings, $filter_type) {
        if (empty($options)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $radio_name = 'pgfe_' . $filter_type . '_' . uniqid();
        
        echo '<div class="pgfe-filter-radio pgfe-' . esc_attr($filter_type) . '-radio" data-filter-type="' . esc_attr($filter_type) . '">';
        
        // Option "Tous" par défaut
        $all_id = $radio_name . '_all';
        echo '<div class="pgfe-radio-item">';
        echo '<input type="radio" id="' . esc_attr($all_id) . '" name="' . esc_attr($radio_name) . '" value="" class="pgfe-radio" checked>';
        echo '<label for="' . esc_attr($all_id) . '" class="pgfe-radio-label">';
        echo '<span class="pgfe-radio-text">' . __('Tous', 'pgfe-lite') . '</span>';
        echo '</label>';
        echo '</div>';
        
        foreach ($options as $option) {
            $this->render_radio_item($option, $settings, $filter_type, $radio_name);
        }
        
        echo '</div>';
    }
    
    /**
     * Rend un élément radio individuel
     * 
     * @param array $option
     * @param array $settings
     * @param string $filter_type
     * @param string $radio_name
     */
    protected function render_radio_item($option, $settings, $filter_type, $radio_name) {
        $value = $option['value'] ?? '';
        $label = $option['label'] ?? '';
        $count = $option['count'] ?? 0;
        
        if (empty($value) || empty($label)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $radio_id = $radio_name . '_' . $value;
        
        echo '<div class="pgfe-radio-item">';
        echo '<input type="radio" id="' . esc_attr($radio_id) . '" name="' . esc_attr($radio_name) . '" value="' . esc_attr($value) . '" class="pgfe-radio">';
        echo '<label for="' . esc_attr($radio_id) . '" class="pgfe-radio-label">';
        echo '<span class="pgfe-radio-text">' . esc_html($label) . '</span>';
        
        if ($show_count && $count > 0) {
            echo '<span class="pgfe-radio-count">(' . esc_html($count) . ')</span>';
        }
        
        echo '</label>';
        echo '</div>';
    }
    
    /**
     * Rend un filtre en format liste
     * 
     * @param array $options Options du filtre
     * @param array $settings Paramètres du widget
     * @param string $filter_type Type de filtre
     */
    protected function render_list($options, $settings, $filter_type) {
        if (empty($options)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $hierarchical = $settings['hierarchical'] ?? false;
        $allow_multiple = $settings['allow_multiple'] ?? true;
        
        $classes = [
            'pgfe-filter-list',
            'pgfe-' . $filter_type . '-list'
        ];
        
        if ($hierarchical) {
            $classes[] = 'pgfe-hierarchical';
        }
        
        if ($allow_multiple) {
            $classes[] = 'pgfe-multiple';
        }
        
        echo '<ul class="' . esc_attr(implode(' ', $classes)) . '" data-filter-type="' . esc_attr($filter_type) . '">';
        
        foreach ($options as $option) {
            $this->render_list_item($option, $settings, $filter_type);
        }
        
        echo '</ul>';
    }
    
    /**
     * Rend un élément de liste individuel
     * 
     * @param array $option
     * @param array $settings
     * @param string $filter_type
     */
    protected function render_list_item($option, $settings, $filter_type) {
        $value = $option['value'] ?? '';
        $label = $option['label'] ?? '';
        $count = $option['count'] ?? 0;
        $level = $option['level'] ?? 0;
        $children = $option['children'] ?? [];
        
        if (empty($value) || empty($label)) {
            return;
        }
        
        $show_count = $settings['show_count'] ?? false;
        $hierarchical = $settings['hierarchical'] ?? false;
        
        echo '<li class="pgfe-list-item" data-value="' . esc_attr($value) . '"';
        
        if ($hierarchical && $level > 0) {
            echo ' data-level="' . esc_attr($level) . '"';
        }
        
        echo '>';
        
        echo '<a href="#" class="pgfe-list-link" data-value="' . esc_attr($value) . '">';
        echo '<span class="pgfe-list-text">' . esc_html($label) . '</span>';
        
        if ($show_count && $count > 0) {
            echo '<span class="pgfe-list-count">(' . esc_html($count) . ')</span>';
        }
        
        echo '</a>';
        
        // Rendu des enfants si hiérarchique
        if ($hierarchical && !empty($children)) {
            echo '<ul class="pgfe-list-children">';
            foreach ($children as $child) {
                $child['level'] = $level + 1;
                $this->render_list_item($child, $settings, $filter_type);
            }
            echo '</ul>';
        }
        
        echo '</li>';
    }
    
    /**
     * Rend un message d'état (loading, empty, error)
     * 
     * @param string $type Type de message
     * @param string $message Message à afficher
     * @param array $settings Paramètres du widget
     */
    protected function render_status_message($type, $message, $settings = []) {
        $classes = [
            'pgfe-status-message',
            'pgfe-status-' . $type
        ];
        
        echo '<div class="' . esc_attr(implode(' ', $classes)) . '">';
        
        switch ($type) {
            case 'loading':
                echo '<div class="pgfe-loading-spinner"></div>';
                break;
                
            case 'empty':
                echo '<div class="pgfe-empty-icon">📭</div>';
                break;
                
            case 'error':
                echo '<div class="pgfe-error-icon">⚠️</div>';
                break;
        }
        
        echo '<p class="pgfe-status-text">' . esc_html($message) . '</p>';
        echo '</div>';
    }
    
    /**
     * Rend les contrôles de filtre (reset, apply, etc.)
     * 
     * @param array $settings Paramètres du widget
     */
    protected function render_filter_controls($settings) {
        $show_reset = $settings['show_reset_button'] ?? true;
        $show_apply = $settings['show_apply_button'] ?? false;
        $auto_apply = $settings['auto_apply'] ?? true;
        
        if (!$show_reset && !$show_apply) {
            return;
        }
        
        echo '<div class="pgfe-filter-controls">';
        
        if ($show_reset) {
            $reset_text = $settings['reset_button_text'] ?? __('Réinitialiser', 'pgfe-lite');
            echo '<button type="button" class="pgfe-reset-button pgfe-button pgfe-button-secondary">';
            echo esc_html($reset_text);
            echo '</button>';
        }
        
        if ($show_apply && !$auto_apply) {
            $apply_text = $settings['apply_button_text'] ?? __('Appliquer', 'pgfe-lite');
            echo '<button type="button" class="pgfe-apply-button pgfe-button pgfe-button-primary">';
            echo esc_html($apply_text);
            echo '</button>';
        }
        
        echo '</div>';
    }
}