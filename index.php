<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being main.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

use P4\MasterTheme\Features\Dev\ListingPageGridView;
use P4\MasterTheme\Features\ListingPagePagination;
use Timber\Timber;

$context          = Timber::get_context();
$context['posts'] = Timber::get_posts();
$context['title'] = html_entity_decode( $context['wp_title'] );

$templates = [ 'index.twig' ];

if ( is_home() ) {
	array_unshift( $templates, 'all-posts.twig' );

	if ( ListingPagePagination::is_active() ) {
		$view = ListingPageGridView::is_active() ? 'grid' : 'list';

		$query_template = file_get_contents( get_template_directory() . "/parts/query-$view.html" );

		$content = do_blocks( $query_template );

		$context['query_loop'] = $content;
		Timber::render( $templates, $context );
		exit();
	}
}

Timber::render( $templates, $context );
