<?php

namespace WpStoreFront;

/**
 * Class Url_Utils
 *
 * @since 2.0.0
 */
class Url_Utils {

	/**
	 * Try to build an URL.
	 *
	 * @param string $base_url The base URL with the protocol.
	 * @param string $path The path to append.
	 * @param array $query The HTTP query ($_GET)
	 *
	 * @return false|string If it's valid, return the URL, false otherwise.
	 * @since 2.0.0
	 */
	public static function build_url( $base_url, $path = '', $query = array() ) {
		if ( ! filter_var( $base_url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$base_url = rtrim( $base_url, '/' );
		$base_url = trailingslashit( $base_url );
		if ( ! empty( $path ) ) {
			$base_url .= $path;
		}

		if ( ! empty( $query ) ) {
			return $base_url . '?' . http_build_query( $query );
		}

		return $base_url;
	}
}
