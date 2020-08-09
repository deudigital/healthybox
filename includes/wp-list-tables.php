<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	require_once('wp-list-tables.php');
}
add_filter( 'manage_edit-cliente_columns',  'clientes_add_new_columns' );
add_filter( 'manage_edit-cliente_sortable_columns', 'clientes_register_sortable_columns' );
add_filter( 'request', 'hits_column_orderby' );
add_action( 'manage_posts_custom_column' , 'clientes_custom_columns' );
/**
* Add new columns to the post table
*
* @param Array $columns - Current columns on the list post
*/
function clientes_add_new_columns($columns){
    $column_meta	=	array( 
							'correo' => 'Correo',
							'telefono' => 'Telefono',
							'dieta_asignada' => 'Tiene Dieta asignada?',
							'ultima_orden' => 'Ultima Orden',
						);
    $columns = array_slice( $columns, 0, 2, true ) + $column_meta + array_slice( $columns, 2, NULL, true );
	$columns['title'] = 'Cliente';
    return $columns;
}
function clientes_register_sortable_columns( $columns ) {
    $columns['correo'] = 'Correo';
    $columns['telefono'] = 'Telefono';
    $columns['dieta_asignada'] = 'Tiene Dieta asignada?';
    $columns['ultima_orden'] = 'Ultima Orden';
    return $columns;
}
//Add filter to the request to make the hits sorting process numeric, not string
function hits_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'hits' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'hits',
            'orderby' => 'meta_value_num'
        ) );
    }
    return $vars;
}
/**
* Display data in new columns
*
* @param  $column Current column
*
* @return Data for the column
*/
function clientes_custom_columns($column) {
    global $post;
    switch ( $column ) {
        case 'title':
            echo 'Cliente';
			break;
        case 'correo':
            echo get_post_meta( $post->ID, '_esc_cliente_correo', true );
			break;
        case 'telefono':
            echo get_post_meta( $post->ID, '_esc_cliente_telefono', true );
			break;
        case 'dieta_asignada':
			if(_esc_clienteTieneDieta( $post->ID ))
				echo 'Si';
			else
				echo 'no';
			break;
        case 'ultima_orden':
            //echo '__/__/____';
			$args = array(
					'post_type'   	=>	'orden',
					'post_status'  	=>	'publish',
					'numberposts'	=>	1,
					'meta_query'	=>	array(
											array(
												'key'   => '_esc_orden_cliente_id',
												'value' => $post->ID,
											)
										)
					);
			$last_orden	=	get_posts( $args );
			if($last_orden){
				$last_orden	=	$last_orden[0];
				$fecha	=	new DateTime($last_orden->post_date);
				echo $last_orden->post_title;// . '<br>' . $fecha->format('d/m/Y');
				//echo $last_orden->post_date;
			}
			break;
    }
}
add_filter( 'manage_edit-alianza_columns',  'alianza_add_new_columns' );
add_filter( 'manage_edit-alianza_sortable_columns', 'alianza_register_sortable_columns' );
/*add_filter( 'request', 'hits_column_orderby' );*/
add_action( 'manage_posts_custom_column' , 'alianza_custom_columns' );
/**
* Add new columns to the post table
*
* @param Array $columns - Current columns on the list post
*/
function alianza_add_new_columns($columns){
    $column_meta	=	array( 
							'correo' => 'Correo Electronico',
							'telefono' => 'Telefono',
							'clientes_referidos' => 'Clientes Referidos',
							/*'platos_referidos' => 'Platos Referidos',*/
						);
    $columns = array_slice( $columns, 0, 2, true ) + $column_meta + array_slice( $columns, 2, NULL, true );
	$columns['title'] = 'Alianza';
    return $columns;
}
function alianza_register_sortable_columns( $columns ) {
    $columns['correo'] = 'Correo Electronico';
    $columns['telefono'] = 'Telefono';
    $columns['clientes_referidos'] = 'Clientes Referidos';
    /*$columns['platos_referidos'] = 'Platos Referidos';*/
    return $columns;
}
//Add filter to the request to make the hits sorting process numeric, not string
/*function hits_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'hits' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'hits',
            'orderby' => 'meta_value_num'
        ) );
    }
    return $vars;
}*/
/**
* Display data in new columns
*
* @param  $column Current column
*
* @return Data for the column
*/
function alianza_custom_columns($column) {
    global $post;
    switch ( $column ) {
        case 'title':
            echo 'Cliente';
			break;
        case 'correo':
            echo get_post_meta( $post->ID, '_esc_alianza_correo', true );
			break;
        case 'telefono':
            echo get_post_meta( $post->ID, '_esc_alianza_telefono', true );
			break;
        case 'clientes_referidos':
			$_esc_cliente_codigo_alianza	=	esc_textarea(get_post_meta($post->ID, '_esc_alianza_codigo', true));
			$args = array(
						'post_type'   	=>	'cliente',
						'post_status'  	=>	'publish',
						'numberposts'	=>	-1,
						'meta_query'	=>	array(
												array(
													'key'   => '_esc_cliente_codigo_alianza',
													'value' => $_esc_cliente_codigo_alianza
												)
											)
					);
			$clientes_referidos	=	get_posts( $args );
			echo count($clientes_referidos);
			break;
        /*case 'platos_referidos':
			echo '0';
			break;*/
    }
}
add_filter( 'manage_edit-alimento_columns',  'alimentos_add_new_columns' );
add_filter( 'manage_edit-alimento_sortable_columns', 'alimentos_register_sortable_columns' );
add_filter( 'request', 'precio_column_orderby' );
add_action( 'manage_posts_custom_column' , 'alimentos_custom_columns' );
/**
* Add new columns to the post table
*
* @param Array $columns - Current columns on the list post
*/
function alimentos_add_new_columns($columns){
    $column_meta	=	array( 
							'categoria' => 'Categoria',
							'semana' => 'Semana',
							'dia' => 'Dias',
							'precio' => 'Precio'
						);
    $columns = array_slice( $columns, 0, 2, true ) + $column_meta + array_slice( $columns, 2, NULL, true );
	$columns['title'] = 'Nombre';
    return $columns;
}
/*	Sorteable Feature	*/
function alimentos_register_sortable_columns( $columns ) {
    $columns['categoria'] = 'categoria';
    $columns['semana'] = 'semana';
    $columns['dia'] = 'dia';
    $columns['precio'] = 'precio';
    return $columns;
}
//Add filter to the request to make the hits sorting process numeric, not string
function precio_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'precio' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => '_esc_alimento_precio',
            'orderby' => 'meta_value_num'
        ) );
    }
    return $vars;
}
/**
* Display data in new columns
*
* @param  $column Current column
*
* @return Data for the column
*/
function alimentos_custom_columns($column) {
    global $post;
    switch ( $column ) {
        case 'categoria':
			$_esc_alimento_categoria	=	get_post_meta($post->ID, '_esc_alimento_categoria', true);
            /*_print($_esc_alimento_categoria);*/
			$args = array(
               'taxonomy' => 'categoria_alimento',
			   'hide_empty'   => 0,
           );
		   $categorias_plato = get_categories($args);
		   $_aCategorias	=	array();
		   foreach($categorias_plato as $key=>$categoria){
				$term_id = absint( $categoria->term_id );
				$_aCategorias[$categoria->slug]	=	$categoria->name;
			}
			$html	=	'';
			foreach($_esc_alimento_categoria as $key=>$categorias){
				if(count($categorias)>0){
					/*$html	.=	'<li>' . ucfirst($key) . ': ' . implode(',',$categorias) . '</li>';*/
					$html_cat	=	'';
					foreach($categorias as $cat){
						if(!empty($html_cat))
							$html_cat	.=	', ';
						$html_cat	.=	$_aCategorias[$cat];
					}
					$html	.=	'<li>' . ucfirst($key) . ': ' . $html_cat . '</li>';
				}
			}
			if(!empty($html))
				echo '<ul style="margin:0">' . $html . '</ul>';
			break;
        case 'semana':
			$_esc_alimento_semana_dias	=	get_post_meta($post->ID, '_esc_alimento_semana_dias', true);
			$output	=	array();
			foreach($_esc_alimento_semana_dias as $semana=>$dias)
				$output[]	=	ucfirst($semana);
			$output	=	implode(', ', $output);
			echo $output;
			break;
        case 'dia':
			$_esc_alimento_semana_dias	=	get_post_meta($post->ID, '_esc_alimento_semana_dias', true);
			$output	=	array();
			foreach($_esc_alimento_semana_dias as $semana=>$dias){
				$res	=	array();
				foreach($dias as $dia)
					$res[]	=	ucfirst($dia);
				//$output[ucfirst($semana)]	=	implode(', ', $res);
				$output[]	=	ucfirst($semana) . ': ' . implode(', ', $res);
			}
			$output	=	implode('<br>', $output);
			echo $output;
			break;
        /*case 'dia':
			$_esc_alimento_dias	=	get_post_meta($post->ID, '_esc_alimento_dias', true);
			$output	=	implode(', ', array_keys($_esc_alimento_dias));
            echo ucfirst($output);
			break;*/
        case 'precio':
			$_esc_alimento_precio	=	get_post_meta($post->ID, '_esc_alimento_precio', true);
            echo $_esc_alimento_precio;
			break;
    }
}
add_filter( 'manage_edit-orden_columns',  'ordens_add_new_columns' );
add_filter( 'manage_edit-orden_sortable_columns', 'ordens_register_sortable_columns' );
/*add_filter( 'request', 'hits_column_orderby' );*/
add_action( 'manage_posts_custom_column' , 'ordens_custom_columns' );
/**
* Add new columns to the post table
*
* @param Array $columns - Current columns on the list post
*/
function ordens_add_new_columns($columns){
    $column_meta	=	array( 
							'cliente' => 'Cliente',
							'lunes' => 'Entrega Lunes',
							'miercoles' => 'Entrega Miercoles',
							'monto' => 'Monto',
							'estado' => 'Estado'
						);
    $columns = array_slice( $columns, 0, 2, true ) + $column_meta + array_slice( $columns, 2, NULL, true );
	$columns['title'] = 'Nombre';
    return $columns;
}
function ordens_register_sortable_columns( $columns ) {
    $columns['cliente'] = 'Cliente';
    $columns['lunes'] = 'Entrega Lunes';
    $columns['miercoles'] = 'Entrega Miercoles';
    $columns['monto'] = 'Monto';
    $columns['estado'] = 'Estado';
    return $columns;
}
//Add filter to the request to make the hits sorting process numeric, not string
/*function hits_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'hits' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'hits',
            'orderby' => 'meta_value_num'
        ) );
    }
    return $vars;
}*/
/**
* Display data in new columns
*
* @param  $column Current column
*
* @return Data for the column
*/
function ordens_custom_columns($column) {
    global $post;
    switch ( $column ) {		
        case 'cliente':
			$_esc_orden_cliente_id		=	get_post_meta($post->ID, '_esc_orden_cliente_id', true);
			echo _esc_getFullnameOfCliente($_esc_orden_cliente_id);			
			break;
        case 'lunes':
			$_esc_orden_categoria	=	get_post_meta($post->ID, '_esc_orden_args', true);
			if(count($_esc_orden_categoria['lunes']['platos'])==0)
				break;

			$_esc_orden_cliente_id		=	get_post_meta($post->ID, '_esc_orden_cliente_id', true);
			$conDieta	=	_esc_clienteTieneDieta( $_esc_orden_cliente_id );
			$alimentos	=	_esc_getAlimentosFromOrdenArgs($_esc_orden_categoria['lunes']['platos']);
			$html	=	'';
			$html_platos	=	'';
			$num_plato	=	1;
			$aPlatos	=	array();
			$subtotal	=	0;
			
			foreach($_esc_orden_categoria['lunes']['platos'] as $pkey=>$_plato){
				$monto	=	0;
				foreach($_plato as $cat=>$plato){
					if($plato && $cat!='tipo'){
						$alimento_id	=	$plato['alimento_id'];							
						$cantidad	=	empty($alimento_id)? 0:1;							
						if($plato['dieta'])
							$cantidad	=	$plato['dieta'];
						if($plato['precios']['alimento'])
							$monto +=	$plato['precios']['alimento'] * $cantidad;
						else
							$monto +=	$plato['precios']['cat'] * $cantidad;						
					}
				}
				$subtotal	+=	$monto;
				/*$html	.=	'<li style="margin:0">Plato ' . $num_plato . '<small style="float:right">&#8353; ' . number_format($monto) . '</small></li>';*/				
				$html	.=	'<li style="margin:0">Plato ' . $num_plato;
				$html	.=	'<small style="float:right">';
				if($conDieta)
					$html	.=	'&#8353; ' . number_format($monto);
				else
					$html	.=	'N/A';
				$html	.=	'</small>';
				$html	.=	'</li>';
				$num_plato++;
			}
			$subtotal +=	$_esc_orden_categoria['lunes']['entrega']['precio'];
			/*$html	=	'<ul style="margin:0">' . $html . '</ul>';*/
			$html	.=	'<li>Entrega';
			$html	.=	'<small style="float:right">&#8353; ' . number_format($_esc_orden_categoria['lunes']['entrega']['precio']) . '</small>';
			$html	.=	'</li>';
			$html	=	'<ul style="margin:0">' . $html . '</ul>';
			$html	.=	'<hr>';
			$html	.=	'<ul>';
			/*$html	.=	'<li>Subtotal <small style="float:right">&#8353; ' . number_format($subtotal) . '</small></li>';*/
			$html	.=	'<li>Subtotal ';
			$html	.=	'<small style="float:right">';
			if($conDieta)
				$html	.=	'&#8353; ' . number_format($subtotal);
			else
				$html	.=	'N/A';
			$html	.=	'</small>';
			$html	.=	'</li>';
			$html	.=	'</ul>';
			$html	=	'<div style="font-size:12px;line-height:1em">' . $html . '</div>';
			echo $html;			
			break;
        case 'miercoles':
			$_esc_orden_categoria	=	get_post_meta($post->ID, '_esc_orden_args', true);			
			if(count($_esc_orden_categoria['miercoles']['platos'])==0)
				break;
			
			$_esc_orden_cliente_id		=	get_post_meta($post->ID, '_esc_orden_cliente_id', true);
			$conDieta	=	_esc_clienteTieneDieta( $_esc_orden_cliente_id );
			$alimentos	=	_esc_getAlimentosFromOrdenArgs($_esc_orden_categoria['miercoles']['platos']);
			$html	=	'';
			$num_plato	=	1;
			$subtotal	=	0;
			foreach($_esc_orden_categoria['miercoles']['platos'] as $pkey=>$_plato){
				$monto	=	0;
				foreach($_plato as $cat=>$plato){
					if($plato && $cat!='tipo'){
						$alimento_id	=	$plato['alimento_id'];							
						$cantidad	=	empty($alimento_id)? 0:1;							
						if($plato['dieta'])
							$cantidad	=	$plato['dieta'];
						if($plato['precios']['alimento'])
							$monto +=	$plato['precios']['alimento'] * $cantidad;
						else
							$monto +=	$plato['precios']['cat'] * $cantidad;						
					}
				}
				$subtotal	+=	$monto;
				$html	.=	'<li style="margin:0">Plato ' . $num_plato;
				$html	.=	'<small style="float:right">';
				if($conDieta)
					$html	.=	'&#8353; ' . number_format($monto);
				else
					$html	.=	'N/A';
				$html	.=	'</small>';
				$html	.=	'</li>';
				$num_plato++;
			}
			$subtotal +=	$_esc_orden_categoria['miercoles']['entrega']['precio'];
			$html	.=	'<li>Entrega';
			$html	.=	'<small style="float:right">&#8353; ' . number_format($_esc_orden_categoria['miercoles']['entrega']['precio']) . '</small>';
			$html	.=	'</li>';
			$html	=	'<ul style="margin:0">' . $html . '</ul>';
			$html	.=	'<hr>';
			$html	.=	'<ul>';
			$html	.=	'<li>Subtotal ';
			$html	.=	'<small style="float:right">';
			if($conDieta)
				$html	.=	'&#8353; ' . number_format($subtotal);
			else
				$html	.=	'N/A';
			$html	.=	'</small>';
			$html	.=	'</li>';			
			$html	.=	'</ul>';
			echo $html;	
			break;
        case 'monto': 
			$_esc_orden_cliente_id		=	get_post_meta($post->ID, '_esc_orden_cliente_id', true);
			$conDieta	=	_esc_clienteTieneDieta( $_esc_orden_cliente_id );
			if($conDieta){			
				$_esc_orden_categoria	=	get_post_meta($post->ID, '_esc_orden_args', true);
				$alimentos	=	_esc_getAlimentosFromOrdenArgs($_esc_orden_categoria['miercoles']['platos']);
				$html	=	'';
				$monto	=	0;
				foreach($_esc_orden_categoria as $dia=>$data){/*_print($data);*/
					$aPlatos	=	array();
					foreach($data['platos'] as $pkey=>$_plato){
						foreach($_plato as $cat=>$plato){/*_print($cat);*/
							if($plato && $cat!='tipo'){/*_print($plato);*/
								$alimento_id	=	$plato['alimento_id'];/*$cantidad	=	0;*/								
								$cantidad	=	empty($alimento_id)? 0:1;								
								if($plato['dieta'])
									$cantidad	=	$plato['dieta'];
								if($plato['precios']['alimento'])
									$monto +=	$plato['precios']['alimento'] * $cantidad;
								else
									$monto +=	$plato['precios']['cat'] * $cantidad;
							}
						}
					}
					$monto +=	$data['entrega']['precio'];
				}
				echo '<small>&#8353;</small> ' . number_format($monto);
			}else
				echo 'N/A';
			break;
        case 'estado':
			$estados	=	_esc_getEstadosOrder();
			$_esc_orden_status	=	get_post_meta($post->ID, '_esc_orden_status', true);
			$_esc_orden_cliente_id		=	get_post_meta($post->ID, '_esc_orden_cliente_id', true);	
			$conDieta	=	_esc_clienteTieneDieta( $_esc_orden_cliente_id );
			echo '<span data-status="' . $_esc_orden_status . '">' . $estados[$_esc_orden_status] . '</span>';
			if(!$conDieta)
				echo '<br><strong id="cliente-sin-dieta"><small>Cliente sin Dieta</small></strong>';
			break;
    }
}
//Display our custom content on the quick-edit interface, no values can be pre-populated (all done in JavaScript)
add_action('quick_edit_custom_box', 'display_quick_edit_custom', 10, 2);//output form elements for quickedit interface
function display_quick_edit_custom($column_name, $post_type){
	if($post_type!='orden')
		return ;
    /*wp_nonce_field('post_metadata', 'post_metadata_field');*/
    switch( $column_name ) :
		case 'estado': {
			global $post;
			$estados	=	_esc_getEstadosOrder();
			$_esc_orden_status	=	get_post_meta($post->ID, '_esc_orden_status', true);
			$estados	=	_esc_getEstadosOrder(); 
?>
<fieldset class="inline-edit-col-left">
	<div class="inline-edit- col">
		<div class="inline-edit-group wp-clearfix">
			<label class="alignleft">
				<span class="title">Estado</span>
				<span class="input-text-wrap">
					<select name="esc_orden_status" required>
						<option value="">---</option>
	<?php	foreach($estados as $key=>$estado)	:	
				$selected	=	'';
				if($_esc_orden_status==$key)
					$selected	=	' selected="selected"';
	?>
						<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $estado ?></option>
	<?php 	endforeach; ?>
					</select>
				</span>
			</label>
		</div>
	</div>
</fieldset>
<?php
			// you can also print Nonce here, do not do it ouside the switch() because it will be printed many times
			wp_nonce_field( 'misha_q_edit_nonce', 'misha_nonce' );
			// please note: the <fieldset> classes could be:
			// inline-edit-col-left, inline-edit-col-center, inline-edit-col-right
			// each class for each column, all columns are float:left,
			// so, if you want a left column, use clear:both element before
			// the best way to use classes here is to look in browser "inspect element" at the other fields
			// for the FIRST column only, it opens <fieldset> element, all our fields will be there
			/*echo '<fieldset class="inline-edit-col-right">
				<div class="inline-edit-col">
					<div class="inline-edit-group wp-clearfix">';
			echo '<label class="alignleft">
					<span class="title">Price</span>
					<span class="input-text-wrap"><input type="checkbox" name="price" value=""></span>
				</label>';*/
			break;
		}
		/*case 'featured': {
			echo '<label class="alignleft">
					<input type="checkbox" name="featured">
					<span class="checkbox-title">Featured product</span>
				</label>';
			// for the LAST column only - closing the fieldset element
			echo '</div></div></fieldset>';
			break;
		}*/
	endswitch;
}
add_action( 'save_post', 'misha_quick_edit_save' );
function misha_quick_edit_save( $post_id ){
	// check user capabilities
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// check nonce
	if ( !wp_verify_nonce( $_POST['misha_nonce'], 'misha_q_edit_nonce' ) ) {
		return;
	}
	// update the price
	if ( isset( $_POST['esc_orden_status'] ) ) {
		$conDieta	=	false;
		if($_POST['esc_orden_status']=='aprobado'){
			$_esc_orden_cliente_id		=	get_post_meta($post_id, '_esc_orden_cliente_id', true);	
			$conDieta	=	_esc_clienteTieneDieta( $_esc_orden_cliente_id );
		}
		if($conDieta)
			update_post_meta( $post_id, '_esc_orden_status', $_POST['esc_orden_status'] );
	}
 /*
	// update checkbox
	if ( isset( $_POST['featured'] ) ) {
		update_post_meta( $post_id, 'product_featured', 'on' );
	} else {
		update_post_meta( $post_id, 'product_featured', '' );
	}
 */
}
add_filter( 'months_dropdown_results', '_eschb_months_dropdown_results', 10, 2);
function _eschb_months_dropdown_results( $months, $post_type ){
	if(in_array($post_type, array('orden', 'alimento')))
		$months	=	false;
	return $months;
}
/*	Alimento	*/
function _esc_alimento_restrict_manage_posts_filters($post_type, $which) {
	if(!in_array($post_type, array('alimento')))
		return ;
    $filter_categoria_alimento	=	isset( $_REQUEST['filter_categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['filter_categoria_alimento'] ) ) : '';
    $filter_semana 				=	isset( $_REQUEST['filter_semana'] ) ? wp_unslash( trim( $_REQUEST['filter_semana'] ) ) : '';
    $filter_dia					=	isset( $_REQUEST['filter_dia'] ) ? wp_unslash( trim( $_REQUEST['filter_dia'] ) ) : '';
	$html	.=	_esc_filterCategoriasAlimento( $filter_categoria_alimento );
	$html	.=	_esc_filterSemana($filter_semana);
	$html	.=	_esc_filterDia($filter_dia);
	echo $html;
}
add_action('restrict_manage_posts','_esc_alimento_restrict_manage_posts_filters', 10, 2);
add_filter( "bulk_actions-edit-alimento", '_esc_bulk_actions');
add_filter( "bulk_actions-edit-orden", '_esc_bulk_actions');
function _esc_bulk_actions($_actions){
	return array();
}
function _esc_orden_restrict_manage_posts_filters($post_type, $which) {
	if(!in_array($post_type, array('orden')))
		return ;
    $filter_periodo = isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'actual';
    $filter_estado_orden = isset( $_REQUEST['orden_status'] ) ? wp_unslash( trim( $_REQUEST['orden_status'] ) ) : '';
	$html	=	_esc_filterPeriodos( $filter_periodo );
	$html	.=	_esc_filterEstadosOrden($filter_estado_orden );
	echo $html;
}
add_action('restrict_manage_posts','_esc_orden_restrict_manage_posts_filters', 10, 2);
add_action( 'parse_query', '_eschb_parse_query_orden');
function _eschb_parse_query_orden($wp_query){
	global $plugin_page, $typenow;
	if($typenow!='orden')
		return ;
	if($wp_query->query_vars['post_type'] != 'orden')
		return ;
	$filter_periodo		=	isset( $_REQUEST['periodo'] ) ? wp_unslash( trim( $_REQUEST['periodo'] ) ) : 'actual';
    $filter_estado_orden=	isset( $_REQUEST['orden_status'] ) ? wp_unslash( trim( $_REQUEST['orden_status'] ) ) : '';
	$meta_query	=	array();
	$args	=	array();
	$args	=	_esc_getArgsPeriodo($args, $filter_periodo);
	$wp_query->query_vars['date_query']	=	$args['date_query'];
	if(!empty($filter_estado_orden)){
		$_esc_orden_status	=	get_post_meta($post->ID, '_esc_orden_status', true);
		$meta_query[]	=	array(
								'key'		=>	'_esc_orden_status',
								'value'		=>	$filter_estado_orden,
							);
		$wp_query->query_vars['meta_query']	=	$meta_query;
	}
}
add_action( 'parse_query', '_eschb_parse_query');
function _eschb_parse_query($wp_query){
global $plugin_page, $typenow;
	if($typenow!='alimento')
		return ;
	if($wp_query->query_vars['post_type'] != 'alimento')
		return ;
	$filter_categoria_alimento	=	isset( $_REQUEST['filter_categoria_alimento'] ) ? wp_unslash( trim( $_REQUEST['filter_categoria_alimento'] ) ) : '';
    $filter_semana				=	isset( $_REQUEST['filter_semana'] ) ? wp_unslash( trim( $_REQUEST['filter_semana'] ) ) : '';
    $filter_dia					=	isset( $_REQUEST['filter_dia'] ) ? wp_unslash( trim( $_REQUEST['filter_dia'] ) ) : '';
	$meta_query	=	array();
	if(!empty($filter_semana)){	
		$meta_query[]	=	array(
								'key'		=>	'_esc_alimento_semana_dias',
								'value'		=>	serialize($filter_semana),
								'compare'	=>	'LIKE'
							);
	}
	if(!empty($filter_categoria_alimento)){
		$meta_query[]	=	array(
								'key'		=>	'_esc_alimento_categoria',
								'value'		=>	serialize($filter_categoria_alimento),
								'compare'	=>	'LIKE'
							);
	}
	if(!empty($filter_dia)){
		$meta_query[]	=	array(
								'key'		=>	'_esc_alimento_semana_dias',
								'value'		=>	serialize($filter_dia),
								'compare'	=>	'LIKE'
							);
	}
	$wp_query->query_vars['meta_query']	=	$meta_query;
}