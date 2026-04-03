<?php
/**
 * Custom Taxonomies: Country & Travel Category
 */

if (!defined('ABSPATH')) {
    exit;
}

class TMP_Travel_Taxonomy {
    
    public function __construct() {
        add_action('init', [$this, 'register']);
    }
    
    /**
     * Register taxonomies
     */
    public function register() {
        $this->register_country();
        $this->register_travel_category();
    }
    
    /**
     * Register Country taxonomy
     */
    private function register_country() {
        $labels = [
            'name' => __('Countries', 'travel-membership-pro'),
            'singular_name' => __('Country', 'travel-membership-pro'),
            'menu_name' => __('Countries', 'travel-membership-pro'),
            'search_items' => __('Search Countries', 'travel-membership-pro'),
            'all_items' => __('All Countries', 'travel-membership-pro'),
            'edit_item' => __('Edit Country', 'travel-membership-pro'),
            'update_item' => __('Update Country', 'travel-membership-pro'),
            'add_new_item' => __('Add New Country', 'travel-membership-pro'),
            'new_item_name' => __('New Country Name', 'travel-membership-pro'),
            'parent_item' => __('Parent Region', 'travel-membership-pro'),
            'parent_item_colon' => __('Parent Region:', 'travel-membership-pro'),
        ];
        
        $args = [
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_admin_column' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'country', 'with_front' => false],
            'show_in_graphql' => true,
            'graphql_single_name' => 'country',
            'graphql_plural_name' => 'countries',
        ];
        
        register_taxonomy('country', 'destination', $args);
    }
    
    /**
     * Register Travel Category taxonomy
     */
    private function register_travel_category() {
        $labels = [
            'name' => __('Travel Categories', 'travel-membership-pro'),
            'singular_name' => __('Travel Category', 'travel-membership-pro'),
            'menu_name' => __('Categories', 'travel-membership-pro'),
            'search_items' => __('Search Categories', 'travel-membership-pro'),
            'all_items' => __('All Categories', 'travel-membership-pro'),
            'edit_item' => __('Edit Category', 'travel-membership-pro'),
            'update_item' => __('Update Category', 'travel-membership-pro'),
            'add_new_item' => __('Add New Category', 'travel-membership-pro'),
            'new_item_name' => __('New Category Name', 'travel-membership-pro'),
        ];
        
        $args = [
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_admin_column' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'travel-category', 'with_front' => false],
            'show_in_graphql' => true,
            'graphql_single_name' => 'travelCategory',
            'graphql_plural_name' => 'travelCategories',
        ];
        
        register_taxonomy('travel_category', 'destination', $args);
    }
}
