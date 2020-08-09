<?php	
if ( !defined('ABSPATH') )
    die ( 'No direct script access allowed' ); 
 
add_action( 'wp_ajax_nopriv_get_ubicaciones', '_esc_get_ubicaciones' );
add_action( 'wp_ajax_get_ubicaciones', '_esc_get_ubicaciones' );
function _esc_get_ubicaciones(){
	/*check_ajax_referer( 'get_data', 'security' );*/
	global $wpdb;
	$sql	=	"SELECT * FROM {$wpdb->prefix}ubicaciones";
	$ubicaciones =	$wpdb->get_results( $sql );
	/*_print($ubicaciones);*/
	$return['data']		=	$ubicaciones;
	$return['status']	=	'finished';
	/*_print($return);*/
	echo json_encode( $return );
	wp_die();
}
/*add_action( 'wp_ajax_nopriv_get_data', '_esc_get_data' );*/
add_action( 'wp_ajax_get_data', '_esc_get_data' );
function _esc_get_data(){
	date_default_timezone_set('America/Costa_Rica');
	/*check_ajax_referer( 'get_data', 'security' );*/
	if(isset($_REQUEST['cliente_id']) && !empty($_REQUEST['cliente_id'])){
		$cliente_id	=	$_REQUEST['cliente_id'];
		$cliente	=	get_post($cliente_id);
		$user_id	=	get_post_meta($cliente->ID, '_esc_cliente_user_id', true);
	}else{
		$user_id		=	get_current_user_id();
		$args = array(
			'post_type'   	=>	'cliente',
			'post_status'  	=>	'publish',
			'numberposts'	=>	1,
			'meta_query'	=>	array(
									array(
										'key'   => '_esc_cliente_user_id',
										'value' => $user_id,
									)
								)
		);
		$cliente_post	=	get_posts( $args );
		if(count($cliente_post)>0)
			$cliente	=	$cliente_post[0];
	}
	$custom_par_impar_week	=	'';
	if(isset($_REQUEST['week']) && !empty($_REQUEST['week']))
		$custom_par_impar_week	=	$_REQUEST['week'];
	$_alimentos		=	_esc_getAlimentos(false, $custom_par_impar_week);
	$_categorias	=	_esc_getCategorias();
	/*$_clienteData	=	_esc_getClienteData( $cliente_id );*/
	$_clienteData	=	_esc_getClienteData( $cliente->ID );
	$_clienteData['user_id']	=	$user_id;
	global $_parImpar;
	$return['data'] = array(
							'categorias'=>	$_categorias,
							'alimentos'	=>	$_alimentos,
							'cliente'	=>	$_clienteData,
							'semana'	=>	$_parImpar,
							/*'current_DateTime'	=>	date('l jS \of F Y h:i:s A')*/
						);
	$return['status'] = 'finished';
	/*_print($return);*/
	echo json_encode( $return );
	wp_die();
}
/*add_action( 'wp_ajax_import', '_esc_ajax_import' );*/
function _esc_ajax_import(){
	/*check_ajax_referer( 'get_data', 'security' );*/	
	_esc_import();
	wp_die();
}
add_action( 'wp_ajax_export', '_esc_ajax_export' );
function _esc_ajax_export(){
	/*check_ajax_referer( 'get_data', 'security' );*/	
	$filter_categoria	=	isset( $_REQUEST['categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['categoria_alimento'] ) ) : '';
	$filter_dia			=	isset( $_REQUEST['dia'] ) ? wp_unslash( trim( $_REQUEST['dia'] ) ) : '';
	$filter_periodo		=	isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'anterior';
	_esc_export($filter_categoria, $filter_dia, $filter_periodo);
	wp_die();
}
add_action( 'wp_ajax_set_plato_empaquetado', '_esc_set_plato_empaquetado' );
function _esc_set_plato_empaquetado(){
	$plato_id	=	$_REQUEST['plato_id'];
	$empaquetados	=	get_post_meta($_REQUEST['orden_id'], '_esc_orden_empaquetado_args', true);
	if(!$empaquetados)
		$empaquetados	=	array();
	$quitar	=	!isset($_REQUEST['status']);
	if(in_array($plato_id, $empaquetados)){
		if($quitar){
			$index	=	array_search($plato_id, $empaquetados);
			unset($empaquetados[$index]);
		}
	}else{
		$empaquetados[]	=	$plato_id . '';
	}
	update_post_meta($_REQUEST['orden_id'], '_esc_orden_empaquetado_args', $empaquetados);
	$return['action'] = $quitar? 'remove':'add';
	$return['status'] = 'finished';
	echo json_encode( $return );
	wp_die();	
}
add_action( 'wp_ajax_update_direccion', '_esc_update_direccion' );
function _esc_update_direccion(){
/*	_print($_REQUEST);*/
	/*check_ajax_referer( 'get_data', 'security' );*/
	$user_id		=	get_current_user_id();
	$_dia				=	$_REQUEST['dia'];
	$direccion_posted	=	$_REQUEST['direccion'];
	$args = array(
		'post_type'   	=>	'cliente',
		'post_status'  	=>	'publish',
		'numberposts'	=>	1,
		'meta_query'	=>	array(
								array(
									'key'   => '_esc_cliente_user_id',
									'value' => $user_id,
								)
							)
	);		
	$cliente_post	=	get_posts( $args );
	if(count($cliente_post)>0)
		$cliente	=	$cliente_post[0];
	$_direccion			=	get_post_meta($cliente->ID, '_esc_cliente_direccion', true);
	/*_print($_direccion);*/
	$_direccion[$_dia]	=	$direccion_posted;
	/*_print($_direccion);*/
	update_post_meta($cliente->ID, '_esc_cliente_direccion', $_direccion);
	$return['detalles'] = $direccion_posted['detalles'];
	$return['status'] = 'finished';
	echo json_encode( $return );
	wp_die();
}
add_action( 'wp_ajax_get_box_plato', '_esc_ajax_getBoxPlato' );
function _esc_ajax_getBoxPlato(){
	$dia	=	$_REQUEST['dia'];
	$cliente=	$_REQUEST['cliente'];
	$source=	isset($_REQUEST['source'])? $_REQUEST['source']:'backend';
	$box	=	_esc_boxPlato($dia, $cliente, $source);
	$return['html']	=	$box;
	$return['status']	=	'finished';
	echo json_encode( $return );
	wp_die();
}
add_action( 'wp_ajax_get_box_dieta', '_esc_ajax_getBoxDietaCliente' );
function _esc_ajax_getBoxDietaCliente(){
	$cliente=	$_REQUEST['cliente'];
	$_esc_cliente_dieta	=	get_post_meta($cliente, '_esc_cliente_dieta', true);
	$box	=	_esc_form_dieta($_esc_cliente_dieta, false);
	$return['html']	=	$box;
	$return['status']	=	'finished';
	echo json_encode( $return );
	wp_die();
}
add_action( 'wp_ajax_update_dieta', '_esc_ajax_setBoxDietaCliente' );
function _esc_ajax_setBoxDietaCliente(){
	$cliente=	$_REQUEST['cliente'];
	$dieta	=	$_REQUEST['dieta'];
	/*_print($_REQUEST);exit;*/
	update_post_meta($cliente, '_esc_cliente_dieta', $dieta);
	$_esc_cliente_dieta	=	get_post_meta($cliente, '_esc_cliente_dieta', true);
	$return['dieta']	=	$dieta;
	$return['with_dieta']	=	_esc_clienteTieneDieta($cliente);
	$return['status']	=	'finished';
	echo json_encode( $return );
	wp_die();
}
add_action( 'wp_ajax_get_dieta', '_esc_ajax_getDietaCliente' );
function _esc_ajax_getDietaCliente(){
	$cliente=	$_REQUEST['cliente'];
	$dieta	=	get_post_meta($cliente, '_esc_cliente_dieta', true);
	$return['dieta']	=	$dieta;
	$return['with_dieta']	=	_esc_clienteTieneDieta($cliente);
	$return['status']	=	'finished';
	echo json_encode( $return );
	wp_die();
}

add_action('wp_ajax_se_lookup', 'se_lookup');
add_action('wp_ajax_nopriv_se_lookup', 'se_lookup');
function se_lookup() {
    global $wpdb;

    $search = like_escape($_REQUEST['q']);
	
	$args = array(
		'post_type'   	=>	'cliente',
		'post_status'  	=>	'publish',
		'numberposts'	=>	-1,
		'meta_query'	=>	array(
								array(
									'key'   => '_esc_cliente_codigo_alianza',
									'value' => $search,
									'compare' => 'LIKE'
								)
							)
	);		
	$clientes	=	get_posts( $args );
	if(count($clientes)>0){
		foreach($clientes as $cliente){
			$meta = get_post_meta($cliente->ID, '_esc_cliente_codigo_alianza', true);			
			$aCodigoAlianza[]	=	$meta;
		}
		$aCodigoAlianza	=	array_unique($aCodigoAlianza);
		foreach($aCodigoAlianza as $codigoAlianza){
			echo $codigoAlianza . "\n";
		}
	}
    die();
}
/*add_action( 'wp_ajax_change_par_impar', '_esc_ajax_change_par_impar' );*/
//add_action( 'wp_ajax_nopriv_change_par_impar', '_esc_ajax_change_par_impar' );
function _esc_ajax_change_par_impar(){
	_esc_changeWeekParImpar();
	wp_die();
}
