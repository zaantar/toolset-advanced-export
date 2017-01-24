<?php

namespace ToolsetAdvancedExport\Gui;

use ToolsetAdvancedExport as e;


/**
 * Adds a tab in the Toolset Export / Import page.
 *
 * @since 1.0
 */
class Toolset_Ie_Section extends Page_Import_Export {

    // Slug of the Toolset Import/Export section
    const TAB_SLUG = 'toolset_advanced_export';

    const EXPORT_SECTION_SLUG = 'export';
    const IMPORT_SECTION_SLUG = 'import';


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

    	$self = $this;

    	$tab_items = [
		    'export' => [
			    'slug' => self::EXPORT_SECTION_SLUG,
			    'title' => __( 'Export Theme settings', 'toolset-advanced-export' ),
			    'callback' => function() use( $self ) { $self->render( 'export' ); }
		    ]
	    ];

	    /**
	     * toolset_extra_export_show_import_gui
	     *
	     * Allow for showing or hiding the Import part of the GUI on the Toolset Export / Import page.
	     *
	     * @param bool $show_import_gui
	     * @since 1.0
	     */
    	if( apply_filters( 'toolset_extra_export_show_import_gui', false ) ) {
    		$tab_items['import'] = [
			    'slug' => self::IMPORT_SECTION_SLUG,
			    'title' => __( 'Import Theme settings', 'toolset-advanced-export' ),
			    'callback' => function() use( $self ) { $self->render( 'import' ); }
		    ];
	    }

        $sections[ self::TAB_SLUG ] = [
            'slug' => self::TAB_SLUG,
            'title' => __( 'Theme (TBT)', 'toolset-advanced-export' ),

	        // ontego-resources icon
            'icon' => '<i class="icon-toolset-export ont-icon-16"></i>',
            'items' => $tab_items
        ];

        return $sections;
    }

}