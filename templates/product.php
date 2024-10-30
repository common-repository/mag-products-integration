<?php
/**
 * @var array $wp_storefront An array of extracted variables.
 */

/** @var WpStoreFront\Product $product */
$product = $wp_storefront['product'];
?>
<li class="product">
	<?php
	do_action( 'wp_storefront_before_product', $product );
	if ( ! $wp_storefront['hide_image'] && $product->has_image() ):
		do_action( 'wp_storefront_before_image', $product ); ?>

		<div class="image">
			<a href="<?php echo esc_url( $product->get_url() ) ?>" target="<?php esc_attr_e( $wp_storefront['target'] ) ?>">
				<?php echo $product->get_image( $wp_storefront['image_width'], $wp_storefront['image_height'] ) ?>
			</a>
		</div>

		<?php
		do_action( 'wp_storefront_after_image', $product );
	endif; ?>

	<?php do_action( 'wp_storefront_before_title', $product ); ?>
	<?php echo sprintf( '<a href="%1$s" target="%2$s"><%3$s class="name">%4$s</%3$s></a>', $product->get_url(), $wp_storefront['target'], esc_attr( $wp_storefront['title'] ), esc_html( $product->get_name() ) ); ?>
	<?php do_action( 'wp_storefront_after_title', $product ); ?>

	<?php if ( $wp_storefront['show_description'] && $product->has_short_description() ): ?>
		<?php do_action( 'wp_storefront_before_short_description', $product ); ?>
		<?php echo sprintf(
				'<div class="short-description"><p>%1$s</p></div>',
				$wp_storefront['description_length'] > 0 ?
						substr( $product->get_short_description(), 0, $wp_storefront['description_length'] ) :
						$product->get_short_description()
		) ?>
		<?php do_action( 'wp_storefront_after_short_description', $product ); ?>
	<?php endif; ?>

	<?php if ( $product->has_price() ): ?>
		<?php do_action( 'wp_storefront_before_price', $product ); ?>
		<div class="<?php echo trim( implode( ' ', [
				'price',
				$product->has_special_price() ? 'has-special' : ''
		] ) ) ?>">
			<span class="base-price"><?php echo sprintf( '%1$s%2$s%3$s', esc_attr( $wp_storefront['prefix'] ), esc_attr( number_format( $product->get_price(), 2 ) ), esc_attr( $wp_storefront['suffix'] ) ) ?></span>
			<?php if ( $product->has_special_price() ): ?>
				<span class="special-price"><?php echo sprintf( '%1$s%2$s%3$s', esc_attr( $wp_storefront['prefix'] ), esc_attr( number_format( $product->get_special_price(), 2 ) ), esc_attr( $wp_storefront['suffix'] ) ) ?></span>
			<?php endif; ?>
		</div>
		<?php do_action( 'wp_storefront_after_price', $product ); ?>
	<?php endif; ?>

	<?php
	do_action( 'wp_storefront_before_add_to_cart_button', $product );
	echo sprintf( '<div class="url"><a class="view-details" href="%1$s">%2$s</a></div>', $product->get_url(), __( 'View details', 'wp-storefront' ) );
	do_action( 'wp_storefront_after_add_to_cart_button', $product );
	do_action( 'wp_storefront_after_product', $wp_storefront['product'] );
	?>
</li>
