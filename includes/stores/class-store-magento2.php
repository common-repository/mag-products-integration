<?php

namespace WpStoreFront\stores;

use WpStoreFront\Array_Utils;
use WpStoreFront\Parameters_Bag;
use WpStoreFront\Product;
use WpStoreFront\Url_Utils;

/**
 * Class Store_Magento2
 *
 * @since 2.0.0
 */
class Store_Magento2 extends Store_Abstract {

	/**
	 * @inheritdoc
	 */
	public static function label() {
		return __( 'Magento 2', 'wp-storefront' );
	}

	/**
	 * @inheritdoc
	 */
	public function admin_page() {
		?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Base URL', 'wp-storefront' ); ?></th>
			<td>
				<input type="text" class="regular-text" name="wp_storefront_m2_base_url"
				       value="<?php esc_attr_e( get_option( 'wp_storefront_m2_base_url' ) ); ?>"/>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Access Token', 'wp-storefront' ); ?></th>
			<td>
				<input type="password" class="regular-text" name="wp_storefront_m2_access_token"
				       value="<?php esc_attr_e( get_option( 'wp_storefront_m2_access_token' ) ); ?>"/>
			</td>
		</tr>
		<?php
	}

	/**
	 * @inheritdoc
	 */
	public function get_products( Parameters_Bag $parameters_bag ) {
		$products = array();
		$skus     = array_map( function ( $sku ) {
			return trim( $sku );
		}, explode( ',', $parameters_bag->get( 'products', $parameters_bag->get( 'product' ) ) ) );

		$path = '/rest/all/V1/products';
		if ( $parameters_bag->has( 'store' ) ) {
			$path = '/rest/' . $parameters_bag->get( 'store' ) . '/V1/products';
		}
		$url = Url_Utils::build_url(
			get_option( 'wp_storefront_m2_base_url' ),
			$path,
			array(
				'searchCriteria[pageSize]'                                    => count( $skus ),
				'searchCriteria[filterGroups][0][filters][0][value]'          => implode( ',', $skus ),
				'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'in',
				'searchCriteria[filterGroups][0][filters][0][field]'          => 'sku',
				'searchCriteria[sortOrders][0][field]'                        => 'name',
				'searchCriteria[sortOrders][0][direction]'                    => 'asc'
			)
		);

		if ( $url ) {
			$response = wp_remote_get( $url, [
				'headers' => array(
					'Authorization' => 'Bearer ' . get_option( 'wp_storefront_m2_access_token' ),
				),
			] );

			if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == 200 ) {
				$data = json_decode( $response['body'], true );
				if ( isset( $data['items'] ) ) {
					foreach ( $data['items'] as $item ) {
						$products[] = new Product( [
							'name'              => $item['name'],
							'sku'               => $item['sku'],
							'image_url'         => $this->get_image_url( get_option( 'wp_storefront_m2_base_url' ), $item ),
							'url'               => $this->get_url( get_option( 'wp_storefront_m2_base_url' ), $item, $parameters_bag ),
							'short_description' => $this->get_custom_attribute( Array_Utils::value( $item, 'custom_attributes', [], 'array' ), 'short_description', '' ),
							'price'             => Array_Utils::value( $item, 'price' ),
							'special_price'     => $this->get_special_price( $item ),
							'raw_data'          => $item
						] );
					}
				}
			}
		}

