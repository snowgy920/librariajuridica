<?php
/**
 * Single Product Rating
 *
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;
$id = $product->get_id();

if ( ( function_exists( 'wc_review_ratings_enabled' ) && ! wc_review_ratings_enabled() ) || ( ! function_exists( 'wc_review_ratings_enabled' ) && 'no' === get_option( 'woocommerce_enable_review_rating' ) ) ) {
	return;
}

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

?>

<div class="woocommerce-product-rating"<?php echo (int) $rating_count ? ' itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"' : ''; ?>>
	<div class="star-rating" title="<?php echo esc_attr( $average ); ?>">
		<span style="width:<?php echo ( 100 * ( $average / 5 ) ); ?>%">
			<?php /* translators: %s: Rating value */ ?>
			<strong<?php echo (int) $rating_count ? ' itemprop="ratingValue"' : ''; ?> class="rating"><?php echo esc_html( $average ); ?></strong> <?php printf( esc_html__( 'out of %1$s5%2$s', 'woocommerce' ), '', '' ); ?>
		</span>
	</div>
	<?php if ( $rating_count > 0 ) : ?>
	<meta content="<?php echo (int) $rating_count; ?>" itemprop="ratingCount" />
	<meta content="5" itemprop="bestRating" />
	<meta content="1" itemprop="worstRating" />
	<?php endif; ?>
	<?php if ( comments_open() ) : ?>
		<?php //phpcs:disable ?>
		<?php if ( $rating_count > 0 ) : ?>
			<?php /* translators: %s: Review count */ ?>
			<div class="review-link"><a href="<?php echo porto_is_ajax() ? esc_url( get_the_permalink() ) : ''; ?>#reviews" class="woocommerce-review-link" rel="nofollow"><?php printf( _n( '%s customer review', '%s customer reviews', (int) $review_count, 'woocommerce' ), '<span itemprop="reviewCount" class="count">' . ( (int) $review_count ) . '</span>' ); ?></a>|<a href="<?php echo porto_is_ajax() ? esc_url( get_the_permalink() ) : ''; ?>#review_form" class="woocommerce-write-review-link" rel="nofollow"><?php esc_html_e( 'Add a review', 'woocommerce' ); ?></a></div>
		<?php else : ?>
			<div class="review-link noreview">
				<a href="<?php echo porto_is_ajax() ? esc_url( get_the_permalink() ) : ''; ?>#review_form" class="woocommerce-write-review-link" rel="nofollow">( <?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?> )</a>
			</div>
		<?php endif; ?>
		<?php //phpcs:enable ?>
	<?php endif; ?>
</div>
<div class="product-more-info">
	<?php
	$custom_author = get_field('author_priority');

	if (empty($custom_author)) {
		$terms = get_the_terms( $id, 'product_author' );
		if (!empty($terms)){

			echo '<p class="product-author">Autor: ';
			foreach ($terms as $term){
				$term_link = get_term_link($term);
				echo '<a href="'.$term_link.'">'.$term->name.'<span class="common">,</span></a> ';
			}
			echo '</p>';
		}
	} else {
		echo '<p class="product-author">Autor: ';
		foreach ($custom_author as $term){
			$term_link = get_term_link($term);
			echo '<a href="'.$term_link.'">'.$term->name.'<span class="common">,</span></a> ';
		}
		echo '</p>';
	}


	$publishers = get_the_terms( $id, 'publisher' );
	if (!empty($publishers[0])){
		$term_link = get_term_link( $publishers[0] );
		echo '<p class="product-publisher">Editura: <a href="'.$term_link.'">'.$publishers[0]->name.'</a></p>';
	}

	if (!empty(get_field('publication_date'))){
	?>
    <p class="publication-date">Data apariției: <?php the_field('publication_date'); ?></p>
	<?php }

	if (!empty(get_field('updated_to'))){
	?>
    <p class="publication-date">Actualizat la: <?php the_field('updated_to'); ?></p>
	<?php } ?>

</div>
<div class="product-extract">
	<?php
	$extract_url = !empty(get_field('contents_extract_copy')) ? get_field('contents_extract_copy') : get_field('contents_extract');
	$book_url = !empty(get_field('book_extract_copy')) ? get_field('book_extract_copy') : get_field('book_extract');
	if (!empty($extract_url)){
		?>
        <a href="<?php echo $extract_url?>" target="_blank"><i class="fa fa-download" aria-hidden="true"></i>Cuprins</a>
        <?php
	}
	if (!empty($book_url)){
		?>
        <a href="<?php echo $book_url?>" target="_blank"><i class="fa fa-download" aria-hidden="true"></i>Extras</a>
        <?php
	}
	?>
</div>