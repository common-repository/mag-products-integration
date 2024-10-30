<?php

namespace WpStoreFront;

use WpStoreFront\stores\Store_Magento2;

/**
 * Class Store_Manager
 *
 * @since 2.0.0
 */
class Store_Manager {

	/**
	 * Get all the registered store classes that implements \WpStoreFront\stores\Store_Interface.
	 *
	 * @return string[] All store classes.
	 * @since 2.0.0
	 */
	public function get_registered_stores() {
		return apply_filters( 'wp_storefront_stores', [] ) + [
				Store_Magento2::class,
			];
	}

	/**
	 * Get all the stores with their labels.
	 *
	 * @return array Return registered stores with their labels.
	 * @since 2.0.0
	 */
	public function get_stores() {
		$stores = array();
		foreach ( $this->get_registered_stores() as $store ) {
			//@todo Throw exception if another class has the same store ID as another.
			$stores[ $store ] = call_user_func( $store . '::label' );
		}

		return $stores;
	}

	/**
	 * Get the instance of a store with it's ID.
	 *
	 * @param string $id The ID of the store.
	 *
	 * @return mixed|null The instance if the class is found, null otherwise.
	 * @since 2.0.0
	 */
	public function get_store( $id ) {
		foreach ( $this->get_registered_stores() as $store_class ) {
			if ( call_user_func( $store_class . '::id' ) == $id ) {
				return new $store_class;
			}
		}

		return null;
	}

	/**
	 * Generate the final HTML of the products list.
	 *
	 * @param \WpStoreFront\Product[] $products An array of product to render.
	 * @param array $atts The attributes from the shortcode.
	 *
	 * @return false|string The HTML of the products.
	 * @since 2.0.0
	 */
	public function products_html( array $products, Parameters_Bag $parameters_bag ) {
		$html = '';

		if ( ! empty( $products ) ) {
			ob_start();
			wp_storefront()->get_template_loader()->load(
				'products',
				array(
					'products'       => $products,
					'class'          => trim( implode( ' ', array(
						'magento-wrapper',
						$parameters_bag->get( 'class', '' )
					) ) ),
					'parameters_bag' => $parameters_bag
				)
			);
			$html = ob_get_clean();
		}

		return $html;
	}

}
