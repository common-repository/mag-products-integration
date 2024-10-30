<?php
/**
 * Administration page definition for settings and options.
 *
 * @package WpStoreFront
 */

namespace WpStoreFront;

use WpStoreFront\stores\Store_Magento2;

/**
 * Class Admin
 *
 * @since   1.0.0
 */
class Admin {

	/**
	 * This function is executed in the admin area.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_ajax_script' ) );
		add_action( 'wp_ajax_flush_cache', array( $this, 'flush_cache' ) );
	}

	/**
	 * Flush cache storage (transient)
	 *
	 * @since 1.2.2
	 */
	public function flush_cache() {
		Plugin::get_instance()->get_cache()->force_update_cache();

		wp_send_json(
				array(
						'message' => __( 'The cache storage has been flushed.', 'wp-storefront' ),
				)
		);
		wp_die();
	}

	/**
	 * Enqueue AJAX JavaScript script for the plugin admin page.
	 *
	 * @param string $hook Hook executed which allow us to target a specific admin page.
	 *
	 * @since 1.0.0
	 *
	 */
	public function load_ajax_script( $hook ) {
		if ( preg_match( '/^toplevel_page_wp-storefront/i', $hook ) ) {
			wp_enqueue_script( 'ajax-script', plugins_url( '/js/script.min.js', dirname( __FILE__ ) ), array( 'jquery' ) );
		}
		wp_localize_script(
				'ajax-script', 'ajax_object', array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
		);
	}

	/**
	 * Register settings for the plugin admin page.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {
		register_setting( 'wp_storefront', 'wp_storefront_disable_customizer_css' );
		register_setting( 'wp_storefront', 'wp_storefront_cache_enabled', array(
				$this,
				'validate_cache_enabled',
		) );
		register_setting( 'wp_storefront', 'wp_storefront_cache_lifetime', array(
				$this,
				'validate_cache_lifetime',
		) );
		foreach ( wp_storefront_store_manager()->get_stores() as $class => $label ) {
			$store = new $class;
			$store->register_settings();
		}
	}


	/**
	 * Live preview color picker JS script.
	 *
	 * @since 1.2.7
	 */
	public function customize_preview() {
		wp_enqueue_script(
				'Plugin-products-integration-preview',
				plugins_url( '/js/preview.min.js', dirname( __FILE__ ) ),
				array( 'customize-preview', 'jquery' )
		);
	}

	/**
	 * Render the admin configuration page
	 *
	 * @since 1.0.0
	 */
	public function page() {
		$tab = $_GET['tab'] ?? call_user_func( get_option( 'wp_storefront_selected_store_class', Store_Magento2::class ) . '::id' );
		?>
		<div class="wrap">
			<h2><?php _e( 'WP Storefront', 'wp-storefront' ); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<nav class="nav-tab-wrapper">
					<?php
					foreach ( wp_storefront_store_manager()->get_stores() as $class => $label ): $id = call_user_func( $class . '::id' ) ?>
						<a href="?page=wp-storefront&tab=<?php esc_attr_e( $id ) ?>" class="nav-tab <?php if ( $tab == $id ): ?>nav-tab-active<?php endif; ?>"><?php esc_html_e( $label ) ?></a>
					<?php endforeach; ?>
					<a href="?page=wp-storefront&tab=general" class="nav-tab <?php if ( 'general' == $tab ): ?>nav-tab-active<?php endif; ?>"><?php _e( 'General', 'wp-storefront' ) ?></a>
					<a href="?page=wp-storefront&tab=documentation" class="nav-tab <?php if ( 'documentation' == $tab ): ?>nav-tab-active<?php endif; ?>"><?php _e( 'Documentation', 'wp-storefront' ) ?></a>
				</nav>
				<fieldset>
					<div id="store-form">
						<?php
						if ( 'general' == $tab ):
							settings_fields( 'wp_storefront' );
							do_settings_sections( 'wp_storefront' );
							$this->general_settings_html();
						elseif ( 'documentation' == $tab ):
							$this->documentation_settings_html();
						else:
							settings_fields( wp_storefront_store_manager()->get_store( $tab )->get_option_group() );
							do_settings_sections( wp_storefront_store_manager()->get_store( $tab )->get_option_group() ); ?>
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><?php _e( 'Shortcode', 'wp-storefront' ); ?></th>
									<td>
										<code>[wp-storefront platform="<?php esc_attr_e( $tab ) ?>"]</code>
										<p><small>Take a look at the documentation to know more about all available attributes.</small></p>
									</td>
								</tr>
								<?php wp_storefront_store_manager()->get_store( $tab )->admin_page(); ?>
							</table>
						<?php endif; ?>
					</div>
					<?php if ( 'documentation' != $tab ): ?>
						<p class="submit">
							<?php submit_button( null, 'primary', 'submit', false ); ?>
						</p>
					<?php endif; ?>
				</fieldset>
			</form>
		</div>
		<?php
	}

