# PGFE Lite - Product Grid Filter Extended for Elementor

Un plugin WordPress puissant qui étend Elementor avec des widgets avancés de filtrage et d'affichage de produits WooCommerce.

## Description

PGFE Lite ajoute une collection complète de widgets Elementor pour créer des grilles de produits sophistiquées avec des fonctionnalités de filtrage avancées. Parfait pour créer des boutiques en ligne modernes et interactives.

## Fonctionnalités

### Widgets Inclus

1. **Price Slider Widget** - Filtre de prix avec curseur interactif
2. **Parent Category Filter Widget** - Filtre par catégories parentes
3. **Child Category Filter Widget** - Filtre par sous-catégories avec chargement dynamique
4. **Archive Category Filter Widget** - Filtre de catégories pour pages d'archives
5. **Vendor Filter Widget** - Filtre par vendeurs (compatible Dokan, WCFM, WC Vendors)
6. **Attribute Filter Widget** - Filtre par attributs avec support des nuanciers
7. **Product Grid Extended Widget** - Grille de produits avancée avec filtres intégrés

### Caractéristiques Principales

- **Interface Moderne** : Design responsive et accessible
- **Filtrage AJAX** : Filtrage en temps réel sans rechargement de page
- **Multi-vendeurs** : Support complet des plateformes multi-vendeurs
- **Personnalisation Avancée** : Contrôles de style complets dans Elementor
- **Performance Optimisée** : Code optimisé pour la vitesse
- **SEO Friendly** : Structure HTML sémantique

## Prérequis

- WordPress 5.0+
- WooCommerce 4.0+
- Elementor 3.0+
- PHP 7.4+

## Installation

1. Téléchargez le plugin
2. Uploadez le dossier `LLDA SHOP-FILTER` dans `/wp-content/plugins/`
3. Activez le plugin dans l'administration WordPress
4. Les widgets apparaîtront dans la catégorie "PGFE Widgets" d'Elementor

## Configuration

### Activation Automatique

Le plugin s'active automatiquement si WooCommerce et Elementor sont installés et activés.

### Widgets Disponibles

Après activation, vous trouverez les widgets dans Elementor sous la catégorie "PGFE Widgets".

## Utilisation

### Price Slider Widget

```
- Glissez le widget dans votre page
- Configurez la plage de prix (automatique ou manuelle)
- Personnalisez l'apparence (couleurs, tailles, etc.)
- Activez l'échelle logarithmique si nécessaire
```

### Category Filter Widgets

```
- Choisissez le type d'affichage (dropdown, liste, pills, etc.)
- Configurez les catégories à inclure/exclure
- Activez l'affichage du nombre de produits
- Personnalisez les styles
```

### Vendor Filter Widget

```
- Sélectionnez le type d'affichage
- Configurez les vendeurs à afficher
- Activez les avatars et évaluations
- Personnalisez l'apparence
```

### Attribute Filter Widget

```
- Sélectionnez l'attribut à filtrer
- Choisissez le type d'affichage (checkbox, nuanciers, etc.)
- Configurez les options d'affichage
- Personnalisez les styles
```

### Product Grid Extended Widget

```
- Configurez la requête de produits
- Définissez la mise en page (colonnes, espacement)
- Activez les filtres intégrés
- Personnalisez l'affichage des produits
- Configurez la pagination
```

## Structure du Plugin

```
LLDA SHOP-FILTER/
├── pgfe-lite.php                 # Fichier principal
├── README.md                     # Documentation
├── assets/
│   ├── css/
│   │   └── pgfe-centralized-styles.css  # Styles CSS consolidés
│   └── js/
│       └── pgfe-lite.js         # Scripts JavaScript
├── includes/
│   ├── elementor/
│   │   ├── ElementorManager.php  # Gestionnaire Elementor
│   │   └── widgets/             # Widgets Elementor
│   │       ├── SimplePriceSliderWidget.php
│   │       ├── ParentCategoryFilterWidget.php
│   │       ├── ChildCategoryFilterWidget.php
│   │       ├── ArchiveCategoryFilterWidget.php
│   │       ├── SimpleVendorFilterWidget.php
│   │       ├── SimpleAttributeFilterWidget.php
│   │       ├── TagFilterWidget.php
│   │       └── SimpleGridWidget.php (unifié)
│   ├── core/
│   │   ├── ProductQuery.php     # Requêtes de produits
│   │   └── ProductFormatter.php # Formatage des données
│   ├── display/
│   │   └── GridRenderer.php     # Rendu des grilles
│   └── ajax/
│       └── AjaxHandler.php      # Gestionnaire AJAX
```

## Hooks et Filtres

