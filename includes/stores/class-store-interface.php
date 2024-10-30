<?php
/**
 * Interface with must have functions for store integrations.
 *
 * @package WpStoreFront
 */

namespace WpStoreFront\stores;

use WpStoreFront\Parameters_Bag;
use WpStoreFront\Product;

interface Store_Interface {

	/**
	 * The name of the e-commerce store integration.
	 *
	 * @return string The name of the store (used in the dropdown on the settings page).
	 * @since 2.0.0
	 */
	public static function label();

	/**
	 * The id of the e-commerce store integration.
	 *
	 * @return string The id of the store (used for the tabs in the settings page).
	 * @since 2.0.0
	 */
	public static function id();

	/**
	 * Generate the required inputs for the settings form.
	 *
	 * @return string The HTML fields.
	 * @since 2.0.0
	 */
	public function admin_page();

	/**
	 * Verify if everything is ready to fetch the products and render them.
	 *
	 * @return bool True if ready, false if not.
	 * @since 2.0.0
	 */
	public function ready();

	/**
	 * Register the settings for the admin form.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public function register_settings();

	/**
	 * Fetch multiple products by their SKUs or IDs.
	 *
	 * @param Parameters_Bag $parameters_bag Parsed parameters from the [wp-storefront] shortcode.
	 *
	 * @return Product[] Array of products.
	 * @since 2.0.1
	 */
	public function get_products( Parameters_Bag $parameters_bag );
}
