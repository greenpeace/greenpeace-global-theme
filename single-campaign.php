<?php
/**
 * Template Variables for Campaigns.
 *
 * @package P4MT
 */

use Timber\Timber;

// Initializing variables.
$context = Timber::get_context();

/**
 * Post object.
 *
 * @var P4_Post $post
 * */
$post            = Timber::query_post( false, 'P4_Post' );
$context['post'] = $post;

// Save custom style settings.
$custom_styles = [];

$custom_styles['css']['nav_color']     = $post->campaign_nav_color ? ".navbar { background-color: {$post->campaign_nav_color} !important;}" : null;
$custom_styles['nav_type']             = $post->campaign_nav_type;
$custom_styles['campaign_logo_color']  = $post->campaign_logo_color ?? 'light';
$custom_styles['css']['header_color']  = $post->campaign_header_color ? "h1, h2, h3 { color: {$post->campaign_header_color} !important;}" : null;
$custom_styles['css']['header_serif']  = $post->campaign_header_serif ? ".page-header { font-family: {$post->campaign_header_serif}!important;}" : null;
$custom_styles['css']['header_sans']   = $post->campaign_header_sans ? ".page-header { font-family: {$post->campaign_header_sans} !important;}" : null;
$custom_styles['css']['body_font']     = $post->campaign_body_font ? "body { font-family: '{$post->campaign_body_font}' !important;}" : null;
$custom_styles['css']['btn_primary']   = $post->campaign_primary_color ? ".btn-primary { background: {$post->campaign_primary_color} !important; border-color: {$post->campaign_primary_color} }" : null;
$custom_styles['css']['btn_secondary'] = $post->campaign_secondary_color ? ".btn-secondary { background: {$post->campaign_secondary_color} !important; border-color: {$post->campaign_primary_color} }" : null;
$custom_styles['campaign_logo']        = $post->campaign_logo ?? null;


// Set Navigation Issues links.
$post->set_issues_links();

// Get the cmb2 custom fields data.
$page_meta_data    = get_post_meta( $post->ID );
$campaign_template = ! empty( $page_meta_data['_campaign_page_template'][0] ) ? $page_meta_data['_campaign_page_template'][0] : 'antarctic';

$context['post']                = $post;
$context['header_title']        = is_front_page() ? ( $page_meta_data['p4_title'][0] ?? '' ) : ( $page_meta_data['p4_title'][0] ?? $post->title );
$context['header_subtitle']     = $page_meta_data['p4_subtitle'][0] ?? '';
$context['header_description']  = wpautop( $page_meta_data['p4_description'][0] ?? '' );
$context['header_button_title'] = $page_meta_data['p4_button_title'][0] ?? '';
$context['header_button_link']  = $page_meta_data['p4_button_link'][0] ?? '';
$context['page_category']       = is_front_page() ? 'Front Page' : ( $category->name ?? 'Unknown page' );
$context['social_accounts']     = $post->get_social_accounts( $context['footer_social_menu'] );
$context['post_tags']           = implode( ', ', $post->tags() );

$background_image_id                = get_post_meta( get_the_ID(), 'background_image_id', 1 );
$context['background_image']        = wp_get_attachment_url( $background_image_id );
$context['background_image_srcset'] = wp_get_attachment_image_srcset( $background_image_id, 'full' );
$context['og_title']                = $post->get_og_title();
$context['og_description']          = $post->get_og_description();
$context['og_image_data']           = $post->get_og_image();
$context['custom_styles']           = $custom_styles;
$context['custom_body_classes']     = 'brown-bg theme-' . $campaign_template;


if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
