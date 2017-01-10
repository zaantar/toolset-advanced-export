<?php

namespace ToolsetAdvancedExport;

/**
 * Holds a reference to a post that should survive the post's ID change.
 *
 * Use to_post_id() in order to determine ID on the current site.
 *
 * @since 1.0
 */
class Migration_Data_Portable_Post extends Migration_Data_Raw {


	/**
	 * Migration_Data_Portable_Post constructor.
	 *
	 * @param $value int|array Old post ID in order to generate the portable post data, or the array with portable
	 *      post data to be accepted without changes.
	 */
	public function __construct( $value ) {
		if( ! is_array( $value ) ) {
			$value = utils\get_portable_post_data( $value );
		}
		parent::__construct( $value );
	}


	public static function from_array( $array_input ) {
		return new self( $array_input );
	}


	public function to_array() {
		return $this->value;
	}


	/**
	 * Find a matching post that exists on current site.
	 *
	 * Tries to match the GUID and if that fails, it will search by post slug and type.
	 *
	 * @return int Existing post ID or zero.
	 */
	public function to_post_id() {

		$data = $this->value;

		if( ! toolset_getarr( $data, 'exists', false ) ) {
			return 0;
		}

		$post_id = $this->get_post_id_by_guid( toolset_getarr( $data, 'guid', null ) );

		if( 0 === $post_id ) {
			$post_id = $this->get_post_id_by_slug(
				toolset_getarr( $data, 'slug', null ),
				toolset_getarr( $data, 'post_type', null )
			);
		}

		return $post_id;
	}


	private function get_post_id_by_guid( $guid ) {
		global $wpdb;

		$post_id = $wpdb->get_var(
			$wpdb->prepare( "SELECT ID from {$wpdb->posts} WHERE guid = %s", $guid )
		);

		return (int) $post_id;
	}


	private function get_post_id_by_slug( $slug, $post_type ) {

		if( ! is_string( $slug ) || ! is_string( $post_type ) ) {
			return 0;
		}

		$query = new \WP_Query( [
			'post_type' => $post_type,
			'name' => $slug,
			'post_status' => 'any',
			'fields' => 'ids',
			'posts_per_page' => 1,
			'ignore_sticky_posts' => true,
			'orderby' => 'none',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'suppress_filters' => true
		] );

		if( $query->post_count != 1 ) {
			return 0;
		}

		return $query->posts[0];
	}


}