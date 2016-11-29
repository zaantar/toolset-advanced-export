<?php

namespace ToolsetExtraExport\Tests\Api;
use ToolsetExtraExport\Tests as tests;

class Generic_Api_Tests extends tests\Test_Case {


	function test_api_is_available() {
		$this->assertTrue( apply_filters( 'is_toolset_extra_export_available', false ) );
	}

}