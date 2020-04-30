<?php
add_action('wp_enqueue_scripts', 'enqueue_child_theme_styles', PHP_INT_MAX);
function enqueue_child_theme_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_register_style('child-theme-style', get_stylesheet_directory_uri() . '/style.css');
    wp_enqueue_style('child-theme-style');
    //wp_register_script('myfirstscript',  get_stylesheet_directory_uri() .'/myscript.js',   array ('jquery', 'jquery-masonry'),  false, false);
    //wp_enqueue_script('myfirstscript');

    wp_register_script('porto-child',  get_stylesheet_directory_uri() .'/porto-custom.js',   array ('jquery'), true, true);
    wp_enqueue_script('porto-child');
}

add_action('init', 'custom_taxonomy_Product_author');
function custom_taxonomy_Product_author()
{
    $labels = array(
        'name'                       => 'Authors',
        'singular_name'              => 'Author',
        'menu_name'                  => 'Author',
        'all_items'                  => 'All Authors',
        'parent_item'                => 'Parent Author',
        'parent_item_colon'          => 'Parent Author:',
        'new_item_name'              => 'New Author Name',
        'add_new_item'               => 'Add New Author',
        'edit_item'                  => 'Edit Author',
        'update_item'                => 'Update Author',
        'separate_items_with_commas' => 'Separate Author with commas',
        'search_items'               => 'Search Authors',
        'add_or_remove_items'        => 'Add or remove Authors',
        'choose_from_most_used'      => 'Choose from the most used Authors',
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy('product_author', 'product', $args);
    register_taxonomy_for_object_type('product_author', 'product');
}

add_action('init', 'custom_taxonomy_Product_publisher');
function custom_taxonomy_Product_publisher()
{
    $labels = array(
        'name'                       => 'Publishers',
        'singular_name'              => 'Publisher',
        'menu_name'                  => 'Publisher',
        'all_items'                  => 'All Publisher',
        'parent_item'                => 'Parent Publisher',
        'parent_item_colon'          => 'Parent Publisher:',
        'new_item_name'              => 'New Publisher Name',
        'add_new_item'               => 'Add New Publisher',
        'edit_item'                  => 'Edit Publisherr',
        'update_item'                => 'Update Publisher',
        'separate_items_with_commas' => 'Separate Publisher with commas',
        'search_items'               => 'Search Publisher',
        'add_or_remove_items'        => 'Add or remove Publisher',
        'choose_from_most_used'      => 'Choose from the most used Publishers',
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy('publisher', 'product', $args);
    register_taxonomy_for_object_type('publisher', 'product');
}

add_action('init', 'porto_hook_4_product_title');
function porto_hook_4_product_title() {
    remove_action('woocommerce_shop_loop_item_title', 'porto_woocommerce_shop_loop_item_title');
    add_action('woocommerce_shop_loop_item_title', 'porto_custom_woocommerce_shop_loop_item_title');
    function porto_custom_woocommerce_shop_loop_item_title() {
        echo '<h3 class="woocommerce-loop-product__title" title="'.get_the_title().'">';
        the_title();
        echo '</h3>';
    }
}

add_action( 'woocommerce_after_single_product_summary', 'porto_output_same_authors', 21 );
function porto_output_same_authors() {
    wc_get_template( 'single-product/same_authors.php');
}

add_action( 'woocommerce_after_single_product_summary', 'porto_output_same_categories', 21 );
function porto_output_same_categories() {
    wc_get_template( 'single-product/same_categories.php');
}


add_action('woocommerce_single_product_summary','woo_custom_out_of_stock', 35);
function woo_custom_out_of_stock() {
	global $product;
	$availability = $product->get_availability();

	if ($availability['availability']) {
?>
    <form class="cart" action="<?php echo get_permalink( get_page_by_path( 'contact-us' ) );?>" method="post">
        <button type="submit" class="button alt">Solicită actualizarea stocului sau retipărirea cărții</button>
    </form>
<?php
    }
}

require_once(dirname( __FILE__ ) . '/widgets/date_filter_list.php');
