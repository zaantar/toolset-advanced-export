<?php

namespace ToolsetAdvancedExport\MigrationHandler;

use ToolsetAdvancedExport as e;


/**
 * Manage the migration of widgets.
 *
 * The relevant part was, with the deepest gratitude to its authors, taken from
 * the Widget Importer && Exporter plugin (WIE) with only very little changes.
 *
 * It imports all active widgets. It expects the same active theme as on export, and  the same set of plugins that
 * register the types active widgets. If a sidebar is missing from the site, its widgets are imported as inactive
 * and the migration handler will report an error.
 *
 * @since 1.0
 */
class Widgets implements IMigration_Handler {

	/**
	 * @inheritdoc
	 *
	 * @return e\IMigration_Data
	 */
	function export() {

		$output = [
			'wie' => $this->wie_generate_export_data()
		];

		return e\Migration_Data_Nested_Array::from_array( $output );
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

		if ( ! array_key_exists( 'wie', $data ) ) {
			throw new \InvalidArgumentException( 'Data for the widget import are corrupt.' );
		}

		$results = new \Toolset_Result_Set();

		try {
			$wie_results = $this->wie_import_data( (object) $data['wie'] );
			$results->add( $this->process_wie_import_results( $wie_results ) );
		} catch( \Exception $e ) {
			$results->add( $e );
		}

		return $results;
	}


	/**
	 * Process the result array coming from the WIE code and generate a \Toolset_Result_Set.
	 *
	 * @param array $wie_results
	 *
	 * @return \Toolset_Result_Set
	 * @since 1.0
	 */
	private function process_wie_import_results( $wie_results ) {

		$results = new \Toolset_Result_Set();

		foreach ( $wie_results as $sidebar_id => $sidebar ) {

			if ( ! isset( $sidebar['widgets'] ) ) {
				$results->add( false, sprintf(
						__( 'Sidebar "%s" does not exist in the active theme.', 'toolset-advanced-export' ),
						$sidebar['name'] )
				);
				continue;
			}

			foreach ( $sidebar['widgets'] as $widget_instance_id => $widget ) {

				if ( empty( $widget['message_type'] ) ) {
					continue;
				}

				$is_success = ( 'success' == $widget['message_type'] );

				$results->add( $is_success, sprintf(
					__( 'Widget "%s" (%s) in the sidebar "%s": %s' ),
					$widget['name'],
					$widget['title'],
					$sidebar['name'],
					$widget['message']
				) );
			}
		}

		return $results;

	}



	// WIE code below.
	//
	// The methods below have been taken directly from WIE with minimal changes:
	// - removed most of the filter and action hooks
	// - polished the code style a little
	// - renamed the textdomain in display strings
	// - the methods now accept nested arrays with widget data instead of JSON strings.

	/**
	 * Generate export data
	 *
	 * @return array
	 */
	private function wie_generate_export_data() {

		// Get all available widgets site supports
		$available_widgets = $this->wie_available_widgets();

		// Get all widget instances for each widget
		$widget_instances = array();
		foreach ( $available_widgets as $widget_data ) {

			// Get all instances for this ID base
			$instances = get_option( 'widget_' . $widget_data['id_base'] );

			// Have instances
			if ( ! empty( $instances ) ) {

				// Loop instances
				foreach ( $instances as $instance_id => $instance_data ) {

					// Key is ID (not _multiwidget)
					if ( is_numeric( $instance_id ) ) {
						$unique_instance_id = $widget_data['id_base'] . '-' . $instance_id;
						$widget_instances[ $unique_instance_id ] = $instance_data;
					}
				}
			}
		}

		// Gather sidebars with their widget instances
		$sidebars_widgets = get_option( 'sidebars_widgets' ); // get sidebars and their unique widgets IDs
		$sidebars_widget_instances = array();
		foreach ( $sidebars_widgets as $sidebar_id => $widget_ids ) {

			// Skip inactive widgets
			if ( 'wp_inactive_widgets' == $sidebar_id ) {
				continue;
			}

			// Skip if no data or not an array (array_version)
			if ( ! is_array( $widget_ids ) || empty( $widget_ids ) ) {
				continue;
			}

			// Loop widget IDs for this sidebar
			foreach ( $widget_ids as $widget_id ) {

				// Is there an instance for this widget ID?
				if ( isset( $widget_instances[ $widget_id ] ) ) {

					// Add to array
					$sidebars_widget_instances[ $sidebar_id ][ $widget_id ] = $widget_instances[ $widget_id ];
				}
			}
		}

		// Return contents
		return $sidebars_widget_instances;
	}