	/**
	 * Output the documentation of the plugin.
	 * @since 2.0.0
	 */
	protected function documentation_settings_html() {
		?>
		<h2><?php _e( 'Shortcode', 'wp-storefront' ) ?></h2>
		<p><?php _e( 'Here is the list of all available shortcode attributes.', 'wp-storefront' ) ?></p>
		<table class="wp-list-table widefat striped table-view-list">
			<thead>
			<tr>
				<th width="200"><?php _e( 'Attribute', 'wp-storefront' ) ?></th>
				<th width="200"><?php _e( 'Required', 'wp-storefront' ) ?></th>
				<th><?php _e( 'Description', 'wp-storefront' ) ?></th>
				<th width="200"><?php _e( 'Default', 'wp-storefront' ) ?></th>
			</tr>
			</thead>
			<tbody>
			<tr valign="top">
				<td><code>products</code></td>
				<td><strong><?php _e( 'Yes', 'wp-storefront' ) ?></strong></td>
				<td><?php _e( 'The product SKU or SKUs comma separated for multiple products. The list is sorted by name ascendent.', 'wp-storefront' ) ?></td>
				<td></td>
			</tr>
			<tr valign="top">
				<td><code>platform</code></td>
				<td><strong><?php _e( 'Yes', 'wp-storefront' ) ?></strong></td>
				<td><?php _e( 'The Ecommerce platform to use (<code>magento-2</code>).', 'wp-storefront' ) ?></td>
				<td></td>
			</tr>
			<tr valign="top">
				<td><code>title</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'The HTML tag for the product name.', 'wp-storefront' ) ?></td>
				<td>h2</td>
			</tr>
			<tr valign="top">
				<td><code>class</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Extra CSS classes to append to the product <code>' . esc_attr( '<div>' ) . '</code> wrapper.', 'wp-storefront' ) ?></td>
				<td></td>
			</tr>
			<tr valign="top">
				<td><code>store</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Define the shop to be used as a context (multishop).', 'wp-storefront' ) ?></td>
				<td></td>
			</tr>
			<tr valign="top">
				<td><code>target</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Specify a default target for all hyperlinks to the product page.', 'wp-storefront' ) ?></td>
				<td>_self</td>
			</tr>
			<tr valign="top">
				<td><code>prefix</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Price prefix.', 'wp-storefront' ) ?></td>
				<td></td>
			</tr>
			<tr valign="top">
				<td><code>suffix</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Price suffix.', 'wp-storefront' ) ?></td>
				<td>$</td>
			</tr>
			<tr valign="top">
				<td><code>image_width</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Image width. The image is pulled from your store and is not resized, only the <code>width</code> attribute of the <code>img</code> tag is set.', 'wp-storefront' ) ?></td>
				<td></td>
			</tr>
			<tr valign="top">
				<td><code>image_height</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Image height. The image is pulled from your store and is not resized, only the <code>height</code> attribute of the <code>img</code> tag is set.', 'wp-storefront' ) ?></td>
				<td></td>
			</tr>
			<tr valign="top">
				<td><code>hide_image</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Hide product image.', 'wp-storefront' ) ?></td>
				<td>false</td>
			</tr>
			<tr valign="top">
				<td><code>show_description</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Show product short description.', 'wp-storefront' ) ?></td>
				<td>true</td>
			</tr>
			<tr valign="top">
				<td><code>url_html_suffix</code></td>
				<td><?php _e( 'No', 'wp-storefront' ) ?></td>
				<td><?php _e( 'Add the ".html" suffix to all product URLs.', 'wp-storefront' ) ?></td>
				<td>true</td>
			</tr>
			</tbody>
		</table>

