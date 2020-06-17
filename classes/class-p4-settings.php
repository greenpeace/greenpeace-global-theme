<?php
/**
 * Settings Class
 *
 * @package P4MT
 */

/**
 * Class P4_Settings
 */
class P4_Settings {

	/**
	 * ID of the Metabox
	 */
	private const METABOX_ID = 'option_metabox';

	/**
	 * Option key, and option page slug
	 *
	 * @var string
	 */
	private $key = 'planet4_options';

	/**
	 * Options Page title
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 *
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 */
	public function __construct() {

		// Set our title.
		$this->title = __( 'Planet4', 'planet4-master-theme-backend' );

		$this->fields = [
				[
						'name' => __( 'Website Navigation Title', 'planet4-master-theme-backend' ),
						'id'   => 'website_navigation_title',
						'type' => 'text',
				],
				[
						'name' => __( 'Select Act Page', 'planet4-master-theme-backend' ),
						'id'   => 'act_page',
						'type' => 'act_page_dropdown',
				],

				[
						'name' => __( 'Select Explore Page', 'planet4-master-theme-backend' ),
						'id'   => 'explore_page',
						'type' => 'explore_page_dropdown',
				],

				[
						'name' => __( 'Select Issues Parent Category', 'planet4-master-theme-backend' ),
						'id'   => 'issues_parent_category',
						'type' => 'category_select_taxonomy',
				],

				[
						'name' => __( 'Google Tag Manager Container', 'planet4-master-theme-backend' ),
						'id'   => 'google_tag_manager_identifier',
						'type' => 'text',
				],

				[
						'name' => __( 'Google Optimize anti-flicker snippet', 'planet4-master-theme-backend' ),
						'desc' => __(
								'It will include the relevant snippet for A/B testing.',
								'planet4-master-theme-backend'
						),
						'id'   => 'google_optimizer',
						'type' => 'checkbox',
				],

				[
						'name' => __( 'Facebook Page ID', 'planet4-master-theme-backend' ),
						'id'   => 'facebook_page_id',
						'type' => 'text',
				],

				[
						'name' => __( 'Default P4 Post Type', 'planet4-master-theme-backend' ),
						'id'   => 'default_p4_pagetype',
						'type' => 'pagetype_select_taxonomy',
				],

				[
						'name' => __( 'Default title for related articles block', 'planet4-master-theme-backend' ),
						'id'   => 'articles_block_title',
						'type' => 'text',
						'desc' => __(
								'If no title set for <b>Article Block</b>, the default title will appear.',
								'planet4-master-theme-backend'
						),
				],

				[
						'name' => __(
								'Default button title for related articles block',
								'planet4-master-theme-backend'
						),
						'id'   => 'articles_block_button_title',
						'type' => 'text',
						'desc' => __(
								'If no button title set for <b>Article Block</b>, the default button title will appear.',
								'planet4-master-theme-backend'
						),
				],

				[
						'name'       => __( 'Default Number Of Related Articles', 'planet4-master-theme-backend' ),
						'id'         => 'articles_count',
						'type'       => 'text',
						'attributes' => [
								'type' => 'number',
						],
						'desc'       => __(
								'If no number of Related Articles set for <b>Article Block</b>, the default number of Related Articles will appear.',
								'planet4-master-theme-backend'
						),
				],

				[
						'name'       => __(
								'Take Action Covers default button text',
								'planet4-master-theme-backend'
						),
						'id'         => 'take_action_covers_button_text',
						'type'       => 'text',
						'attributes' => [
								'type' => 'text',
						],
						'desc'       => __(
								'Add default button text which appears on <b>Take Action</b> card of <b>Take Action Covers</b> block. <br>
									 Also it would be used for Take Action Cards inside Posts and Take Action Cards in search results',
								'planet4-master-theme-backend'
						),
				],

				[
						'name'       => __( 'Donate button link', 'planet4-master-theme-backend' ),
						'id'         => 'donate_button',
						'type'       => 'text',
						'attributes' => [
								'type' => 'text',
						],
				],

				[
						'name'       => __( 'Donate button text', 'planet4-master-theme-backend' ),
						'id'         => 'donate_text',
						'type'       => 'text',
						'attributes' => [
								'type' => 'text',
						],
				],

				[
						'name' => __(
								'Show donate button below mobile navigation bar',
								'planet4-master-theme-backend'
						),
						'desc' => __(
										'Enable visibility of the donate button on the homepage. Please check legal restrictions on images associated with this button before using it.',
										'planet4-master-theme-backend'
								) . ' <a href="https://planet4.greenpeace.org/handbook/admin-setup/#donate-button-visible-on-mobile">' . __(
										'Read more',
										'planet4-master-theme-backend'
								) . '</a>',
						'id'   => 'donate_btn_visible_on_mobile',
						'type' => 'checkbox',
				],

				[
						'name'       => __( '404 Background Image', 'planet4-master-theme-backend' ),
						'id'         => '404_page_bg_image',
						'type'       => 'file',
						'options'    => [
								'url' => false,
						],
						'text'       => [
								'add_upload_file_text' => __(
										'Add 404 Page Background Image',
										'planet4-master-theme-backend'
								),
						],
						'query_args' => [
								'type' => 'image',
						],
						'desc'       => __(
								'Minimum image width should be 1920px',
								'planet4-master-theme-backend'
						),
				],

				[
						'name'    => __( '404 Page text', 'planet4-master-theme-backend' ),
						'id'      => '404_page_text',
						'type'    => 'wysiwyg',
						'options' => [
								'textarea_rows' => 3,
								'media_buttons' => false,
						],
						'desc'    => __( 'Add 404 page text', 'planet4-master-theme-backend' ),
				],

				[
						'name' => __( 'Happy point Subscribe Form URL', 'planet4-master-theme-backend' ),
						'id'   => 'engaging_network_form_id',
						'type' => 'text',
				],

				[
						'name'       => __(
								'Default Happy Point Background Image',
								'planet4-master-theme-backend'
						),
						'id'         => 'happy_point_bg_image',
						'type'       => 'file',
						'options'    => [
								'url' => false,
						],
						'text'       => [
								'add_upload_file_text' => __(
										'Add Default Happy Point Background Image',
										'planet4-master-theme-backend'
								),
						],
						'query_args' => [
								'type' => 'image',
						],
						'desc'       => __(
								'Minimum image width should be 1920px',
								'planet4-master-theme-backend'
						),
				],

				[
						'name'       => __( 'Preconnect Domains', 'planet4-master-theme-backend' ),
						'desc'       => __(
								'Add a list of third-party URLs to "preconnect" (e.g.: https://in.hotjar.com). Look for "preconnect" in the P4 Handbook for details.',
								'planet4-master-theme-backend'
						),
						'id'         => 'preconnect_domains',
						'type'       => 'textarea',
						'attributes' => [
								'type' => 'text',
						],
				],
				[
						'name' => __( 'Exclude campaign styles when importing', 'planet4-master-theme-backend' ),
						'desc' => __(
								'Whether to exclude campaign theme and style settings when importing a campaign.',
								'planet4-master-theme-backend'
						),
						'id'   => 'campaigns_import_exclude_style',
						'type' => 'checkbox',
				],
				[
						'name'    => __( 'Include archived content in search for', 'planet4-master-theme-backend' ),
						'id'      => 'include_archive_content_for',
						'type'    => 'select',
						'default' => 'nobody',
						'options' => [
								'nobody'    => __( 'Nobody', 'planet4-master-theme-backend' ),
								'logged_in' => __( 'Logged in users', 'planet4-master-theme-backend' ),
								'all'       => __( 'All users', 'planet4-master-theme-backend' ),
						],
				],
				[
						'name' => __( 'Search content decay', 'planet4-master-theme-backend' ),
						'desc' => __(
								'Amount of lowering of the relevancy score for older results. Between 0 and 1. The lower this number is, the lower older content will be ranked. See image. <br>We use the exponential function (exp, green curve).<br/> <img style="max-width:350px" alt="ElasticSearch decay function graph" src="https://www.elastic.co/guide/en/elasticsearch/reference/current/images/decay_2d.png">',
								'planet4-master-theme-backend'
						),
						'id'   => 'epwr_decay',
						'type' => 'text',
				],
				[
						'name' => __( 'Search content decay scale', 'planet4-master-theme-backend' ),
						'desc' => __(
								'Timescale for lowering the relevance of older results. See image above.',
								'planet4-master-theme-backend'
						),
						'id'   => 'epwr_scale',
						'type' => 'text',
				],
				[
						'name' => __( 'Search content decay offset', 'planet4-master-theme-backend' ),
						'desc' => __(
								'How old should a post be before relevance is lowered. See image above.',
								'planet4-master-theme-backend'
						),
						'id'   => 'epwr_offset',
						'type' => 'text',
				],
				[
						'name' => __( 'Local Projects Smartsheet ID', 'planet4-master-theme-backend' ),
						'desc' => __(
								'The smartsheet that is used to get analytics values from local(NRO) smartsheet.',
								'planet4-master-theme-backend'
						),
						'id'   => 'analytics_local_smartsheet_id',
						'type' => 'text',
				],

				[
						'name'    => __( 'Cookies Text', 'planet4-master-theme-backend' ),
						'id'      => 'cookies_field',
						'type'    => 'wysiwyg',
						'options' => [
								'textarea_rows' => 5,
								'media_buttons' => false,
						],
				],

				[
						'name' => __( 'Enforce Cookies Policy', 'planet4-master-theme-backend' ),
						'desc' => __(
								'GDPR related setting. By enabling this option specific content will be blocked and will require user consent to be shown.',
								'planet4-master-theme-backend'
						),
						'id'   => 'enforce_cookies_policy',
						'type' => 'checkbox',
				],

				[
						'name'    => __( 'Copyright Text Line 1', 'planet4-master-theme-backend' ),
						'id'      => 'copyright_line1',
						'type'    => 'wysiwyg',
						'options' => [
								'textarea_rows' => 3,
								'media_buttons' => false,
						],
				],

				[
						'name'    => __( 'Copyright Text Line 2', 'planet4-master-theme-backend' ),
						'id'      => 'copyright_line2',
						'type'    => 'wysiwyg',
						'options' => [
								'textarea_rows' => 2,
								'media_buttons' => false,
						],
				],
		];
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 */
	public function hooks() {
		add_action( 'admin_init', [ $this, 'init' ] );
		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'cmb2_save_options-page_fields_' . self::METABOX_ID, [ $this, 'add_notifications' ] );
		add_filter( 'cmb2_render_act_page_dropdown', [ $this, 'p4_render_act_page_dropdown' ], 10, 2 );
		add_filter( 'cmb2_render_explore_page_dropdown', [ $this, 'p4_render_explore_page_dropdown' ], 10, 2 );
		add_filter( 'cmb2_render_category_select_taxonomy', [ $this, 'p4_render_category_dropdown' ], 10, 2 );
		add_filter( 'cmb2_render_pagetype_select_taxonomy', [ $this, 'p4_render_pagetype_dropdown' ], 10, 2 );

		// Make settings multilingual if wpml plugin is installed and activated.
		if ( function_exists( 'icl_object_id' ) ) {
			add_action( 'init', [ $this, 'make_settings_multilingual' ] );
		}
	}

