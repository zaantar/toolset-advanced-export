<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;


/**
 * Handles the migration of Customizer settings, theme_mods and arbitrary theme options (if supported).
 *
 * If invoked on the Customizer admin page, theoretically it might break it.
 *
 * Props to the Customizer Export/Import plugin created by the Beaver Builder team. Most of the implementation ideas
 * has been taken from it.
 *
 * @since 1.0
 */
class Customizer implements IMigration_Handler {


    /**
     * @inheritdoc
     * @return e\IMigration_Data
     * @since 1.0
     */
    function export() {

        $output = [
            // In case a child theme is used, 'theme' will hold name of the child theme and 'template' a name of
            // the parent one. Otherwise, these values will be equal (name == name of the directory in wp-content/themes).
            'theme' => get_stylesheet(),
            'template' => get_template(),

            'theme_mods' => $this->export_theme_mods(),
            'custom_options' => $this->export_custom_theme_options(),
            'customizer' => $this->export_customizer(),
	        'custom_css' => $this->export_custom_css(),
        ];


        return e\Migration_Data_Nested_Array::from_array( $output );
    }


    /**
     * Export theme_mods as an associative array.
     *
     * @return array
     * @since 1.0
     */
    private function export_theme_mods() {
        $mods = toolset_ensarr( get_theme_mods() );

        // This references menus by ID and it would break on import.
        // Menu locations migration will be handled in the Menu migration handler.
        unset( $mods['nav_menu_locations'] );

        return $mods;
    }


    /**
     * Returns a migration handler for custom theme options specified by a third party.
     *
     * For this to work, the theme needs to hook into toolset_export_custom_theme_options on both export and import.
     * The migration handler is cached.
     *
     * @return Option_Array_Custom
     * @since 1.0
     */
    private function get_custom_theme_option_migration_handler() {

        static $migration_handler = null;

        if( null == $migration_handler ) {

            /**
             * toolset_export_custom_theme_options
             *
             * Allows for specifying a set of theme-specific options that should be exported and imported
             * together with Customizer settings.
             *
             * Each option can be:
             * - a string, which will be interpreted as an option name, and it will be exported without sanitization.
             * - an associative array, in which case its key will be interpreted as the option name; the array can
             *   contain a 'sanitize_callback' callable and a 'default_value'
             * - an instance of Option (or any subclass of it), which will be used directly
             *
             * Invalid items will result in an exception.
             *
             * @param [] $theme_options
             * @return []
             * @since 1.0
             * @throws \InvalidArgumentException
             */
            $options = toolset_ensarr( apply_filters( 'toolset_export_custom_theme_options', [] ) );

            // For each option, create a dedicated migration handler.
            $option_migration_handlers = array_map( function ( $option, $index ) {

                $identity = function ( $i ) {
                    return $i;
                };

                if ( is_string( $option ) ) {
                    // No sanitization or default value available
                    return new Option( $option, $identity );

                } elseif ( is_array( $option ) && is_string( $index ) ) {
                    // We *may* have some additional information about sanitization and default value.

                    $sanitize_callback = toolset_getarr( $option, 'sanitize_callback', $identity );
                    if ( ! is_callable( $sanitize_callback ) ) {
                        $sanitize_callback = $identity;
                    }

                    // false is the default default value in get_option().
                    $default_value = toolset_getarr( $option, 'default_value', false );

                    return new Option( $index, $sanitize_callback, $default_value );

                } elseif( $option instanceof Option ) {
                    // This allows for passing a Post_Option, for example.
                    return $option;

                } else {
                    throw new \InvalidArgumentException( 'Invalid option configuration provided via toolset_export_custom_theme_options.' );
                }
            }, $options, array_keys( $options ) );

            $migration_handler = new Option_Array_Custom( $option_migration_handlers );
        }

        return $migration_handler;
    }


