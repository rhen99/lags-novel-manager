<?php get_header(); ?>

<div class="lnm-single-novel">

    <div class="lnm-novel-header">

        <div class="lnm-novel-cover">
            <?php if (has_post_thumbnail()) {
                the_post_thumbnail('medium');
            } ?>
        </div>

        <div class="lnm-novel-meta">
            <h1 class="lnm-novel-title"><?php the_title(); ?></h1>

            <div class="lnm-novel-description">
                <?php the_content(); ?>
            </div>
        </div>

    </div>
    <?php
    $novel_id = get_the_ID();

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
    ?>
    <div class="lnm-chapter-section">
        <h2><?php echo esc_html(count($chapters)); ?> Chapters</h2>

        <?php
        if ($chapters) {
            foreach ($chapters as $chapter) {

                $number = get_post_meta($chapter->ID, 'lnm_chapter_number', true);
                $link = get_permalink($chapter->ID);
                $title = get_the_title($chapter->ID);
        ?>
                <ul class="lnm-chapter-list">
                    <li class='lnm-chapter-item'>
                        <a href='<?php echo esc_attr($link); ?>'>Chapter <?php echo esc_html($number); ?> - <?php echo esc_html($title); ?></a>
                    </li>
                </ul>
            <?php
            }
        } else {
            ?>
            <p>No chapters yet.</p>
        <?php
        }
        ?>
    </div>

</div>

<?php get_footer(); ?>

<?php
// $novel_id = get_the_ID();

// $chapters = get_posts([
//     'post_type' => 'lnm_chapter',
//     'numberposts' => -1,
//     'meta_query' => [
//         [
//             'key' => 'lnm_novel_id',
//             'value' => $novel_id
//         ]
//     ],
//     'meta_key' => 'lnm_chapter_number',
//     'orderby' => 'meta_value_num',
//     'order' => 'ASC'
// ]);

// if ($chapters) {
//     foreach ($chapters as $chapter) {

//         $number = get_post_meta($chapter->ID, 'lnm_chapter_number', true);
//         $link = get_permalink($chapter->ID);
//         $title = get_the_title($chapter->ID);

//         echo "<div class='lnm-chapter-item'>";
//         echo "<a href='{$link}'>Chapter {$number} - {$title}</a>";
//         echo "</div>";
//     }
// } else {
//     echo "<p>No chapters yet.</p>";
// }
?>