<?php

namespace ToolsetAdvancedExport;

/**
 * Enum class holding names of different site sections that can be exported by this plugin.
 *
 * @since 1.0
 */
final class Data_Section {

	private function __construct() { }

	private function __clone() { }

	const SETTINGS_READING = 'settings_reading';
	const APPEARANCE_CUSTOMIZE = 'appearance_customize';
	const APPEARANCE_MENU = 'appearance_menu';
	const APPEARANCE_WIDGETS = 'appearance_widgets';


	public static function values() {
	    return [
	        self::SETTINGS_READING,
            self::APPEARANCE_CUSTOMIZE,
            self::APPEARANCE_MENU,
            self::APPEARANCE_WIDGETS
        ];
    }


	public static function labels() {
	    $label_descriptions = [
	        self::SETTINGS_READING => [
                'section_name' => __( 'Reading settings', 'toolset-advanced-export' ),
                'location' => __( 'Settings &rarr; Reading', 'toolset-advanced-export' ),
		        'url' => admin_url( 'options-reading.php' )
            ],
            self::APPEARANCE_CUSTOMIZE => [
                'section_name' => __( 'Customizer setup', 'toolset-advanced-export' ),
                'location' => __( 'Appearance &rarr; Customize', 'toolset-advanced-export' ),
	            'url' => admin_url( 'customize.php' )
            ],
            self::APPEARANCE_MENU => [
                'section_name' => __( 'Menu setup', 'toolset-advanced-export' ),
                'location' => __( 'Appearance &rarr; Menus', 'toolset-advanced-export' ),
	            'url' => admin_url( 'nav-menus.php' )
            ],
            self::APPEARANCE_WIDGETS => [
                'section_name' => __( 'Widget setup', 'toolset-advanced-export' ),
                'location' => __( 'Appearance &rarr; Widgets', 'toolset-advanced-export' ),
	            'url' => admin_url( 'widgets.php' )
            ],
        ];

	    $labels = array_reduce(
	    	array_keys( $label_descriptions ),
		    function( $labels, $label_key ) use( $label_descriptions ) {
	    		$label_description = $label_descriptions[ $label_key ];

		        $labels[ $label_key ] = sprintf(
		        	'%s (<em><a href="%s" target="_blank">%s</a></em>)',
			        $label_description['section_name'],
			        esc_url_raw( $label_description['url'] ),
			        $label_description['location']
		        );

		        return $labels;
	        }, []
	    );

	    return $labels;
    }



    public static function label( $section_name ) {
	    $labels = self::labels();
	    return ( array_key_exists( $section_name, $labels ) ? $labels[ $section_name ] : '' );
    }

}