    /**
     * Exports custom theme options specified by a third party.
     *
     * @return array
     * @since 1.0
     */
    private function export_custom_theme_options() {
        $migration_handler = $this->get_custom_theme_option_migration_handler();
        return $migration_handler->export()->to_array();
    }


    /**
     * Exports Customizer settings.
     *
     * The settings depend on the active theme and other plugins that may be extending the Customize page.
     *
     * See self::get_customize_manager() for technical details and possible side-effects.
     *
     * @return array
     * @since 1.0
     */
    private function export_customizer() {

        $wp_customize = $this->get_customize_manager();

        /** @var \WP_Customize_Setting[] $customizer_settings */
        $customizer_settings = $wp_customize->settings();

        // An array of core options that shouldn't be exported.
        static $core_options = array(
            'blogname',
            'blogdescription',
            'show_on_front',
            'page_on_front',
            'page_for_posts',
        );

        // Don't save core options or widget or sidebar data.
        $customizer_settings = array_filter( $customizer_settings, function( $setting, $key ) use( $core_options ) {
            return (
                'option' == $setting->type
                && false == stristr( $key, 'widget_' )
                && false == stristr( $key, 'sidebars_' )
                && ! in_array( $key, $core_options )
            );
        }, ARRAY_FILTER_USE_BOTH );

        /**
         * toolset_export_customizer_settings
         *
         * Add, remove or modify customizer settings that are to be exported.
         *
         * @param \WP_Customize_Setting[]
         * @return \WP_Customize_Setting[]
         * @since 1.0
         */
        $customizer_settings = apply_filters( 'toolset_export_customizer_settings', $customizer_settings );

        // Transform into a simple associative array (setting name => value).
        $options = array_reduce( $customizer_settings, function( $result, $setting ) {
            /** @var \WP_Customize_Setting $setting */
            $result[ $setting->id ] = $setting->value();
            return $result;
        }, [] );

        return $options;
    }


	/**
	 * Get the custom CSS currently used for the active theme.
	 *
	 * @return string
	 * @since 1.0
	 */
    private function export_custom_css() {
    	return wp_get_custom_css();
    }


    /**
     * Obtain the \WP_Customize_Manager instance.
     *
     * The process is rather tricky and fragile, with a possible side-effect on the Customizer page.
     * Works well with WordPress 4.7
     *
     * @return \WP_Customize_Manager
     * @since 1.0
     */
    private function get_customize_manager() {

        /**
         * toolset_export_get_customize_manager
         *
         * Allow for hijacking the process of obtaining a \WP_Customize_Manager instance and implementing
         * a custom hack.
         *
         * @param null
         * @return \WP_Customize_Manager
         * @since 1.0
         */
        $custom_customize_manager = apply_filters( 'toolset_export_get_customize_manager', null );
        if( $custom_customize_manager instanceof \WP_Customize_Manager ) {
            return $custom_customize_manager;
        }

        /** @var \WP_Customize_Manager $wp_customize */
        global $wp_customize;

        // The object is available only on-demand, mainly on the Customize page after plugins_loaded (and then passed
        // by the customize_register action). But at this point, we can be in the middle of a WP-CLI command or an AJAX
        // request. We have no other choice than hacking WordPress into loading it.
        if( null == $wp_customize ) {

            $_REQUEST['wp_customize'] = 'on';
            _wp_customize_include();

            // Populate customizer options.
            //
            // Some themes (including twentyseventeen) hook into customize_register with the default priority 10
            // but assume that \WP_Customize_Manager::register_controls() has already run. This is exactly what happens
            // on the Customize page but here, we have a race condition. Thus we have to re-register the relevant hook
            // with a lower priority.
            remove_action( 'customize_register', array( $wp_customize, 'register_controls' ) );
            add_action( 'customize_register', array( $wp_customize, 'register_controls' ), -1 );

            // This is now redundant (we don't expect this to be happening on the actual Customizer page, it would
            // probably break things there).
            remove_action( 'customize_register', array( $wp_customize, 'schedule_customize_register' ), 1 );
            remove_action( 'wp_loaded',   array( $wp_customize, 'wp_loaded' ) );

            // Now we can finally populate the customizer options.
            do_action( 'customize_register', $wp_customize );
        }

        return $wp_customize;
    }