	/**
	 * Register our setting to WP.
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page.
	 */
	public function add_options_page() {
		$this->options_page = add_options_page(
				$this->title,
				$this->title,
				'manage_options',
				$this->key,
				[ $this, 'admin_page_display' ]
		);
	}

	/**
	 * Display notifications of success and error
	 * This is the method used by WordPress to add a success notification
	 *
	 * @see https://github.com/WordPress/WordPress/blob/57fb3c6cf016678ab38d7a636b8df41fa2d955f1/wp-admin/options.php#L313
	 */
	public function add_notifications(): void {
		if ( ! count( get_settings_errors() ) ) {
			add_settings_error(
					'general',
					'settings_updated',
					__( 'Settings saved.', 'planet4-master-theme-backend' ),
					'success'
			);
		}

		settings_errors();
	}

	/**
	 * Render act page dropdown.
	 *
	 * @param array  $field_args Field arguments.
	 * @param string $value Value.
	 */
	public function p4_render_act_page_dropdown( $field_args, $value ) {
		wp_dropdown_pages(
				[
						'show_option_none' => esc_html__( 'Select Page', 'planet4-master-theme-backend' ),
						'hide_empty'       => 0,
						'hierarchical'     => true,
						'selected'         => esc_attr( $value ),
						'name'             => 'act_page',
				]
		);
	}

