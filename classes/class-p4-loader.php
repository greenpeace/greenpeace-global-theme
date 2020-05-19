<?php
/**
 * P4 Loader Class
 *
 * @package P4MT
 */

namespace P4MT;

/**
 * Class P4_Loader.
 * Loads all necessary classes for Planet4 Master Theme.
 */
final class P4_Loader {
	/**
	 * A static instance of Loader.
	 *
	 * @var P4_Loader $instance
	 */
	private static $instance;
	/**
	 * Indexed array of all the classes/services that are needed.
	 *
	 * @var array $services
	 */
	private $services;
	/**
	 * Indexed array of all the classes/services that are used by Planet4.
	 *
	 * @var array $default_services
	 */
	private $default_services;

	/**
	 * Singleton creational pattern.
	 * Makes sure there is only one instance at all times.
	 *
	 * @param array $services The Controller services to inject.
	 *
	 * @return P4_Loader
	 */
	public static function get_instance( $services = [] ) : P4_Loader {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $services );
		}
		return self::$instance;
	}

	/**
	 * P4_Loader constructor.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function __construct( $services ) {
		$this->load_services( $services );
		$this->add_filters();
	}

	/**
	 * Inject dependencies.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function load_services( $services ) {

		$this->default_services = [
			'P4MT\P4_Custom_Taxonomy',
			'P4MT\P4_Post_Campaign',
			'P4MT\P4_Settings',
			'P4MT\P4_Post_Report_Controller',
			'P4MT\P4_Cookies',
			'P4MT\P4_Dev_Report',
			'P4MT\P4_Master_Site',
		];

		if ( is_admin() ) {
			global $pagenow;

			// Load P4 Control Panel only on Dashboard page.
			$this->default_services[] = 'P4MT\P4_Control_Panel';

			// Load P4 Metaboxes only when adding/editing a new Page/Post/Campaign.
			if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
				$this->default_services[] = 'P4MT\P4_Metabox_Register';
			}

			// Load P4 Metaboxes only when adding/editing a new Page/Post/Campaign.
			if ( 'edit-tags.php' === $pagenow || 'term.php' === $pagenow ) {
				$this->default_services[] = 'P4MT\P4_Campaigns';
			}

			// Load `P4_Campaign_Exporter` class on admin campaign listing page and campaign export only.
			if ( 'campaign' === filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING ) || 'export_data' === filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ) ) {
				$this->default_services[] = 'P4MT\P4_Campaign_Exporter';
			}

			// Load `P4_Campaign_Importer` class on admin campaign import only.
			// phpcs:disable
			if ( 'wordpress' === filter_input( INPUT_GET, 'import', FILTER_SANITIZE_STRING ) ) {
				// phpcs:enable
				$this->default_services[] = 'P4MT\P4_Campaign_Importer';
			}
		}

		// Run P4_Activator after theme switched to planet4-master-theme or a planet4 child theme.
		if ( get_option( 'theme_switched' ) ) {
			$this->default_services[] = 'P4MT\P4_Activator';
		}

		$services = array_merge( $services, $this->default_services );
		if ( $services ) {
			foreach ( $services as $service ) {
				$this->services[ $service ] = new $service();
			}
		}
	}

	/**
	 * Gets the loaded services.
	 *
	 * @return array The loaded services.
	 */
	public function get_services() : array {
		return $this->services;
	}

	/**
	 * Add some filters.
	 *
	 * @return void
	 */
	private function add_filters(): void {
		add_filter( 'pre_delete_post', [ $this, 'do_not_delete_autosave' ], 1, 3 );
	}

	/**
	 * Due to a bug in WordPress core the "autosave revision" of a post is created and deleted all of the time.
	 * This is pretty pointless and makes it impractical to add any post meta to that revision.
	 * The logic was probably that some space could be saved it is can be determined that the autosave doesn't differ
	 * from the current post content. However that advantage doesn't weigh up to the overhead of deleting the record and
	 * inserting it again, each time burning through another id of the posts table.
	 *
	 * @see https://core.trac.wordpress.org/ticket/49532
	 *
	 * @param null $delete Whether to go forward with the delete (sic, see original filter where it is null initally, not used here).
	 * @param null $post Post object.
	 * @param null $force_delete Is true when post is not trashed but deleted permanently (always false for revisions but they are deleted anyway).
	 *
	 * @return bool|null If the filter returns anything else than null the post is not deleted.
	 */
	public function do_not_delete_autosave( $delete = null, $post = null, $force_delete = null ): ?bool {
		if (
			$force_delete
			|| ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ( isset( $_GET['delete_all'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ! preg_match( '/autosave-v\d+$/', $post->post_name ) ) {

			return null;
		}

		return false;
	}
}
