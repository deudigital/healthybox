<?php

class Direccion_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Direccion', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Direcciones', 'sp' ), //plural name of the listed records
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
	public static function get_direcciones( $per_page = 5, $page_number = 1, $filter_periodo='', $filter_dia='', $filter_provincia='', $filter_canton='', $filter_distrito='' ){
		/*'numberposts'	=>	( $page_number - 1 ),*/
		/*_print(func_get_args());*/
		
		$filter_ubicacion	=	true;
		if(empty($filter_provincia) && empty($filter_canton) && empty($filter_distrito) )
			$filter_ubicacion	=	false;		
		/*_print($filter_ubicacion? 'Yes':'No');*/
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
		$result	=	array();
		foreach($ordenes as $key=>$orden){
			$_esc_orden_cliente_id		=	get_post_meta($orden->ID, '_esc_orden_cliente_id', true);
			$direcciones	=	get_post_meta($_esc_orden_cliente_id, '_esc_cliente_direccion', true);			
			$cliente	=	get_post($_esc_orden_cliente_id);
			if(!empty($filter_dia))
				$direcciones	=	array($filter_dia=>$direcciones[$filter_dia]);
			
			$__direcciones	=	array();
			foreach($direcciones as $__dia=>$__direccion){
				if($filter_ubicacion){
					if($filter_provincia){
						if($filter_provincia!=$__direccion['provincia'])
							continue ;
					}
					if($filter_canton){
						if($filter_canton!=$__direccion['canton'])
							continue ;
					}
					if($filter_distrito){
						if($filter_distrito!=$__direccion['distrito'])
							continue ;
					}
				}
				$__direcciones[$__dia]	=	$__direccion;
			}
			$direccion	=	_esc_getDireccionesFormateadas($__direcciones);
			if($direccion){
				$result[]	=	array(
									'direccion'	=>	$direccion,
									'nombre'	=>	'Order #' . $orden->ID . ': ' . $cliente->post_title
								);
			}
		}
		$_result	=	array();
		foreach($result	as $key=>$value){
			$row	=	array();
			$row['nombre']		=	$value['nombre'];
			$row['direccion']	=	$value['direccion'];
			$_result[]	=	$row;
		}
		return $_result;
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
		_e( 'No hay direcciones disponibles.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {/*_print($column_name . ' * ');/*_print($item);*/
		switch ( $column_name ) {
			case 'nombre':
				return $item[$column_name];
			case 'direccion':
				$direcciones	=	$item[$column_name];
				$html	=	'';
				foreach($direcciones as $dia=>$direccion){
					$html	.=		$dia . ': ' . $direccion . '<br>';
				}
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
			/*'direcciones'    => __( 'direcciones', 'sp' ),*/
			'nombre'    => __( 'Nombre', 'sp' ),
			'direccion'    => __( 'Direccion', 'sp' )
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
		if($which=='top'){/*_print($_REQUEST);*/
			$filter_provincia	=	isset( $_REQUEST['filter_provincia'] ) ? wp_unslash( trim( $_REQUEST['filter_provincia'] ) ) : '';
			$filter_canton		=	isset( $_REQUEST['filter_canton'] ) ? wp_unslash( trim( $_REQUEST['filter_canton'] ) ) : '';
			$filter_distrito	=	isset( $_REQUEST['filter_distrito'] ) ? wp_unslash( trim( $_REQUEST['filter_distrito'] ) ) : '';
		
			$filter	=	'<div class="direccion-entrega">';
			$filter	.=		'<div class="dropdowns">';
			$filter	.=			'<div class="combo">';
			$filter	.=				'<label>Provincia</label>';
			$filter	.=				'<select name="filter_provincia" class="provincia" data-selected="' . $filter_provincia . '">';
			$filter	.=					'<option value="">---</option>';
			$filter	.=				'</select>';
			$filter	.=			'</div>';
			$filter	.=			'<div class="combo">';
			$filter	.=				'<label>Canton</label>';
			$filter	.=				'<select name="filter_canton" class="canton" data-selected="' . $filter_canton . '">';
			$filter	.=					'<option value="">---</option>';
			$filter	.=				'</select>';
			$filter	.=			'</div>';
			$filter	.=			'<div class="combo">';
			$filter	.=				'<label>Distrito</label>';
			$filter	.=				'<select name="filter_distrito" class="distrito" data-selected="' . $filter_distrito . '">';
			$filter	.=					'<option value="">---</option>';
			$filter	.=				'</select>';
			$filter	.=			'</div>';
			$filter	.=			'<div class="combo">';
			$filter	.=				'<input type="submit" name="filtrar" value="Filtrar" class="button">';
			$filter	.=			'</div>';
			$filter	.=		'</div>';
			$filter	.=	'</div>';
			echo $filter;
		
		}
			
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$user_search_key	=	isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
		$filter_periodo		=	isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';
		$dia				=	isset( $_REQUEST['dia'] ) ? wp_unslash( trim( $_REQUEST['dia'] ) ) : '';
		$filter_provincia	=	isset( $_REQUEST['filter_provincia'] ) ? wp_unslash( trim( $_REQUEST['filter_provincia'] ) ) : '';
		$filter_canton		=	isset( $_REQUEST['filter_canton'] ) ? wp_unslash( trim( $_REQUEST['filter_canton'] ) ) : '';
		$filter_distrito	=	isset( $_REQUEST['filter_distrito'] ) ? wp_unslash( trim( $_REQUEST['filter_distrito'] ) ) : '';
		$this->_column_headers = $this->get_column_info();
		/** Process bulk action */
		$this->process_bulk_action();		
		// filter the data in case of a search.
		if( $user_search_key ) {
			$table_data = $this->filter_table_data( $table_data, $user_search_key );
		}
		$per_page     	=	$this->get_items_per_page( 'comandoProduccion_per_page', 5 );
		$current_page	=	$this->get_pagenum();
		
		$this->items = self::get_direcciones( $per_page, $current_page, $filter_periodo, $dia, $filter_provincia, $filter_canton, $filter_distrito );
		
		$total_items	=	self::record_count();
		$total_items	=	count($this->items);
		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page'    => $per_page
		]);
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
		
		$filter_cat = isset( $_REQUEST['categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['categoria_alimento'] ) ) : '';
		$filter_dia = isset( $_REQUEST['dia'] ) ? wp_unslash( trim( $_REQUEST['dia'] ) ) : '';
		$filter_periodo = isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';

		$html	=	'';
		
		$html	.=	_esc_filterPeriodos( $filter_periodo );
		
		$html	.=	'<p class="search-box">';
		$html	.=		'<label for="dia">D&iacute;a de entrega</label>';
		$html	.=		'<select name="dia" onchange="this.form.submit()">';
		$html	.=			'<option value="">Todos</option>';
		$html	.=			'<option value="lunes"' . ($filter_dia=='lunes'? ' selected="selected"':''  ). '>Lunes</option>';
		$html	.=			'<option value="miercoles"' . ($filter_dia=='miercoles'? ' selected="selected"':''  ). '>Miercoles</option>';
		$html	.=		'</select>';
		$html	.=	'</p>';
		echo $html;
		
		$filter	=	'<script type="text/javascript">'. "\n";
		$filter	.=	'var _ubicaciones;'. "\n";
		$filter	.=	'var _provincias = [];'. "\n";
		$filter	.=	'var _cantones = [];'. "\n";
		$filter	.=	'var _distritos= [];'. "\n";
		$filter	.=	'	jQuery(document).ready(function($){'. "\n";
		$filter	.=	'			$.ajax({'. "\n";
		$filter	.=	'				url: ajaxurl,'. "\n";
		$filter	.=	'				data: {'. "\n";
		$filter	.=	'					action: "get_ubicaciones",'. "\n";
		$filter	.=	'					security: "' . wp_create_nonce('get_ubicaciones') . '",'. "\n";
		$filter	.=	'				},'. "\n";
		$filter	.=	'				dataType: "json"'. "\n";
		$filter	.=	'			}).done(function( response ){				'. "\n";
		$filter	.=	'				if ( \'finished\' == response.status ) {'. "\n";
		$filter	.=	'					_ubicaciones	=	response.data;'. "\n";
		$filter	.=	'					console.log(response.data);'. "\n";
		$filter	.=	'					_esc_createInfoUbicacion();'. "\n";
		$filter	.=	'					_esc_fillSelectProvincia();'. "\n";
		$filter	.=	'					_esc_setDefaultValues();'. "\n";
		$filter	.=	'				};'. "\n";
		$filter	.=	'			});'. "\n";
		$filter	.=	'		jQuery(\'.direccion-entrega select.provincia\').on(\'change\', function(){'. "\n";
		$filter	.=	'			_esc_fillSelectCanton(jQuery(this));'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'		jQuery(\'.direccion-entrega select.canton\').on(\'change\', function(){'. "\n";
		$filter	.=	'			_esc_fillSelectDistrito(jQuery(this));'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'		jQuery(\'#same-address-monday\').on(\'change\', function(){'. "\n";
		$filter	.=	'			_esc_copyFromMonday(jQuery(this));'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'	});'. "\n";
		
		
		
		$filter	.=	'	function _esc_setDefaultValues(){'. "\n";
		$filter	.=	'		jQuery(\'select.provincia\').each(function(){'. "\n";
		$filter	.=	'			_esc_fillSelectCanton(jQuery(this));'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'		jQuery(\'select.canton\').each( function(){'. "\n";
		$filter	.=	'			_esc_fillSelectDistrito(jQuery(this));'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'	}'. "\n";
		
		$filter	.=	'	function _esc_fillSelectCanton(_selectProvincia){'. "\n";
		$filter	.=	'		const provincia_id	=	_selectProvincia.val();'. "\n";
		$filter	.=	'		var _select;'. "\n";
		$filter	.=	'		var direccion_entregas	=	_selectProvincia.closest(\'.direccion-entrega\').find(\'select.canton:first\');'. "\n";
		$filter	.=	'		jQuery.each(direccion_entregas, function( index, ele ) {'. "\n";
		$filter	.=	'			var option = new Option(\'Loading...\', \'\');'. "\n";
		$filter	.=	'			_select	=	jQuery(this);'. "\n";
		$filter	.=	'			_select.html(\'\').append(option);'. "\n";
		$filter	.=	'			'. "\n";
		$filter	.=	'			const _cantones_de_provincia = _cantones.filter(x => x.codigo_provincia==provincia_id);'. "\n";
		$filter	.=	'			if(_cantones_de_provincia){'. "\n";
		$filter	.=	'				option = new Option(\'-- Seleccionar --\', \'\');'. "\n";
		$filter	.=	'				_select.html(\'\').append(option);				'. "\n";
		$filter	.=	'				jQuery.each(_cantones_de_provincia, function(key, canton){'. "\n";
		$filter	.=	'					let _value	=	canton.codigo_canton;'. "\n";
		$filter	.=	'					let _text	=	canton.codigo_provincia + \'-\'+ canton.codigo_canton + \':\'+ canton.nombre_canton;'. "\n";
		$filter	.=	'					option = new Option(_text, _value);'. "\n";
		$filter	.=	'					_select.append(option);'. "\n";
		$filter	.=	'				});'. "\n";
		$filter	.=	'				_select.val( _select.data(\'selected\') );'. "\n";
		$filter	.=	'			}else{'. "\n";
		$filter	.=	'				_select.html(\'\');'. "\n";
		$filter	.=	'			}'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'	}'. "\n";
		
		
		$filter	.=	'	function _esc_fillSelectDistrito(_selectCanton){'. "\n";
		$filter	.=	'		const canton_id	=	_selectCanton.val();'. "\n";
		$filter	.=	'		const provincia_id	=	_selectCanton.closest(\'.direccion-entrega\').find(\'select.provincia:first\').val();'. "\n";
		$filter	.=	'		var _select;'. "\n";
		$filter	.=	'		var direccion_entregas	=	_selectCanton.closest(\'.direccion-entrega\').find(\'select.distrito:first\');'. "\n";
		$filter	.=	'		jQuery.each(direccion_entregas, function( index, ele ) {'. "\n";
		$filter	.=	'			var option = new Option(\'Loading...\', \'\');'. "\n";
		$filter	.=	'			_select	=	jQuery(this);'. "\n";
		$filter	.=	'			_select.html(\'\').append(option);'. "\n";
		$filter	.=	'			'. "\n";
		$filter	.=	'			const _distritos_de_canton = _distritos.filter(x => x.codigo_provincia==provincia_id && x.codigo_canton==canton_id);'. "\n";
		$filter	.=	'			if(_distritos_de_canton){'. "\n";
		$filter	.=	'				option = new Option(\'-- Seleccionar --\', \'\');'. "\n";
		$filter	.=	'				_select.html(\'\').append(option);				'. "\n";
		$filter	.=	'				jQuery.each(_distritos_de_canton, function(key, distrito){'. "\n";
		$filter	.=	'					let _value	=	distrito.codigo_distrito;'. "\n";
		$filter	.=	'					let _text	=	distrito.codigo_provincia + \'-\'+ distrito.codigo_canton + \'-\'+ distrito.codigo_distrito + \':\'+ distrito.nombre_distrito;'. "\n";
		$filter	.=	'					option = new Option(_text, _value);'. "\n";
		$filter	.=	'					_select.append(option);'. "\n";
		$filter	.=	'				});'. "\n";
		$filter	.=	'				_select.val( _select.data(\'selected\') );'. "\n";
		$filter	.=	'			}else{'. "\n";
		$filter	.=	'				_select.html(\'\');'. "\n";
		$filter	.=	'			}'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'	}'. "\n";
		$filter	.=	'	function _esc_fillSelectProvincia(provincia_id=false){'. "\n";
		$filter	.=	'		var _select;'. "\n";
		$filter	.=	'		var direccion_entregas	=	jQuery(\'.direccion-entrega select.provincia\');'. "\n";
		$filter	.=	'		jQuery.each(direccion_entregas, function( index, ele ) {'. "\n";
		$filter	.=	'			var option = new Option(\'Loading...\', \'\');'. "\n";
		$filter	.=	'			_select	=	jQuery(this);'. "\n";
		$filter	.=	'			_select.html(\'\').append(option);'. "\n";
		$filter	.=	''. "\n";
		$filter	.=	'			if(_provincias){'. "\n";
		$filter	.=	'				option = new Option(\'-- Seleccionar --\', \'\');'. "\n";
		$filter	.=	'				_select.html(\'\').append(option);				'. "\n";
		$filter	.=	'				jQuery.each(_provincias, function(key, provincia){'. "\n";
		$filter	.=	'					let _value	=	provincia.codigo_provincia;'. "\n";
		$filter	.=	'					let _text	=	provincia.codigo_provincia + \':\'+ provincia.nombre_provincia;'. "\n";
		$filter	.=	'					option = new Option(_text, _value);'. "\n";
		$filter	.=	'					_select.append(option);'. "\n";
		$filter	.=	'				});				'. "\n";
		$filter	.=	'				_select.val( _select.data(\'selected\') );'. "\n";
		$filter	.=	'			}else{'. "\n";
		$filter	.=	'				_select.html(\'\');'. "\n";
		$filter	.=	'			}'. "\n";
		$filter	.=	'		});'. "\n";
		$filter	.=	'	}'. "\n";
		
		$filter	.=	'function _esc_createInfoUbicacion() {'. "\n";
		$filter	.=	'	var ubicacion;'. "\n";
		$filter	.=	'	var obj;'. "\n";
		$filter	.=	'	var prov;'. "\n";
		$filter	.=	'	var cant;'. "\n";
		$filter	.=	'	var dist;'. "\n";
		$filter	.=	'	if (!this._ubicaciones) {'. "\n";
		$filter	.=	'	  return;'. "\n";
		$filter	.=	'	}'. "\n";
		$filter	.=	'	for (var i in _ubicaciones) {'. "\n";
		$filter	.=	'		ubicacion = _ubicaciones[i];'. "\n";
		$filter	.=	'		var item = _provincias.find('. "\n";
		$filter	.=	'			item => item.codigo_provincia === ubicacion.codigo_provincia'. "\n";
		$filter	.=	'		);'. "\n";
		$filter	.=	'		if (!item) {'. "\n";
		$filter	.=	'			prov = new Object();'. "\n";
		$filter	.=	'			prov.codigo_provincia = ubicacion.codigo_provincia;'. "\n";
		$filter	.=	'			prov.nombre_provincia = ubicacion.nombre_provincia;'. "\n";
		$filter	.=	'			_provincias.push(prov);'. "\n";
		$filter	.=	'		}'. "\n";
		$filter	.=	'		var item = _cantones.find('. "\n";
		$filter	.=	'			item =>'. "\n";
		$filter	.=	'			  item.codigo_canton === ubicacion.codigo_canton &&'. "\n";
		$filter	.=	'			  item.codigo_provincia === ubicacion.codigo_provincia'. "\n";
		$filter	.=	'			);'. "\n";
		$filter	.=	'		if (!item) {'. "\n";
		$filter	.=	'			cant = new Object();'. "\n";
		$filter	.=	'			cant.codigo_canton = ubicacion.codigo_canton;'. "\n";
		$filter	.=	'			cant.nombre_canton = ubicacion.nombre_canton;'. "\n";
		$filter	.=	'			cant.codigo_provincia = ubicacion.codigo_provincia;'. "\n";
		$filter	.=	'			_cantones.push(cant);'. "\n";
		$filter	.=	'			}'. "\n";
		$filter	.=	'			var item = _distritos.find('. "\n";
		$filter	.=	'			item =>'. "\n";
		$filter	.=	'			  item.codigo_provincia === ubicacion.codigo_provincia &&'. "\n";
		$filter	.=	'			  item.codigo_canton === ubicacion.codigo_canton &&'. "\n";
		$filter	.=	'			  item.codigo_distrito === ubicacion.codigo_distrito'. "\n";
		$filter	.=	'			);'. "\n";
		$filter	.=	'		if (!item) {'. "\n";
		$filter	.=	'			dist = new Object();'. "\n";
		$filter	.=	'			dist.codigo_provincia = ubicacion.codigo_provincia;'. "\n";
		$filter	.=	'			dist.codigo_canton = ubicacion.codigo_canton;'. "\n";
		$filter	.=	'			dist.codigo_distrito = ubicacion.codigo_distrito;'. "\n";
		$filter	.=	'			dist.nombre_distrito = ubicacion.nombre_distrito;'. "\n";
		$filter	.=	'			_distritos.push(dist);'. "\n";
		$filter	.=	'		}'. "\n";
		$filter	.=	'	}'. "\n";
		$filter	.=	'}'. "\n";

		$filter	.=	'</script>'. "\n";
		echo $filter;
		echo '<style>';
		echo 'p.search-box{margin:5px 10px;min-width:150px}';
		echo 'p.search-box label{display:block}';
		echo 'p.search-box select{width:100%;max-width:100%}';
		echo '.combo{display:inline-block}';
		echo '.combo label{display:block}';
		echo '.combo select{min-width:100px}';
		echo '</style>';

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
