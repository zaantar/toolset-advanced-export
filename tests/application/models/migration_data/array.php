<?php

namespace ToolsetExtraExport\Tests\Application;

use ToolsetExtraExport\Tests as tests;
use ToolsetExtraExport as e;

class Test_Migration_Data_Nested_Array extends tests\Test_Case {


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


	function get_sample_arrays() {
		return [
			'empty_array' => [ [] ],
			'flat_array' => [ [ 1, 5, 42 ] ],
			'associative_array' => [ [ 'key1' => 'val1', 'key2' => 'val2' ] ],
			'nested_array' => [ [
				'key1' => [
					'key1_1' => [
						'key1_1_1' => 'val1_1_1'
					],
					'key1_2' => 'val1_2'
				],
				'key2' => 'val2'
			] ]
		];
	}


	function get_sample_invalid_json() {
		return [
			'empty_string' => [ '' ],
			'non_string' => [ new \stdClass() ],
			'invalid_json' => [ '{"key":"value"' ]
		];
	}


	/**
	 * @dataProvider get_sample_nonarrays
	 * @expectedException \InvalidArgumentException
	 * @param $input
	 */
	function test_from_array_with_nonarray( $input ) {
		e\Migration_Data_Nested_Array::from_array( $input );
	}


	/**
	 * @dataProvider get_sample_invalid_json
	 * @param $input
	 * @expectedException \InvalidArgumentException
	 */
	function test_from_json_with_invalid_json( $input ) {
		e\Migration_Data_Nested_Array::from_json( $input );
	}


	/**
	 * @dataProvider get_sample_arrays
	 * @param $input
	 */
	function test_from_to_array( $input ) {
		$md = e\Migration_Data_Nested_Array::from_array( $input );

		$this->assertTrue( $md instanceof e\Migration_Data_Nested_Array );
		$this->assertAssociativeArrayEquals( $input, $md->to_array() );
	}


	/**
	 * @dataProvider get_sample_arrays
	 * @param $input
	 */
	function test_from_to_json( $input ) {
		$json = json_encode( $input );

		$md = e\Migration_Data_Nested_Array::from_json( $json );

		$this->assertTrue( $md instanceof e\Migration_Data_Nested_Array );
		$this->assertAssociativeArrayEquals( $input, $md->to_array() );
		$this->assertEquals( $json, $md->to_json() );
	}
}