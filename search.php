<?php
/**
 * Search results page
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

use P4MT\P4_ElasticSearch;

/**
 * Planet4 - Search functionality.
 */
if ( is_main_query() && is_search() ) {
	if ( 'GET' === filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) ) {
		$selected_sort    = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$selected_filters = $_GET['f'] ?? ''; // phpcs:ignore
		$filters          = [];

		// Handle submitted filter options.
		if ( $selected_filters && is_array( $selected_filters ) ) {
			foreach ( $selected_filters as $type_name => $filter_type ) {
				foreach ( $filter_type as $name => $filter_id ) {
					$filters[ $type_name ][] = [
						'id'   => $filter_id,
						'name' => $name,
					];
				}
			}
		}

		$p4_search = new P4_ElasticSearch();
		$p4_search->load( trim( get_search_query() ), $selected_sort, $filters );
		$p4_search->add_load_more();
		$p4_search->view();
	}
}
