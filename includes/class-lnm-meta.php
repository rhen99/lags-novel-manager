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

        <select name="lnm_novel_id" id="lnm_novel_id" style="width:100%; margin-top:5px;">
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
            name="lnm_chapter_number"
            id="lnm_chapter_number"
            value="<?php echo esc_attr($value); ?>"
            style="width:100%; margin-top:5px;"
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
}
