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


	public function assertAssociativeArrayEquals( $expected, $actual, $message, $depth = 10 ) {
        $this->assertEquals( $expected, $actual, $message, 0, $depth, true );

        $this->assertArrayKeysMatch( $expected, $actual );
    }


    private function assertArrayKeysMatch( $expected, $actual ) {

	    $this->assertTrue( is_array( $expected ) );
	    $this->assertTrue( is_array( $actual ) );
	    $this->assertEquals( array_keys( $expected ), array_keys( $actual ) );

	    foreach( $expected as $key => $value ) {
	        if( is_array( $expected[ $key ] ) ) {
	            $this->assertArrayKeysMatch( $expected[ $key ], $actual[ $key ] );
            }
        }
    }
}