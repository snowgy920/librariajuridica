<?php
/**
 * Same Authors Products
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $porto_settings, $porto_woocommerce_loop, $porto_product_layout;

if ( empty( $product ) || ! $product->exists() ) {
	return;
}

$cat_ids = wp_get_post_terms( get_the_id(), 'product_cat', array('fields' => 'ids') ); // array
$args = array(
	'post_type'           => 'product',
  'post_status'           => 'publish',
	'ignore_sticky_posts' => 1,
	'no_found_rows'       => 1,
	'posts_per_page'      => $porto_settings['product-related-count'],
	'post__not_in'          => array( get_the_id() ),
	'orderby'             => $orderby,
  'tax_query'             => array(
    array(
      'taxonomy'      => 'product_cat',
      'field' => 'term_id', //This is optional, as it defaults to 'term_id'
      'terms'         => $cat_ids,
      'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
    ),
    array(
      'taxonomy'      => 'product_visibility',
      'field'         => 'slug',
      'terms'         => 'exclude-from-catalog', // Possibly 'exclude-from-search' too
      'operator'      => 'NOT IN'
    )
  )
);

$products = new WP_Query( $args );

$porto_woocommerce_loop['columns'] = isset( $porto_settings['product-related-cols'] ) ? $porto_settings['product-related-cols'] : $porto_settings['product-cols'];

if ( ! $porto_woocommerce_loop['columns'] ) {
	$porto_woocommerce_loop['columns'] = 4;
}

if ( 'left_sidebar' == $porto_product_layout ) {
	$container_class = '';
} elseif ( porto_is_wide_layout() ) {
	$container_class = 'container-fluid';
} else {
	$container_class = 'container';
}

if ( $products->have_posts() ) : ?>
	<div class="related products">
		<div class="<?php echo esc_attr( $container_class ); ?>">
			<?php
				$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Same category\'s', 'woocommerce' ) );

			if ( $heading ) :
				?>
				<h2 class="slider-title"><?php echo esc_html( $heading ); ?></h2>
			<?php endif; ?>

			<div class="slider-wrapper">

				<?php
				$porto_woocommerce_loop['view']       = 'products-slider';
				$porto_woocommerce_loop['navigation'] = false;
				$porto_woocommerce_loop['pagination'] = true;
				$porto_woocommerce_loop['el_class']   = 'show-dots-title-right';

				woocommerce_product_loop_start();
				?>

				<?php
				while ( $products->have_posts() ) :
					$products->the_post();
					?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

				<?php
				woocommerce_product_loop_end();
				?>
			</div>
		</div>
	</div>
	<?php
endif;

wp_reset_postdata();
