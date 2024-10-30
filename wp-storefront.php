<?php
/**
 * MagProductsIntegration functions and definitions.
 *
 * @package WpStoreFront
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/*
Plugin Name: WP Storefront - Enlighten your products
Plugin URI: https://wordpress.org/plugins/mag-products-integration/
Description: Display products from your favourite Ecommerce platform directly into your articles and pages.
Version: 2.0.1
Requires at least: 4.6
Author: Francis Santerre
Author URI: https://wpstorefront.com/
Domain Path: /languages
Text Domain: wp-storefront
*/

define( 'WP_STOREFRONT_PLUGIN_VERSION', '2.0.1' );
define( 'WP_STOREFRONT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once 'autoload.php';

if ( ! function_exists( 'wp_storefront' ) ) {
	/**
	 * Create global instance of the plugin. Allows developer to remove/add plugin actions/filters.
	 *
	 * @return WpStoreFront\Plugin
	 * @since 1.2.2
	 *
	 */
	function wp_storefront() {
		static $wp_storefront;

		if ( ! isset( $wp_storefront ) ) {
			$wp_storefront = WpStoreFront\Plugin::get_instance();
			$wp_storefront->init();
		}

		return $wp_storefront;
	}

	wp_storefront();

	register_activation_hook( __FILE__, array( wp_storefront(), 'activate' ) );
	register_deactivation_hook( __FILE__, array( wp_storefront(), 'deactivate' ) );
}

if ( ! function_exists( 'wp_storefront_store_manager' ) ) {

	function wp_storefront_store_manager() {
		static $wp_storefront_store_manager;

		if ( ! isset( $wp_storefront_store_manager ) ) {
			$wp_storefront_store_manager = WpStoreFront\Plugin::get_instance()->get_store_manager();
		}

		return $wp_storefront_store_manager;
	}

}

if ( ! function_exists( 'wp_storefront_admin' ) ) {
	/**
	 * Create global instance of the plugin admin. Allows developer to remove/add plugin actions/filters.
	 *
	 * @return WpStoreFront\Admin
	 * @since 1.2.2
	 *
	 */
	function wp_storefront_admin() {
		static $wp_storefront_admin;

		if ( ! isset( $wp_storefront_admin ) ) {
			$wp_storefront_admin = WpStoreFront\Plugin::get_instance()->get_admin();
			$wp_storefront_admin->init();
		}

		return $wp_storefront_admin;
	}

	wp_storefront_admin();
}