    /**
     * @inheritdoc
     *
     * @param e\IMigration_Data $data Correct migration data for the section
     *
     * @return \Toolset_Result|\Toolset_Result_Set
     * @throws \InvalidArgumentException
     * @since 1.0
     */
    function import( $data ) {

        $data = $data->to_array();
        $theme_matches = ( toolset_getarr( $data, 'theme' ) == get_stylesheet() );
        $template_matches = ( toolset_getarr( $data, 'template' ) == get_template() );

        if( ! $theme_matches || ! $template_matches ) {
            return new \Toolset_Result( false, __( 'The Customizer settings to be imported are not for the current theme.', 'toolset-advanced-export' ) );
        }

        $results = new \Toolset_Result_Set();

        $results->add( $this->import_custom_options( toolset_ensarr( toolset_getarr( $data, 'custom_options' ) ) ) );
        $results->add( $this->import_customizer( toolset_ensarr( toolset_getarr( $data, 'customizer' ) ) ) );
        $results->add( $this->import_theme_mods( toolset_ensarr( toolset_getarr( $data, 'theme_mods' ) ) ) );
        $results->add( $this->import_custom_css( toolset_getarr( $data, 'custom_css' ) ) );

        // Finish the actions on Customizer.
        do_action( 'customize_save_after', $this->get_customize_manager() );

        return $results;
    }


    /**
     * Import the custom theme options.
     *
     * The options need to be registered via the toolset_export_custom_theme_options also on import.
     *
     * @param array $custom_options
     * @return \Toolset_Result|\Toolset_Result_Set
     * @since 1.0
     */
    private function import_custom_options( $custom_options ) {
        try {
            $migration_handler = $this->get_custom_theme_option_migration_handler();

            return $migration_handler->import( e\Migration_Data_Nested_Array::from_array( $custom_options ) );
        } catch( \Exception $e ) {
            return new \Toolset_Result( $e );
        }
    }


    /**
     * Import Customizer options.
     *
     * @param $customizer_options
     * @return \Toolset_Result
     * @since 1.0
     */
    private function import_customizer( $customizer_options ) {

        $wp_customize = $this->get_customize_manager();

        foreach ( $customizer_options as $option_key => $option_value ) {
            $option = new e\Customize_Setting( $wp_customize, $option_key );
            $option->import( $option_value );
        }

        // Now persist the customization.
        do_action( 'customize_save', $wp_customize );

        return new \Toolset_Result( true );
    }


    /**
     * Import Theme Mods.
     *
     * @param array $theme_mods
     * @return \Toolset_Result
     * @since 1.0
     */
    private function import_theme_mods( $theme_mods ) {

        $wp_customize = $this->get_customize_manager();

        foreach ( $theme_mods as $mod_name => $mod_value ) {

            do_action( 'customize_save_' . $mod_name, $wp_customize );

            set_theme_mod( $mod_name, $mod_value );
        }

        return new \Toolset_Result( true );

    }


	/**
	 * Import custom CSS code for the currently active theme.
	 *
	 * @param string $custom_css
	 * @return \Toolset_Result
	 * @since 1.0
	 */
    private function import_custom_css( $custom_css ) {
	    $result = wp_update_custom_css_post( $custom_css );
	    if( $result instanceof \WP_Post ) {
	    	return new \Toolset_Result( true, __( 'Custom CSS imported.', 'toolset-advanced-export' ) );
	    } elseif( $result instanceof \WP_Error ) {
	    	return new \Toolset_Result( $result );
	    } else {
	    	return new \Toolset_Result( false, __( 'An error happened when importing custom CSS', 'toolset-advanced-export' ) );
	    }
    }

}