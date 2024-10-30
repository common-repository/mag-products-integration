<?php
/**
 * Load plugin text domain and execute initialization functions.
 *
 * @package WpStoreFront
 */

namespace WpStoreFront;

/**
 * Class Plugin
 *
 * @since   1.0.0
 */
class Plugin {

	/**
	 * Singleton of Plugin.
	 *
	 * @var Plugin $instance
	 */
	protected static $instance;

	/**
	 * Instance of Admin.
	 *
	 * @var Admin $admin
	 */
	protected $admin;

	/**
	 * Instance of Cache.
	 *
	 * @var Cache $cache
	 */
	protected $cache;

	/**
	 * Instance of Store_Manager
	 *
	 * @var Store_Manager $store_manager
	 */
	protected $store_manager;

	/**
	 * Instance of Template_Loader
	 *
	 * @var Template_Loader $template_loader
	 */
	protected $template_loader;

	/**
	 * Create the instances of $admin and $shortcode
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->admin           = new Admin();
		$this->cache           = new Cache();
		$this->store_manager   = new Store_Manager();
		$this->template_loader = new Template_Loader( 'wp-storefront' );
	}

	/**
	 * Return the singleton of the current class.
	 *
	 * @return Plugin Singleton
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {
		if ( empty( self::$instance ) || ! self::$instance instanceof self ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialization of the plugin. Load plugin text domain and execute initialization functions.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		add_action( 'init', array( self::$instance, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'euqueue_scripts' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );
		add_action( 'wp_head', array( $this, 'output_colors_css' ), 999 );
		add_action( 'customize_register', array( $this->admin, 'customize_register' ) );
		add_action( 'customize_preview_init', array( $this->admin, 'customize_preview' ) );
		add_shortcode( 'wp-storefront', array( $this, 'do_shortcode' ) );
		add_action( 'wp_storefront_products', array( $this, 'show_products' ), 10, 2 );
	}

	/**
	 * Enqueue plugin's default CSS styles for the products list
	 *
	 * @since 1.0.0
	 */
	public function euqueue_scripts() {
		wp_enqueue_style( 'wp-storefront-style', plugins_url( 'css/style.min.css', dirname( __FILE__ ) ), array(), WP_STOREFRONT_PLUGIN_VERSION );
	}

	/**
	 * Add Settings link on the plugins page.
	 *
	 * @param array $links Links shown under the plugin name in the plugins page.
	 *
	 * @return array
	 * @since 1.0.0
	 *
	 */
	public function add_action_links( $links ) {
		$settings = array(
				'<a href="' . admin_url( 'admin.php?page=Plugin-products-integration/class.Plugin-products-integration-admin.php' ) . '">' . __( 'Settings' ) . '</a>',
		);

		return array_merge( $settings, $links );
	}

	/**
	 * Return the instance of the administration class.
	 *
	 * @return Admin Instance.
	 * @since 1.0.0
	 *
	 */
	public function get_admin() {
		return $this->admin;
	}

	/**
	 * Return the instance of the cache class.
	 *
	 * @return Cache Instance.
	 * @since 1.2.0
	 *
	 */
	public function get_cache() {
		return $this->cache;
	}

	/**
	 * Return the instance of the store manager.
	 *
	 * @return Store_Manager Instance.
	 * @since 2.0.0
	 */
	public function get_store_manager() {
		return $this->store_manager;
	}

	/**
	 * Return the instance of the template loader.
	 *
	 * @return Template_Loader Instance.
	 * @since 2.0.0
	 */
	public function get_template_loader() {
		return $this->template_loader;
	}

	/**
	 * Function executed on plugin activation.
	 * Update plugin's options to set default values.
	 *
	 * @since 1.0.0
	 */
	public function activate() {

	}

