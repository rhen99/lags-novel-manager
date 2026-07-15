<?php

if (!defined('ABSPATH')) {
    exit;
}

class LNM_Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('wp_ajax_lnm_get_chapters', [$this, 'ajax_get_chapters']);
        add_action('wp_ajax_lnm_get_chapter', [$this, 'ajax_get_chapter']);
        add_action('wp_ajax_lnm_reorder_chapters', [$this, 'ajax_reorder_chapters']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_lnm_save_chapter', [$this, 'ajax_save_chapter']);
        add_action('wp_ajax_lnm_create_chapter', [$this, 'ajax_create_chapter']);
        add_action('wp_ajax_lnm_delete_chapter', [$this, 'ajax_delete_chapter']);
    }
    public function register_menu()
    {

        add_menu_page(
            'Novel Editor',
            'Novel Editor',
            'manage_options',
            'lnm-editor',
            [$this, 'render_editor_page'],
            'dashicons-book',
            25
        );
    }
    public function render_editor_page()
    {
?>

        <div class="wrap">
            <h1>Novel Editor</h1>

            <div id="lnm-editor-app">

                <div class="lnm-top-bar">
                    <select id="lnm-novel-select">
                        <option value="">Select a Novel</option>

                        <?php
                        $novels = get_posts([
                            'post_type' => 'lnm_novel',
                            'numberposts' => -1,
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ]);

                        if ($novels) {
                            foreach ($novels as $novel) {
                                echo "<option value='" . esc_attr($novel->ID) . "'>" . esc_html($novel->post_title) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="lnm-editor-layout">

                    <div class="lnm-sidebar">
                        <h3>Chapters</h3>
                        <button id="lnm-new-chapter" class="button">
                            + New Chapter
                        </button>
                        <ul id="lnm-chapter-list">
                            <li>Select a novel</li>
                        </ul>
                    </div>

                    <div class="lnm-main">
                        <input
                            type="text"
                            id="lnm-chapter-title"
                            placeholder="Chapter Title">

                        <input type="hidden" id="lnm-current-chapter-id" value="">

                        <input type="hidden" id="lnm-current-chapter-id" value="">

                        <?php
                        wp_editor(
                            '',
                            'lnm_chapter_content',
                            [
                                'textarea_name' => 'lnm_chapter_content',
                                'media_buttons' => false,
                                'textarea_rows' => 20,
                            ]
                        );
                        ?>

                        <button id="lnm-save-chapter" class="button button-primary">
                            Save Chapter
                        </button>
                    </div>

                </div>

            </div>
        </div>

<?php
    }

    public function ajax_get_chapters()
    {

        $novel_id = intval($_POST['novel_id'] ?? 0);

        if (!$novel_id) {
            wp_send_json_error('Invalid novel ID');
        }

        $chapters = get_posts([
            'post_type' => 'lnm_chapter',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => 'lnm_novel_id',
                    'value' => $novel_id
                ]
            ],
            'meta_key' => 'lnm_chapter_number',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);

        $data = [];

        if ($chapters) {
            foreach ($chapters as $chapter) {
                $data[] = [
                    'id' => $chapter->ID,
                    'title' => $chapter->post_title,
                    'number' => get_post_meta($chapter->ID, 'lnm_chapter_number', true)
                ];
            }
        }

        wp_send_json_success($data);
    }

    public function ajax_get_chapter()
    {

        $chapter_id = intval($_POST['chapter_id'] ?? 0);

        if (!$chapter_id) {
            wp_send_json_error('Invalid ID');
        }

        $post = get_post($chapter_id);

        if (!$post) {
            wp_send_json_error('Not found');
        }

        $number = get_post_meta($chapter_id, 'lnm_chapter_number', true);

        wp_send_json_success([
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'number' => $number
        ]);
    }
    public function ajax_save_chapter()
    {

        $chapter_id = intval($_POST['chapter_id'] ?? 0);
        $content = $_POST['content'] ?? '';
        $title = $_POST['title'] ?? '';

        if (!$chapter_id) {
            wp_send_json_error('Invalid ID');
        }

        if (!current_user_can('edit_post', $chapter_id)) {
            wp_send_json_error('Permission denied');
        }

        wp_update_post([
            'ID' => $chapter_id,
            'post_title' => sanitize_text_field($title),
            'post_content' => wp_kses_post($content)
        ]);

        wp_send_json_success('Saved');
    }

    public function ajax_reorder_chapters()
    {

        $order = json_decode(stripslashes($_POST['order'] ?? ''), true);

        if (!$order || !is_array($order)) {
            wp_send_json_error('Invalid data');
        }

        foreach ($order as $item) {

            $chapter_id = intval($item['id']);
            $number = intval($item['number']);

            if ($chapter_id) {
                update_post_meta($chapter_id, 'lnm_chapter_number', $number);
            }
        }

        wp_send_json_success('Order saved');
    }

    public function ajax_create_chapter()
    {

        $novel_id = intval($_POST['novel_id'] ?? 0);

        if (!$novel_id) {
            wp_send_json_error('No novel selected');
        }

        // Get next chapter number
        $chapters = get_posts([
            'post_type' => 'lnm_chapter',
            'numberposts' => -1,
            'meta_key' => 'lnm_chapter_number',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'meta_query' => [
                [
                    'key' => 'lnm_novel_id',
                    'value' => $novel_id
                ]
            ]
        ]);

        $next_number = 1;

        if ($chapters) {
            $last = $chapters[0];
            $last_number = get_post_meta($last->ID, 'lnm_chapter_number', true);
            $next_number = intval($last_number) + 1;
        }

        // Create post
        $chapter_id = wp_insert_post([
            'post_type' => 'lnm_chapter',
            'post_status' => 'publish',
            'post_title' => 'New Chapter',
            'post_content' => ''
        ]);

        // Save meta
        update_post_meta($chapter_id, 'lnm_novel_id', $novel_id);
        update_post_meta($chapter_id, 'lnm_chapter_number', $next_number);

        wp_send_json_success([
            'id' => $chapter_id,
            'title' => 'New Chapter',
            'number' => $next_number,
            'content' => ''
        ]);
    }
    public function ajax_delete_chapter()
    {

        $chapter_id = intval($_POST['chapter_id'] ?? 0);

        if (!$chapter_id) {
            wp_send_json_error('Invalid ID');
        }

        if (!current_user_can('delete_post', $chapter_id)) {
            wp_send_json_error('Permission denied');
        }

        wp_delete_post($chapter_id, true);

        wp_send_json_success('Deleted');
    }
    public function enqueue_admin_assets($hook)
    {

        if ($hook !== 'toplevel_page_lnm-editor') return;

        wp_enqueue_script(
            'lnm-admin-js',
            LNM_URL . 'assets/js/admin.js',
            [],
            LNM_VERSION,
            true
        );
        wp_enqueue_script(
            'sortable-js',
            'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js',
            [],
            null,
            true
        );
        wp_enqueue_style(
            'lnm-admin-css',
            LNM_URL . 'assets/css/admin.css',
            [],
            LNM_VERSION
        );
        wp_localize_script('lnm-admin-js', 'lnm_admin_ajax', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }
}
