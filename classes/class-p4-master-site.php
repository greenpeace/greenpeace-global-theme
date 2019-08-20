<?php
/**
 * P4 Master Site Class
 *
 * @package P4MT
 */

use Timber\Timber;
use Timber\Site as TimberSite;
use Timber\Menu as TimberMenu;

/**
 * Class P4_Master_Site.
 * The main class that handles Planet4 Master Theme.
 */
class P4_Master_Site extends TimberSite {

	/**
	 * Theme directory
	 *
	 * @var string $theme_dir
	 */
	protected $theme_dir;

	/**
	 * Theme images directory
	 *
	 * @var string $theme_images_dir
	 */
	protected $theme_images_dir;

	/**
	 * Sort options
	 *
	 * @var array $sort_options
	 */
	protected $sort_options;

	/**
	 * Child CSS
	 *
	 * @var array $child_css
	 */
	protected $child_css = [];

	/**
	 * P4_Master_Site constructor.
	 */
	public function __construct() {
		$this->settings();
		$this->hooks();
		parent::__construct();
	}

	/**
	 * Define settings for the Planet4 Master Theme.
	 */
	protected function settings() {
		Timber::$autoescape     = true;
		Timber::$dirname        = [ 'templates', 'views' ];
		$this->theme_dir        = get_template_directory_uri();
		$this->theme_images_dir = $this->theme_dir . '/images/';
		$this->sort_options     = [
			'_score'    => [
				'name'  => __( 'Most relevant', 'planet4-master-theme' ),
				'order' => 'DESC',
			],
			'post_date' => [
				'name'  => __( 'Most recent', 'planet4-master-theme' ),
				'order' => 'DESC',
			],
		];
	}

	/**
	 * Hooks the theme.
	 */
	protected function hooks() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_post_type_support( 'page', 'excerpt' );  // Added excerpt option to pages.

