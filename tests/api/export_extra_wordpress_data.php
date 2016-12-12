<?php

namespace ToolsetExtraExport\Tests\Api;
use ToolsetExtraExport\Tests as tests;

class Export_Extra_Wordpress_Data extends tests\Test_Case {

    const FILTER_NAME = 'toolset_export_extra_wordpress_data';


    function test_export_filter_works() {
        // passing invalid argument, we should get an empty array of results
        $this->assertTrue( is_array( apply_filters( self::FILTER_NAME, null ) ) );
    }


    function get_default_values() {
        return [
            [
                'section_name' => 'settings_reading',
                'default_value' => [
                    'settings_reading' => [
                        'blog_charset' => ['UTF-8'],
                        'show_on_front' => ['posts'],
                        'page_on_front' => [ 'exists' => false ],
                        'page_for_posts' => [ 'exists' => false ],
                        'posts_per_page' => [10],
                        'posts_per_rss' => [10],
                        'rss_use_excerpt' => [0],
                        'blog_public' => [1]
                    ]
                ]
            ]
        ];
    }


    /**
     * @dataProvider get_default_values
     *
     * @param $section_name
     * @param $expected_default_value
     */
    function test_default_values( $section_name, $expected_default_value ) {

        $output = apply_filters( self::FILTER_NAME, null, [ $section_name ] );

        $this->assertNotNull( $output );

        $this->assertAssociativeArrayEquals( $expected_default_value, $output, 'Default values are not equal' );

    }
}