	/**
	 * Function executed on plugin deactivation.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {

	}

	/**
	 * Output customizer colours values into an inline CSS style.
	 *
	 * @since 1.2.7
	 */
	public function output_colors_css() {
		$hide_css = get_option( 'wp_storefront_disable_customizer_css', false );

		if ( empty( $hide_css ) || false === $hide_css ) {
			$current_price_color = get_theme_mod( 'magento_color_current_price', '#3399cc' );
			$regular_price_color = get_theme_mod( 'magento_color_regular_price', '#858585' );
			$button_color        = get_theme_mod( 'magento_color_button', '#3399cc' );
			$button_text_color   = get_theme_mod( 'magento_color_button_text', '#FFFFFF' );
			$button_hover_color  = get_theme_mod( 'magento_color_button_hover', '#2e8ab8' );

			ob_start();
			?>
			<style>
				.magento-wrapper ul.products li.product .price .special-price {
					color: <?php echo esc_html( $current_price_color ); ?>;
				}

				.magento-wrapper ul.products li.product .price .base-price {
					color: <?php echo esc_html( $regular_price_color ); ?>;
				}

				.magento-wrapper ul.products li.product .url a {
					background: <?php echo esc_html( $button_color ); ?>;
					color: <?php echo esc_html( $button_text_color ); ?>;
				}

				.magento-wrapper ul.products li.product .url a:hover {
					background: <?php echo esc_html( $button_hover_color ); ?>;
				}
			</style>
			<?php
			$css = ob_get_clean();
			echo str_replace( array( "\t", "\n", '  ' ), '', $css );
		}
	}

	/**
	 * Run the shortcode.
	 *
	 * @param array $atts Attributes values.
	 * @param string $content The content of the shortcode.
	 *
	 * @return string The content of the shortcode (rendered products).
	 * @since 1.0.0
	 */
	public function do_shortcode( $atts, $content = '' ) {
		$shortcode_cache_id = sha1( serialize( $atts ) );

		$products = array();
		$parameters_bag  = Parameters_Bag::createFromShortcode( $atts );
		if ( Plugin::get_instance()->get_cache()->is_enabled() ) {
			if ( ! Plugin::get_instance()->get_cache()->is_expired( $shortcode_cache_id ) ) {
				$products = Plugin::get_instance()->get_cache()->get_cached_products( $shortcode_cache_id );
			}
		}

		if ( empty( $products ) ) {
			$platform    = $parameters_bag->get( 'platform' );
			$product_ids = $parameters_bag->get( 'products', $parameters_bag->get( 'product' ) );
			if ( empty( $platform ) || empty( $product_ids ) ) {
				return '';
			}

			$products = $this->store_manager->get_store( $platform )->get_products( $parameters_bag );
			if ( $products instanceof \WP_Error ) {
				return '<p class="wp-storefront-error">' . $products->get_error_message() . '</p>';
			}
			Plugin::get_instance()->get_cache()->set_cached_products( $products, $shortcode_cache_id );
		}

		return $this->store_manager->products_html( $products, $parameters_bag );
	}

	/**
	 * Show the product inside the products.php template.
	 *
	 * @param array $products The products to show (only 1).
	 * @param Parameters_Bag $parameters_bag The parameters bag.
	 *
	 * @since 2.0.0
	 */
	public function show_products( $products, Parameters_Bag $parameters_bag ) {
		$products = ! is_array( $products ) ? [ $products ] : $products;
		foreach ( $products as $product ) {
			if ( $product instanceof Product ) {
				wp_storefront()->get_template_loader()->load(
						'product',
						array(
								'product'            => apply_filters( 'wp_storefront_product', $product ),
								'hide_image'         => $parameters_bag->get( 'hide_image', false, 'boolean' ),
								'target'             => $parameters_bag->get( 'target', '_self', 'string' ),
								'image_width'        => $parameters_bag->get( 'image_width', 0, 'int' ),
								'image_height'       => $parameters_bag->get( 'image_width', 0, 'int' ),
								'title'              => $parameters_bag->get( 'title', 'h2', 'string' ),
								'show_description'   => $parameters_bag->get( 'show_description', true, 'boolean' ),
								'description_length' => $parameters_bag->get( 'description_length', 0, 'int' ),
								'prefix'             => $parameters_bag->get( 'prefix', '', 'string' ),
								'suffix'             => $parameters_bag->get( 'suffix', ' $', 'string' ),
						)
				);
			}
		}
	}
}
