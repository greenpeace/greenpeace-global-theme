<?php
use Timber\Timber;

// Initializing variables.
$context         = Timber::get_context();
/** @var P4_Post $post */
$post            = Timber::query_post( false, 'P4_Post' );
$context['post'] = $post;

// Save custom style settings.
$custom_styles = [];

$custom_styles['css']['nav_color']     = $post->campaign_nav_color ? ".navbar { background-color: {$post->campaign_nav_color} !important;}" : null;
$custom_styles['nav_type']             = $post->campaign_nav_type;
$custom_styles['logo_color']           = $post->campaign_logo_color;
$custom_styles['css']['header_bg']     = $post->campaign_header_color ? ".page-header { background: {$post->campaign_header_color} !important;}" : null;
$custom_styles['css']['header_serif']  = $post->campaign_header_serif ? ".page-header { font-family: {$post->campaign_header_serif}!important;}" : null;
$custom_styles['css']['header_sans']   = $post->campaign_header_sans ? ".page-header { font-family: {$post->campaign_header_sans} !important;}" : null;
$custom_styles['css']['body_font']     = $post->campaign_body_font ? "body { font-family: '{$post->campaign_body_font}' !important;}" : null;
$custom_styles['css']['btn_primary']   = $post->campaign_primary_color ? ".btn-primary { background: {$post->campaign_primary_color} !important; border-color: {$post->campaign_primary_color} }" : null;
$custom_styles['css']['btn_secondary'] = $post->campaign_secondary_color ? ".btn-secondary { background: {$post->campaign_secondary_color} !important; border-color: {$post->campaign_primary_color} }" : null;
$custom_styles['campaign_logo']        = $post->campaign_logo ?? null;


// Set Navigation Issues links.
$post->set_issues_links();


// Get the cmb2 custom fields data
// Articles block parameters to populate the articles block
// p4_take_action_page parameter to populate the take action boxout block
// Author override parameter. If this is set then the author profile section will not be displayed.
$page_meta_data                 = get_post_meta( $post->ID );
$page_terms_data                = get_the_terms( $post, 'p4-page-type' );
$context['background_image']    = $page_meta_data['p4_background_image_override'][0] ?? '';
$take_action_page               = $page_meta_data['p4_take_action_page'][0] ?? '';
$context['page_type']           = $page_terms_data[0]->name ?? '';
$context['page_term_id']        = $page_terms_data[0]->term_id ?? '';
$context['page_category']       = $category->name ?? __( 'Post page', 'planet4-master-theme' );
$context['page_type_slug']      = $page_terms_data[0]->slug ?? '';
$context['social_accounts']     = $post->get_social_accounts( $context['footer_social_menu'] );
$context['og_title']            = $post->get_og_title();
$context['og_description']      = $post->get_og_description();
$context['og_image_data']       = $post->get_og_image();
$context['custom_body_classes'] = 'brown-bg theme-oil';

$context['filter_url'] = add_query_arg( [
	's'                                       => ' ',
	'orderby'                                 => 'relevant',
	'f[ptype][' . $context['page_type'] . ']' => $context['page_term_id'],
], get_home_url()
);


// Build the shortcode for articles block.
if ( 'yes' === $post->include_articles ) {
	$post->articles = "[shortcake_articles exclude_post_id='" . $post->ID . "' /]";
}

// Build the shortcode for take action boxout block
// Break the content to retrieve first 2 paragraphs and split the content if the take action page has been defined.
if ( ! empty( $take_action_page ) ) {
	$post->take_action_page   = $take_action_page;
	$post->take_action_boxout = "[shortcake_take_action_boxout take_action_page='$take_action_page' /]";
}

// Build an arguments array to customize WordPress comment form.
$comments_args = [
	'comment_notes_before' => '',
	'comment_notes_after'  => '',
	'comment_field'        => Timber::compile( 'comment_form/comment_field.twig' ),
	'submit_button'        => Timber::compile( 'comment_form/submit_button.twig' ),
	'title_reply'          => __( 'Leave Your Reply', 'planet4-master-theme' ),
	'fields'               => apply_filters( 'comment_form_default_fields',
		[
			'author' => Timber::compile( 'comment_form/author_field.twig' ),
			'email'  => Timber::compile( 'comment_form/email_field.twig' ),
		]
	),
];

$context['comments_args']       = $comments_args;
$context['show_comments']       = comments_open( $post->ID );
$context['post_comments_count'] = get_comments(
	[
		'post_id' => $post->ID,
		'status'  => 'approve',
		'type'    => 'comment',
		'count'   => true,
	]
);

$context['post_tags'] = implode( ', ', $post->tags() );

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}