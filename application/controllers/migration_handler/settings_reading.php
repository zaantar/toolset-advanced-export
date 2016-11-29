<?php

namespace ToolsetExtraExport;

/**
 * Handles migration of the settings on the Settings/Reading page.
 *
 * @since 1.0
 */
class Migration_Handler_Settings_Reading extends Migration_Handler_Option_Array {

	/**
	 * @return array[string]
	 */
	protected function get_option_list() {
		// todo defaults, sanitization
		return [
			'blog_charset' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
			'show_on_front' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
			'page_on_front' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
			'page_for_posts' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
			'posts_per_page' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
			'posts_per_rss' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
			'rss_use_excerpt' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
			'blog_public' => [
				'default_value' => '',
				'sanitize_callback' => ''
			],
		];
	}

}