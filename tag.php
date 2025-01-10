<?php

/**
 * Displays a Campaign (Tag) page.
 *
 * Category <-> Issue
 * Tag <-> Campaign
 * Post <-> Action
 *
 * @package P4MT
 */

use P4\MasterTheme\Context;
use P4\MasterTheme\Post;
use P4\MasterTheme\ListingPage;
use Timber\Timber;

if (!is_tag()) {
    exit();
}

$tag = get_queried_object();
$redirect_id = $tag !== null && get_term_meta($tag->term_id, 'redirect_page', true);
$redirect_page = get_post($redirect_id);

if ($redirect_page) {
    global $wp_query;
    $wp_query->queried_object = $redirect_page;
    $wp_query->queried_object_id = $redirect_page->ID;

    // Allow modification of redirect page behavior.
    do_action('p4_action_tag_page_redirect', $redirect_page);

    include 'page.php';
    exit();
}

$post = Timber::query_post(false, Post::class); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$context = Timber::get_context();
if ($post instanceof \WP_Post) {
    $post = new Post($post->ID);
    Context::set_og_meta_fields($context, $post);
}

$context['taxonomy'] = $tag;
$context['tag_name'] = single_tag_title('', false);
$context['tag_description'] = wpautop($tag->description);

// Temporary fix with rewind, cf. https://github.com/WordPress/gutenberg/issues/53593
rewind_posts();

$templates = ['tag.twig', 'archive.twig', 'index.twig'];
$page = new ListingPage($templates, $context);
