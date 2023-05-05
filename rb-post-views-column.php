<?php
/**
 * Plugin Name:       RB Post Views Column
 * Plugin URI:        https://github.com/BashirRased/wp-plugin-rb-post-views-column
 * Description:       RB Post Views Column plugin use for your posts visit count.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Tested up to: 6.2
 * Requires PHP: 7.1
 * Author:            Bashir Rased
 * Author URI:        https://profiles.wordpress.org/bashirrased2017/
 * Text Domain:       rb-post-views-column
 * Domain Path: 	  /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin Text domain loaded
function rbpvc_textdomain() {
    load_plugin_textdomain('rb-post-views-column', false, dirname(plugin_basename(__FILE__)).'/languages'); 
}
add_action('plugins_loaded', 'rbpvc_textdomain');

// Github Page Link
add_filter('plugin_row_meta', function ($links, $plugin) {
	if (plugin_basename(__FILE__) == $plugin) {
		$link = sprintf("<a href='%s' style='color:#b32d2e;'>%s</a>", esc_url('https://github.com/BashirRased/wp-plugin-rb-post-views-column'), __('Fork on Github', 'rb-post-views-column'));
		array_push($links, $link);
	}
	return $links;
}, 10, 2);

// Post Views Count Meta Key
function rbpvc_post_view() {	
	if(is_singular()){
		$rbpvc_post_view_meta = 'rbpvc_post_view';
		$rbpvc_post_view_count = get_post_meta(get_the_ID(), $rbpvc_post_view_meta, true);
		$rbpvc_post_view_count++;
		update_post_meta(get_the_ID(), $rbpvc_post_view_meta, $rbpvc_post_view_count);
	}
}
add_action('wp_head', 'rbpvc_post_view');

// Add Post Columns
function rbpvc_add_custom_columns( $columns ) {    
   $columns['rbpvc_views_count']  = 'Post Views Count';    
   return $columns;
}
add_filter('manage_post_posts_columns', 'rbpvc_add_custom_columns', 20, 1);
add_filter('manage_page_posts_columns', 'rbpvc_add_custom_columns', 10, 1);
add_filter('manage_product_posts_columns', 'rbpvc_add_custom_columns', 20, 1);


// Add Post Columns Value
function rbpvc_custom_columns_value( $column, $post_id ) {
   if ($column == 'rbpvc_views_count'){

    $rbpvc_post_view_count = get_post_meta(get_the_ID(), 'rbpvc_post_view', true);
    echo esc_html($rbpvc_post_view_count); 
   }
}
add_action('manage_posts_custom_column' , 'rbpvc_custom_columns_value', 10, 2);
add_action('manage_pages_custom_column' , 'rbpvc_custom_columns_value', 10, 2);

// Add Custom Post Columns Sortable
function rbpvc_sortable_column( $columns ) {
	$columns['rbpvc_views_count'] = 'rbpvc_post_view';
	return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'rbpvc_sortable_column');

// Add Custom Post Columns Filter
function rbpvc_filter_column() {
	
	$filter_value = isset( $_GET['RBPVC'] ) ? absint($_GET['RBPVC']) : '';
	$values       = array(
		'0' => __('All Posts', 'rb-post-views-column'),
		'1' => __('View Posts', 'rb-post-views-column'),
		'2' => __('No View Posts', 'rb-post-views-column'),
	);
	?>
    <select name="<?php echo esc_attr('RBPVC'); ?>">
		<?php
		foreach ( $values as $key => $value ) {
			printf( 
				"<option value='%s' %s>%s</option>", 
				$key,
				$key == $filter_value ? strip_tags("selected = 'selected'") : '',
				$value
			);
		}
		?>
    </select>
	<?php
}
add_action('restrict_manage_posts', 'rbpvc_filter_column');

// Add Custom Post Columns Filter Value
function rbpvc_filter_data($rbpvc_query) {
	if(!is_admin()){
		return;
	}
	$filter_value = isset( $_GET['RBPVC'] ) ? absint($_GET['RBPVC']) : '';

	if ( '1' == $filter_value ) {
		$rbpvc_query->set( 'meta_query', array(
			array(
				'key'     => 'rbpvc_post_view',
				'compare' => 'EXISTS'
			)
		) );
	} else if ( '2' == $filter_value ) {
		$rbpvc_query->set( 'meta_query', array(
			array(
				'key'     => 'rbpvc_post_view',
				'compare' => 'NOT EXISTS'
			)
		) );
	}

	$rbpvc_orderby = $rbpvc_query->get('orderby');
	if('rbpvc_post_view' === $rbpvc_orderby){
		$rbpvc_query->set('meta_key','rbpvc_post_view');
		$rbpvc_query->set('orberby','meta_value_num');
	}

}
add_action('pre_get_posts', 'rbpvc_filter_data');