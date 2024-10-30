<?php

namespace WpStoreFront;

/**
 * @todo Use a parameters bag instead of a new $attributes mecanism?
 * Class Product
 *
 * @since 2.0.0
 */
class Product {

	/**
	 * @var array Attributes of the product.
	 */
	protected $attributes = [];

	/**
	 * Create a new Product instance to be used by the templates. The create an abstraction between ecommerce platforms.
	 *
	 * @param array $attributes
	 *
	 * @since 2.0.0
	 */
	public function __construct( $attributes = [] ) {
		$this->attributes = $attributes;
	}

	/**
	 * Get the value of an attribute.
	 *
	 * @param string $key The attribute to look for.
	 * @param null|mixed $default_value The default value.
	 *
	 * @return mixed The value of the attributes if found, the default value otherwise.
	 * @since 2.0.0
	 */
	protected function get( $key, $default_value = null ) {
		$key   = strtolower( trim( $key ) );
		$value = isset( $this->attributes[ $key ] ) ? $this->attributes[ $key ] : $default_value;

		return apply_filters( "wp_storefront_product_{$key}", $value );
	}

	/**
	 * Set the value of an attribute. Create the attribute if it does not exists.
	 *
	 * @param string $key The attribute.
	 * @param string $value The value.
	 *
	 * @return $this Return itself. Allow to do multiple set_attribute.
	 * @since 2.0.0
	 */
	protected function set_attribute( $key, $value ) {
		$key                      = strtolower( trim( $key ) );
		$this->attributes[ $key ] = $value;

		return $this;
	}

	/**
	 * Add multiple attributes at once.
	 *
	 * @param array $attributes The attributes to add.
	 *
	 * @return $this Return itself. Allow to do multiple add_attributes.
	 * @since 2.0.0
	 */
	public function add_attributes( array $attributes ) {
		foreach ( $attributes as $key => $value ) {
			$this->set_attribute( $key, $value );
		}

		return $this;
	}

	/**
	 * Shortcut to get the name of the product.
	 *
	 * @return string The name of the product if set.
	 * @since 2.0.0
	 */
	public function get_name() {
		return wp_strip_all_tags( $this->get( 'name', '' ) );
	}

	/**
	 * Shortcut to get the image URL of the product.
	 *
	 * @return string The image URL of the product if set.
	 * @since 2.0.0
	 */
	public function get_image_url() {
		return $this->get( 'image_url', '' );
	}

	/**
	 * Shortcut to know if the product has an image.
	 *
	 * @return bool True if the image URL is not empty.
	 * @since 2.0.0
	 */
	public function has_image() {
		return ! empty( $this->get_image_url() );
	}

	/**
	 * Shortcut to get the URL of the product.
	 *
	 * @return string The URL of the product if set.
	 * @since 2.0.0
	 */
	public function get_url() {
		return $this->get( 'url', '' );
	}

	/**
	 * Generate the HTML image tag of the product with proper dimensions.
	 *
	 * @param int $width The width of the image, if 0, full width.
	 * @param int $height The height of the image, if 0, full height.
	 *
	 * @return string The image tag.
	 * @since 2.0.0
	 */
	public function get_image( $width = 0, $height = 0 ) {
		ob_start(); ?>
		<img
			src="<?php esc_attr_e( $this->get_image_url() ) ?>"
			alt="<?php esc_attr_e( $this->get_name() ) ?>"
			<?php echo $width ? 'width="' . esc_attr( $width ) . '"' : '' ?>
			<?php echo $height ? 'width="' . esc_attr( $height ) . '"' : '' ?>
		/>
		<?php
		$image_html = ob_get_clean();

		return apply_filters( 'wp_storefront_product_image', $image_html, $this, $width, $height );
	}

	/**
	 * Shortcut to get the short description of the product.
	 *
	 * @return string The short description of the product if set.
	 * @since 2.0.0
	 */
	public function get_short_description( $strip_tags = true ) {
		return $strip_tags ? wp_strip_all_tags( $this->get( 'short_description', '' ) ) : $this->get( 'short_description', '' );
	}

	/**
	 * Tells if the product has a short description.
	 *
	 * @return bool True if the product has a short description.
	 */
	public function has_short_description() {
		return ! empty( $this->get_short_description() );
	}

	/**
	 * Tells if the product has a price.
	 *
	 * @return bool True if the product has a price.
	 */
	public function has_price() {
		return ! empty( $this->get_price() );
	}

	/**
	 * Shortcut to get the price of the product.
	 *
	 * @return float The price of the product if set.
	 * @since 2.0.0
	 */
	public function get_price() {
		return floatval( $this->get( 'price', 0 ) );
	}

	/**
	 * Tells if a special price is available.
	 *
	 * @return bool True if the product has a special price.
	 */
	public function has_special_price() {
		return ! empty( $this->get_special_price() );
	}

	/**
	 * Shortcut to get the special price of the product.
	 *
	 * @return float The special price of the product if set.
	 * @since 2.0.0
	 */
	public function get_special_price() {
		return floatval( $this->get( 'special_price', 0 ) );
	}
}
