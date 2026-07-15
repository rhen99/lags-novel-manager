<?php

if (!defined('ABSPATH')) {
    exit;
}

class LNM_Meta
{

    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta']);
        add_action('wp_ajax_lnm_get_next_chapter_number', [$this, 'get_next_chapter_number']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'lnm_novel_select',
            'Select Novel',
            [$this, 'render_novel_dropdown'],
            'lnm_chapter',
            'side'
        );
        add_meta_box(
            'lnm_chapter_number',
            'Chapter Number',
            [$this, 'render_chapter_number'],
            'lnm_chapter',
            'side'
        );
    }

    public function render_novel_dropdown($post)
    {

        $selected = get_post_meta($post->ID, 'lnm_novel_id', true);

        $novels = get_posts([
            'post_type' => 'lnm_novel',
            'numberposts' => -1
        ]);
        ob_start();
?>
        <div class="editor-post-panel__section">

        </div>

        <select name="lnm_novel_id" id="lnm_novel_id">
            <option value="">-- Select Novel --</option>

            <?php foreach ($novels as $novel): ?>
                <option value="<?php echo esc_attr($novel->ID); ?>"
                    <?php selected($selected, $novel->ID); ?>>
                    <?php echo esc_html($novel->post_title); ?>
                </option>
            <?php endforeach; ?>

        </select>
    <?php
        echo ob_get_clean();
    }
    public function render_chapter_number($post)
    {

        $value = get_post_meta($post->ID, 'lnm_chapter_number', true);

        ob_start();
    ?>

        <label for="lnm_chapter_number"><strong>Chapter Number</strong></label>
        <input
            type="number"
            name="lnm_chapter_number_input"
            id="lnm_chapter_number_input"
            value="<?php echo esc_attr($value); ?>"
            min="1" />

<?php

        echo ob_get_clean();
    }

    public function save_meta($post_id)
    {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (isset($_POST['lnm_novel_id'])) {
            update_post_meta(
                $post_id,
                'lnm_novel_id',
                intval($_POST['lnm_novel_id'])
            );
        }
        if (isset($_POST['lnm_chapter_number'])) {
            update_post_meta(
                $post_id,
                'lnm_chapter_number',
                intval($_POST['lnm_chapter_number'])
            );
        }
    }
    public function get_next_chapter_number()
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
            'order' => 'DESC'
        ]);

        $next_number = 1;

        if (!empty($chapters)) {
            $last = get_post_meta($chapters[0]->ID, 'lnm_chapter_number', true);
            $next_number = intval($last) + 1;
        }

        wp_send_json_success($next_number);
    }

    public function enqueue_scripts($hook)
    {

        global $post;

        if ($hook === 'post.php' || $hook === 'post-new.php') {
            if (isset($post) && $post->post_type === 'lnm_chapter') {

                wp_enqueue_script(
                    'lnm-admin-js',
                    LNM_URL . 'assets/js/admin.js',
                    [],
                    '1.0',
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
    }
}
