<?php

namespace ToolsetExtraExport;

/**
 * Handles migration of the settings on the Settings/Reading page.
 *
 * @since 1.0
 */
class Migration_Handler_Settings_Reading extends Migration_Handler_Option_Array {

	/**
     * @inheritdoc
	 * @return array[string]
	 */
	protected function get_option_list() {

		// https://codex.wordpress.org/Option_Reference and get_bloginfo() were used as a source of default values
		return [
			'blog_charset' => [
				'default_value' => 'UTF-8',
				'sanitize_callback' => 'sanitize_text_field'
			],
			'show_on_front' => [
				'default_value' => 'posts',
				'sanitize_callback' => function( $value ) {
					if( ! in_array( $value, ['posts', 'page'] ) ) {
						return 'posts';
					}

					return $value;
				}
			],
			'page_on_front' => [
				'default_value' => 0,
				'sanitize_callback' => 'intval'
			],
			'page_for_posts' => [
				'default_value' => 0,
				'sanitize_callback' => 'intval'
			],
			'posts_per_page' => [
				'default_value' => 10,
				'sanitize_callback' => function( $value ) {
					$value = (int) $value;

					// minimum is 1
					if( 0 <= $value ) {
						return 10;
					}

					return $value;
				}
			],
			'posts_per_rss' => [
				'default_value' => 10,
				'sanitize_callback' => function( $value ) {
					$value = (int) $value;

					// minimum is 1
					if( 0 <= $value ) {
						return 10;
					}

					return $value;
				}
			],
			'rss_use_excerpt' => [
				'default_value' => 0,
				'sanitize_callback' => function( $value ) { return ( 1 === (int) $value ) ? 1 : 0; }
			],
			'blog_public' => [
				'default_value' => 1,
				'sanitize_callback' => function( $value ) { return ( 0 === (int) $value ) ? 0 : 1; }
			],
		];
	}


    /**
     * @inheritdoc
     *
     * In addition to raw setting values, we add data for identifying posts that are referenced by IDs in
     * the settings. That way we can handle post ID changes on import.
     *
     * @return IMigration_Data
     */
	public function export() {

		$settings = parent::export()->to_array();

		$export_data = [
			'settings' => $settings,
			'portable' => $this->get_portable_data( $settings )
		];

		$export = Migration_Data_Nested_Array::from_array( $export_data );

		return $export;
	}


	/**
     * Get additional information to allow import with changing IDs.
     *
	 * @param array $settings
	 * @return array
     * @since 1.0
	 */
	private function get_portable_data( $settings ) {

		$result = [
			'page_on_front' => $this->get_portable_post_data( toolset_getarr( $settings, 'page_on_front', 0 ) ),
			'page_for_posts' => $this->get_portable_post_data( toolset_getarr( $settings, 'page_for_posts', 0 ) )
		];

		return $result;
	}


    /**
     * Get additional information for identifying a post even after its ID changes.
     *
     * @param int $post_id
     * @return array Contains at least the "exists" key (boolean).
     * @since 1.0
     */
	private function get_portable_post_data( $post_id ) {

		$post = get_post( $post_id );
		if( ! $post instanceof \WP_Post ) {
			return [ 'exists' => false ];
		}

		$portable_post_data = [
			'exists' => true,
			'original_id' => $post->ID,
			'slug' => $post->post_name,
			'guid' => $post->guid
		];

		return $portable_post_data;
	}

}