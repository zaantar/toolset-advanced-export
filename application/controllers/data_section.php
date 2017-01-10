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
	    return [
	        self::SETTINGS_READING => sprintf(
	            '%s (<em>%s</em>)',
                __( 'Reading settings', 'toolset-advanced-export' ),
                __( 'Settings --> Reading', 'toolset-advanced-export' )
            ),
            self::APPEARANCE_CUSTOMIZE => sprintf(
                '%s (<em>%s</em>)',
                __( 'Customizer setup', 'toolset-advanced-export' ),
                __( 'Appearance --> Customize', 'toolset-advanced-export' )
            ),
            self::APPEARANCE_MENU => sprintf(
                '%s (<em>%s</em>)',
                __( 'Menu setup', 'toolset-advanced-export' ),
                __( 'Appearance --> Menus', 'toolset-advanced-export' )
            ),
            self::APPEARANCE_WIDGETS => sprintf(
                '%s (<em>%s</em>)',
                __( 'Widget setup', 'toolset-advanced-export' ),
                __( 'Appearance --> Widgets', 'toolset-advanced-export' )
            ),
        ];
    }



    public static function label( $section_name ) {
	    $labels = self::labels();
	    return ( array_key_exists( $section_name, $labels ) ? $labels[ $section_name ] : '' );
    }

}