<?php

namespace WpStoreFront;

/**
 * Class Parameters_Bag
 *
 * @since 2.0.0
 */
class Parameters_Bag {

	/**
	 * @var array Available attributes.
	 */
	protected $attributes = array();

	/**
	 * This class is used to pass data between shortcode, REST API and templates.
	 *
	 * @param array $attributes The attributes that will be available.
	 *
	 * @since 2.0.0
	 */
	protected function __construct( $attributes = array() ) {
		$this->attributes = $attributes;
		$this->process_attributes();
	}

	/**
	 * Clean attributes and remove them if they are empty or not scalar.
	 * @since 2.0.0
	 */
	protected function process_attributes() {
		foreach ( $this->attributes as $key => $value ) {
			$value = trim( $value );
			if ( ! is_scalar( $value ) || $value === '' || is_null( $value ) ) {
				unset( $this->attributes[ $key ] );
			}
		}
	}

	/**
	 * @param string $key The attribute to look for.
	 *
	 * @return bool True if the attribute exists, false otherwise.
	 * @since 2.0.0
	 */
	public function has( $key ) {
		return isset( $this->attributes[ $key ] );
	}

	/**
	 * Retreive an attribute value and allow a default value and a cast.
	 *
	 * @param string $key The attribute to look for.
	 * @param null|mixed $default_value The default value if the attribute is not found.
	 * @param null|mixed $cast The cast to do on the value (string to int).
	 *
	 * @return array|mixed The casted value if the attribute is found, the default value otherwise.
	 * @since 2.0.0
	 */
	public function get( $key, $default_value = null, $cast = null ) {
		return Array_Utils::value( $this->attributes, $key, $default_value, $cast );
	}

	/**
	 * Factory method to create a parameters bag from shortcode attributes.
	 *
	 * @param array $atts The shortcode attributes.
	 *
	 * @return static The new instance.
	 * @since 2.0.0
	 */
	public static function createFromShortcode( $atts ) {
		return new static( shortcode_atts( [
			'title'            => 'h2',
			'class'            => '',
			'product'          => '',
			'products'         => '',
			'store'            => null,
			'platform'         => '',
			'target'           => '',
			'prefix'           => '',
			'suffix'           => '$',
			'url_html_suffix'  => true,
			'image_width'      => '',
			'image_height'     => '',
			'hide_image'       => false,
			'show_description' => true,
		], $atts, 'wp-storefront' ) );
	}

}
