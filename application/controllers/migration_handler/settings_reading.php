<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;


/**
 * Handles migration of the settings on the Settings/Reading page.
 *
 * @since 1.0
 */
class Settings_Reading extends Option_Array {

	/**
     * @inheritdoc
	 * @return Option[]
	 */
	protected function get_option_list() {

	    static $option_list = null;

	    if( null === $option_list ) {
            // https://codex.wordpress.org/Option_Reference and get_bloginfo() were used as a source of default values.
            $option_list = [
                new Option( 'blog_charset', 'sanitize_text_field', 'UTF-8' ),

                new Option( 'show_on_front', function ( $value ) {
                    if ( ! in_array( $value, [ 'posts', 'page' ] ) ) {
                        return 'posts';
                    }

                    return $value;
                }, 'posts' ),
                new Post_Option( 'page_on_front', 0 ),
                new Post_Option( 'page_for_posts', 0 ),
                new Option( 'posts_per_page', function ( $value ) {
                    $value = (int) $value;

                    // minimum is 1
                    if ( 0 >= $value ) {
                        return 10;
                    }

                    return $value;
                }, 10 ),
                new Option( 'posts_per_rss', function ( $value ) {
                    $value = (int) $value;

                    // minimum is 1
                    if ( 0 >= $value ) {
                        return 10;
                    }

                    return $value;
                }, 10 ),
                new Option( 'rss_use_excerpt', function ( $value ) {
                    return ( 1 === (int) $value ) ? 1 : 0;
                }, 0 ),
                new Option( 'blog_public', function ( $value ) {
                    return ( 0 === (int) $value ) ? 0 : 1;
                }, 1 )
            ];
        }

        return $option_list;
	}

}