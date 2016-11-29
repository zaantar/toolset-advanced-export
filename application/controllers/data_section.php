<?php

namespace ToolsetExtraExport;

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

}