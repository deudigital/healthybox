<?php

/*
Plugin Name: Healthy Box
Plugin URI: https://deudigital.com/projects/healthy-box/
Description: Custom plugin for manage platos.
Version: 1.0
Author: Jaime Isidro
Author URI:  https://deudigital.com
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	
	require_once('wp-list-tables.php');
}

class HB_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
		add_action( 'init', array($this,'init') );
		/*add_action( 'init', array($this,'remove_menus') );*/
		/*add_filter( 'menu_order', array($this,'_esc_menu_order'), 999);*/
		require_once('posttypes.php');
		require_once('taxonomies.php');
		require_once('functions.php');
		require_once('shortcodes.php');
		require_once('custom-fields.php');
		/*add_action( 'admin_menu', array($this,'prefix_remove_menu_pages' ));*/
	}
	function prefix_remove_menu_pages() {
		global $menu;
		echo '<pre>' . print_r($menu) . '</pre>';
/*
	foreach ( $menu as $i => $item ) {
		if ( $menu_slug == $item[2] ) {
			unset( $menu[ $i ] );
			return $item;
		}
	}
*/
		remove_menu_page('edit-comments.php');
		remove_menu_page('upload.php'); 
		remove_menu_page('edit.php');
		remove_menu_page('edit.php?post_type=page');
		remove_menu_page('tools.php');
		// Remove any item you want
	  }
	
	function _esc_menu_order($menu_order ){
/*
Array
(
    [0] => index.php
    [1] => separator1
    [2] => edit.php
    [3] => edit.php?post_type=plato
    [4] => upload.php
    [5] => edit.php?post_type=page
    [6] => edit-comments.php
    [7] => edit.php?post_type=product
    [8] => woocommerce
    [9] => separator2
    [10] => themes.php
    [11] => plugins.php
    [12] => users.php
    [13] => tools.php
    [14] => options-general.php
    [15] => separator-last
    [16] => separator-woocommerce
    [17] => wp_healthy_box_class
)
*/
		/*echo '<pre>' . print_r($menu_order) . '</pre>';*/
		$menu_healthbox	=	array('index.php', 'wp_healthy_box_class', 'edit.php?post_type=plato', 'users.php');
		$new_menu	=	array();
		foreach($menu_order as $menu){
			if(in_array($menu,$menu_healthbox))
				$new_menu[]	=	$menu;
		}
		return $new_menu;
	}
	function remove_menus() {
		global $menu;
		$restricted = array(__('Dashboard'), __('Posts'), __('Media'), __('Links'), __('Pages'), __('Appearance'), __('Tools'), __('Users'), __('Settings'), __('Comments'), __('Plugins'));
		end ($menu);
		while (prev($menu)){
			$value = explode(' ',$menu[key($menu)][0]);
			if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
		}
	}
	public function init(){
		
		
		
		// Register Custom Post Type

			$labels = array(
				'name'                  => _x( 'Alimentos', 'Post Type General Name', 'text_domain' ),
				'singular_name'         => _x( 'Alimento', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'             => __( 'Alimentos', 'text_domain' ),
				'name_admin_bar'        => __( 'Alimentos', 'text_domain' ),
				'archives'              => __( 'Alimento Archives', 'text_domain' ),
				'attributes'            => __( 'Alimento Attributes', 'text_domain' ),
				'parent_item_colon'     => __( 'Parent Alimento:', 'text_domain' ),
				'all_items'             => __( 'Alimentos', 'text_domain' ),
				'add_new_item'          => __( 'Add New Alimento', 'text_domain' ),
				'add_new'               => __( 'Add New', 'text_domain' ),
				'new_item'              => __( 'New Alimento', 'text_domain' ),
				'edit_item'             => __( 'Edit Alimento', 'text_domain' ),
				'update_item'           => __( 'Update Alimento', 'text_domain' ),
				'view_item'             => __( 'View Alimento', 'text_domain' ),
				'view_items'            => __( 'View Alimentos', 'text_domain' ),
				'search_items'          => __( 'Search Alimento', 'text_domain' ),
				'not_found'             => __( 'Not found', 'text_domain' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
				'featured_image'        => __( 'Featured Image', 'text_domain' ),
				'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
				'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
				'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
				'insert_into_item'      => __( 'Insert into Alimento', 'text_domain' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Alimento', 'text_domain' ),
				'items_list'            => __( 'Alimentos list', 'text_domain' ),
				'items_list_navigation' => __( 'Alimentos list navigation', 'text_domain' ),
				'filter_items_list'     => __( 'Filter Alimentos list', 'text_domain' ),
			);
			$args = array(
				'label'                 => __( 'Alimento', 'text_domain' ),
				'description'           => __( 'Post Type Description', 'text_domain' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'thumbnail' ),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 5,
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'page',
/*				'taxonomies'          => array( 'tipo_alimento' ),*/
			);
			register_post_type( 'alimento', $args );

			$labels = array(
				'name'                  => _x( 'Platos', 'Post Type General Name', 'text_domain' ),
				'singular_name'         => _x( 'Plato', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'             => __( 'Platos', 'text_domain' ),
				'name_admin_bar'        => __( 'Platos', 'text_domain' ),
				'archives'              => __( 'Plato Archives', 'text_domain' ),
				'attributes'            => __( 'Plato Attributes', 'text_domain' ),
				'parent_item_colon'     => __( 'Parent Plato:', 'text_domain' ),
				'all_items'             => __( 'Platos', 'text_domain' ),
				'add_new_item'          => __( 'Add New Plato', 'text_domain' ),
				'add_new'               => __( 'Add New', 'text_domain' ),
				'new_item'              => __( 'New Plato', 'text_domain' ),
				'edit_item'             => __( 'Edit Plato', 'text_domain' ),
				'update_item'           => __( 'Update Plato', 'text_domain' ),
				'view_item'             => __( 'View Plato', 'text_domain' ),
				'view_items'            => __( 'View Platos', 'text_domain' ),
				'search_items'          => __( 'Search Plato', 'text_domain' ),
				'not_found'             => __( 'Not found', 'text_domain' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
				'featured_image'        => __( 'Featured Image', 'text_domain' ),
				'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
				'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
				'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
				'insert_into_item'      => __( 'Insert into Plato', 'text_domain' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Plato', 'text_domain' ),
				'items_list'            => __( 'Platos list', 'text_domain' ),
				'items_list_navigation' => __( 'Platos list navigation', 'text_domain' ),
				'filter_items_list'     => __( 'Filter Platos list', 'text_domain' ),
			);
			$args = array(
				'label'                 => __( 'Plato', 'text_domain' ),
				'description'           => __( 'Post Type Description', 'text_domain' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'thumbnail' ),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 5,
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'page',
/*				'taxonomies'          => array( 'tipo_plato' ),*/
			);
			/*register_post_type( 'plato', $args );*/
						// Register Custom Taxonomy
$labels = array(
				'name'                  => _x( 'Alianzas', 'Post Type General Name', 'text_domain' ),
				'singular_name'         => _x( 'Alianza', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'             => __( 'Alianzas', 'text_domain' ),
				'name_admin_bar'        => __( 'Alianzas', 'text_domain' ),
				'archives'              => __( 'Alianza Archives', 'text_domain' ),
				'attributes'            => __( 'Alianza Attributes', 'text_domain' ),
				'parent_item_colon'     => __( 'Parent Alianza:', 'text_domain' ),
				'all_items'             => __( 'Alianzas', 'text_domain' ),
				'add_new_item'          => __( 'Add New Alianza', 'text_domain' ),
				'add_new'               => __( 'Add New', 'text_domain' ),
				'new_item'              => __( 'New Alianza', 'text_domain' ),
				'edit_item'             => __( 'Edit Alianza', 'text_domain' ),
				'update_item'           => __( 'Update Alianza', 'text_domain' ),
				'view_item'             => __( 'View Alianza', 'text_domain' ),
				'view_items'            => __( 'View Alianzas', 'text_domain' ),
				'search_items'          => __( 'Search Alianza', 'text_domain' ),
				'not_found'             => __( 'Not found', 'text_domain' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
				'featured_image'        => __( 'Featured Image', 'text_domain' ),
				'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
				'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
				'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
				'insert_into_item'      => __( 'Insert into Alianza', 'text_domain' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Alianza', 'text_domain' ),
				'items_list'            => __( 'Alianzas list', 'text_domain' ),
				'items_list_navigation' => __( 'Alianzas list navigation', 'text_domain' ),
				'filter_items_list'     => __( 'Filter Alianzas list', 'text_domain' ),
			);
			$args = array(
				'label'                 => __( 'Alianza', 'text_domain' ),
				'description'           => __( 'Post Type Description', 'text_domain' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'thumbnail' ),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => 'wp_healthy_box_class',
				'menu_position'         => 5,
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'page',
/*				'taxonomies'          => array( 'tipo_Alianza' ),*/
			);
			register_post_type( 'alianza', $args );

			$labels = array(
				'name'                  => _x( 'Clientes', 'Post Type General Name', 'text_domain' ),
				'singular_name'         => _x( 'Cliente', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'             => __( 'Clientes', 'text_domain' ),
				'name_admin_bar'        => __( 'Clientes', 'text_domain' ),
				'archives'              => __( 'Cliente Archives', 'text_domain' ),
				'attributes'            => __( 'Cliente Attributes', 'text_domain' ),
				'parent_item_colon'     => __( 'Parent Cliente:', 'text_domain' ),
				'all_items'             => __( 'Clientes', 'text_domain' ),
				'add_new_item'          => __( 'Add New Cliente', 'text_domain' ),
				'add_new'               => __( 'Add New', 'text_domain' ),
				'new_item'              => __( 'New Cliente', 'text_domain' ),
				'edit_item'             => __( 'Edit Cliente', 'text_domain' ),
				'update_item'           => __( 'Update Cliente', 'text_domain' ),
				'view_item'             => __( 'View Cliente', 'text_domain' ),
				'view_items'            => __( 'View Clientes', 'text_domain' ),
				'search_items'          => __( 'Search Cliente', 'text_domain' ),
				'not_found'             => __( 'Not found', 'text_domain' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
				'featured_image'        => __( 'Featured Image', 'text_domain' ),
				'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
				'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
				'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
				'insert_into_item'      => __( 'Insert into Cliente', 'text_domain' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Cliente', 'text_domain' ),
				'items_list'            => __( 'Clientes list', 'text_domain' ),
				'items_list_navigation' => __( 'Clientes list navigation', 'text_domain' ),
				'filter_items_list'     => __( 'Filter Clientes list', 'text_domain' ),
			);
			$args = array(
				'label'                 => __( 'Cliente', 'text_domain' ),
				'description'           => __( 'Post Type Description', 'text_domain' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'thumbnail' ),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => 'wp_healthy_box_class',
				'menu_position'         => 5,
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'page',
			);
			register_post_type( 'cliente', $args );
			$labels = array(
				'name'                  => _x( 'Ordenes', 'Post Type General Name', 'text_domain' ),
				'singular_name'         => _x( 'Orden', 'Post Type Singular Name', 'text_domain' ),
				'menu_name'             => __( 'Ordenes', 'text_domain' ),
				'name_admin_bar'        => __( 'Ordenes', 'text_domain' ),
				'archives'              => __( 'Orden Archives', 'text_domain' ),
				'attributes'            => __( 'Orden Attributes', 'text_domain' ),
				'parent_item_colon'     => __( 'Parent Orden:', 'text_domain' ),
				'all_items'             => __( 'Ordenes', 'text_domain' ),
				'add_new_item'          => __( 'Add New Orden', 'text_domain' ),
				'add_new'               => __( 'Add New', 'text_domain' ),
				'new_item'              => __( 'New Orden', 'text_domain' ),
				'edit_item'             => __( 'Edit Orden', 'text_domain' ),
				'update_item'           => __( 'Update Orden', 'text_domain' ),
				'view_item'             => __( 'View Orden', 'text_domain' ),
				'view_items'            => __( 'View Ordenes', 'text_domain' ),
				'search_items'          => __( 'Search Orden', 'text_domain' ),
				'not_found'             => __( 'Not found', 'text_domain' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
				'featured_image'        => __( 'Featured Image', 'text_domain' ),
				'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
				'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
				'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
				'insert_into_item'      => __( 'Insert into Orden', 'text_domain' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Orden', 'text_domain' ),
				'items_list'            => __( 'Ordenes list', 'text_domain' ),
				'items_list_navigation' => __( 'Ordenes list navigation', 'text_domain' ),
				'filter_items_list'     => __( 'Filter Ordenes list', 'text_domain' ),
			);
			$args = array(
				'label'                 => __( 'Orden', 'text_domain' ),
				'description'           => __( 'Post Type Description', 'text_domain' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'thumbnail' ),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => 'wp_healthy_box_class',
				'menu_position'         => 5,
				'show_in_admin_bar'     => false,
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'page',
			);
			register_post_type( 'orden', $args );

			 $labels = array(
				'name'             => _x( 'Tipos de Plato', 'taxonomy general name' ),
				'singular_name'    => _x( 'Tipo de Plato', 'taxonomy singular name' ),
				'search_items'     =>  __( 'Buscar por Tipo de Plato' ),
				'all_items'        => __( 'Todos los Tipos de Plato' ),
				'parent_item'      => __( 'Tipo de Plato padre' ),
				'parent_item_colon'=> __( 'Tipo de Plato padre:' ),
				'edit_item'        => __( 'Editar Tipo de Plato' ),
				'update_item'      => __( 'Actualizar Tipo de Plato' ),
				'add_new_item'     => __( 'Añadir nuevo Tipo de Plato' ),
				'new_item_name'    => __( 'Nombre del nuevo Tipo de Plato' ),
			  );
			  
			  /* Registramos la taxonomía y la configuramos como jerárquica (al estilo de las categorías) */
			  register_taxonomy( 'tipo_plato', array( 'plato' ), array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_ui'            => true,
				'query_var'          => true,
				/*'meta_box_cb'           => false,*/
			  ));
			  

			 $labels = array(
				'name'             => _x( 'Categorias de Plato', 'taxonomy general name' ),
				'singular_name'    => _x( 'Categoria de Plato', 'taxonomy singular name' ),
				'search_items'     =>  __( 'Buscar por Categoria de Plato' ),
				'all_items'        => __( 'Todos los Categorias de Plato' ),
				'parent_item'      => __( 'Categoria de Plato padre' ),
				'parent_item_colon'=> __( 'Categoria de Plato padre:' ),
				'edit_item'        => __( 'Editar Categoria de Plato' ),
				'update_item'      => __( 'Actualizar Categoria de Plato' ),
				'add_new_item'     => __( 'Añadir nuevo Categoria de Plato' ),
				'new_item_name'    => __( 'Nombre del nuevo Categoria de Plato' ),
			  );
			  
			  /* Registramos la taxonomía y la configuramos como jerárquica (al estilo de las categorías) */
			  register_taxonomy( 'categoria_plato', array( 'plato' ), array(
				'hierarchical'       => true,
				'labels'             => $labels,
				'show_ui'            => true,
				'query_var'          => true,
				'meta_box_cb'           => false,
			  ));
  			
			add_filter( 'rwmb_meta_boxes', 'your_prefix_get_meta_box' );
	}
	function your_prefix_get_meta_box( $meta_boxes ) {
				$prefix = 'esc_';

				$meta_boxes[] = array(
					'id' => 'plato_fields',
					'title' => esc_html__( 'Fields', 'metabox-online-generator' ),
					'post_types' => array('plato' ),
					'context' => 'advanced',
					'priority' => 'default',
					'autosave' => 'false',
				);

				return $meta_boxes;
			}
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function plugin_menu() {

		$hook = add_menu_page(
			'Deudigital Healthy Box',
			'Healthy Box',
			'manage_options',
			'wp_healthy_box_class',
			[ $this, 'plugin_settings_page' ]
		);

		add_action( "load-$hook", [ $this, 'screen_option' ] );

	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2>Healthy Box</h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Customers',
			'default' => 5,
			'option'  => 'customers_per_page'
		];

		add_screen_option( $option, $args );

		$this->customers_obj = new Customers_List();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


add_action( 'plugins_loaded', function () {
	HB_Plugin::get_instance();
} );
