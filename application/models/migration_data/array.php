<?php

namespace ToolsetAdvancedExport;

/**
 * Migration data from a WordPress section that consist of (possibly nested) associative or non-associative arrays
 * and primitive value types.
 *
 * @since 1.0
 */
class Migration_Data_Nested_Array implements IMigration_Data {

	private $data = [];


	/**
	 * Migration_Data_Nested_Array constructor.
	 *
	 * @param array $data
	 */
	protected function __construct( $data ) {
		if( ! is_array( $data ) ) {
			throw new \InvalidArgumentException();
		}
		$this->data = $data;
	}


	/**
	 * @param array $array_input
	 *
	 * @return Migration_Data_Nested_Array
	 */
	public static function from_array( $array_input ) {
		if( ! is_array( $array_input ) ) {
			throw new \InvalidArgumentException( 'Invalid input.' );
		}

		return new self( $array_input );
	}

	/**
	 * @return array
	 */
	public function to_array() {
		return $this->data;
	}

	/**
	 * @param string $json_input
	 *
	 * @return Migration_Data_Nested_Array
     * @throws \InvalidArgumentException
	 */
	public static function from_json( $json_input ) {

		if( ! is_string( $json_input ) ) {
			throw new \InvalidArgumentException( 'Not a JSON string.' );
		}

		$input = json_decode( $json_input, true );

		if( json_last_error() != JSON_ERROR_NONE ) {
			throw new \InvalidArgumentException( 'Invalid JSON string.' );
		}

		return self::from_array( $input );
	}


	/**
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function to_json() {
		$output = $this->to_array();

		$output = json_encode( $output );

		if( false === $output ) {
			throw new \RuntimeException( 'Unable to produce valid JSON output.' );
		}

		return $output;
	}

}