		<h2><?php _e( 'Actions', 'wp-storefront' ) ?></h2>
		<p><?php _e( 'Coming soon, please refer to the templates files <code>templates/products.php</code> and <code>templates/product.php</code>.', 'wp-storefront' ) ?></p>

		<h2><?php _e( 'Filters', 'wp-storefront' ) ?></h2>
		<p><?php _e( 'Coming soon, please refer to the templates files <code>templates/products.php</code> and <code>templates/product.php</code>.', 'wp-storefront' ) ?></p>
		<?php
	}

	/**
	 * Output the general settings page.
	 * @since 2.0.0
	 */
	protected function general_settings_html() {
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Disable Customizer CSS', 'wp-storefront' ); ?></th>
				<td>
					<input type="checkbox"
						   name="wp_storefront_disable_customizer_css"<?php echo get_option( 'wp_storefront_disable_customizer_css', false ) ? ' checked' : ''; ?> />
				</td>
			</tr>
			<tr valign="top">
				<th scope="now"><?php _e( 'Enable cache', 'wp-storefront' ); ?></th>
				<td>
					<input type="checkbox"
						   name="wp_storefront_cache_enabled"<?php echo Plugin::get_instance()->get_cache()->is_enabled() ? ' checked' : ''; ?> />
				</td>
			</tr>

			<tr valign="top"
				class="cache-lifetime"
					<?php
					if ( ! Plugin::get_instance()->get_cache()->is_enabled() ) :
						?>
						style="display: none;"<?php endif; ?>>
				<th scope="now"><?php _e( 'Cache lifetime', 'wp-storefront' ); ?></th>
				<td>
					<?php
					$this->display_cache_lifetime_html(
							get_option(
									'wp_storefront_cache_lifetime',
									Cache::DEFAULT_CACHE_LIFETIME
							)
					);
					?>
					<button type="button" class="button button-secondary"
							data-flush-cache><?php _e( 'Flush cache', 'wp-storefront' ) ?></button>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Display <select> for cache lifetime
	 *
	 * @param int $default_lifetime Default lifetime to be selected.
	 *
	 * @since 1.2.0
	 *
	 */
	protected function display_cache_lifetime_html( $default_lifetime = Cache::DEFAULT_CACHE_LIFETIME ) {
		// Compatibility with 1.2.1.
		if ( 'indefinite' == $default_lifetime ) {
			$default_lifetime = YEAR_IN_SECONDS;
		}
		$options = array(
				array(
						'lifetime' => HOUR_IN_SECONDS,
						'label'    => __( '1 hour', 'wp-storefront' ),
				),
				array(
						'lifetime' => 6 * HOUR_IN_SECONDS,
						'label'    => __( '6 hours', 'wp-storefront' ),
				),
				array(
						'lifetime' => 12 * HOUR_IN_SECONDS,
						'label'    => __( '12 hours', 'wp-storefront' ),
				),
				array(
						'lifetime' => DAY_IN_SECONDS,
						'label'    => __( '1 day', 'wp-storefront' ),
				),
				array(
						'lifetime' => 3 * DAY_IN_SECONDS,
						'label'    => __( '3 days', 'wp-storefront' ),
				),
				array(
						'lifetime' => WEEK_IN_SECONDS,
						'label'    => __( '1 week', 'wp-storefront' ),
				),
				array(
						'lifetime' => YEAR_IN_SECONDS,
						'label'    => __( '1 year', 'wp-storefront' ),
				),
		);

		$html = '<select name="wp_storefront_cache_lifetime">';
		foreach ( $options as $option ) {
			$html .= '<option value="' . $option['lifetime'] . '"';
			if ( $option['lifetime'] == $default_lifetime ) {
				$html .= ' selected';
			}
			$html .= '>' . $option['label'] . '</option>';
		}
		$html .= '</select>';

		echo $html;
	}

	/**
	 * Tells if the jquery script is enabled or disabled
	 *
	 * @return bool
	 * @deprecated Since 1.2.7. The script has been replaced with flex-box.
	 *
	 * @since      1.2.0
	 */
	public function use_jquery_script() {
		return get_option( 'wp_storefront_jquery_script', true );
	}

	/**
	 * Create new Magento admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		ob_start();
		readfile( plugin_dir_path( dirname( __FILE__ ) ) . 'images/icon.svg' );
		$icon = ob_get_clean();

		add_menu_page(
				__( 'WP Storefront', 'wp-storefront' ),
				__( 'WP Storefront', 'wp-storefront' ),
				'manage_options',
				'wp-storefront',
				array( Plugin::get_instance()->get_admin(), 'page' ),
				'data:image/svg+xml;base64,' . base64_encode( $icon )
		);
	}

	/**
	 * Register new customizer settings.
	 *
	 * @param \WP_Customize_Manager $wp_customize Customizer instance to add panels, sections and settings.
	 *
	 * @since 1.2.7
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_panel(
				'magento_settings', array(
						'title' => __( 'Magento', 'wp-storefront' ),
				)
		);

		$wp_customize->add_section(
				'magento_settings_colors', array(
						'title' => __( 'Colors', 'wp-storefront' ),
						'panel' => 'magento_settings',
				)
		);

		$wp_customize->add_setting(
				'magento_color_current_price', array(
						'default'           => '#3399cc',
						'sanitize_callback' => 'sanitize_hex_color',
						'transport'         => 'postMessage',
				)
		);

		$wp_customize->add_setting(
				'magento_color_regular_price', array(
						'default'           => '#858585',
						'sanitize_callback' => 'sanitize_hex_color',
						'transport'         => 'postMessage',
				)
		);

		$wp_customize->add_setting(
				'magento_color_button', array(
						'default'           => '#3399cc',
						'sanitize_callback' => 'sanitize_hex_color',
						'transport'         => 'postMessage',
				)
		);

		$wp_customize->add_setting(
				'magento_color_button_text', array(
						'default'           => '#FFFFFF',
						'sanitize_callback' => 'sanitize_hex_color',
						'transport'         => 'postMessage',
				)
		);

		$wp_customize->add_setting(
				'magento_color_button_hover', array(
						'default'           => '#2e8ab8',
						'sanitize_callback' => 'sanitize_hex_color',
						'transport'         => 'postMessage',
				)
		);

		$wp_customize->add_control(
				new \WP_Customize_Color_Control(
						$wp_customize, 'magento_color_current_price',
						array(
								'label'    => __( 'Current Price', 'wp-storefront' ),
								'settings' => 'magento_color_current_price',
								'section'  => 'magento_settings_colors',
						)
				)
		);

		$wp_customize->add_control(
				new \WP_Customize_Color_Control(
						$wp_customize, 'magento_color_regular_price',
						array(
								'label'    => __( 'Regular Price', 'wp-storefront' ),
								'settings' => 'magento_color_regular_price',
								'section'  => 'magento_settings_colors',
						)
				)
		);

		$wp_customize->add_control(
				new \WP_Customize_Color_Control(
						$wp_customize, 'magento_color_button', array(
								'label'    => __( 'Button', 'wp-storefront' ),
								'settings' => 'magento_color_button',
								'section'  => 'magento_settings_colors',
						)
				)
		);

		$wp_customize->add_control(
				new \WP_Customize_Color_Control(
						$wp_customize, 'magento_color_button_text', array(
								'label'    => __( 'Button text', 'wp-storefront' ),
								'settings' => 'magento_color_button_text',
								'section'  => 'magento_settings_colors',
						)
				)
		);

		$wp_customize->add_control(
				new \WP_Customize_Color_Control(
						$wp_customize, 'magento_color_button_hover', array(
								'label'    => __( 'Button hover', 'wp-storefront' ),
								'settings' => 'magento_color_button_hover',
								'section'  => 'magento_settings_colors',
						)
				)
		);
	}
}
