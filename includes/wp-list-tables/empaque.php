<?php 
if ( !defined('ABSPATH') )
    die ( 'No direct script access allowed' );

class Empaque_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Empaque', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Empaques', 'sp' ), //plural name of the listed records
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
	public static function get_empaques( $per_page = 5, $page_number = 1, $filter_cat='', $filter_dia='', $filter_periodo='' ) {
		$args = array(
				   'taxonomy' => 'categoria_alimento',
				   'hide_empty'   => 0,
				);
		$categorias_plato = get_categories($args);
		$categorias	=	array();
		foreach($categorias_plato as $key=>$taxonomy)
			$categorias[$taxonomy->slug]	=	$taxonomy->name;

		$args	=	array(
						'post_type'   	=>	'orden',
						'numberposts'	=>	-1,
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
		$empaques	=	array();
		foreach($ordenes as $key=>$orden){
			$pedido		=	get_post_meta($orden->ID, '_esc_orden_args', true);
			$cliente_id	=	get_post_meta($orden->ID, '_esc_orden_cliente_id', true);
			$cliente	=	 _esc_getFullnameOfCliente($cliente_id);
			if(!empty($filter_dia))
				$pedido	=	array($filter_dia=>$pedido[$filter_dia]);

			$paquetes=	array();
			foreach($pedido as $dia=>$data){				
				$__platos	=	array();
				foreach($data['platos'] as $_key=>$plato){
					$__plato	=	array();
					foreach($plato as $cat=>$alimento){
						if($cat!='tipo' && !empty($alimento['alimento_id'])){
							if($cat==$filter_cat || empty($filter_cat )){
								
								$_alimento	=	get_post( $alimento['alimento_id'] );
								$__plato[]	=	array(
													'cat_nombre'	=>	$categorias[$cat],
													'cat'	=>	$cat,
													'dieta'	=>	$alimento['dieta'],
													'alimento_id'	=>	$alimento['alimento_id'],
													'alimento_nombre'	=>	$_alimento->post_title,
												);
							}
						}
					}
					$__platos[$plato['tipo']][$_key]	=	$__plato;
				}
				$paquetes[$dia]	=	$__platos;
			}
			$empaques[]	=	array(
									'paquetes'	=>	$paquetes,
									'cliente'	=>	$cliente,
									'orden'	=>	$orden->ID,
									);
		}
		$ids	=	implode(',', array_keys($resumen));
		$alimentos = get_posts( array(
									'include'   => $ids,
									'post_type' => 'alimento',
									'orderby'   => 'post__in',
								) );
		$result	=	array();
		foreach($empaques as $key=>$empaque){
			$row	=	array();
			$row['empaques']	=	$empaque;
			$result[]	=	$row;
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
		
		global $wpdb;
/*/
		_print($wpdb);

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
		_e( 'No comandoProduccion avaliable.', 'sp' );
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
			case 'empaques':
				$data	=	$item[ $column_name ];
				
				$empaquetados	=	get_post_meta($data['orden'], '_esc_orden_empaquetado_args', true);
				$html	=	'<h3 class="cliente">' . $data['cliente'] . '</h3>';
				$html	.=	'<table class="wp-list-table empaques">';
				foreach($data['paquetes'] as $dia=>$____plato){
					$html_plato	=	'';
					foreach($____plato as $tipo=>$empaque){
						foreach($empaque as $__key=>$platos){
							$empaquetado	=	'';
							$checked	=	'';
							if(in_array($__key, $empaquetados)){
								$checked	=	' checked="checked"';
								$empaquetado	=	' empaquetado';
							}
							$html_plato	.=	'<ul class="platos' . $empaquetado . '">';
							$html_plato	.=		'<li>';
							$html_plato	.=		'<label>';
							$html_plato	.=			'<input type="checkbox" data-orden="' . $data['orden'] . '" class="toggle-estado-empaquetado" value="' . $__key . '"' . $checked . '>';
							$html_plato	.=			'<span></span>';
							$html_plato	.=		'</label>';
							$html_plato	.=		'</li>';
							$html_plato	.=		'<li>';
							$html_plato	.=			'<img width="25" src="' . ESCHB_URL . '/assets/images/icons/50/calendar-' . $dia . '.png" class="img-empaquetado">';
							$html_plato	.=			'<br>';
							$html_plato	.=			'<img width="25" src="' . ESCHB_URL . '/assets/images/icons/50/tiempo-' . $tipo . '.png" class="img-empaquetado">';
							$html_plato	.=		'</li>';
							foreach($platos as $ekey=>$plato){
								$html_plato	.=	'<li class="plato">';
								$html_plato	.=		'<div class="categoria">' . $plato['cat_nombre'] . '</div>';
								$html_plato	.=		'<div class="dieta">' . $plato['dieta'] . '</div>';
								$html_plato	.=		'<div class="alimento">' . $plato['alimento_nombre'] . '</div>';
								$html_plato	.=	'</li>';
							}
							$html_plato	.=	'</ul>';
						}
					}
					$html	.=	'<tr>';
					$html	.=		'<td class="actions">' . $dia . '</td>';
					$html	.=		'<td>';
					$html	.=	$html_plato;
					$html	.=		'</td>';
					$html	.=	'</tr>';					
				}
				$html	.=	'</table>';
				return $html;
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
			'empaques'    => __( 'empaques', 'sp' )
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


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$user_search_key	=	isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$categoria_alimento =	isset( $_REQUEST['categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['categoria_alimento'] ) ) : '';
		$filter_dia			=	isset( $_REQUEST['filter_dia'] ) ? wp_unslash( trim( $_REQUEST['filter_dia'] ) ) : '';
		$filter_periodo		=	isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';
		$this->_column_headers = $this->get_column_info();
		/** Process bulk action */
		$this->process_bulk_action();

		// filter the data in case of a search.
		if( $user_search_key ) {
			$table_data = $this->filter_table_data( $table_data, $user_search_key );
		}

		$per_page     = $this->get_items_per_page( 'comandoProduccion_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_empaques( $per_page, $current_page, $categoria_alimento, $filter_dia, $filter_periodo );
	}

	public function filter_box( $text, $input_id ) {
/*		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;*/
		
		$args = array(
				   'taxonomy' => 'categoria_alimento',
				   'hide_empty'   => 0,
				);
		$categorias_plato = get_categories($args);
		$_aCategorias	=	array();
		
		$filter_cat 	=	isset( $_REQUEST['categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['categoria_alimento'] ) ) : '';
		$filter_dia		=	isset( $_REQUEST['filter_dia'] ) ? wp_unslash( trim( $_REQUEST['filter_dia'] ) ) : '';
		$filter_periodo =	isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';

		$html	=	'';
		$html	.=	_esc_filterPeriodos( $filter_periodo );
		$html	.=	_esc_filterDia($filter_dia);
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
