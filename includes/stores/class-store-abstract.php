<?php
/**
 * Abstract class of store with reusable functions.
 *
 * @package WpStoreFront
 */

namespace WpStoreFront\stores;

use WpStoreFront\Cache;
use WpStoreFront\Plugin;

/**
 * Class Store_Abstract
 *
 * @since 2.0.0
 */
abstract class Store_Abstract implements Store_Interface {

	/**
	 * @inheritdoc
	 */
	public static function id() {
		return strtolower( sanitize_title( static::label() ) );
	}

	/**
	 * Returns the name of the settings group for the current store class.
	 *
	 * @return string The option group.
	 * @since 2.0.0
	 */
	public function get_option_group() {
		// @codingStandardsIgnoreStart
		return 'wp_storefront_' . sha1( base64_encode( static::class ) );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Wrapper for the register_setting function to automatically use the option group.
	 *
	 * @param string $option_name The name of an option to sanitize and save.
	 * @param array $args Data used to describe the setting when registered.
	 *
	 * @since 2.0.0
	 */
	protected function register_setting( $option_name, $args = array() ) {
		register_setting( $this->get_option_group(), $option_name, $args );
	}

	/**
	 * Validate the checkbox value for the "enable cache" option.
	 *
	 * @param int $cache_enabled The value of the checkbox on the admin page.
	 *
	 * @return bool Whether or not the cached is enabled by the user.
	 * @since 2.0.0
	 */
	public function validate_cache_enabled( $cache_enabled ) {
		if ( empty( $cache_enabled ) ) {
			Plugin::get_instance()->get_cache()->force_update_cache();
		}

		return $cache_enabled;
	}

	/**
	 * Make sure that the lifetime is not altered.
	 *
	 * If the selected lifetime is different from the current, update to expire option value.
	 *
	 * @param int $wp_storefront_cache_lifetime Lifetime choosen by the user.
	 *
	 * @return string Validated lifetime.
	 * @since 1.2.0
	 *
	 */
	public function validate_cache_lifetime( $wp_storefront_cache_lifetime ) {
		$valid_values = array(
			HOUR_IN_SECONDS,
			6 * HOUR_IN_SECONDS,
			12 * HOUR_IN_SECONDS,
			DAY_IN_SECONDS,
			3 * DAY_IN_SECONDS,
			WEEK_IN_SECONDS,
			YEAR_IN_SECONDS,
		);

		$current_lifetime = Plugin::get_instance()->get_cache()->get_lifetime();
		if ( $wp_storefront_cache_lifetime != $current_lifetime ) {
			Plugin::get_instance()->get_cache()->update_expiration( time() + $wp_storefront_cache_lifetime );
		}

		if ( ! in_array( $wp_storefront_cache_lifetime, $valid_values ) ) {
			$wp_storefront_cache_lifetime = Cache::DEFAULT_CACHE_LIFETIME;
		}

		return $wp_storefront_cache_lifetime;
	}


}
