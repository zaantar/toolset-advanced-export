<?php

namespace ToolsetAdvancedExport\Tests\Application;

use ToolsetAdvancedExport\Tests as tests;
use ToolsetAdvancedExport as e;

class Test_Migration_Data_Raw extends tests\Test_Case {

	function get_sample_nonarrays() {
		return [
			'null' => [
				null
			],
			'object' => [
				new \stdClass()
			],
			'scalar' => [
				42
			],
			'string' => [
				'not an array'
			]
		];
	}


	function get_invalid_arrays() {
		return [
			'empty' => [ [] ],
			'too_large' => [ [ 'first_item', 'second_item' ] ],
		];
	}

	/**
	 * @dataProvider get_sample_nonarrays
	 * @expectedException \InvalidArgumentException
	 * @param $input
	 */
	function test_from_array_with_nonarray( $input ) {
		e\Migration_Data_Raw::from_array( $input );
	}


	/**
	 * @dataProvider get_invalid_arrays
	 * @param $input
	 * @expectedException \InvalidArgumentException
	 */
	function test_from_array_with_invalid_arrays( $input ) {
		e\Migration_Data_Raw::from_array( $input );
	}


	/**
	 * @dataProvider get_sample_nonarrays
	 * @param $input
	 */
	function test_from_array_to_raw( $input ) {
		/** @var e\Migration_Data_Raw $md */
		$md = e\Migration_Data_Raw::from_array( [ $input ] );

		$this->assertTrue( $md instanceof e\Migration_Data_Raw );
		$this->assertEquals( $input, $md->get_raw_value() );

		$to_array = $md->to_array();

		$this->assertTrue( is_array( $to_array ) );
		$this->assertTrue( count( $to_array ) == 1 );
		$this->assertEquals( $input, $to_array[0] );
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Not implemented.
	 */
	function test_from_json() {
		e\Migration_Data_Raw::from_json( '{"key":"val"}' );
	}


	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Not implemented.
	 */
	function test_to_json() {
		$md = e\Migration_Data_Raw::from_array( [ 42 ] );
		$md->to_json();
	}


	/**
	 * @dataProvider get_sample_nonarrays
	 * @param $input
	 */
	function test_constructor( $input ) {
		$md = new e\Migration_Data_Raw( $input );
		$this->assertEquals( $input, $md->get_raw_value() );
	}

}