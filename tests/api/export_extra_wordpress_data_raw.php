<?php

namespace ToolsetExtraExport\Tests\Api;
use ToolsetExtraExport\Tests as tests;

class Export_Extra_Wordpress_Data_Raw extends tests\Test_Case {


	function test_export_filter_works() {
		// passing invalid argument, we should get an empty array of results
		$this->assertTrue( is_array( apply_filters( 'toolset_export_extra_wordpress_data_raw', null ) ) );
	}


	/*function test_default_raw_export() {

	}*/
}