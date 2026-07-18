<?php get_header(); ?>
<?php
$chapter_id = get_the_ID();

$chapter_number = get_post_meta($chapter_id, 'lnm_chapter_number', true);
$novel_id = get_post_meta($chapter_id, 'lnm_novel_id', true);

$novel_title = get_the_title($novel_id);
$novel_link = get_permalink($novel_id);
?>
<div class="lnm-reader">

    <div class="lnm-reader-header">
        <a href="<?php echo get_permalink($novel_id); ?>" class="lnm-back">
            ← Back to Novel
        </a>

        <h1 class="lnm-chapter-title">
            <?php the_title(); ?>
        </h1>
    </div>

    <div class="lnm-reader-content">
        <?php the_content(); ?>
    </div>
    <?php
    // Get current data
    $current_number = intval($chapter_number);

    // Previous chapter
    $prev = get_posts([
        'post_type' => 'lnm_chapter',
        'numberposts' => 1,
        'meta_query' => [
            [
                'key' => 'lnm_novel_id',
                'value' => $novel_id
            ],
            [
                'key' => 'lnm_chapter_number',
                'value' => $current_number - 1,
                'compare' => '='
            ]
        ]
    ]);

    // Next chapter
    $next = get_posts([
        'post_type' => 'lnm_chapter',
        'numberposts' => 1,
        'meta_query' => [
            [
                'key' => 'lnm_novel_id',
                'value' => $novel_id
            ],
            [
                'key' => 'lnm_chapter_number',
                'value' => $current_number + 1,
                'compare' => '='
            ]
        ]
    ]);
    ?>
    <div class="lnm-reader-navigation">

        <div class="lnm-prev">
            <?php if ($prev) : ?>
                <a href="<?php echo get_permalink($prev[0]->ID); ?>">
                    ← Chapter <?php echo $chapter_number - 1; ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="lnm-next">
            <?php if ($next) : ?>
                <a href="<?php echo get_permalink($next[0]->ID); ?>">
                    Chapter <?php echo $chapter_number + 1; ?> →
                </a>
            <?php endif; ?>
        </div>

    </div>

</div>
<?php get_footer(); ?>





<?php
// $chapter_id = get_the_ID();

// $chapter_number = get_post_meta($chapter_id, 'lnm_chapter_number', true);
// $novel_id = get_post_meta($chapter_id, 'lnm_novel_id', true);

// $novel_title = get_the_title($novel_id);
// $novel_link = get_permalink($novel_id);
?>
<?php
// // Get current data
// $current_number = intval($chapter_number);

// // Previous chapter
// $prev = get_posts([
//     'post_type' => 'lnm_chapter',
//     'numberposts' => 1,
//     'meta_query' => [
//         [
//             'key' => 'lnm_novel_id',
//             'value' => $novel_id
//         ],
//         [
//             'key' => 'lnm_chapter_number',
//             'value' => $current_number - 1,
//             'compare' => '='
//         ]
//     ]
// ]);

// // Next chapter
// $next = get_posts([
//     'post_type' => 'lnm_chapter',
//     'numberposts' => 1,
//     'meta_query' => [
//         [
//             'key' => 'lnm_novel_id',
//             'value' => $novel_id
//         ],
//         [
//             'key' => 'lnm_chapter_number',
//             'value' => $current_number + 1,
//             'compare' => '='
//         ]
//     ]
// ]);
