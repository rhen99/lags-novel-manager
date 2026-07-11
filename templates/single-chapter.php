<?php get_header(); ?>

<div class="lnm-container lnm-reader">

    <?php
    $chapter_id = get_the_ID();

    $chapter_number = get_post_meta($chapter_id, 'lnm_chapter_number', true);
    $novel_id = get_post_meta($chapter_id, 'lnm_novel_id', true);

    $novel_title = get_the_title($novel_id);
    $novel_link = get_permalink($novel_id);
    ?>

    <!-- Novel Link -->
    <div class="lnm-breadcrumb">
        <a href="<?php echo esc_url($novel_link); ?>">
            <?php echo esc_html($novel_title); ?>
        </a>
    </div>

    <!-- Chapter Title -->
    <h1 class="lnm-chapter-title">
        Chapter <?php echo esc_html($chapter_number); ?> - <?php the_title(); ?>
    </h1>

    <!-- Chapter Content -->
    <div class="lnm-chapter-content">
        <?php the_content(); ?>
    </div>

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

<div class="lnm-navigation">

    <div class="lnm-prev">
        <?php if ($prev): ?>
            <a href="<?php echo get_permalink($prev[0]->ID); ?>">
                ← Chapter <?php echo $current_number - 1; ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="lnm-next">
        <?php if ($next): ?>
            <a href="<?php echo get_permalink($next[0]->ID); ?>">
                Chapter <?php echo $current_number + 1; ?> →
            </a>
        <?php endif; ?>
    </div>

</div>
<?php get_footer(); ?>