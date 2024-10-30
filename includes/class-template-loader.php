<?php

namespace WpStoreFront;

/**
 * Class Tempalte_Loader
 *
 * @since 2.0.0
 */
class Template_Loader {

	/**
	 * @var string The path to the current theme of plugin to find templates files.
	 * @since 2.0.0
	 */
	protected $theme_path;

	/**
	 * Create a new instance of Template_Loader.
	 *
	 * @param string $theme_path The path to the templates files.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $theme_path ) {
		$this->theme_path = apply_filters( 'wp_storefront_theme_path', $theme_path );
	}

	/**
	 * Loads a template part into a template.
	 *
	 * Provides a simple mechanism for child themes to overload reusable sections of code
	 * in the theme.
	 *
	 * Includes the named template part for a theme or if a name is specified then a
	 * specialised part will be included. If the theme contains no {slug}.php file
	 * then no template will be included.
	 *
	 * The template is included using require, not require_once, so you may include the
	 * same template part multiple times.
	 *
	 * For the $name parameter, if the file is called "{slug}-special.php" then specify
	 * "special".
	 *
	 * @param string $slug The slug name for the generic template.
	 * @param string $name The name of the specialised template.
	 * @param array $variables Optional. Additional arguments passed to the template.
	 *                     Default empty array.
	 *
	 * @since 2.0.0
	 */
	public function load( $slug, $variables = array() ) {
		$found     = true;
		$templates = array( $this->theme_path . "/{$slug}.php" );

		$file_path = locate_template( $templates );
		if ( empty( $file_path ) ) {
			$found = false;
			foreach ( $templates as $template_name ) {
				$template_name = preg_replace( '/^' . $this->theme_path . '/i', '', $template_name );
				if ( ! $template_name ) {
					continue;
				}

				$file_path = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'templates/' . $template_name;
				if ( file_exists( $file_path ) ) {
					$found = true;
					break;
				}
			}
		}

		if ( $found ) {
			if ( is_array( $variables ) ) {
				$variables = array( 'wp_storefront' => $variables );
				// @codingStandardsIgnoreStart
				extract( $variables );
				// @codingStandardsIgnoreEnd
			}

			include $file_path;
		}
	}

}
