<?php

namespace ToolsetAdvancedExport;

/**
 * A class that extends WP_Customize_Setting so we can access
 * the protected updated method when importing options.
 *
 * Props Custom Export/Import plugin.
 *
 * @since 1.0
 */
class Customize_Setting extends \WP_Customize_Setting {


    public function __construct( \WP_Customize_Manager $manager, $id, $args = null ) {

        // Make our life a bit simpler.
        if( null == $args ) {
            $args = [
                'default' => '',
                'type' => 'option',
                'capability' => 'edit_theme_options'
            ];
        }

        parent::__construct( $manager, $id, $args );
    }

    /**
     * Import an option value for this setting.
     *
     * @since 1.0
     * @param mixed $value The option value.
     * @return void
     */
    public function import( $value )
    {
        $this->update( $value );
    }
}