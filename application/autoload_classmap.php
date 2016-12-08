<?php

return [
	'ToolsetExtraExport\Api' => __DIR__ . '/controllers/api.php',
	'ToolsetExtraExport\ApiHandlers\Api_Handler_Interface' => __DIR__ . '/controllers/api_handler/interface.php',
	'ToolsetExtraExport\ApiHandlers\Export_Extra_Wordpress_Data_Raw' => __DIR__ . '/controllers/api_handler/export_extra_wordpress_data_raw.php',
	'ToolsetExtraExport\ApiHandlers\Export_Extra_Wordpress_Data' => __DIR__ . '/controllers/api_handler/export_extra_wordpress_data.php',
	'ToolsetExtraExport\ApiHandlers\Export_Extra_Wordpress_Data_Json' => __DIR__ . '/controllers/api_handler/export_extra_wordpress_data_json.php',
	'ToolsetExtraExport\Customized_Twig_Autoloader' => __DIR__ . '/controllers/customized_twig_autoloader.php',
	'ToolsetExtraExport\Data_Section' => __DIR__ . '/controllers/data_section.php',
	'ToolsetExtraExport\Exporter' => __DIR__ . '/controllers/exporter.php',
	'ToolsetExtraExport\IMigration_Data' => __DIR__ . '/controllers/migration_data/interface.php',
	'ToolsetExtraExport\Migration_Data_Nested_Array' => __DIR__ . '/controllers/migration_data/array.php',
	'ToolsetExtraExport\Migration_Handler_Factory' => __DIR__ . '/controllers/migration_handler_factory.php',
	'ToolsetExtraExport\IMigration_Handler' => __DIR__ . '/controllers/migration_handler/interface.php',
	'ToolsetExtraExport\Migration_Handler_Option_Array' => __DIR__ . '/controllers/migration_handler/option_array.php',
	'ToolsetExtraExport\Migration_Handler_Settings_Reading' => __DIR__ . '/controllers/migration_handler/settings_reading.php',
	'ToolsetExtraExport\Gui\Page_Import_Export' => __DIR__ . '/controllers/page/import_export.php',
	'ToolsetExtraExport\Gui\Page_Tools' => __DIR__ . '/controllers/page/tools.php',
	'ToolsetExtraExport\Gui\Toolset_Ie_Section' => __DIR__ . '/controllers/page/toolset_ie_section.php',
];