	/**
	 * Available widgets
	 *
	 * Gather site's widgets into array with ID base, name, etc.
	 * Used by export and import functions.
	 *
	 * @global array $wp_registered_widget_updates
	 * @return array Widget information
	 */
	private function wie_available_widgets() {

		global $wp_registered_widget_controls;

		$widget_controls = $wp_registered_widget_controls;

		$available_widgets = array();

		foreach ( $widget_controls as $widget ) {

			if ( ! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] ) ) { // no dupes
				$available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
				$available_widgets[ $widget['id_base'] ]['name'] = $widget['name'];
			}
		}

		return $available_widgets;

	}


	/**
	 * Import widget data
	 *
	 * @global array $wp_registered_sidebars
	 *
	 * @param object $data widget data
	 *
	 * @return array Results array
	 */
	private function wie_import_data( $data ) {

		global $wp_registered_sidebars;

		// Have valid data?
		// If no data or could not decode
		if ( empty( $data ) || ! is_object( $data ) ) {
			throw new \InvalidArgumentException(
				esc_html__( 'Import data could not be read. Please try a different file.', 'toolset-advanced-export' )
			);
		}

		// Get all available widgets site supports
		$available_widgets = $this->wie_available_widgets();

		// Get all existing widget instances
		$widget_instances = array();
		foreach ( $available_widgets as $widget_data ) {
			$widget_instances[ $widget_data['id_base'] ] = get_option( 'widget_' . $widget_data['id_base'] );
		}

		// Begin results
		$results = array();

		// Loop import data's sidebars
		foreach ( $data as $sidebar_id => $widgets ) {

			// Skip inactive widgets
			// (should not be in export file)
			if ( 'wp_inactive_widgets' == $sidebar_id ) {
				continue;
			}

			// Check if sidebar is available on this site
			// Otherwise add widgets to inactive, and say so
			if ( isset( $wp_registered_sidebars[ $sidebar_id ] ) ) {
				$sidebar_available = true;
				$use_sidebar_id = $sidebar_id;
				$sidebar_message_type = 'success';
				$sidebar_message = '';
			} else {
				$sidebar_available = false;
				$use_sidebar_id = 'wp_inactive_widgets'; // add to inactive if sidebar does not exist in theme
				$sidebar_message_type = 'error';
				$sidebar_message = esc_html__( 'Sidebar does not exist in theme (using Inactive)', 'toolset-advanced-export' );
			}

			// Result for sidebar
			$results[ $sidebar_id ]['name'] = ! empty( $wp_registered_sidebars[ $sidebar_id ]['name'] ) ? $wp_registered_sidebars[ $sidebar_id ]['name'] : $sidebar_id; // sidebar name if theme supports it; otherwise ID
			$results[ $sidebar_id ]['message_type'] = $sidebar_message_type;
			$results[ $sidebar_id ]['message'] = $sidebar_message;
			$results[ $sidebar_id ]['widgets'] = array();

			// Loop widgets
			foreach ( $widgets as $widget_instance_id => $widget ) {

				$fail = false;

				// Get id_base (remove -# from end) and instance ID number
				$id_base = preg_replace( '/-[0-9]+$/', '', $widget_instance_id );

				// Does site support this widget?
				if ( ! $fail && ! isset( $available_widgets[ $id_base ] ) ) {
					$fail = true;
					$widget_message_type = 'error';
					$widget_message = esc_html__( 'Site does not support widget', 'toolset-advanced-export' ); // explain why widget not imported
				}

				// Convert multidimensional objects to multidimensional arrays
				// Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
				// Without this, they are imported as objects and cause fatal error on Widgets page
				// If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
				// It is probably much more likely that arrays are used than objects, however
				$widget = json_decode( json_encode( $widget ), true );

				// Does widget with identical settings already exist in same sidebar?
				if ( ! $fail && isset( $widget_instances[ $id_base ] ) ) {

					// Get existing widgets in this sidebar
					$sidebars_widgets = get_option( 'sidebars_widgets' );
					$sidebar_widgets = isset( $sidebars_widgets[ $use_sidebar_id ] ) ? $sidebars_widgets[ $use_sidebar_id ] : array(); // check Inactive if that's where will go

					// Loop widgets with ID base
					$single_widget_instances = ! empty( $widget_instances[ $id_base ] ) ? $widget_instances[ $id_base ] : array();
					foreach ( $single_widget_instances as $check_id => $check_widget ) {

						// Is widget in same sidebar and has identical settings?
						if ( in_array( "$id_base-$check_id", $sidebar_widgets ) && (array) $widget == $check_widget ) {

							$fail = true;
							$widget_message_type = 'warning';
							$widget_message = esc_html__( 'Widget already exists', 'toolset-advanced-export' ); // explain why widget not imported

							break;
						}
					}
				}

				// No failure
				if ( ! $fail ) {

					// Add widget instance
					$single_widget_instances = get_option( 'widget_' . $id_base ); // all instances for that widget ID base, get fresh every time
					$single_widget_instances = ! empty( $single_widget_instances ) ? $single_widget_instances : array( '_multiwidget' => 1 ); // start fresh if have to
					$single_widget_instances[] = $widget; // add it

					// Get the key it was given
					end( $single_widget_instances );
					$new_instance_id_number = key( $single_widget_instances );

					// If key is 0, make it 1
					// When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it)
					if ( '0' === strval( $new_instance_id_number ) ) {
						$new_instance_id_number = 1;
						$single_widget_instances[ $new_instance_id_number ] = $single_widget_instances[0];
						unset( $single_widget_instances[0] );
					}

					// Move _multiwidget to end of array for uniformity
					if ( isset( $single_widget_instances['_multiwidget'] ) ) {
						$multiwidget = $single_widget_instances['_multiwidget'];
						unset( $single_widget_instances['_multiwidget'] );
						$single_widget_instances['_multiwidget'] = $multiwidget;
					}

					// Update option with new widget
					update_option( 'widget_' . $id_base, $single_widget_instances );

					// Assign widget instance to sidebar
					$sidebars_widgets = get_option( 'sidebars_widgets' ); // which sidebars have which widgets, get fresh every time
					$new_instance_id = $id_base . '-' . $new_instance_id_number; // use ID number from new widget instance
					$sidebars_widgets[ $use_sidebar_id ][] = $new_instance_id; // add new instance to sidebar
					update_option( 'sidebars_widgets', $sidebars_widgets ); // save the amended data

					// Success message
					if ( $sidebar_available ) {
						$widget_message_type = 'success';
						$widget_message = esc_html__( 'Imported', 'toolset-advanced-export' );
					} else {
						$widget_message_type = 'warning';
						$widget_message = esc_html__( 'Imported to Inactive', 'toolset-advanced-export' );
					}

				}

				// Result for widget instance
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['name'] = isset( $available_widgets[ $id_base ]['name'] ) ? $available_widgets[ $id_base ]['name'] : $id_base; // widget name or ID if name not available (not supported by site)
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['title'] = ! empty( $widget['title'] ) ? $widget['title'] : esc_html__( 'No Title', 'toolset-advanced-export' ); // show "No Title" if widget instance is untitled
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message_type'] = isset( $widget_message_type ) ? $widget_message_type : '';
				$results[ $sidebar_id ]['widgets'][ $widget_instance_id ]['message'] = isset( $widget_message ) ? $widget_message : '';

			}

		}

		return $results;

	}

}
