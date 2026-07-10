<?php

if (!defined('ABSPATH')) {
    exit;
}

class LNM_CPT
{
    public function __construct()
    {
        add_action('init', [$this, 'register_post_types']);
    }

    public function register_post_types()
    {
        $this->register_novel_cpt();
        $this->register_chapter_cpt();
    }
    private function register_novel_cpt()
    {

        $labels = [
            'name' => 'Novels',
            'singular_name' => 'Novel',
            'add_new' => 'Add New Novel',
            'add_new_item' => 'Add New Novel',
            'edit_item' => 'Edit Novel',
            'new_item' => 'New Novel',
            'view_item' => 'View Novel',
            'search_items' => 'Search Novels',
            'not_found' => 'No novels found',
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'novels'],
            'menu_icon' => 'dashicons-book',
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
        ];

        register_post_type('lnm_novel', $args);
    }
    private function register_chapter_cpt()
    {

        $labels = [
            'name' => 'Chapters',
            'singular_name' => 'Chapter',
            'add_new' => 'Add New Chapter',
            'add_new_item' => 'Add New Chapter',
            'edit_item' => 'Edit Chapter',
            'new_item' => 'New Chapter',
            'view_item' => 'View Chapter',
            'search_items' => 'Search Chapters',
            'not_found' => 'No chapters found',
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => false,
            'rewrite' => ['slug' => 'chapter'],
            'menu_icon' => 'dashicons-media-document',
            'supports' => ['title', 'editor'],
            'show_in_rest' => true,
        ];

        register_post_type('lnm_chapter', $args);
    }
}