		return $products;
	}

	/**
	 * @inheritdoc
	 */
	public function ready() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function register_settings() {
		$this->register_setting( 'wp_storefront_m2_base_url' );
		$this->register_setting( 'wp_storefront_m2_access_token' );
	}

	/**
	 * Retreive custom attribute values.
	 *
	 * @param array $custom_attributes The array of all the custom attributes of the product.
	 * @param string $attribute_code The attribute code to find the value.
	 * @param mixed $default_value The default value if the attribute is not found.
	 *
	 * @return mixed The value of the attribute or the default value.
	 * @since 2.0.0
	 */
	protected function get_custom_attribute( array $custom_attributes, $attribute_code, $default_value ) {
		$value          = $default_value;
		$attribute_code = strtolower( trim( $attribute_code ) );
		foreach ( $custom_attributes as $custom_attribute ) {
			if ( isset( $custom_attribute['attribute_code'] ) && isset( $custom_attribute['value'] ) ) {
				if ( $attribute_code == $custom_attribute['attribute_code'] ) {
					$value = $custom_attribute['value'];
					break;
				}
			}
		}

		return $value;
	}

	/**
	 * Logic to get the special price based on product type and date range.
	 *
	 * @param array $product The parsed response from the REST API call.
	 *
	 * @return false|float The special price or false if not available.
	 * @since 2.0.0
	 */
	protected function get_special_price( array $product ) {
		$special_price     = $this->get_custom_attribute( Array_Utils::value( $product, 'custom_attributes', [], 'array' ), 'special_price', false );
		$special_from_date = $this->get_custom_attribute( Array_Utils::value( $product, 'custom_attributes', [], 'array' ), 'special_from_date', false );
		$special_to_date   = $this->get_custom_attribute( Array_Utils::value( $product, 'custom_attributes', [], 'array' ), 'special_to_date', false );
		$type_id           = Array_Utils::value( $product, 'type_id' );

		if ( ! empty( $special_price ) ) {
			$special_from_date = empty( $special_from_date ) ? ( time() - 10 ) : strtotime( $special_from_date );
			$special_to_date   = empty( $special_to_date ) ? ( time() + 60 * 60 * 24 ) : strtotime( $special_to_date );

			if ( $special_from_date > time() || $special_to_date <= time() ) {
				$special_price = false;
			}

			if ( 'bundle' == $type_id ) {
				$price         = Array_Utils::value( $product, 'price' );
				$special_price = round( $price * ( $special_price / 100 ), 2 );
			}
		}

		return $special_price;
	}

	/**
	 * Build the image URL from the REST API response data.
	 *
	 * @param string $base_url The base URL of the Magento 2 store.
	 * @param array $product The REST API response data.
	 *
	 * @return string The image URL.
	 */
	protected function get_image_url( $base_url, array $product ) {
		$media_gallery_entries = Array_Utils::value( $product, 'media_gallery_entries' );
		$image_url             = '';

		if ( ! empty( $media_gallery_entries ) ) {
			foreach ( $media_gallery_entries as $media_gallery_entry ) {
				if ( ! empty( $media_gallery_entry['types'] ) && in_array( 'image', $media_gallery_entry['types'] ) ) {
					$image_url = $base_url . '/media/catalog/product' . $media_gallery_entry['file'];
					break;
				}
			}
		}

		if ( empty( $image_url ) ) {
			$image_path = Array_Utils::value( $product, 'media_gallery_entries.0.file' );
			if ( ! empty( $image_path ) ) {
				$image_url = $base_url . '/media/catalog/product' . $image_path;
			}
		}

		return $image_url;
	}

	/**
	 * Build the product URL from the REST API response data.
	 *
	 * @param string $base_url The base URL of the Magento 2 store.
	 * @param array $product The REST API response data.
	 * @param Parameters_Bag $parameters_bag The shortcode attributes.
	 *
	 * @return string The product URL without categories.
	 * @since 2.0.0
	 */
	protected function get_url( $base_url, array $product, Parameters_Bag $parameters_bag ) {
		$url_key = $this->get_custom_attribute( Array_Utils::value( $product, 'custom_attributes', [], 'array' ), 'url_key', false );
		$url     = '';

		if ( $url_key ) {
			$url = $base_url . '/' . $url_key . ($parameters_bag->get( 'url_html_suffix', true, 'boolean' ) ? '.html' : '');
		}

		return apply_filters( 'wp_storefront_product_url', $url );
	}

}