	/**
	 * Render explore page dropdown.
	 *
	 * @param array  $field_args Field arguments.
	 * @param string $value Value.
	 */
	public function p4_render_explore_page_dropdown( $field_args, $value ) {
		wp_dropdown_pages(
				[
						'show_option_none' => esc_html__( 'Select Page', 'planet4-master-theme-backend' ),
						'hide_empty'       => 0,
						'hierarchical'     => true,
						'selected'         => esc_attr( $value ),
						'name'             => 'explore_page',
				]
		);
	}

	/**
	 * Render category dropdown.
	 *
	 * @param array  $field_args Field arguments.
	 * @param string $value Value.
	 */
	public function p4_render_category_dropdown( $field_args, $value ) {

		wp_dropdown_categories(
				[
						'show_option_none' => __( 'Select Category', 'planet4-master-theme-backend' ),
						'hide_empty'       => 0,
						'hierarchical'     => true,
						'orderby'          => 'name',
						'selected'         => $value,
						'name'             => 'issues_parent_category',
				]
		);
	}

	/**
	 * Render p4-pagetype dropdown.
	 *
	 * @param CMB2_Field $field_args CMB2 field Object.
	 * @param int        $value Pagetype taxonomy ID.
	 */
	public function p4_render_pagetype_dropdown( $field_args, $value ) {

		wp_dropdown_categories(
				[
						'show_option_none' => __( 'Select Posttype', 'planet4-master-theme-backend' ),
						'hide_empty'       => 0,
						'orderby'          => 'name',
						'selected'         => $value,
						'name'             => 'default_p4_pagetype',
						'taxonomy'         => 'p4-page-type',
				]
		);
	}

	/**
	 * Admin page markup. Mostly handled by CMB2.
	 */
	public function admin_page_display() {
		?>
		<div class="wrap <?php echo esc_attr( $this->key ); ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( $this->option_metabox(), $this->key ); ?>
		</div>
		<?php
	}

	/**
	 * Defines the theme option metabox and field configuration.
	 *
	 * @return array
	 */
	public function option_metabox() {
		return [
				'id'         => self::METABOX_ID,
				'show_on'    => [
						'key'   => 'options-page',
						'value' => [
								$this->key,
						],
				],
				'show_names' => true,
				'fields'     => $this->fields,
		];
	}

	/**
	 * Hook for wpml plugin.
	 * Enables the possibility to save a different value per language for the theme options using WPML language switcher.
	 */
	public function make_settings_multilingual() {
		do_action( 'wpml_multilingual_options', 'planet4_options' );
	}
}

/**
 * Wrapper function around cmb2_get_option.
 *
 * @param string $key Options array key.
 * @param bool   $default The default value to use if the options is not set.
 * @return mixed Option value.
 */
function planet4_get_option( $key = '', $default = null ) {
	$options = get_option( 'planet4_options' );

	return $options[ $key ] ?? $default;
}
