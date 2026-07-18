<?php get_header(); ?>

<div class="lnm-archive lnm-container">
    <h1>Novel Library</h1>

    <form role="search" method="get" class="lnm-search-form" action="<?php echo home_url('/'); ?>">
        <input type="search" name="s" placeholder="Search novels..." value="<?php echo get_search_query(); ?>">
        <input type="hidden" name="post_type" value="lnm_novel">
        <button type="submit">Search</button>
    </form>

    <?php
    if (is_search()) {
        echo '<h1>Search Results for: ' . get_search_query() . '</h1>';
    }
    ?>

    <div class="lnm-novel-grid">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <div class="lnm-novel-card">

                    <a href="<?php the_permalink(); ?>" class="lnm-card-link">

                        <div class="lnm-novel-cover">
                            <?php if (has_post_thumbnail()) {
                                the_post_thumbnail('medium');
                            } ?>
                        </div>

                        <div class="lnm-card-content">
                            <h2 class="lnm-novel-title"><?php the_title(); ?></h2>
                            <p class="lnm-novel-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15); ?></p>
                        </div>

                    </a>

                </div>

        <?php endwhile;
        endif; ?>

    </div>
    <?php
    if (!have_posts()) {
        echo '<p>No novels found.</p>';
    }
    ?>
</div>

<?php get_footer(); ?>