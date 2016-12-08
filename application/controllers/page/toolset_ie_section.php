<?php

namespace ToolsetExtraExport\Gui;

use ToolsetExtraExport as e;


/**
 * Adds a tab in the Toolset Export / Import page.
 *
 * @since 1.0
 */
class Toolset_Ie_Section extends Page_Import_Export {

    // Slug of the Toolset Import/Export section
    const TAB_SLUG = 'toolset_extra_export';

    const EXPORT_SECTION_SLUG = 'export';


    /**
     * @return Toolset_Ie_Section
     */
    public static function get_instance() {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::get_instance();
    }


    /**
     * Registers the tab on the page.
     *
     * This needs to be added to the toolset_filter_register_export_import_section hook.
     * @param $sections
     *
     * @return mixed
     */
    public function register( $sections ) {

        $sections[ self::TAB_SLUG ] = [
            'slug' => self::TAB_SLUG,
            'title' => __( 'Theme (TBT)', 'toolset-ee' ),
            'icon' => '<i class="icon-toolset-logo ont-icon-16"></i>', // todo add specific icon
            'items' => [
                'export' => [
                    'slug' => self::EXPORT_SECTION_SLUG,
                    'title' => __( 'Export Theme settings', 'toolset-ee' ),
                    'callback' => [ $this, 'render' ]
                ]
            ]
        ];

        return $sections;
    }

}