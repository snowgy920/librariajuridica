<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'widgets_init', 'porto_load_date_filter_widget' );

function porto_load_date_filter_widget() {
	register_widget( 'Porto_WC_Widget_Date_Filter' );
}

class Porto_WC_Widget_Date_Filter extends WP_Widget {

	protected static $instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		// $classname = 'yith-woocommerce-ajax-product-filter yith-woo-ajax-navigation woocommerce widget_layered_nav';
		// $classname .= defined( 'YITH_WCAN_PREMIUM' ) && 'checkboxes' == yith_wcan_get_option( 'yith_wcan_ajax_shop_filter_style', 'standard' ) ? ' with-checkbox' : '';
		// $widget_ops  = array( 'classname' => $classname, 'description' => __( 'Filter the list of products without reloading the page', 'yith-woocommerce-ajax-navigation' ) );
		// $control_ops = array( 'width' => 400, 'height' => 350 );
		// add_action('wp_ajax_yith_wcan_select_type', array( $this, 'ajax_print_terms') );
		// parent::__construct( 'yith-woo-ajax-navigation', _x( 'YITH Ajax Product Filter', '[Plugin Name] Admin: Widget Title', 'yith-woocommerce-ajax-navigation' ), $widget_ops, $control_ops );


		if ( ! self::$instance ) {
			self::$instance = $this;
		}

		$widget_ops = array(
			'classname'   => 'woocommerce porto_widget_date_filter widget_layered_nav yith-woocommerce-ajax-product-filter yith-woo-ajax-navigation',
			'description' => __( 'Display input boxes to filter products in your store by publication date.', 'porto-functionality' ),
		);

		$control_ops = array( 'id_base' => 'porto_woocommerce_date_filter-widget' );

		parent::__construct( 'porto_woocommerce_date_filter-widget', __( 'Porto: Filter by Publication Date', 'porto-functionality' ), $widget_ops, $control_ops );
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		global $wp;

		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		// Find min and max price in current result set.
		$date_range = $this->get_filtered_date();
		$min    = $date_range->min_date;
		$max    = $date_range->max_date;

		if ( $min === $max ) {
			return;
		}

		echo porto_filter_output( $before_widget );

		if ( $title ) {
			echo $before_title . sanitize_text_field( $title ) . $after_title;
		}

		if ( '' === get_option( 'permalink_structure' ) ) {
			$form_action = remove_query_arg( array( 'page', 'paged', 'product-page' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
		} else {
			$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
		}

		$min_date = apply_filters( 'woocommerce_date_filter_widget_min_amount', $min );
		$max_date = apply_filters( 'woocommerce_date_filter_widget_max_amount', $max );

		echo '<form method="get" action="' . esc_url( $form_action ) . '">
			<div class="fields">
			<input type="text" class="form-control" name="min_date" value="' . ( isset( $_GET['min_date'] ) ? esc_attr( $_GET['min_date'] ) : '' ) . '" placeholder="' . esc_attr( $min_date ) . '" data-min="' . esc_attr( apply_filters( 'woocommerce_date_widget_min_amount', $min ) ) . '" placeholder="" /> <span>-</span>
			<input type="text" class="form-control" name="max_date" value="' . ( isset( $_GET['max_date'] ) ? esc_attr( $_GET['max_date'] ) : '' ) . '" placeholder="' . esc_attr( $max_date ) . '" data-max="' . esc_attr( apply_filters( 'woocommerce_date_widget_max_amount', $max ) ) . '" placeholder="" />
			<button type="submit" class="button">' . esc_html__( 'Filter', 'woocommerce' ) . '</button>
			' . ( function_exists( 'wc_query_string_form_fields' ) ? wc_query_string_form_fields( null, array( 'min_date', 'max_date' ), '', true ) : '' ) . '
			</div>
		</form>';

		echo porto_filter_output( $after_widget );
	}

	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	function form( $instance ) {

		$defaults = array( 'title' => __( 'Price', 'porto-functionality' ) );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<strong><?php esc_html_e( 'Title', 'porto-functionality' ); ?>:</strong>
				<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo isset( $instance['title'] ) ? porto_strip_script_tags( $instance['title'] ) : ''; ?>" />
			</label>
		</p>
		<?php
	}

	public function get_filtered_date() {
		global $wpdb;

		if ( wc()->query->get_main_query() ) {
			$args = wc()->query->get_main_query()->query_vars;
		} else {
			$args = array();
		}
		$tax_query  = isset( $args['tax_query'] ) ? $args['tax_query'] : array();
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

		if ( ! is_post_type_archive( 'product' ) && ! empty( $args['taxonomy'] ) && ! empty( $args['term'] ) ) {
			$tax_query[] = array(
				'taxonomy' => $args['taxonomy'],
				'terms'    => array( $args['term'] ),
				'field'    => 'slug',
			);
		}

		foreach ( $meta_query + $tax_query as $key => $query ) {
			if ( ! empty( $query['date_filter'] ) || ! empty( $query['rating_filter'] ) ) {
				unset( $meta_query[ $key ] );
			}
		}

		$meta_query = new WP_Meta_Query( $meta_query );
		$tax_query  = new WP_Tax_Query( $tax_query );

		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		$sql  = "SELECT DATE_FORMAT(MIN(DATE(date_meta.meta_value)), '%%d.%%m.%%Y') as min_date, DATE_FORMAT(MAX(DATE(date_meta.meta_value)), '%%d.%%m.%%Y') as max_date FROM {$wpdb->posts} ";
		$sql .= " LEFT JOIN {$wpdb->postmeta} as date_meta ON {$wpdb->posts}.ID = date_meta.post_id " . $tax_query_sql['join'] . $meta_query_sql['join'];
		$sql .= "   WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', apply_filters( 'woocommerce_price_filter_post_type', array( 'product' ) ) ) ) . "')
					AND {$wpdb->posts}.post_status = %s
					AND date_meta.meta_key = 'publication_date'
					AND date_meta.meta_value > '' ";
		$sql .= $tax_query_sql['where'] . $meta_query_sql['where'];

		if ( wc()->query->get_main_query() && $search = WC_Query::get_main_search_query_sql() ) {
			$sql .= ' AND ' . $search;
		}

		return $wpdb->get_row( $wpdb->prepare( $sql, 'publish' ) );
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
