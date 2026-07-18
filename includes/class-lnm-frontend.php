<?php

if (!defined('ABSPATH')) {
    exit;
}

class LNM_Frontend
{

    public function __construct()
    {
        add_filter('the_content', [$this, 'append_chapters_to_novel']);
        add_filter('the_content', [$this, 'add_chapter_navigation']);
        add_filter('template_include', [$this, 'load_templates']);
        add_filter('body_class', function ($classes) {
            if (is_singular(['lnm_novel', 'lnm_chapter'])) {
                $classes[] = 'lnm-active';
            }
            return $classes;
        });
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

    public function load_templates($template)
    {

        if (is_singular('lnm_novel')) {
            $custom = LNM_PATH . 'templates/single-novel.php';

            if (file_exists($custom)) {
                return $custom;
            }
        }

        if (is_singular('lnm_chapter')) {
            $custom = LNM_PATH . 'templates/single-chapter.php';

            if (file_exists($custom)) {
                return $custom;
            }
        }
        if (is_post_type_archive('lnm_novel')) {
            $custom = LNM_PATH . 'templates/archive-lnm_novel.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }
        if (is_search() && get_query_var('post_type') === 'lnm_novel') {
            $custom = LNM_PATH . 'templates/archive-lnm_novel.php';
            if (file_exists($custom)) {
                return $custom;
            }
        }


        return $template;
    }

    public function append_chapters_to_novel($content)
    {

        if (!is_singular('lnm_novel') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;

        $chapters = get_posts([
            'post_type' => 'lnm_chapter',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => 'lnm_novel_id',
                    'value' => $post->ID
                ]
            ],
            'meta_key' => 'lnm_chapter_number',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        ]);

        ob_start();
?>

        <div class="lnm-chapters">
            <h2>Chapters</h2>

            <?php if (!empty($chapters)): ?>
                <ul>
                    <?php foreach ($chapters as $chapter): ?>
                        <li>
                            <a href="<?php echo get_permalink($chapter->ID); ?>">
                                <?php echo esc_html($chapter->post_title); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No chapters yet.</p>
            <?php endif; ?>

        </div>

    <?php

        return $content . ob_get_clean();
    }

    public function add_chapter_navigation($content)
    {

        if (!is_singular('lnm_chapter') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        global $post;

        // Get novel ID
        $novel_id = get_post_meta($post->ID, 'lnm_novel_id', true);

        if (!$novel_id) {
            return $content;
        }

        // Get all chapters in this novel
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

        if (empty($chapters)) {
            return $content;
        }

        // Find current chapter index
        $current_index = null;

        foreach ($chapters as $index => $chapter) {
            if ($chapter->ID == $post->ID) {
                $current_index = $index;
                break;
            }
        }

        if ($current_index === null) {
            return $content;
        }

        $prev = $chapters[$current_index - 1] ?? null;
        $next = $chapters[$current_index + 1] ?? null;

        ob_start();
    ?>

        <div class="lnm-navigation" style="margin-top:40px; display:flex; justify-content:space-between;">

            <div class="lnm-prev">
                <?php if ($prev): ?>
                    <a href="<?php echo get_permalink($prev->ID); ?>">
                        ← <?php echo esc_html($prev->post_title); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="lnm-next">
                <?php if ($next): ?>
                    <a href="<?php echo get_permalink($next->ID); ?>">
                        <?php echo esc_html($next->post_title); ?> →
                    </a>
                <?php endif; ?>
            </div>

        </div>

<?php

        return $content . ob_get_clean();
    }
    public function enqueue_styles()
    {

        if (is_singular(['lnm_novel', 'lnm_chapter']) || is_post_type_archive('lnm_novel')) {
            wp_enqueue_style(
                'lnm-frontend-css',
                LNM_URL . 'assets/css/frontend.css',
                [],
                LNM_VERSION
            );
        }
    }
}
