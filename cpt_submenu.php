<?php
/*
 * Plugin Name: CPT Submenu
 * Plugin URI: https://github.com/djnolder/cpt_submenu
 * Description: Simply creates submenu in a nav menu pulled from Post Types.
 * Version: 1.0
 * Author: DJ Nolder
 * Author URI: https://github.com/djnolder
 */

define('CPTS_PATH', plugin_dir_path(__FILE__));
define('CPTS_URL', plugin_dir_url(__FILE__));

class cpt_submenu {
	public $cpts;

/**
 * Initialize the class by perparing hooks
 */
	function __construct() {
		add_action( 'init', array($this, 'set_cpt_data'), 10, 4);

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'wp_nav_menu_item_custom_fields', array($this, 'wp_nav_menu_item_custom_fields'), 10, 4);

		add_action( 'wp_update_nav_menu_item', array($this, 'update_nav_menu_item_fields' ), 10, 4);

		// we don't want to actually add menu items to the admin
		if (!is_admin()) {
			add_filter('wp_get_nav_menu_items', array($this, 'cpt_submenu_posts' ), null, 3);
		}

	}

/**
 * Hook for wp action admin_enqueue_scripts that 
 * prepares css and js files for the admin interface
 * 
 * @param  string $hook The current admin page.
 */
	function admin_enqueue_scripts($hook) {
		if ($hook != 'nav-menus.php') {
			return;
		}
   
		wp_enqueue_style( 'cpt_submenu_style', CPTS_URL . '/css/style.css' );
		wp_enqueue_script( 'cpt_submenu_script', CPTS_URL . '/js/script.js', array('jquery'), false, true );

		wp_localize_script( 'cpt_submenu_script', 'cpts', $this->cpts );
	}

/**
 * Hook for wp action wp_nav_menu_item_custom_fields
 * that allows us to inject our custom settings
 * for the nav item
 * 
 * @param  integer $id The id of the menu item
 * @param  object $item The menu item object, refernce only
 * @param  integer $depth Current level of the menu
 * @param  array $args Origional arguments used to create the menu
 */
	function wp_nav_menu_item_custom_fields($id, $item, $depth, $args) {
		if (!$data = get_metadata('post', $id, '_menu_item_cpt_submenu', true)) {
			$data= ['enabled' => false];
		}

		include(CPTS_PATH . 'templates/nav_menu.php');
	}

/**
 * Function used to build the data array used by the
 * admin for picking post type, taxonomy and terms
 */
	function set_cpt_data() {
		$this->cpts = array();
		$post_types = get_post_types(array('public' => true, '_builtin' => false), 'object');
		//var_dump($post_types);
		foreach ($post_types as $post_tag => $post_type) {
			$this->cpts[$post_tag]['name'] = $post_type->label;
		}
		$this->cpts['post']['name'] = 'Posts';
		$this->cpts['page']['name'] = 'Pages';

		foreach ($this->cpts as $name => &$data) {
			$taxes = get_object_taxonomies( $name, 'object' );
			foreach ($taxes as $tax_tag => $tax) {
				$data['taxes'][$tax_tag]['name'] = $tax->label;
				$terms = get_terms([
    				'taxonomy' => $tax->name,
    				'hide_empty' => false,
				]);
				foreach ($terms as $term) {
					$data['taxes'][$tax_tag]['terms'][$term->slug]['name'] = $term->name;
				}
			}
		}
	}

/**
 * Hook for wp_update_nav_menu_item that updates the cpt_submenu options per menu item
 * 
 * @param  integer $menu_id - the id of the menu
 * @param  integer $menu_item_db_id - the id used to store the menu details in the database
 * @param  array $atgs - arguments used to build the menu
 */
	function update_nav_menu_item_fields($menu_id, $menu_item_db_id, $args) {
		global $wpdb;

		$ids = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->prefix}term_relationships WHERE term_taxonomy_id = %d", $menu_id ) );

		$post_types = $_POST['cpt_submenu_post_type'];
		$taxonomies = $_POST['cpt_submenu_taxonomy'];
		$terms = $_POST['cpt_submenu_term'];
		$orderbys = $_POST['cpt_submenu_orderby'];
		$orders = $_POST['cpt_submenu_order'];

		foreach ($ids as $id) {
			if ($post_types[$id]) {
				// update post meta
				$data = array(
					'enabled' => 1,
					'post_type' => $post_types[$id],
					'taxonomy' => $taxonomies[$id],
					'term' => $terms[$id],
					'orderby' => $orderbys[$id],
					'order' => $orders[$id],
				);
				update_post_meta($id, '_menu_item_cpt_submenu', $data);
			}else {
				// delete post meta
				delete_post_meta($id, '_menu_item_cpt_submenu');
			}
		}
	}

/**
 * Hook for wp_get_nav_menu_items that adds our new menu items into the items array
 * 
 * @param  array $items - list of menu items
 * @param  object $menu - the menu object
 * @param  array $args - arguments used to build the menu
 * 
 * @return array $items - a modified version of @param $items
 */
	function cpt_submenu_posts($items, $menu, $args) {
		$order = 1000000;
		
		foreach ($items as $item) {
			$data = get_metadata('post', $item->ID, '_menu_item_cpt_submenu', true);
			if ($data['enabled']) {
				$post_args = [
					'post_type' => $data['post_type'],
					'numberposts' => -1,
					'orderby' => $data['orderby'],
					'order' => $data['order']
				];
				if ($data['taxonomy'] && $data['term']) {
					$post_args['tax_query'] = [
						[
							'taxonomy' => $data['taxonomy'],
	                		'field' => 'slug',
	                		'terms' => $data['term'],
	                		'include_children' => false
						]
					];
				}
//var_dump($post_args);
				if ($posts = get_posts($post_args)) {
					foreach ($posts as $post) {
						$order++;
						$old_post_id = $post->ID;

						$post->ID = 1000000 + $order;
						$post->post_type = 'nav_menu_item';
						$post->menu_order = $order;
						$post->menu_item_parent = $item->ID;
						$post->object_id = $old_post_id;
						$post->object = $data['post_type'];
						$post->type = "post_type";
						$post->type_label = get_post_type_object($data['post_type'])->labels->singular_name;
						$post->url = get_post_permalink($old_post_id);
						$post->title = $post->title?$post->title:$post->post_title;
						$post->attr_title = '';
						$post->description = '';
						$post->classes = array('', 'menu-item', 'menu-item-type-post_type', 'menu-item-object-'.$data['post_type']);
						if (get_the_ID() == $old_post_id) {
							$post->classes[] = 'current-menu-item';
						}
						$post->xfn = '';
						$post->current = (get_the_ID() == $old_post_id); // are we viewing the CPT?
						$post->current_item_ancestor = false;
						$post->current_item_parent = false;

						$items[] = $post;
					}
				}
			}

		}
		return $items;
	}

}

new cpt_submenu();