		add_filter( 'timber_context', [ $this, 'add_to_context' ] );
		add_filter( 'get_twig', [ $this, 'add_to_twig' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ], 2 );
		add_action( 'init', [ $this, 'register_oembed_provider' ] );
		add_action( 'pre_get_posts', [ $this, 'add_search_options' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
		add_filter( 'safe_style_css', [ $this, 'set_custom_allowed_css_properties' ] );
		add_filter( 'wp_kses_allowed_html', [ $this, 'set_custom_allowed_attributes_filter' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box_search' ] );
		add_action( 'save_post', [ $this, 'save_meta_box_search' ], 10, 2 );
		add_action( 'save_post', [ $this, 'set_featured_image' ], 10, 3 );
		add_action( 'post_updated', [ $this, 'clean_post_cache' ], 10, 3 );
		add_action( 'after_setup_theme', [ $this, 'p4_master_theme_setup' ] );
		add_action( 'admin_menu', [ $this, 'add_restricted_tags_box' ] );
		add_action( 'do_meta_boxes', [ $this, 'remove_default_tags_box' ] );
		add_action( 'pre_insert_term', [ $this, 'disallow_insert_term' ], 1, 2 );
		add_filter( 'wp_image_editors', [ $this, 'allowedEditors' ] );
		add_filter(
			'jpeg_quality',
			function( $arg ) {
				return 60;
			}
		);
		add_filter(
			'http_request_timeout',
			function( $timeout ) {
				return 10;
			}
		);
		add_action( 'after_setup_theme', [ $this, 'add_image_sizes' ] );
		add_action( 'admin_head', [ $this, 'remove_add_post_element' ] );
		add_filter( 'post_gallery', [ $this, 'carousel_post_gallery' ], 10, 2 );
		add_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10, 2 );
		add_filter( 'img_caption_shortcode', [ $this, 'override_img_caption_shortcode' ], 10, 3 );

		add_action( 'wp_ajax_get_paged_posts', [ 'P4_ElasticSearch', 'get_paged_posts' ] );
		add_action( 'wp_ajax_nopriv_get_paged_posts', [ 'P4_ElasticSearch', 'get_paged_posts' ] );

		add_action( 'admin_head', [ $this, 'add_help_sidebar' ] );

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		register_nav_menus(
			[
				'navigation-bar-menu' => __( 'Navigation Bar Menu', 'planet4-master-theme-backend' ),
			]
		);

		add_filter( 'login_headerurl', [ $this, 'add_login_logo_url' ] );
		add_filter( 'login_headertext', [ $this, 'add_login_logo_url_title' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'add_login_stylesheet' ] );
		add_filter( 'comment_form_submit_field', [ $this, 'gdpr_cc_comment_form_add_class' ], 150, 2 );
		add_filter( 'embed_oembed_html', [ $this, 'filter_youtube_oembed_nocookie' ], 10, 2 );
		add_filter(
			'editable_roles',
			function( $roles ) {
				uasort(
					$roles,
					function( $a, $b ) {
						return $b['name'] <=> $a['name'];
					}
				);
				return $roles;
			}
		);
	}

	/**
	 * Sets the URL for the logo link in the login page.
	 */
	function add_login_logo_url() {
		return home_url();
	}

	/**
	 * Sets the title for the logo link in the login page.
	 */
	function add_login_logo_url_title() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Sets a custom stylesheet for the login page.
	 */
	public function add_login_stylesheet() {
		wp_enqueue_style( 'custom-login', $this->theme_dir . '/style-login.css' );
	}

	/**
	 * Sets as featured image of the post the first image found attached in the post's content (if any).
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post The current Post.
	 * @param bool    $update Whether this is an existing post being updated or not.
	 */
	public function set_featured_image( $post_id, $post, $update ) {

		// Ignore autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check user's capabilities.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// Check if user has set the featured image manually or if he has removed it.
		$user_set_featured_image = get_post_meta( $post_id, '_thumbnail_id', true );

		// Apply this behavior to a Post and only if it does not already a featured image.
		if ( 'post' === $post->post_type && ! $user_set_featured_image ) {
			// Find all matches of <img> html tags within the post's content and get the id of the image from the elements class name.
			preg_match_all( '/<img.+wp-image-(\d+).*>/i', $post->post_content, $matches );
			if ( isset( $matches[1][0] ) && is_numeric( $matches[1][0] ) ) {
				set_post_thumbnail( $post_id, $matches[1][0] );
			}
		}
	}

	/**
	 * Sets as featured image of the post the first image found attached in the post's content (if any).
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post_after The current Post.
	 * @param WP_Post $post_before Whether this is an existing post being updated or not.
	 */
	public function clean_post_cache( $post_id, $post_after, $post_before ) {

		// Ignore autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		clean_post_cache( $post_id );
	}

	/**
	 * Add extra image sizes as needed.
	 */
	public function add_image_sizes() {
		add_image_size( 'retina-large', 2048, 1366, false );
		add_image_size( 'articles-medium-large', 510, 340, false );
	}

	/**
	 * Force WordPress to use P4_Image_Compression as image manipulation editor.
	 */
	public function allowedEditors() {
		return [ 'P4_Image_Compression' ];
	}

	/**
	 * Load translations for master theme
	 */
	function p4_master_theme_setup() {
		$domains = [
			'planet4-master-theme',
			'planet4-master-theme-backend',
		];
		$locale  = is_admin() ? get_user_locale() : get_locale();

		foreach ( $domains as $domain ) {
			$mofile = get_template_directory() . '/languages/' . $domain . '-' . $locale . '.mo';
			load_textdomain( $domain, $mofile );
		}
	}

	/**
	 * Adds more data to the context variable that will be passed to the main template.
	 *
	 * @param array $context The associative array with data to be passed to the main template.
	 *
	 * @return mixed
	 */
	public function add_to_context( $context ) {
		global $wp;
		$context['cookies']      = [
			'text' => planet4_get_option( 'cookies_field' ),
		];
		$context['data_nav_bar'] = [
			'images'                  => $this->theme_images_dir,
			'home_url'                => home_url( '/' ),
			'search_query'            => trim( get_search_query() ),
			'country_dropdown_toggle' => __( 'Toggle worldwide site selection menu', 'planet4-master-theme' ),
			'navbar_search_toggle'    => __( 'Toggle search box', 'planet4-master-theme' ),
		];
		$context['domain']       = 'planet4-master-theme';
		$context['foo']          = 'bar';   // For unit test purposes.
		if ( function_exists( 'icl_get_languages' ) ) {
			$context['languages'] = count( icl_get_languages() );
		}
		$context['navbar_menu']  = new TimberMenu( 'navigation-bar-menu' );
		$context['site']         = $this;
		$context['current_url']  = home_url( $wp->request );
		$context['sort_options'] = $this->sort_options;
		$context['default_sort'] = P4_Search::DEFAULT_SORT;

		$options = get_option( 'planet4_options' );

		// Do not embed google tag manager js if 'greenpeace' cookie is not set or enforce_cookies_policy setting is not enabled.
		$context['enforce_cookies_policy'] = isset( $options['enforce_cookies_policy'] ) ? true : false;
		$context['google_tag_value']       = $options['google_tag_manager_identifier'] ?? '';
		$context['google_optimizer']       = isset( $options['google_optimizer'] ) ? true : false;
		$context['facebook_page_id']       = $options['facebook_page_id'] ?? '';

		$context['donatelink']           = $options['donate_button'] ?? '#';
		$context['donatetext']           = $options['donate_text'] ?? __( 'DONATE', 'planet4-master-theme' );
		$context['website_navbar_title'] = $options['website_navigation_title'] ?? __( 'International (English)', 'planet4-master-theme' );

		// Footer context.
		$context['copyright_text_line1']  = $options['copyright_line1'] ?? '';
		$context['copyright_text_line2']  = $options['copyright_line2'] ?? '';
		$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
		$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
		$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
		$context['p4_comments_depth']     = get_option( 'thread_comments_depth' ) ?? 1; // Default depth level set to 1 if not selected from admin.
		return $context;
	}

	/**
	 * Add your own functions to Twig.
	 *
	 * @param Twig_ExtensionInterface $twig The Twig object that implements the Twig_ExtensionInterface.
	 *
	 * @return mixed
	 */
	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig_Extension_StringLoader() );
		$twig->addFilter( new Twig_SimpleFilter( 'svgicon', [ $this, 'svgicon' ] ) );

		return $twig;
	}

