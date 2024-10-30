<?php
/**
 * @var array $wp_storefront An array of extracted variables.
 */

do_action( 'wp_storefront_before_products' );
?>
	<div class="<?php esc_attr_e( $wp_storefront['class'] ) ?>">
		<ul class="products">
			<?php
			/**
			 * Here we load the templates/product.php template by passing
			 * the products and the filtered shortcode attributes.
			 */
			do_action( 'wp_storefront_products', $wp_storefront['products'], $wp_storefront['parameters_bag'] ) ?>
		</ul>
	</div>
<?php
do_action( 'wp_storefront_after_products' );
