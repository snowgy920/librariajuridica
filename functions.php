<?php
add_action('wp_enqueue_scripts', 'enqueue_child_theme_styles', PHP_INT_MAX);
function enqueue_child_theme_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_register_style('child-theme-style', get_stylesheet_directory_uri() . '/style.css');
    wp_enqueue_style('child-theme-style');
    //wp_register_script('myfirstscript',  get_stylesheet_directory_uri() .'/myscript.js',   array ('jquery', 'jquery-masonry'),  false, false);
    //wp_enqueue_script('myfirstscript');

    wp_register_script('porto-child',  get_stylesheet_directory_uri() . '/porto-custom.js',   array('jquery'), true, true);
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
function porto_hook_4_product_title()
{
    remove_action('woocommerce_shop_loop_item_title', 'porto_woocommerce_shop_loop_item_title');
    add_action('woocommerce_shop_loop_item_title', 'porto_custom_woocommerce_shop_loop_item_title');
    function porto_custom_woocommerce_shop_loop_item_title()
    {
        echo '<h3 class="woocommerce-loop-product__title" title="' . get_the_title() . '">';
        the_title();
        echo '</h3>';
    }
}

add_action('woocommerce_after_single_product_summary', 'porto_output_same_authors', 21);
function porto_output_same_authors()
{
    wc_get_template('single-product/same_authors.php');
}

add_action('woocommerce_after_single_product_summary', 'porto_output_same_categories', 21);
function porto_output_same_categories()
{
    wc_get_template('single-product/same_categories.php');
}


add_action('woocommerce_single_product_summary', 'woo_custom_out_of_stock', 35);
function woo_custom_out_of_stock()
{
    global $product;
    $availability = $product->get_availability();

    if ($availability['availability']) {
?>
        <form class="cart" action="<?php echo get_permalink(get_page_by_path('contact-us')); ?>" method="post">
            <button type="submit" class="button alt">Solicită actualizarea stocului sau retipărirea cărții</button>
        </form>
<?php
    }
}

// product filter by publication date
require_once(dirname(__FILE__) . '/widgets/date_filter_list.php');
add_filter('posts_clauses', 'pub_date_filter_post_clauses', 11, 2);
function pub_date_filter_post_clauses($args, $wp_query)
{
    global $wpdb;

    if (!$wp_query->is_main_query() || (!isset($_GET['max_date']) && !isset($_GET['min_date']))) {
        return $args;
    }

    if (!strstr($args['join'], 'date_meta')) {
        $args['join'] .= " LEFT JOIN {$wpdb->postmeta} date_meta ON $wpdb->posts.ID = date_meta.post_id AND date_meta.meta_key='publication_date' ";
    }

    if (!empty($_GET['min_date'])) {
        $args['where'] .= $wpdb->prepare(
            ' AND date_meta.meta_value >= STR_TO_DATE("%s", "%%d.%%m.%%Y")',
            wp_unslash($_GET['min_date'])
        );
    }

    if (!empty($_GET['max_date'])) {
        $args['where'] .= $wpdb->prepare(
            ' AND date_meta.meta_value <= STR_TO_DATE("%s", "%%d.%%m.%%Y")',
            wp_unslash($_GET['max_date'])
        );
    }

    return $args;
}


function porto_save_product_custom_meta($post_id, $post, $update)
{
    $post_type = get_post_type($post_id);
    // If this isn't a 'product' post, don't update it.
    if ($post_type != 'product')
        return;

    if (!empty($_POST['attribute_names']) && !empty($_POST['attribute_values'])) {
        $attribute_names = $_POST['attribute_names'];
        $attribute_values = $_POST['attribute_values'];
        foreach ($attribute_names as $key => $attribute_name) {
            if ($attribute_name == 'pa_autor') {
                $custom_author = get_field('author_priority');
                if (empty($custom_author)) {
                    $term = get_term_by('id', $attribute_values[$key][0], $attribute_name);
                    update_post_meta($post_id, $attribute_name, $term->name);
                } else {
                    update_post_meta($post_id, $attribute_name, $custom_author[0]->name);
                }
            } else {
                $term = get_term_by('id', $attribute_values[$key][0], $attribute_name);
                update_post_meta($post_id, $attribute_name, $term->name);
            }
        }
    }
}
add_action('save_post', 'porto_save_product_custom_meta', 10, 3);


// save all attribute info to metainfo
function porto_update_all_products_metainfo()
{
    $query = new WP_Query(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ));
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();

        $product = wc_get_product($post_id);
        $attributes = $product->get_attributes();
        foreach ($attributes as $key => $attr) {
            $options = $attr->get_options();
            if ($key == 'pa_autor') {
                $custom_author = get_field('author_priority');
                if (empty($custom_author)) {
                    $term = get_term_by('id', $options[0], $key);
                    update_post_meta($post_id, $key, $term->name);
                } else {
                    update_post_meta($post_id, $key, $custom_author[0]->name);
                }
            } else {
                $term = get_term_by('id', $options[0], $key);
                update_post_meta($post_id, $key, $term->name);
            }
        }
    }
    exit;
}
// add_action('init', 'porto_update_all_products_metainfo');