	/**
	 * SVG Icon helper
	 *
	 * @param string $name Icon name.
	 */
	public function svgicon( $name ) {
		$svg_icon_template = '<svg viewBox="0 0 32 32" class="icon"><use xlink:href="' . $this->theme_images_dir . 'symbol/svg/sprite.symbol.svg#' . $name . '"></use></svg>';
		return new \Twig_Markup( $svg_icon_template, 'UTF-8' );
	}

	/**
	 * Set CSS properties that should be allowed for posts filter
	 * Allow img object-position.
	 *
	 * @param array $allowedproperties Default allowed CSS properties.
	 *
	 * @return array
	 */
	public function set_custom_allowed_css_properties( $allowedproperties ) {
		$allowedproperties[] = 'object-position';
		return $allowedproperties;
	}

	/**
	 * Set HTML attributes that should be allowed for posts filter
	 * Allow img srcset and sizes attributes.
	 * Allow iframes in posts.
	 *
	 * @param array $allowedposttags Default allowed tags.
	 *
	 * @return array
	 */
	public function set_custom_allowed_attributes_filter( $allowedposttags ) {
		// Allow iframes and the following attributes.
		$allowedposttags['iframe'] = [
			'align'           => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'name'            => true,
			'src'             => true,
			'id'              => true,
			'class'           => true,
			'style'           => true,
			'scrolling'       => true,
			'marginwidth'     => true,
			'marginheight'    => true,
			'allowfullscreen' => true,
		];

		// Allow blockquote and the following attributes. (trigger: allow instagram embeds).
		$allowedposttags['blockquote'] = [
			'style'                  => true,
			'data-instgrm-captioned' => true,
			'data-instgrm-permalink' => true,
			'data-instgrm-version'   => true,
			'class'                  => true,
		];

		// Allow img and the following attributes.
		$allowedposttags['img'] = [
			'alt'    => true,
			'class'  => true,
			'id'     => true,
			'height' => true,
			'hspace' => true,
			'name'   => true,
			'src'    => true,
			'srcset' => true,
			'sizes'  => true,
			'width'  => true,
			'style'  => true,
			'vspace' => true,
		];

		$allowedposttags['script'] = [
			'src' => true,
		];

		// Allow source tag for WordPress audio shortcode to function.
		$allowedposttags['source'] = [
			'type' => true,
			'src'  => true,
		];

		// Allow below tags for carousel slider.
		$allowedposttags['div']['data-ride']    = true;
		$allowedposttags['li']['data-target']   = true;
		$allowedposttags['li']['data-slide-to'] = true;
		$allowedposttags['a']['data-slide']     = true;
		$allowedposttags['span']['aria-hidden'] = true;

		return $allowedposttags;
	}

	/**
	 * Sanitizes the settings input.
	 *
	 * @param string $setting The setting to sanitize.
	 *
	 * @return string The sanitized setting.
	 */
	public function sanitize( $setting ) : string {
		$allowed = [
			'ul'     => [],
			'ol'     => [],
			'li'     => [],
			'strong' => [],
			'del'    => [],
			'span'   => [
				'style' => [],
			],
			'p'      => [
				'style' => [],
			],
			'a'      => [
				'href'   => [],
				'target' => [],
				'rel'    => [],
			],
		];
		return wp_kses( $setting, $allowed );
	}

