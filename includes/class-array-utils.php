<?php

namespace WpStoreFront;

class Array_Utils {

	/**
	 * Find deep value in array using dot annotation.
	 *
	 * $array['a']['b'] can be found using get_array_value($array, 'a.b');
	 *
	 * @param array $array The array to find the value.
	 * @param string $key The path to the array using dot notation.
	 * @param mixed $default_value The default value to return if the value is not found.
	 * @param mixed $cast Cast the requested value to make sure it will be this type that is returned (booleean, int, string or array).
	 *
	 * @return mixed The requested value if found or the default value.
	 * @since 2.0.0
	 */
	public static function value( array $array, $key, $default_value = null, $cast = null ) {
		$key = strtolower( trim( $key ) );

		$key_parts = explode( '.', $key );
		if ( count( $key_parts ) > 1 ) {
			if ( ! isset( $array[ $key_parts[0] ] ) ) {
				return $default_value;
			}

			return static::value( $array[ $key_parts[0] ], implode( '.', array_slice( $key_parts, 1 ) ), $default_value );
		} else {
			$value = ! empty( $array[ $key_parts[0] ] ) ? $array[ $key_parts[0] ] : $default_value;
			if ( $cast ) {
				$cast = strtolower( trim( $cast ) );
				switch ( $cast ) {
					case 'boolean':
						if ( is_numeric( $value ) ) {
							$value = abs( intval( $value ) );
						}
						$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
						break;
					case 'int':
						$value = intval( $value );
						break;
					case 'string':
						$value = strval( $value );
						break;
					case 'array':
						if ( ! is_array( $value ) ) {
							$value = array( $value );
						}
						break;
				}
			}

			return $value;
		}
	}
}