function porto_add_postmeta_ordering_args($sort_args)
{

    $orderby_value = isset($_GET['orderby']) ? wc_clean($_GET['orderby']) : apply_filters('woocommerce_default_catalog_orderby', get_option('woocommerce_default_catalog_orderby'));
    switch ($orderby_value) {
        case 'publication_date':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'publication_date';
            break;
        case 'pa_categorie':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_categorie';
            break;
        case 'pa_editura':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_editura';
            break;
        case 'pa_autor':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_autor';
            break;
        case 'pa_isbn':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_isbn';
            break;
        case 'pa_editie':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_editie';
            break;
        case 'pa_ziua-aparitiei':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_ziua-aparitiei';
            break;
        case 'pa_luna-aparitiei':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_luna-aparitiei';
            break;
        case 'pa_anul-aparitiei':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_anul-aparitiei';
            break;
        case 'pa_ziua-actualizarii':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_ziua-actualizarii';
            break;
        case 'pa_luna-actualizarii':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_luna-actualizarii';
            break;
        case 'pa_anul-actualizarii':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_anul-actualizarii';
            break;
        case 'pa_format':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_format';
            break;
        case 'pa_legare':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_legare';
            break;
        case 'pa_coperta':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_coperta';
            break;
        case 'pa_hartie':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_hartie';
            break;
        case 'pa_numar-pagini':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_numar-pagini';
            break;
        case 'pa_volum':
            $sort_args['orderby']  = 'meta_value';
            $sort_args['order']    = 'asc';
            $sort_args['meta_key'] = 'pa_volum';
            break;
    }

    return $sort_args;
}
add_filter('woocommerce_get_catalog_ordering_args', 'porto_add_postmeta_ordering_args');


// Add these new sorting arguments to the sortby options on the frontend
function porto_add_new_postmeta_orderby($sortby)
{
    $sortby['publication_date'] = __('Sort by publication date', 'woocommerce');
    $sortby['pa_categorie'] = __('Sort by categorie', 'woocommerce');
    $sortby['pa_editura'] = __('Sort by editura', 'woocommerce');
    $sortby['pa_autor'] = __('Sort by autor', 'woocommerce');
    $sortby['pa_isbn'] = __('Sort by isbn', 'woocommerce');
    $sortby['pa_editie'] = __('Sort by editie', 'woocommerce');
    $sortby['pa_ziua-aparitiei'] = __('Sort by ziua apariției', 'woocommerce');
    $sortby['pa_luna-aparitiei'] = __('Sort by luna apariției', 'woocommerce');
    $sortby['pa_anul-aparitiei'] = __('Sort by anul apariției', 'woocommerce');
    $sortby['pa_ziua-actualizarii'] = __('Sort by ziua actualizării', 'woocommerce');
    $sortby['pa_luna-actualizarii'] = __('Sort by luna actualizării', 'woocommerce');
    $sortby['pa_anul-actualizarii'] = __('Sort by anul actualizării', 'woocommerce');
    $sortby['pa_format'] = __('Sort by format', 'woocommerce');
    $sortby['pa_legare'] = __('Sort by legare', 'woocommerce');
    $sortby['pa_coperta'] = __('Sort by copertă', 'woocommerce');
    $sortby['pa_hartie'] = __('Sort by hârtie', 'woocommerce');
    $sortby['pa_numar-pagini'] = __('Sort by număr pagini', 'woocommerce');
    $sortby['pa_volum'] = __('Sort by volum', 'woocommerce');
    return $sortby;
}
add_filter('woocommerce_default_catalog_orderby_options', 'porto_add_new_postmeta_orderby');
add_filter('woocommerce_catalog_orderby', 'porto_add_new_postmeta_orderby');


/*
function porto_custom_search_query($query)
{
    if ($query->is_search) {
        $meta_query_args = $query->get('meta_query');
        $meta_query_args[] = array(
            array(
                'key' => 'pa_categorie',
                'value' => $query->query_vars['s'],
                'compare' => 'LIKE',
            ),
            array(
                'key' => 'pa_isbn',
                'value' => $query->query_vars['s'],
                'compare' => 'LIKE',
            ),
        );
        $query->set('meta_query', $meta_query_args);
        add_filter('get_meta_sql', 'porto_custom_replace_and_with_or');
    };
}
function porto_custom_replace_and_with_or($sql)
{
    var_dump($sql);
    if (1 === strpos($sql['where'], 'AND')) {
        $sql['where'] = substr($sql['where'], 4);
        $sql['where'] = ' OR ' . $sql['where'];
    }
    var_dump($sql);

    //make sure that this filter will fire only once for the meta query
    // remove_filter('get_meta_sql', 'porto_custom_replace_and_with_or');
    return $sql;
}
add_filter('pre_get_posts', 'porto_custom_search_query');
*/



function porto_meta_in_search_query($pieces, $args)
{
    global $wpdb;

    if (!empty($args->query['s'])) { // only run on search query.
        $keyword = $args->query['s'];
        $escaped_percent = $wpdb->placeholder_escape(); // WordPress escapes "%" since 4.8.3 so we can't use percent character directly.
        $query = " (unique_postmeta_selector.meta_value LIKE '{$escaped_percent}{$keyword}{$escaped_percent}') OR ";

        if (!empty($query)) { // append necessary WHERE and JOIN options.
            $pieces['where'] = str_replace("((({$wpdb->posts}.post_title LIKE '{$escaped_percent}", "( {$query} (({$wpdb->posts}.post_title LIKE '{$escaped_percent}", $pieces['where']);
            $pieces['join'] = $pieces['join'] . " INNER JOIN {$wpdb->postmeta} AS unique_postmeta_selector ON ({$wpdb->posts}.ID = unique_postmeta_selector.post_id) ";
        }
        $pieces['distinct'] = 'DISTINCT';
    }
    return $pieces;
}
add_filter('posts_clauses', 'porto_meta_in_search_query', 20, 2);