	/**
	 * Load styling and behaviour on admin pages.
	 *
	 * @param string $hook Hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		$css_creation = filectime( get_template_directory() . '/style.css' );

		// Register jQuery 3 for use wherever needed by adding wp_enqueue_script( 'jquery-3' );.
		wp_register_script( 'jquery-3', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', [], '3.3.1', true );
		wp_enqueue_style( 'bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css', [], '4.1.1' );
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/style.css', [], $css_creation );
	}

	/**
	 * Load styling and behaviour on website pages.
	 */
	public function enqueue_public_assets() {
		// master-theme assets.
		$css_creation = filectime( get_template_directory() . '/style.css' );
		$js_creation  = filectime( get_template_directory() . '/main.js' );

		// CSS files.
		wp_enqueue_style( 'bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css', [], '4.1.1' );
		wp_enqueue_style( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css', [], '1.9.0' );
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/style.css', [], $css_creation );
		// JS files.
		wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', [], '3.3.1', true );
		wp_enqueue_script( 'popperjs', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', [], '1.14.3', true );
		wp_enqueue_script( 'bootstrapjs', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/js/bootstrap.min.js', [], '4.1.1', true );
		wp_enqueue_script( 'main', $this->theme_dir . '/main.js', [ 'jquery' ], $js_creation, true );
		wp_enqueue_script( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', [], '1.9.0', true );
		wp_enqueue_script( 'hammer', 'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js', [], '2.0.8', true );
	}

	/**
	 * Creates a Metabox on the side of the Add/Edit Post/Page
	 * that is used for applying weight to the current Post/Page in search results.
	 *
	 * @param WP_Post $post The currently Added/Edited post.
	 */
	public function add_meta_box_search( $post ) {
		add_meta_box( 'meta-box-search', 'Search', [ $this, 'view_meta_box_search' ], [ 'post', 'page' ], 'side', 'default', $post );
	}

	/**
	 * Renders a Metabox on the side of the Add/Edit Post/Page.
	 *
	 * @param WP_Post $post The currently Added/Edited post.
	 */
	public function view_meta_box_search( $post ) {
		$weight  = get_post_meta( $post->ID, 'weight', true );
		$options = get_option( 'planet4_options' );

		echo '<label for="my_meta_box_text">' . esc_html__( 'Weight', 'planet4-master-theme-backend' ) . ' (1-' . P4_Search::DEFAULT_MAX_WEIGHT . ')</label>
				<input id="weight" type="text" name="weight" value="' . esc_attr( $weight ) . '" />';
		?><script>
			$ = jQuery;
			$( '#parent_id' ).off('change').on( 'change', function () {
				// Check selected Parent page and give bigger weight if it will be an Action page
				if ( '<?php echo esc_js( $options['act_page'] ); ?>' === $(this).val() ) {
					$( '#weight' ).val( <?php echo esc_js( P4_Search::DEFAULT_ACTION_WEIGHT ); ?> );
				} else {
					$( '#weight' ).val( <?php echo esc_js( P4_Search::DEFAULT_PAGE_WEIGHT ); ?> );
				}
			});
		</script>
		<?php
	}

	/**
	 * Saves the Search weight of the Post/Page.
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post The current Post.
	 */
	public function save_meta_box_search( $post_id, $post ) {
		global $pagenow;

		// Ignore autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check user's capabilities.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// Make sure there's input.
		$weight = filter_input(
			INPUT_POST,
			'weight',
			FILTER_VALIDATE_INT,
			[
				'options' => [
					'min_range' => P4_Search::DEFAULT_MIN_WEIGHT,
					'max_range' => P4_Search::DEFAULT_MAX_WEIGHT,
				],
			]
		);

		// If this is a new Page then set default weight for it.
		if ( ! $weight && 'post-new.php' === $pagenow ) {
			if ( 'page' === $post->post_type ) {
				$weight = P4_Search::DEFAULT_PAGE_WEIGHT;
			}
		}

		// Store weight.
		update_post_meta( $post_id, 'weight', $weight );
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
		register_taxonomy_for_object_type( 'post_tag', 'page' );
		register_taxonomy_for_object_type( 'category', 'page' );
	}

	/**
	 * Registers oembed provider for Carto map.
	 */
	public function register_oembed_provider() {
		wp_oembed_add_provider( '#https?://(?:www\.)?[^/^\.]+\.carto(db)?\.com/\S+#i', 'https://services.carto.com/oembed', true );
	}

	/**
	 * Add custom options to the main WP_Query.
	 *
	 * @param WP_Query $wp The WP Query to customize.
	 */
	public function add_search_options( WP_Query $wp ) {
		if ( ! $wp->is_main_query() || ! $wp->is_search() ) {
			return;
		}

		$wp->set( 'posts_per_page', P4_Search::POSTS_LIMIT );
		$wp->set( 'no_found_rows', true );
	}

	/**
	 * Add restricted tags box for all roles besides administrator.
	 * A list of checkboxes representing the tags will be rendered.
	 */
	public function add_restricted_tags_box() {

		if ( current_user_can( 'administrator' ) ) {
			return;
		}
		add_meta_box(
			'restricted_tags_box',
			__( 'Tags', 'planet4-master-theme-backend' ),
			[ $this, 'print_restricted_tags_box' ],
			[ 'post', 'page' ],
			'side'
		);
	}

	/**
	 * Remove "Add Post Element" button for POST & rename on page as "Add Page Element".
	 */
	function remove_add_post_element() {
		if ( 'page' === get_post_type() ) {
			remove_action( 'media_buttons', [ Shortcode_UI::get_instance(), 'action_media_buttons' ] );
			add_action( 'media_buttons', [ $this, 'action_page_media_buttons' ] );
		}
	}

	/**
	 * Output an "Add Page Element" button with the media buttons.
	 *
	 * @param int $editor_id Editor ID.
	 */
	public function action_page_media_buttons( $editor_id ) {
		printf(
			'<button type="button" class="button shortcake-add-post-element" data-editor="%s">' .
				'<span class="wp-media-buttons-icon dashicons dashicons-migrate"></span> %s' .
				'</button>',
			esc_attr( $editor_id ),
			__( 'Add Page Element', 'planet4-master-theme-backend' )
		);
	}

	/**
	 * Apply carousel style to wp image gallery.
	 *
	 * @param string $output Output.
	 * @param mixed  $attr   Attributes.
	 */
	public function carousel_post_gallery( $output, $attr ) {
		return do_shortcode( '[shortcake_carousel multiple_image="' . $attr['ids'] . '"]' );
	}

	/**
	 * Auto generate excerpt for post.
	 *
	 * @param int     $post_id Id of the saved post.
	 * @param WP_Post $post Post object.
	 */
	public function p4_auto_generate_excerpt( $post_id, $post ) {
		if ( '' === $post->post_excerpt && 'post' === $post->post_type ) {

			// Unhook save_post function so it doesn't loop infinitely.
			remove_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10 );

			// Generate excerpt text.
			$post_excerpt   = strip_shortcodes( $post->post_content );
			$post_excerpt   = apply_filters( 'the_content', $post_excerpt );
			$post_excerpt   = str_replace( ']]>', ']]&gt;', $post_excerpt );
			$excerpt_length = apply_filters( 'excerpt_length', 30 );
			$excerpt_more   = apply_filters( 'excerpt_more', '&hellip;' );
			$post_excerpt   = wp_trim_words( $post_excerpt, $excerpt_length, $excerpt_more );

			// Update the post, which calls save_post again.
			wp_update_post(
				[
					'ID'           => $post_id,
					'post_excerpt' => $post_excerpt,
				]
			);

			// re-hook save_post function.
			add_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10, 2 );
		}
	}

	/**
	 * Restrict creation of tags from all roles besides administrator.
	 *
	 * @param string $term The term to be added.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return WP_Error|string
	 */
	public function disallow_insert_term( $term, $taxonomy ) {

		$user = wp_get_current_user();

		if ( 'post_tag' === $taxonomy && ! in_array( 'administrator', (array) $user->roles, true ) ) {

			return new WP_Error(
				'disallow_insert_term',
				__( 'Your role does not have permission to add terms to this taxonomy', 'planet4-master-theme-backend' )
			);

		}

		return $term;
	}

	/**
	 * Fetch all tags and find which are assinged to the post and pass them as arguments to tags box template.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function print_restricted_tags_box( $post ) {
		$all_post_tags = get_terms( 'post_tag', [ 'get' => 'all' ] );
		$assigned_tags = wp_get_object_terms( $post->ID, 'post_tag' );

		$assigned_ids = [];
		foreach ( $assigned_tags as $assigned_tag ) {
			$assigned_ids[] = $assigned_tag->term_id;
		}

		$this->render_partial(
			'partials/tags-box',
			[
				'tags'          => $all_post_tags,
				'assigned_tags' => $assigned_ids,
			]
		);
	}

	/**
	 * Remove default WordPress tags selection box for all roles besides administrator.
	 */
	public function remove_default_tags_box() {

		if ( current_user_can( 'administrator' ) ) {
			return;
		}

		remove_meta_box( 'tagsdiv-post_tag', [ 'post', 'page' ], 'normal' );
		remove_meta_box( 'tagsdiv-post_tag', [ 'post', 'page' ], 'side' );
	}

	/**
	 * Load a partial template and pass variables to it.
	 *
	 * @param string $path  path to template file, minus .php (eg. `content-page`, `partial/template-name`).
	 * @param array  $args  array of variables to load into scope.
	 */
	private function render_partial( $path, $args = [] ) {
		if ( ! empty( $args ) ) {
			extract( $args ); // phpcs:ignore
		}
		include( locate_template( $path . '.php' ) );
	}

	/**
	 * Filter function for img_caption_shortcode. Append image credit to caption.
	 *
	 * @param string $output  The caption output. Passed empty by WordPress.
	 * @param array  $attr    Attributes of the caption shortcode.
	 * @param string $content The image element, possibly wrapped in a hyperlink.
	 *
	 * @return string HTML content to display the caption.
	 */
	public function override_img_caption_shortcode( $output, $attr, $content ) {

		$atts = shortcode_atts(
			[
				'id'      => '',
				'align'   => 'alignnone',
				'width'   => '',
				'caption' => '',
				'class'   => '',
			],
			$attr,
			'caption'
		);

		$image_id     = trim( str_replace( 'attachment_', '', $atts['id'] ) );
		$meta         = get_post_meta( $image_id );
		$image_credit = '';
		if ( isset( $meta['_credit_text'] ) && ! empty( $meta['_credit_text'][0] ) ) {
			$image_credit = ' ' . $meta['_credit_text'][0];
			if ( ! is_numeric( strpos( $meta['_credit_text'][0], '©' ) ) ) {
				$image_credit = ' ©' . $image_credit;
			}
		}

		$class = trim( 'wp-caption ' . $atts['align'] . ' ' . $atts['class'] );

		if ( $atts['id'] ) {
			$atts['id'] = 'id="' . esc_attr( $atts['id'] ) . '" ';
		}

		$output = '<div ' . $atts['id'] . ' class="' . esc_attr( $class ) . '">'
				. do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . $image_credit . '</p></div>';

		return $output;
	}

	/**
	 * Add a help link to the Help sidebars.
	 */
	public function add_help_sidebar() {
		if ( get_current_screen() ) {
			$screen  = get_current_screen();
			$sidebar = $screen->get_help_sidebar();

			$sidebar .= '<p><a target="_blank" href="https://planet4.greenpeace.org/">Planet 4 Handbook</a></p>';

			$screen->set_help_sidebar( $sidebar );
		}
	}

	/**
	 * Filter and add class to GDPR consent checkbox label after the GDPR fields appended to comment form submit field.
	 *
	 * @param string $submit_field The HTML content of comment form submit field.
	 * @param array  $args         The arguments array.
	 *
	 * @return string HTML content of comment form submit field.
	 */
	public function gdpr_cc_comment_form_add_class( $submit_field, $args ) {

		$pattern[0]     = '/(for=["\']gdpr-comments-checkbox["\'])/';
		$replacement[0] = '$1 class="custom-control-description"';
		$pattern[1]     = '/(id=["\']gdpr-comments-checkbox["\'])/';
		$replacement[1] = '$1 style="width:auto;"';

		$submit_field = preg_replace( $pattern, $replacement, $submit_field );

		return $submit_field;
	}

	/**
	 * Filter function for embed_oembed_html.
	 * Transform youtube embeds to youtube-nocookie.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/embed_oembed_html/
	 *
	 * @param mixed  $cache The cached HTML result, stored in post meta.
	 * @param string $url   The attempted embed URL.
	 *
	 * @return mixed
	 */
	public function filter_youtube_oembed_nocookie( $cache, $url ) {
		if ( ! empty( $url ) ) {
			if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
				$cache = str_replace( 'youtube.com', 'youtube-nocookie.com', $cache );
			}
		}

		return $cache;
	}

}
