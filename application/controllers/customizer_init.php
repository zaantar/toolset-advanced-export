<?php

namespace ToolsetAdvancedExport;

/**
 * Handles the initialization of the Customizer and obtaining of the \WP_Customize_Manager instance.
 *
 * @since 1.1
 */
class CustomizerInit {


	/** @var CustomizerInit */
	private static $instance;


	/**
	 * @var bool Set this to true to indicate the customizer has been loaded manually,
	 *     which means it can be present but not fully initialized yet.
	 *     This is especially important in get_customize_manager().
	 */
	private $is_manually_loaded = false;


	/**
	 * @return CustomizerInit
	 */
	public static function get_instance() {
		if( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * This can be used to make sure the Customizer is loaded even before the theme.
	 *
	 * It was implemented specifically to address an issue with Twenty Twenty, which
	 * defines some of its classes like this:
	 *
	 * if ( class_exists( 'WP_Customize_Control' ) ) {
	 *     if ( ! class_exists( 'TwentyTwenty_Separator_Control' ) ) {
	 *         class TwentyTwenty_Separator_Control extends WP_Customize_Control {
	 *             ...
	 *
	 * That means, without the Customizer already loaded, these classes won't be defined
	 * and then we invoke code (via the 'customize_register' action) that assumes their existence.
	 *
	 * For this method to be effective, it needs to be called during
	 * the 'setup_theme' action at the latest.
	 */
	public function pre_initialize_in_ajax() {
		$_REQUEST['wp_customize'] = 'on';
		_wp_customize_include();
		$this->is_manually_loaded = true;
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
	public function get_customize_manager() {

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
		if( null === $wp_customize ) {
			$_REQUEST['wp_customize'] = 'on';
			_wp_customize_include();
			$this->is_manually_loaded = true;
		}

		if( $this->is_manually_loaded ) {
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
}

