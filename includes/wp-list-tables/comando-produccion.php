<?php 
if ( !defined('ABSPATH') )
    die ( 'No direct script access allowed' );


class ComandoProduccion_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Comando Produccion', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Comando Produccion', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );
	}
	/**
	 * Retrieve comandoProduccion data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_comandoProduccion( $per_page = 5, $page_number = 1, $filter_cat='', $filter_dia='', $filter_periodo='' ) {
		/*_print('get_comandoProduccion');*/
		$args = array(
						'post_type'   	=>	'orden',
						'numberposts'	=>	( $page_number - 1 ),
						'meta_query'	=>	array(
												array(
													'key'   => '_esc_orden_status',
													'value' => 'aprobado',
												)
											)
					);
		$args	=	_esc_getArgsPeriodo($args, $filter_periodo);
		$ordenes = get_posts( $args );
		$resumen	=	array();
		foreach($ordenes as $key=>$orden){
			$pedido			=	get_post_meta($orden->ID, '_esc_orden_args', true);		
			if(!empty($filter_dia))
				$pedido	=	array($pedido[$filter_dia]);

			foreach($pedido as $dia=>$data){
				/*_print($data);*/
				foreach($data['platos'] as $cat=>$plato){
					foreach($plato as $cat=>$alimento){
						if($cat!='tipo' && !empty($alimento['alimento_id'])){
							if($cat==$filter_cat || empty($filter_cat )){
								/*_print($alimento);*/
								/*$resumen[$alimento['alimento_id']]	=	$resumen[$alimento['alimento_id']] + $alimento['dieta'];*/
								$count	=	$resumen[$alimento['alimento_id']]['count'];
								$count	+=	$alimento['dieta'];


								$resumen[$alimento['alimento_id']]	=	array(
																			'count'	=>	$count,
																			'cat'	=>	$cat,
																			'dia'	=>	$dia,
																			'dieta'	=>	$alimento['dieta']
																		);
							}							
						}
					}
				}
			}
		}
		if(count($resumen)==0)
			return array();

		$result	=	array();
		$ids	=	implode(',', array_keys($resumen));
	   $alimentos = get_posts( array(
								'include'   => $ids,
								'post_type' => 'alimento',
								'orderby'   => 'post__in',
							) );
		$nro	=	1;
		foreach($alimentos	as $key=>$alimento){
			if($resumen[$alimento->ID]['count']==0)
				continue;
			
			$row	=	array();
			/*$row['nro']	=	($key+1);*/
			$row['nro']		=	$nro;
			/*$row['title']	=	$alimento->ID . ': ' . $alimento->post_title;*/
			$row['title']	=	$alimento->post_title;
			$row['porciones']	=	$resumen[$alimento->ID]['count'];
			$result[]	=	$row;
			$nro++;
		}
		global $_esc_result_count;
		$_esc_result_count	=	count($result);
		return $result;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_customer( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}comandoProduccion",
			[ 'ID' => $id ],
			[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $_esc_result_count;
		return $_esc_result_count;
		
		/*_print('record_count');*/
		global $wpdb;
		if($wpdb->num_rows==0)
			return 0;

		/*_print($wpdb);*/
/*
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}posts where post_type='orden' and post_status='publish'";

		return $wpdb->get_var( $sql );
*/
		
		$args = array(
						'post_type'   	=>	'orden',
						'numberposts'	=>	- 1,
						'meta_query'	=>	array(
												array(
													'key'   => '_esc_orden_status',
													'value' => 'aprobado',
												)
											)
					);
		$result = get_posts( $args );
		/*_print(count($result));*/
		return count($result);
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No hay datos disponibles.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {$item	=	(array)$item;/**/
		switch ( $column_name ) {
			case 'nro':
			case 'title':
			case 'porciones':
				return $item[ $column_name ];
				break;
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {$item	=	(array)$item;
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['ID']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {$item	=	(array)$item;

		$delete_nonce = wp_create_nonce( 'sp_delete_customer' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
			'delete' => sprintf( '<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['ID'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			/*'cb'      => '<input type="checkbox" />',*/
			'nro'    => __( '#', 'sp' ),
			'title'    => __( 'Plato', 'sp' ),
			'porciones'    => __( 'Porciones', 'sp' )
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'name' => array( 'name', true ),
			'city' => array( 'city', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [];
		return $actions;
	}

public function extra_tablenav( $which ) {
		if($which=='top'){
			$filter_categoria	=	isset( $_REQUEST['categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['categoria_alimento'] ) ) : '';
			$filter_dia			=	isset( $_REQUEST['filter_dia'] ) ? wp_unslash( trim( $_REQUEST['filter_dia'] ) ) : '';
			$filter_periodo		=	isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';
		
			$filter	=	'<div class="direccion-entrega">';
			$filter	.=		'<button type="button" id="exportar-excel" class="button">Exportar</button>';
			$filter	.=	'</div>';
			$filter	.=	'<script>';
			$filter	.=	'jQuery(document).ready(function(){';
			$filter	.=	'jQuery("#exportar-excel").on("click", function(){';
			$filter	.=	'window.location= ajaxurl + "?action=export';
			$filter	.=	'&categoria_alimento=' . $filter_categoria;
			$filter	.=	'&dia=' . $filter_dia;
			$filter	.=	'&periodo=' . $filter_periodo;
			$filter	.=	'"';
			$filter	.=	'});';
			$filter	.=	'});';
			$filter	.=	'</script>';
			echo $filter;		
		}			
	}
	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$user_search_key = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$categoria_alimento = isset( $_REQUEST['categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['categoria_alimento'] ) ) : '';
		$filter_dia = isset( $_REQUEST['filter_dia'] ) ? wp_unslash( trim( $_REQUEST['filter_dia'] ) ) : '';
		$filter_periodo = isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';
		/*_print($_REQUEST);*/
		$this->_column_headers = $this->get_column_info();
		$items = self::get_comandoProduccion( $per_page, $current_page, $categoria_alimento, $filter_dia, $filter_periodo );

		/** Process bulk action */
		$this->process_bulk_action();
		
		// filter the data in case of a search.
		if( $user_search_key ) {
			$table_data = $this->filter_table_data( $table_data, $user_search_key );
		}
		

		$per_page     = 1000;/*$this->get_items_per_page( 'comandoProduccion_per_page', 5 );*/
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		/*$this->items = self::get_comandoProduccion( $per_page, $current_page, $categoria_alimento, $dia, $filter_periodo );*/
		$this->items = $items;
	}

	public function filter_box( $text, $input_id ) {
		global $wpdb;
		/*_print('filter_box');
		_print($wpdb);*/
/*		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;*/
		
		$args = array(
				   'taxonomy' => 'categoria_alimento',
				   'hide_empty'   => 0,
				);
		$categorias_plato = get_categories($args);
		$_aCategorias	=	array();
		
		$filter_cat = isset( $_REQUEST['categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['categoria_alimento'] ) ) : '';
		$filter_dia = isset( $_REQUEST['filter_dia'] ) ? wp_unslash( trim( $_REQUEST['filter_dia'] ) ) : '';
		$filter_periodo = isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';

/*		$html	.=	_esc_filterPeriodos( $filter_periodo );*/
		$args = array (
				'actual'	=>	'Siguiente',
				'anterior'	=>	'Actual'
			);

		$html	.=	_esc_filterPeriodos( $filter_periodo, $args );
		$html	.=	_esc_filterDia($filter_dia);
/*
		$html	.=	'<p class="search-box">';
		$html	.=		'<label for="dia">D&iacute;a de entrega</label>';
		$html	.=		'<select name="dia" onchange="this.form.submit()">';
		$html	.=			'<option value="">Todos</option>';
		$html	.=			'<option value="lunes"' . ($filter_dia=='lunes'? ' selected="selected"':''  ). '>Lunes</option>';
		$html	.=			'<option value="miercoles"' . ($filter_dia=='miercoles'? ' selected="selected"':''  ). '>Miercoles</option>';
		$html	.=		'</select>';
		$html	.=	'</p>';
*/
		$html	.=	'<p class="search-box">';
		$html	.=		'<label for="categoria_alimento">Categor&iacute;a</label>';
		$html	.=		'<select name="categoria_alimento" onchange="this.form.submit()">';
		$html	.=			'<option value="">Todos</option>';
		foreach($categorias_plato as $key=>$taxonomy){
			$selected	=	'';
			if($taxonomy->slug==$filter_cat)
				$selected	=	' selected="selected"';
			$html	.=	'<option value="' . $taxonomy->slug .'"' . $selected . '>' . $taxonomy->name . '</option>';
		}
		$html	.=		'</select>';
		$html	.=	'</p>';
		echo $html;

/*?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
	<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
	<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
</p>
<?php 
*/
	}
	public function filter_table_data( $table_data, $search_key ) {
		$filtered_table_data = array_values( array_filter( $table_data, function( $row ) use( $search_key ) {
			foreach( $row as $row_val ) {
				if( stripos( $row_val, $search_key ) !== false ) {
					return true;
				}				
			}			
		} ) );
		
		return $filtered_table_data;
		
	}
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_customer' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_customer( absint( $_GET['customer'] ) );

		                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		                // add_query_arg() return the current url
		                wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_customer( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_redirect( esc_url_raw(add_query_arg()) );
			exit;
		}
	}

}
