<?php
/**
 * The template for displaying Taxonomy pages.
 *
 * Used to display taxonomy-type pages
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package  WordPress
 * @subpackage  Timber
 */

use P4\MasterTheme\Features\Dev\ListingPageGridView;
use P4\MasterTheme\Features\ListingPages\PostTypePagePagination;
use P4\MasterTheme\Post;
use Timber\Timber;

$templates = [ 'taxonomy.twig', 'index.twig' ];

$context             = Timber::get_context();
$context['taxonomy'] = get_queried_object();
$context['wp_title'] = $context['taxonomy']->name;

if ( PostTypePagePagination::is_active() ) {
	$view = ListingPageGridView::is_active() ? 'grid' : 'list';

	$query_template = file_get_contents( get_template_directory() . "/parts/query-$view.html" );

	$content = do_blocks( $query_template );

	$context['query_loop'] = $content;
	Timber::render( $templates, $context );
	exit();
}

$post_args = [
	'posts_per_page' => 10,
	'post_type'      => 'post',
	'paged'          => 1,
	'p4-page-type'   => $context['taxonomy']->slug,
	'has_password'   => false,  // Skip password protected content.
];

$context['page_category'] = 'Post Type Page';

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-taxonomy-post.twig' ];
	$post_args['paged'] = get_query_var( 'page' );
	$pagetype_posts     = new \Timber\PostQuery( $post_args, Post::class );
	foreach ( $pagetype_posts as $pagetype_post ) {
		$context['post'] = $pagetype_post;
		Timber::render( $templates, $context );
	}
} else {
	$pagetype_posts   = new \Timber\PostQuery( $post_args, Post::class );
	$context['posts'] = $pagetype_posts;
	Timber::render( $templates, $context );
}