### Actions

```php
// Avant le rendu de la grille
do_action('pgfe_before_grid_render', $products, $settings);

// Après le rendu de la grille
do_action('pgfe_after_grid_render', $products, $settings);

// Avant le rendu d'un produit
do_action('pgfe_before_product_render', $product, $settings);

// Après le rendu d'un produit
do_action('pgfe_after_product_render', $product, $settings);
```

### Filtres

```php
// Modifier les arguments de requête
$args = apply_filters('pgfe_query_args', $args, $settings);

// Modifier les produits formatés
$products = apply_filters('pgfe_formatted_products', $products, $settings);

// Modifier les paramètres d'affichage
$display_settings = apply_filters('pgfe_display_settings', $display_settings);

// Modifier le HTML de la grille
$html = apply_filters('pgfe_grid_html', $html, $products, $settings);
```

## Personnalisation CSS

### Classes CSS Principales

```css
/* Conteneur principal */
.pgfe-product-grid-container {}

/* Grille de produits */
.pgfe-product-grid {}

/* Carte de produit */
.pgfe-product-card {}

/* Filtres */
.pgfe-filters {}

/* Slider de prix */
.pgfe-price-slider {}

/* Filtres de catégories */
.pgfe-category-filter {}

/* Filtres de vendeurs */
.pgfe-vendor-filter {}

/* Filtres d'attributs */
.pgfe-attribute-filter {}
```

### Variables CSS Personnalisables

```css
:root {
  --pgfe-primary-color: #007cba;
  --pgfe-secondary-color: #50575e;
  --pgfe-border-color: #ddd;
  --pgfe-border-radius: 4px;
  --pgfe-spacing: 20px;
  --pgfe-transition: 0.3s ease;
}
```

## JavaScript API

### Événements Personnalisés

```javascript
// Filtres appliqués
document.addEventListener('pgfe:filters-applied', function(e) {
    console.log('Filtres appliqués:', e.detail);
});

// Produits chargés
document.addEventListener('pgfe:products-loaded', function(e) {
    console.log('Produits chargés:', e.detail);
});

// Pagination
document.addEventListener('pgfe:page-changed', function(e) {
    console.log('Page changée:', e.detail);
});
```

### Méthodes Publiques

```javascript
// Appliquer des filtres
PGFE.applyFilters({
    price: [10, 100],
    categories: [1, 2, 3],
    vendors: [1, 2]
});

// Réinitialiser les filtres
PGFE.resetFilters();

// Charger plus de produits
PGFE.loadMore();
```

## Compatibilité

### Plugins Supportés

- **WooCommerce** : Toutes les fonctionnalités de base
- **Dokan** : Support complet des vendeurs
- **WCFM Marketplace** : Support des vendeurs et boutiques
- **WC Vendors** : Support des vendeurs
- **YITH WooCommerce Wishlist** : Intégration wishlist
- **WooCommerce Product Add-ons** : Support des add-ons

### Thèmes Testés

- Astra
- GeneratePress
- OceanWP
- Storefront
- Hello Elementor

## Performance

### Optimisations Incluses

- **Lazy Loading** : Chargement différé des images
- **Cache AJAX** : Mise en cache des requêtes
- **Minification** : CSS et JS minifiés
- **Requêtes Optimisées** : Requêtes de base de données optimisées

### Recommandations

- Utilisez un plugin de cache (WP Rocket, W3 Total Cache)
- Optimisez vos images
- Limitez le nombre de produits par page
- Utilisez un CDN pour les assets

## Dépannage

### Problèmes Courants

**Les widgets n'apparaissent pas**
- Vérifiez que WooCommerce et Elementor sont activés
- Videz le cache d'Elementor
- Vérifiez les erreurs PHP dans les logs

**Les filtres ne fonctionnent pas**
- Vérifiez que JavaScript est activé
- Contrôlez la console pour les erreurs
- Vérifiez les conflits avec d'autres plugins

**Problèmes de style**
- Videz le cache du navigateur
- Vérifiez les conflits CSS
- Régénérez les CSS d'Elementor



## Support

Pour obtenir de l'aide :

1. Consultez cette documentation
2. Vérifiez les problèmes connus
3. Contactez le support technique

## Changelog

### Version 1.0.0
- Version initiale
- 7 widgets Elementor
- Support multi-vendeurs
- Filtrage AJAX
- Interface responsive

## Licence

Ce plugin est distribué sous licence GPL v2 ou ultérieure.

## Crédits

Développé pour Le Local des Artisans par l'équipe de développement.

---

**Note** : Ce plugin est en développement actif. N'hésitez pas à signaler les bugs ou suggérer des améliorations.