<?php

namespace ToolsetExtraExport\Tests;

abstract class Test_Case extends \WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		\WP_Mock::setUp();
	}


	public function tearDown() {
		parent::tearDown();

		\WP_Mock::tearDown();
	}
}