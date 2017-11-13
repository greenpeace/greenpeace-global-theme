<?php

if ( ! class_exists( 'P4_Taxonomy_Image' ) ) {
	/**
	 * Class P4MT_Taxonomy_Image
	 */
	class P4_Taxonomy_Image {

		/** @var string $taxonomy */
		private $taxonomy = 'post_tag';
		/** @var array $localizations */
		public $localizations = [];

		/**
		 * Taxonomy_Image constructor.
		 */
		public function __construct() {
			$this->localizations = [
				'media_title'  => esc_html__( 'Select Image', 'planet4-master-theme' ),
			];
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'post_tag_add_form_fields',              array( $this, 'add_taxonomy_form_fields' ) );
			add_action( 'post_tag_edit_form_fields',             array( $this, 'add_taxonomy_form_fields' ) );
			add_action( 'create_post_tag',                       array( $this, 'save_taxonomy_meta' ) );
			add_action( 'edit_post_tag',                         array( $this, 'save_taxonomy_meta' ) );
			add_action( 'admin_enqueue_scripts',                 array( $this, 'enqueue_admin_assets' ) );

			add_filter( 'manage_edit-post_tag_columns',          array( $this, 'edit_taxonomy_columns' ) );
			add_filter( 'manage_post_tag_custom_column',         array( $this, 'manage_taxonomy_custom_column' ), 10, 3 );
			add_filter( 'manage_edit-post_tag_sortable_columns', array( $this, 'manage_taxonomy_custom_sortable_column' ), 10, 3 );
		}

		/**
		 * Add custom field(s) to taxonomy form.
		 *
		 * @param WP_Term $wp_tag The object passed to the callback when on Edit Tag page.
		 */
		public function add_taxonomy_form_fields( $wp_tag ) {
			if ( isset( $wp_tag ) && $wp_tag instanceof WP_Term ) {
				$attachment_id    = get_term_meta( $wp_tag->term_id, 'tag_attachment_id', true );
				$image_attributes = wp_get_attachment_image_src( $attachment_id, 'full' );
				$attachment_url   = $image_attributes ? $image_attributes[0] : '';

				$happypoint_attachment_id    = get_term_meta( $wp_tag->term_id, 'happypoint_attachment_id', true );
				$happypoint_image_attributes = wp_get_attachment_image_src( $happypoint_attachment_id, 'full' );
				$happypoint_attachment_url   = $happypoint_image_attributes ? $happypoint_image_attributes[0] : ''; ?>

				<tr class="form-field edit-wrap term-image-wrap">
					<th>
						<label><?php esc_html_e( 'Image', 'planet4-master-theme' ); ?></label>
					</th>
					<td>
						<input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag-attachment-id field-id" value="<?php echo esc_attr( $attachment_id ); ?>" />
						<input type="hidden" name="tag_attachment" id="tag_attachment" class="tag-attachment-url field-url" value="<?php echo esc_url( $attachment_url ); ?>" />
						<button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
							<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'Associate this tag with an image.', 'planet4-master-theme' ); ?></p>
						<img class="attachment-thumbnail size-thumbnail" src="<?php echo esc_url( $attachment_url ); ?>"/>
						<i class="dashicons dashicons-dismiss <?php echo $image_attributes ? '' : 'hidden'; ?>" style="cursor: pointer;"></i>
					</td>
				</tr>
				<tr class="form-field edit-wrap term-happypoint-wrap">
					<th>
						<label><?php esc_html_e( 'Image Subscribe', 'planet4-master-theme' ); ?></label>
					</th>
					<td>
						<input type="hidden" name="happypoint_attachment_id" id="happypoint_attachment_id" class="happypoint-attachment-id field-id" value="<?php echo esc_attr( $happypoint_attachment_id ); ?>" />
						<input type="hidden" name="happypoint_attachment" id="happypoint_attachment" class="happypoint-attachment-url field-url" value="<?php echo esc_url( $happypoint_attachment_url ); ?>" />
						<button class="button insert-media add_media" name="insert_happypoint_image_button" id="insert_happypoint_image_button" type="button">
							<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'Choose a background image for the Subscribe block.', 'planet4-master-theme' ); ?></p>
						<img class="attachment-thumbnail size-thumbnail" src="<?php echo esc_url( $happypoint_attachment_url ); ?>"/>
						<i class="dashicons dashicons-dismiss <?php echo $happypoint_image_attributes ? '' : 'hidden'; ?>" style="cursor: pointer;"></i>
					</td>
				</tr>
			<?php } else { ?>
				<div class="form-field add-wrap term-image-wrap">
					<label><?php esc_html_e( 'Image', 'planet4-master-theme' ); ?></label>
					<input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag_attachment_id field-id" value="" />
					<input type="hidden" name="tag_attachment" id="tag_attachment" class="tag-attachment-url field-url" value="" />
					<button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
						<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Associate this tag with an image.', 'planet4-master-theme' ); ?></p>
					<img class="attachment-thumbnail size-thumbnail" src="" />
					<i class="dashicons dashicons-dismiss hidden" style="cursor: pointer;"></i>
				</div>
				<?php
			}
		}

		/**
		 * Save taxonomy custom field(s).
		 *
		 * @param int $term_id The ID of the WP_Term object that is added or edited.
		 */
		public function save_taxonomy_meta( $term_id ) {
			$field_id       = 'tag_attachment_id';
			$field_url      = 'tag_attachment';
			$attachment_id  = filter_input( INPUT_POST, $field_id, FILTER_VALIDATE_INT );
			$attachment_url = filter_input( INPUT_POST, $field_url, FILTER_VALIDATE_URL );

			if ( $this->validate( $attachment_id ) ) {
				update_term_meta( $term_id, $field_id, $attachment_id );
				update_term_meta( $term_id, $field_url, $attachment_url );
			}

			$field_id       = 'happypoint_attachment_id';
			$field_url      = 'happypoint_attachment';
			$attachment_id  = filter_input( INPUT_POST, $field_id, FILTER_VALIDATE_INT );
			$attachment_url = filter_input( INPUT_POST, $field_url, FILTER_VALIDATE_URL );

			if ( $this->validate( $attachment_id ) ) {
				update_term_meta( $term_id, $field_id, $attachment_id );
				update_term_meta( $term_id, $field_url, $attachment_url );
			}
		}

		/**
		 * Add custom column.
		 *
		 * @param array $columns Associative array with the columns of the taxonomy.
		 *
		 * @return array Associative array with the columns of the taxonomy.
		 */
		public function edit_taxonomy_columns( $columns ) : array {
			$columns['image'] = __( 'Image', 'planet4-master-theme' );
			return $columns;
		}

		/**
		 * Apply custom output to a custom column.
		 *
		 * @param string $output The html to be applied to each row of the $column.
		 * @param string $column The name of the column to be managed.
		 * @param int    $term_id The ID of the WP_Term object that is added or edited.
		 *
		 * @return string The new html to be applied to each row of the $column.
		 */
		public function manage_taxonomy_custom_column( $output, $column, $term_id ) : string {
			if ( 'image' === $column ) {
				$attachment_id = get_term_meta( $term_id, 'tag_attachment_id', true );
				$output        = wp_get_attachment_image( $attachment_id );
			}
			return $output;
		}

		/**
		 * Make column sortable.
		 *
		 * @param array $columns Associative array with the columns of the taxonomy.
		 *
		 * @return array Associative array with the columns of the taxonomy.
		 */
		public function manage_taxonomy_custom_sortable_column( $columns ) : array {
			$columns['image'] = 'image';
			return $columns;
		}

		/**
		 * Validates the input.
		 *
		 * @param int    $id The attachment id to be validated.
		 *
		 * @return bool True if validation is ok, false if validation fails.
		 */
		public function validate( $id ) : bool {
			if ( $id < 0 ) {
				return false;
			}
			return true;
		}

		/**
		 * Load assets.
		 */
		public function enqueue_admin_assets() {
			if ( ! is_admin() || strpos( get_current_screen()->taxonomy, $this->taxonomy ) === false ) {
				return;
			}
			wp_register_script( $this->taxonomy, get_template_directory_uri() . "/assets/js/$this->taxonomy.js", array( 'jquery' ), null, true );
			wp_localize_script( $this->taxonomy, 'localizations', $this->localizations );
			wp_enqueue_script( $this->taxonomy );
			wp_enqueue_media();
		}
	}
}
