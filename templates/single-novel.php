<?php get_header(); ?>

<div class="lnm-container">

    <h1><?php the_title(); ?></h1>

    <div class="lnm-description">
        <?php the_content(); ?>
    </div>

    <hr>

    <h2>Chapters</h2>

    <div class="lnm-chapter-list">
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

        if ($chapters) {
            foreach ($chapters as $chapter) {

                $number = get_post_meta($chapter->ID, 'lnm_chapter_number', true);
                $link = get_permalink($chapter->ID);
                $title = get_the_title($chapter->ID);

                echo "<div class='lnm-chapter-item'>";
                echo "<a href='{$link}'>Chapter {$number} - {$title}</a>";
                echo "</div>";
            }
        } else {
            echo "<p>No chapters yet.</p>";
        }
        ?>
    </div>

</div>

<?php get_footer(); ?>