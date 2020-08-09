<?php	
if ( !defined('ABSPATH') )
    die ( 'No direct script access allowed' );
function _print($data){
	echo '<pre>' . print_r($data,true) . '</pre>';
}
function _eschb_getInfoAlimentosForDietaForm(){
	$tiempos	=	_eschb_getTiemposComida();
	$categorias	=	_esc_getCategorias();
/*
a:2:{s:8:"almuerzo";a:1:{i:0;s:7:"harinas";}s:4:"cena";a:1:{i:0;s:7:"harinas";}}
*/
	$result	=	array();
	foreach($tiempos as $tiempo_id=>$tiempo){
		foreach($categorias as $categoria_id=>$categoria){
			$args = array(
				'post_type'   	=>	'alimento',
				'numberposts'	=>	-1,
				'meta_query'	=>	array(
										array(
											'key'		=>	'_esc_alimento_categoria',
											'value'		=>	serialize($tiempo),
											'compare'	=>	'LIKE'
										),
										array(
											'key'		=>	'_esc_alimento_categoria',
											'value'		=>	serialize($categoria_id),
											'compare'	=>	'LIKE'
										)
									)
			);
			$alimentos = get_posts( $args );
			$result[$tiempo_id][$categoria_id]	=	count($alimentos);
			/*$result	=	array();
			foreach($alimentos as $alimento){
				$row	=	array();
				$row['nombre']	=	$alimento->post_title;
				$row['categories']	=	get_post_meta($alimento->ID, '_esc_alimento_categoria', true);
				$result[]	=	$row;
			}*/
		}
	}
	/*_print($alimentos);*/
	/*_print($result);*/
	return $result;
}
function _esc_form_dieta($cliente_dieta=array(), $echo=true, $disableCatWithNoAlimentos=true){
	$matriz_dieta	=	_eschb_getInfoAlimentosForDietaForm();
	/*_print( $cliente_dieta );*/
/*	$_tipos_de_plato	=	array(
							'desayuno'	=> 'Desayuno',
							'almuerzo'	=> 'Almuerzo',
							'cena'		=> 'Cena'
						);*/
	$_tipos_de_plato	=	_eschb_getTiemposComida();
	$args = array(
			   'taxonomy' => 'categoria_alimento',
			   'hide_empty'   => 0,
		   );
	$categorias_alimento = get_categories($args);
	$_categoria_de_plato	=	array();
	foreach($categorias_alimento as $key=>$categoria){
		$term_id = absint( $categoria->term_id );
		$_categoria_de_plato[$categoria->slug]	=	$categoria->name;
	}
ob_start();
?>
<table class="form-table table dieta">
<?php
$col_th	=	'';
$col_td	=	'';
if($_tipos_de_plato){
	foreach($_categoria_de_plato as $key=>$categoria){
		$col_th	.=	'<th>' . $categoria . '</th>';
	}
	echo '<thead><tr><th>&nbsp;</th>' . $col_th . '</tr></thead>';
	echo '<tbody>';
	foreach($_tipos_de_plato as $key=>$tipo){
		$col_td	=	'';
		foreach($_categoria_de_plato as $keyc=>$categoria){
			$value	=	isset($cliente_dieta[$key][$keyc])? $cliente_dieta[$key][$keyc]:'';
			/*$col_td	.=	'<td data-column="' . $keyc .'"><input type="text" size="2" name="dieta[' . $key. '][' . $keyc .']" value="' . $value .'"></td>';*/
			$col_td	.=	'<td data-column="' . $keyc .'">';
			if($matriz_dieta[$key][$keyc] || !$disableCatWithNoAlimentos)
				$col_td	.=	'<input type="text" size="2" name="dieta[' . $key. '][' . $keyc .']" value="' . $value .'">';
			else
				$col_td	.=	'---';
			$col_td	.=	'</td>';
		}
		echo '<tr><td data-column="tipo">' . $tipo . '</td>' . $col_td . '</tr>';
	}
	echo '</tbody>';
}
?>
</table>
<script>
jQuery(document).ready(function(){
	setTimeout(function(){
		jQuery('.form-table.dieta tr > td').each(function(){
			jQuery(this).removeAttr('colspan');
		});
	}, 500);
});
</script>
<?php 
$html	=	ob_get_clean();
if($echo)
	echo $html;
else
	return $html;
}
function _esc_form_entrega_platos( $args = '' ) {
	$defaults = array(
		'ajax_url'			=>	'login_ajax_object.ajax_url',
		'values'			=>	array(),
		'button_save'		=>	false,
		'copy_from_monday'	=>	false
	);
	$parsed_args = wp_parse_args( $args, $defaults );
	$dias	=	array(
					'lunes'	=>	'Lunes',
					'miercoles'	=>	'Miercoles'
				);
	$_ubicacion	=	array();
?>
<script type="text/javascript">	
var _ubicaciones;
var _provincias = [];
var _cantones = [];
var _distritos= [];
	jQuery(document).ready(function($){
			$.ajax({
				url: <?php echo $parsed_args['ajax_url'] ?>,
				data: {
					action: "get_ubicaciones",
					security: "<?php echo wp_create_nonce('get_ubicaciones') ?>",
				},
				dataType: "json"
			}).done(function( response ){				
				if ( 'finished' == response.status ) {
					_ubicaciones	=	response.data;
					/*console.log(response.data);*/
					_esc_createInfoUbicacion();
					_esc_fillSelectProvincia();
					_esc_setDefaultValues();
				};
			});
		jQuery('.direccion-entrega select.provincia').on('change', function(){
			_esc_fillSelectCanton(jQuery(this));
		});
		jQuery('.direccion-entrega select.canton').on('change', function(){
			_esc_fillSelectDistrito(jQuery(this));
		});
		jQuery('#same-address-monday').on('change', function(){
			_esc_copyFromMonday(jQuery(this));
		});
	});
	function _esc_copyFromMonday(_this){
		if(_this.is(':checked')){
			var _options	=	jQuery('#container-address-lunes select.provincia:first > option').clone();
			var _val	=	jQuery('#container-address-lunes select.provincia:first').val();
			var _select	=	jQuery('#container-address-miercoles select.provincia:first');
			_select.html('');
			_select.append(_options);
			_select.val(_val);
			_options	=	jQuery('#container-address-lunes select.canton:first > option').clone();
			_val	=	jQuery('#container-address-lunes select.canton:first').val();
			_select	=	jQuery('#container-address-miercoles select.canton:first');
			_select.html('');
			_select.append(_options);
			_select.val(_val);
			_options	=	jQuery('#container-address-lunes select.distrito:first > option').clone();
			_val	=	jQuery('#container-address-lunes select.distrito:first').val();
			_select	=	jQuery('#container-address-miercoles select.distrito:first');
			_select.html('');
			_select.append(_options);
			_select.val(_val);
			_val=	jQuery('#container-address-lunes .detalles:first').val();
			_select	=	jQuery('#container-address-miercoles .detalles:first');
			_select.html('');
			_select.val(_val);			
		}
	}
	function _esc_setDefaultValues(){
		jQuery('#direcciones select.provincia').each(function(){
			_esc_fillSelectCanton(jQuery(this));
		});
		jQuery('#direcciones select.canton').each( function(){
			_esc_fillSelectDistrito(jQuery(this));
		});
	}
	function _esc_fillSelectCanton(_selectProvincia){
		const provincia_id	=	_selectProvincia.val();
		var _select;
		var direccion_entregas	=	_selectProvincia.closest('.direccion-entrega').find('select.canton:first');
		jQuery.each(direccion_entregas, function( index, ele ) {
			var option = new Option('Loading...', '');
			_select	=	jQuery(this);
			_select.html('').append(option);
			const _cantones_de_provincia = _cantones.filter(x => x.codigo_provincia==provincia_id);
			if(_cantones_de_provincia){
				option = new Option('-- Seleccionar --', '');
				_select.html('').append(option);				
				jQuery.each(_cantones_de_provincia, function(key, canton){
					let _value	=	canton.codigo_canton;
					/*let _text	=	canton.codigo_provincia + '-'+ canton.codigo_canton + ':'+ canton.nombre_canton;*/
					let _text	=	canton.nombre_canton;
					option = new Option(_text, _value);
					_select.append(option);
				});
				_select.val( _select.data('selected') );
			}else{
				_select.html('');
			}
		});
	}
	function _esc_fillSelectDistrito(_selectCanton){
		const canton_id	=	_selectCanton.val();
		const provincia_id	=	_selectCanton.closest('.direccion-entrega').find('select.provincia:first').val();
		var _select;
		var direccion_entregas	=	_selectCanton.closest('.direccion-entrega').find('select.distrito:first');
		jQuery.each(direccion_entregas, function( index, ele ) {
			var option = new Option('Loading...', '');
			_select	=	jQuery(this);
			_select.html('').append(option);
			const _distritos_de_canton = _distritos.filter(x => x.codigo_provincia==provincia_id && x.codigo_canton==canton_id);
			if(_distritos_de_canton){
				option = new Option('-- Seleccionar --', '');
				_select.html('').append(option);				
				jQuery.each(_distritos_de_canton, function(key, distrito){
					let _value	=	distrito.codigo_distrito;
					/*let _text	=	distrito.codigo_provincia + '-'+ distrito.codigo_canton + '-'+ distrito.codigo_distrito + ':'+ distrito.nombre_distrito;*/
					let _text	=	distrito.nombre_distrito;
					option = new Option(_text, _value);
					_select.append(option);
				});
				_select.val( _select.data('selected') );
			}else{
				_select.html('');
			}
		});
	}
	function _esc_fillSelectProvincia(provincia_id=false){
		var _select;
		var direccion_entregas	=	jQuery('.direccion-entrega select.provincia');
		jQuery.each(direccion_entregas, function( index, ele ) {
			var option = new Option('Loading...', '');
			_select	=	jQuery(this);
			_select.html('').append(option);
			if(_provincias){
				option = new Option('-- Seleccionar --', '');
				_select.html('').append(option);				
				jQuery.each(_provincias, function(key, provincia){
					let _value	=	provincia.codigo_provincia;
					/*let _text	=	provincia.codigo_provincia + ':'+ provincia.nombre_provincia;*/
					let _text	=	provincia.nombre_provincia;
					option = new Option(_text, _value);
					_select.append(option);
				});				
				_select.val( _select.data('selected') );
			}else{
				_select.html('');
			}
		});
	}
	function _esc_createInfoUbicacion() {
		var ubicacion;
		var obj;
		var prov;
		var cant;
		var dist;
		if (!this._ubicaciones) {
		  return;
		}
		for (var i in _ubicaciones) {
			ubicacion = _ubicaciones[i];
			var item = _provincias.find(
				item => item.codigo_provincia === ubicacion.codigo_provincia
			);
			if (!item) {
				prov = new Object();
				prov.codigo_provincia = ubicacion.codigo_provincia;
				prov.nombre_provincia = ubicacion.nombre_provincia;
				_provincias.push(prov);
			}
			var item = _cantones.find(
				item =>
				  item.codigo_canton === ubicacion.codigo_canton &&
				  item.codigo_provincia === ubicacion.codigo_provincia
				);
			if (!item) {
				cant = new Object();
				cant.codigo_canton = ubicacion.codigo_canton;
				cant.nombre_canton = ubicacion.nombre_canton;
				cant.codigo_provincia = ubicacion.codigo_provincia;
				_cantones.push(cant);
				}
				var item = _distritos.find(
				item =>
				  item.codigo_provincia === ubicacion.codigo_provincia &&
				  item.codigo_canton === ubicacion.codigo_canton &&
				  item.codigo_distrito === ubicacion.codigo_distrito
				);
			if (!item) {
				dist = new Object();
				dist.codigo_provincia = ubicacion.codigo_provincia;
				dist.codigo_canton = ubicacion.codigo_canton;
				dist.codigo_distrito = ubicacion.codigo_distrito;
				dist.nombre_distrito = ubicacion.nombre_distrito;
				_distritos.push(dist);
			}
		}
	}
</script>
<div id="direcciones" class="direcciones box-dashed">
<?php 
foreach($dias as $key=>$dia){
?>
	<div id="container-address-<?php echo $key ?>" class="direccion">
		<h4 class="dia"><?php echo $dia?></h4>
<?php
if($key=='miercoles' && $parsed_args['copy_from_monday'] === true)	:
?>
<label for="same-address-monday" class="copy-address">
<input type="checkbox" id="same-address-monday" value="yes" />
Igual que el Lunes
</label>
<?php
endif;
?>
		<div class="direccion-entrega">
			<div class="dropdowns">
				<div class="combo">
					<label>Provincia</label>
					<select name="direccion[<?php echo $key ?>][provincia]" class="provincia" data-selected="<?php echo $parsed_args['values'][$key]['provincia'] ?>">
						<option value="">---</option>
					</select>
				</div>
				<div class="combo">
					<label>Canton</label>
					<select name="direccion[<?php echo $key ?>][canton]" class="canton" data-selected="<?php echo $parsed_args['values'][$key]['canton'] ?>">
						<option value="">---</option>
					</select>
				</div>
				<div class="combo">
					<label>Distrito</label>
					<select name="direccion[<?php echo $key ?>][distrito]" class="distrito" data-selected="<?php echo $parsed_args['values'][$key]['distrito'] ?>">
						<option value="">---</option>
					</select>
				</div>
			</div>
			<label>Detalles/Dirección</label>
			<textarea name="direccion[<?php echo $key ?>][detalles]" class="detalles regular-text"><?php echo $parsed_args['values'][$key]['detalles'] ?></textarea>
		</div>
	</div>
<?php 
}
if($parsed_args['button_save'] === true)	:
?>
<div class="esc-form-group address">
	<button id="save-address" type="button" class="esc-button btn">Guardar Cambios</button>
</div>
<?php
endif;
?>
</div>
<?php 	
}
function _esc_export($filter_categoria='', $filter_dia='', $filter_periodo=''){
	/*_print(func_get_args());*/
	$ccsve_generate_value_arr_new	=	_esc_getComandoProduccion( $filter_categoria, $filter_dia, $filter_periodo );
	$titulos	=	array('Nro', 'Plato', 'Porciones');
	array_unshift($ccsve_generate_value_arr_new,$titulos);
	$filename = 'produccion';
	if(!empty($filter_periodo)){
		$periodos	=	_esc_getPeriodos();		
		$filename	.=	'-' . $periodos[$filter_periodo]['jueves']->format('dMY') . '_' . $periodos[$filter_periodo]['miercoles']->format('dMY');
	}
	if(!empty($filter_dia))
		$filename .= '-' . $filter_dia;
	if(!empty($filter_categoria))
		$filename .= '-' . $filter_categoria;
	$filename .= '.xls';
	ob_start();
	foreach($ccsve_generate_value_arr_new as $data) {
		$data_string = implode("\t", array_map('utf8_decode', array_values($data)));
		echo $data_string . "\r\n";
	}
	$response	=	 ob_get_clean();	
	header('Content-Encoding: UTF-8');
	/*header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');*/
    /*header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8' );*/
	header("Content-Type: Application/vnd.ms-excel; charset=utf-8");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header('Content-Description: File Transfer');
	header("Content-Disposition: Attachment; Filename=\"$filename\"");
	header("Expires: 0");
	header("Pragma: public");
	echo $response;
	exit;
}
function _esc_import(){
	ob_start();
	$datafile = file( dirname(__FILE__) . '/alimentos_csv_testing.csv' );
	$i=0;
	$insertados	=	array();	
	foreach ($datafile as $key => $line) {
		/*if($i>1) return ;*/
		if ( !empty( $line ) && $key>0 ) {
			/*$chunks = preg_split( '/,/', $line );*/
			$row = preg_split( '/;/', $line );
			$cat	=	sanitize_title($row[1]);
			_print(($i+1). ': ' . $row[0] . ' - ' . $row[7] . ' - ' . $cat);			
			$categoria	=	array();
			$min	=	3;
			$max	=	8;
			$precio	=	rand($min,$max) * 100;
			if(!empty($row[4])){
				$categoria['desayuno'][]	=	$cat;
			}
			if(!empty($row[5])){
				$categoria['almuerzo'][]	=	$cat;
			}
			if(!empty($row[6])){
				$categoria['cena'][]	=	$cat;
			}					
			$dias	=	array();
			if(!empty($row[2]))
				$dias['lunes']	=	'yes';
			if(!empty($row[3]))
				$dias['miercoles']	=	'yes';
			$semanas	=	array();
			if(!empty($row[7])){
				$week	=	sanitize_title($row[7]);
				$semanas[ $week ]	=	'yes';
			}
			$week	=	sanitize_title($row[7]);
			$semana_p_i_dias	=	array();
			if(!empty($row[2])){
			//	$semana_p_i_dias[$week]['lunes']	=	'yes';
				$semana_p_i_dias[$week][]	=	'lunes';
			}
			if(!empty($row[3])){
			//	$semana_p_i_dias[$week]['miercoles']	=	'yes';
				$semana_p_i_dias[$week][]	=	'miercoles';
			}			
			if(isset($insertados[$row[0]])){
				$_post_id	=	$insertados[$row[0]];
				$days	=	get_post_meta($_post_id, '_esc_alimento_dias', true);
				$days_updated	=	array_merge($days, $dias);
				update_post_meta($_post_id, '_esc_alimento_dias', $days_updated);
				$weeks	=	get_post_meta($_post_id, '_esc_alimento_semana', true);
				$weeks_updated	=	array_merge($weeks, $semanas);
				update_post_meta($_post_id, '_esc_alimento_semana', $weeks_updated);
				$week_p_i_days	=	get_post_meta($_post_id, '_esc_alimento_semana_dias', true);
				_print('---');
				_print($week_p_i_days);
				_print($semana_p_i_dias);
				foreach($semana_p_i_dias[$week] as $day){					
					if(!in_array($day,$week_p_i_days[$week]))					
						$week_p_i_days[$week][]	=	$day;
				}
				/*$week_p_i_days_updated	=	array_merge($week_p_i_days, $semana_p_i_dias);*/
				/*$week_p_i_days[$week]	=	array_merge($week_p_i_days[$week], $semana_p_i_dias[$week]);*/
				//$week_p_i_days[$week]	=	($week_p_i_days[$week]) + ($semana_p_i_dias[$week]);
				_print('---------------------');
				_print($week_p_i_days);
				_print('---------------------');
				_print('---');
				update_post_meta($_post_id, '_esc_alimento_semana_dias', $week_p_i_days);
			}else{
				$post_data = array(
					'post_title'   => utf8_encode($row[0]),
					'post_status'  => 'publish',
					'post_type' 	=> 'alimento',
					'post_author'  => get_current_user_id(),
					'meta_input'   => array(
						'_esc_alimento_semana' 		=>	$semanas,
						'_esc_alimento_dias' 		=>	$dias,
						'_esc_alimento_categoria' 	=>	$categoria,
						'_esc_alimento_semana_dias'	=>	$semana_p_i_dias,
					),
				);
				$post_id = wp_insert_post( $post_data );
				if ( !$post_id ) {
					_print($post_data);
				}else{
					_print('post_id: ' . $post_id);
					$insertados[$row[0]]	=	$post_id;
				}
			}
			echo ob_get_clean();
		}
		$i++;
	}
}
function _esc_import__old__(){
	ob_start();
	$datafile = file( dirname(__FILE__) . '/alimentos_csv_testing.csv' );
	$i=0;
	$insertados	=	array();	
	foreach ($datafile as $key => $line) {
		/*if($i>1) return ;*/
		if ( !empty( $line ) && $key>0) {
			/*$chunks = preg_split( '/,/', $line );*/
			$row = preg_split( '/;/', $line );
			$cat	=	sanitize_title($row[1]);
			_print(($i+1). ': ' . $row[1] . ' - ' . $cat);			
			$categoria	=	array();
			$min	=	3;
			$max	=	8;
			$precio	=	rand($min,$max) * 100;
			if(!empty($row[4])){
				$categoria['desayuno'][]	=	$cat;
			}
			if(!empty($row[5])){
				$categoria['almuerzo'][]	=	$cat;
			}
			if(!empty($row[6])){
				$categoria['cena'][]	=	$cat;
			}					
			$dias	=	array();
			if(!empty($row[2]))
				$dias['lunes']	=	'yes';
			if(!empty($row[3]))
				$dias['miercoles']	=	'yes';
			$semanas	=	array();
			if(!empty($row[7])){
				$week	=	sanitize_title($row[7]);
				$semanas[ $week ]	=	'yes';
			}
			if(isset($insertados[$row[0]])){
				$_post_id	=	$insertados[$row[0]];
				$days	=	get_post_meta($_post_id, '_esc_alimento_dias', true);
				$days_updated	=	array_merge($days, $dias);
				update_post_meta($_post_id, '_esc_alimento_dias', $days_updated);
				$weeks	=	get_post_meta($_post_id, '_esc_alimento_semana', true);
				$weeks_updated	=	array_merge($weeks, $semanas);
				update_post_meta($_post_id, '_esc_alimento_semana', $weeks_updated);
			}else{
				$post_data = array(
					'post_title'   => utf8_encode($row[0]),
					'post_status'  => 'publish',
					'post_type' 	=> 'alimento',
					'post_author'  => get_current_user_id(),
					'meta_input'   => array(
						/*'_esc_alimento_semana' 		=>	sanitize_title($row[7]),*/
						'_esc_alimento_semana' 		=>	$semanas,
						'_esc_alimento_dias' 		=>	$dias,
						'_esc_alimento_categoria' 	=>	$categoria,
						/*'_esc_alimento_precio'		=>	$precio*/
					),
				);
				$post_id = wp_insert_post( $post_data );
				if ( !$post_id ) {
					_print($post_data);
				}else{
					_print('post_id: ' . $post_id);
					$insertados[$row[0]]	=	$post_id;
				}
			}
			echo ob_get_clean();
		}
		$i++;
	}
}
function _esc_import_old(){
	ob_start();
	$datafile = file( dirname(__FILE__) . '/alimentos_csv_testing.csv' );
	$i=0;
	foreach ($datafile as $key => $line) {
		/*if($i>1) return ;*/
		if ( !empty( $line ) && $key>0) {
			/*$chunks = preg_split( '/,/', $line );*/
			$row = preg_split( '/;/', $line );
			$cat	=	sanitize_title($row[1]);
			_print(($i+1). ': ' . $row[1] . ' - ' . $cat);			
			$categoria	=	array();
			$min	=	3;
			$max	=	8;
			$precio	=	rand($min,$max) * 100;
			if(!empty($row[4])){
				$categoria['desayuno'][]	=	$cat;
			}
			if(!empty($row[5])){
				$categoria['almuerzo'][]	=	$cat;
			}
			if(!empty($row[6])){
				$categoria['cena'][]	=	$cat;
			}					
			$dias	=	array();
			if(!empty($row[2]))
				$dias['lunes']	=	'yes';
			if(!empty($row[3]))
				$dias['miercoles']	=	'yes';
			$post_data = array(
				'post_title'   => utf8_encode($row[0]),
				'post_status'  => 'publish',
				'post_type' 	=> 'alimento',
				'post_author'  => get_current_user_id(),
				'meta_input'   => array(
					'_esc_alimento_semana' 		=>	sanitize_title($row[7]),
					'_esc_alimento_dias' 		=>	$dias,
					'_esc_alimento_categoria' 	=>	$categoria,
					'_esc_alimento_precio'		=>	$precio
				),
			);
			$post_id = wp_insert_post( $post_data );
			if ( !$post_id ) {
				_print($post_data);
			}else
				_print('post_id: ' . $post_id);
			echo ob_get_clean();
		}
		$i++;
	}
}
function _esc_changeWeekParImpar(){
	$array	=	array(
					'par'	=>	'impar',
					'impar'	=>	'par'
				);
	$args = array(
		'post_type'   	=>	'alimento',
		'meta_key'   	=>	'_esc_alimento_semana_dias',
		'numberposts'	=>	-1		
	);
	$alimentos = get_posts( $args );
	/*_print(count($alimentos));/*wp_die('fin');*/
	foreach($alimentos as $alimento){
		$_esc_alimento_semana_dias	=	get_post_meta($alimento->ID, '_esc_alimento_semana_dias', true);
		_print('----- ' . $alimento->ID . ' -----');
		_print($_esc_alimento_semana_dias);
		$_new_data	=	array();
		foreach($_esc_alimento_semana_dias as $key=>$dias){			
			$_new_data[$array[$key]]	=	$dias;
		}
		_print('Changed');
		_print($_new_data);
		update_post_meta($alimento->ID, '_esc_alimento_semana_dias', $_new_data);
	}
	wp_die();
}
function _esc_getAlimentos($date=false, $week=''){
	if(empty($week))
		$week_par_impar	=	_esc_parImpar($date, 'ARRAY');
	else
		$week_par_impar['result']	=	$week;
/*_print($week_par_impar);*/
/*exit;*/
$array_values[]	=	$week_par_impar['result'];
	$args = array(
		'post_type'   	=>	'alimento',
		'numberposts'	=>	-1,
		'meta_query'	=>	array(
								/*array(
									'key'   => '_esc_alimento_semana',
									'value' => $week_par_impar['result'],
								),*/
								/*array(
									'key'		=>	'_esc_alimento_semana',
									'value'		=>	serialize($week_par_impar['result']),
									'compare'	=>	'LIKE'
								),*/
								array(
									'key'		=>	'_esc_alimento_semana_dias',
									'value'		=>	serialize($week_par_impar['result']),
									'compare'	=>	'LIKE'
								)
							)
	);
	$alimentos = get_posts( $args );
	/*_print($alimentos);*/
	global $wpdb;
/*_print($wpdb);exit;*/
// Print last SQL query string
/*_print($wpdb->last_query);*/
// Print last SQL query result
/*_print($wpdb->last_result);*/
// Print last SQL query Error
/*_print($wpdb->last_error);exit;*/
/*	$result	=	array(	
					'week'	=>	array(
									'number'	=>	$currentWeekNumber,
									'status'	=>	$week_par_impar,
								)
				);*/
	/*$result	=	array(	
					'week_info'	=>	$week_par_impar
				);*/
	foreach($alimentos as $alimento){
		//$_esc_alimento_dias	=	get_post_meta($alimento->ID, '_esc_alimento_dias', true);
		/*_print($_esc_alimento_dias);*/
		$_esc_alimento_semana_dias	=	get_post_meta($alimento->ID, '_esc_alimento_semana_dias', true);
		/*_print($_esc_alimento_semana_dias);*/
		$_week	=	$_esc_alimento_semana_dias[$week_par_impar['result']];
		/*_print($_week);*/
		$_esc_alimento_dias	=	implode(', ', $_week);
		$_esc_alimento_categoria	=	get_post_meta($alimento->ID, '_esc_alimento_categoria', true);
		$_esc_alimento_precio		=	get_post_meta($alimento->ID, '_esc_alimento_precio', true);
		/*453$_esc_alimento_precio_desayuno	=	get_post_meta($alimento->ID, '_esc_alimento_precio_desayuno', true);
		$_esc_alimento_precio_almuerzo	=	get_post_meta($alimento->ID, '_esc_alimento_precio_almuerzo', true);
		$_esc_alimento_precio_cena		=	get_post_meta($alimento->ID, '_esc_alimento_precio_cena', true);
		$_esc_alimento_precios			=	get_post_meta($alimento->ID, '_esc_alimento_precios', true);*/
		foreach($_esc_alimento_categoria as $tiempo=>$categorias){
			/*foreach($categorias as $cat){
				$res[$tiempo][$cat][]	=	array(
													'alimento_id'		=>	$alimento->ID,
													'alimento_title'	=>	$alimento->post_title,
													'alimento_precio'	=>	$_esc_alimento_precio,
													'alimento_dias'		=>	$_esc_alimento_dias
												);
			}*/
			foreach($categorias as $cat){
				$__alimento	=	array(
													'alimento_id'		=>	$alimento->ID,
													'alimento_title'	=>	$alimento->post_title,
													'alimento_precio'	=>	$_esc_alimento_precio,
													'alimento_dias'		=>	$_esc_alimento_dias
												);
				/*if(isset($_esc_alimento_dias['lunes']))
					$res['lunes'][$tiempo][$cat][]	=	$__alimento;*/
				if(in_array('lunes', $_week))
					$res['lunes'][$tiempo][$cat][]	=	$__alimento;
				/*if(isset($_esc_alimento_dias['miercoles']))
					$res['miercoles'][$tiempo][$cat][]	=	$__alimento;*/
				if(in_array('miercoles', $_week))
					$res['miercoles'][$tiempo][$cat][]	=	$__alimento;
			}
		}
		/*_print($res);*/
		/*if(isset($_esc_alimento_dias['lunes']))
			$result['lunes']	=	$res;
		if(isset($_esc_alimento_dias['miercoles']))
			$result['miercoles']=	$res;*/
	}
	/*return $result;*/
	/*_print($res);exit;*/
	return $res;
}
function _esc_getCategorias(){
	$args = array(
               'taxonomy' => 'categoria_alimento',
			   'hide_empty'   => 0,
           );
   $categorias_plato = get_categories($args);
   $_aCategorias	=	array();
   foreach($categorias_plato as $key=>$categoria){
		$term_id = absint( $categoria->term_id );	
		$precios	=	array(
							'desayuno'	=>	get_term_meta( $term_id, 'categoria_alimento_precio_desayuno', true ),
							'almuerzo'	=>	get_term_meta( $term_id, 'categoria_alimento_precio_almuerzo', true ),
							'cena'	=>	get_term_meta( $term_id, 'categoria_alimento_precio_cena', true ),
						);
		$_aCategorias[$categoria->slug]	=	array(
												'term_id'		=>	$categoria->term_id,
												'name'		=>	$categoria->name,
												'precios'	=>	$precios
											);
	}
	return $_aCategorias;
}
function _esc_getAlimentosFromOrdenArgs($data){
	$selected_ids	=	array();
	foreach($data as $key=>$platos){/*_print($platos);*/
		foreach($platos as $pkey=>$plato){
			if($pkey!='tipo' && $plato){
				/*_print($pkey);
				_print($plato);*/
				$selected_ids[]	=	$plato['alimento_id'];
			}
		}
	}	
	$ids	=	implode(',', $selected_ids);
	$posts = get_posts( array(
							'include'   => $ids,
							'post_type' => 'alimento',
							'orderby'   => 'post__in',
						) );
	$alimentos	=	array();
	foreach($posts as $key=>$_post)
		$alimentos[$_post->ID]	=	$_post->post_title;
	return $alimentos;	
}
function _esc_getEstadosOrder(){
	return	array(
					'pendiente'			=>	'Pendiente',
					'aprobado'			=>	'Aprobado',
					/*'cliente_sin_dieta'	=>	'Cliente sin dieta',*/
				);
}
function _eschb_getTiemposComida(){
	$tiempos	=	array(
						'desayuno'	=> 'Desayuno',
						'almuerzo'	=> 'Almuerzo',
						'cena'		=> 'Cena'
					);
	return $tiempos;
}
function _esc_get_dias(){
$dias	=	array(
								'lunes'	=> 'Lunes',
								'miercoles'	=> 'Miercoles',
							);
	return $dias;
}
function _esc_getClienteData($cliente_id){
	$return	=	array();
	$cliente = get_post( $cliente_id );
	$_dieta				=	get_post_meta($cliente->ID, '_esc_cliente_dieta', true);
	$_direccion			=	get_post_meta($cliente->ID, '_esc_cliente_direccion', true);
	return array(
					'id'		=>	$cliente_id,
					'dieta'		=>	$_dieta,
					'direccion'	=>	$_direccion
				);
}
function _esc_getClienteOfUser($user_id){
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
	return _esc_getClienteData($cliente->ID);
}
function _esc_clienteTieneDieta($cliente_id){
	$dieta		=	get_post_meta( $cliente_id, '_esc_cliente_dieta', true );
	/*_print($dieta);*/
	$tieneDieta	=	true;
	if(	count(array_filter($dieta['desayuno'])) == 0 && 
		count(array_filter($dieta['almuerzo'])) == 0 && 
		count(array_filter($dieta['cena'])) == 0 )
		$tieneDieta	=	false;
	return $tieneDieta;
}
function _esc_getFullnameOfCliente($cliente_id){
	$cliente = get_post( $cliente_id );
	if($cliente)
		return $cliente->post_title;
	$user_id	=	get_post_meta( $cliente_id, '_esc_cliente_user_id', true );
	$user_meta 	=	get_user_meta( $user_id );/*_print($user_meta);*/
	$fullname	=	trim($user_meta['first_name'][0] . ' ' . $user_meta['last_name'][0]);
	if(!$fullname)
		$fullname	=	$user_meta['nickname'][0];
	return $fullname;
}
function _esc_actualizarDietaEnOrdenesPendientes($cliente_id){
	$args = array(
		'post_type'   	=>	'orden',
		'post_status'  	=>	'publish',
		'numberposts'	=>	1,
		'meta_query'	=>	array(
								array(
									'key'   => '_esc_orden_cliente_id',
									'value' => $cliente_id,
								),
								array(
									'key'   => '_esc_orden_status',
									'value' => 'pendiente'
								)
							)
	);
	$ordenes	=	get_posts( $args );
	/*_print($ordenes);*/
	$dias	=	_esc_get_dias();
	$_clienteData	=	_esc_getClienteData($cliente_id);
	/*_print($_clienteData);*/
	global $ordenes_actualizadas;
	foreach($ordenes as $orden){
		$_new_args	=	array();
		$args	=	get_post_meta($orden->ID, '_esc_orden_args', true);
		$ordenes_actualizadas[]	=	$orden->ID;
		/*_print($args);*/
		foreach($args as $dia=>$data){			
			foreach($data['platos'] as $key=>$plato){
				/*_print($plato);*/
				$tipo	=	$plato['tipo'];
				foreach($plato as $cat=>$alimento){
					if($cat=='tipo')
						$_new_args[$dia]['platos'][$key][$cat]	=	$alimento;
					else{
						if($alimento){
						$alimento['dieta']	=	$_clienteData['dieta'][$tipo][$cat];					
						}
						$_new_args[$dia]['platos'][$key][$cat]	=	$alimento;
					}
				}
			}
			$_new_args[$dia]['entrega']	=	$data['entrega'];
		}
		/*_print($_new_args);*/
		update_post_meta($orden->ID, '_esc_orden_args', $_new_args);
	}
}
function _esc_getDireccionFormatted($direccion){
	global $wpdb;
	$sql = "SELECT * FROM {$wpdb->prefix}ubicaciones ";
	$sql .= "WHERE codigo_provincia='{$direccion['provincia']}' and ";
	$sql .= "codigo_canton='{$direccion['canton']}' and ";
	$sql .= "codigo_distrito='{$direccion['distrito']}'";
	$address	=	$wpdb->get_row( $sql );
	$_direccion[]	=	$direccion['detalles'];
	$_direccion[]	=	$address->nombre_distrito;
	$_direccion[]	=	$address->nombre_canton;
	$_direccion[]	=	$address->nombre_provincia;
	return implode(', ', $_direccion);
}
function _esc_getDireccionesFormateadas($direcciones){
	$result	=	array();
	foreach($direcciones as $dia=>$direccion){
		$result[$dia]	=	_esc_getDireccionFormatted($direccion);
	}
	return $result;
}
function _esc_processSubmittedFormPedido($_SUBMITTED){
	/*_print($_SUBMITTED);*/
	$editing	=	false;
	$args = array(
               'taxonomy' => 'categoria_alimento',
			   'hide_empty'   => 0,
           );
	if(isset($_SUBMITTED['action']) && $_SUBMITTED['action']=='editpost'){
		$editing	=	true;
		/*$user_id		=	get_post_meta($_SUBMITTED['post_ID'], '_esc_cliente_user_id', true);*/
		$_esc_orden_cliente_id		=	get_post_meta($_SUBMITTED['post_ID'], '_esc_orden_cliente_id', true);
		$_clienteData	=	_esc_getClienteData($_esc_orden_cliente_id);
	}else{
		$user_id		=	get_current_user_id();	
		$_clienteData	=	_esc_getClienteOfUser($user_id);
	}
	$clienteConDietaAsignada	=	_esc_clienteTieneDieta($_clienteData['id']);
	$direcciones	=	_esc_getDireccionesFormateadas($_clienteData['direccion']);
	$_categorias		=	_esc_getCategorias();
	$categorias_plato	=	get_categories($args);
	$dias	=	_esc_get_dias();
	$form_data_platos	=	array();
	$selected_ids		=	array();
	$index_key	=	1;
	$with_platos	=	false;
	foreach($dias as $dkey=>$dvalue){
		$_platos_count	=	count($_SUBMITTED[$dkey]['tipo']);
		$array_days	=	array();
		for($i=0;$i<$_platos_count;$i++){
			$array	=	array();
			$array['tipo']	=	$_SUBMITTED[$dkey]['tipo'][$i];		
			foreach($_categorias as $key=>$taxonomy){
				$alimento_id	=	$_SUBMITTED[$dkey][$key][$i];
				if( $alimento_id ){
					$alimento_precio	=	get_post_meta($alimento_id, '_esc_alimento_precio', true);
					$array[$key]	=	array(
											'alimento_id'	=>	$alimento_id,
											'dieta' 		=>	$_clienteData['dieta'][$array['tipo']][$key],
											'precios'		=>	array(
																	'cat'		=>	$taxonomy['precios'][$array['tipo']],
																	'alimento'	=>	$alimento_precio
																)
										);
					$selected_ids[]	=	$alimento_id;
				}else
					$array[$key]	=	'';
			}
/*_print('$array');
_print($array);*/
			if(!empty($array['tipo'])){
				$_array_key	=	time() + $index_key;
				$array_days[$_array_key]	=	$array;
				$index_key++;
			}
		}
		$form_data_platos[$dkey]['platos']	=	$array_days;
		if(count($array_days)>0)
			$with_platos	=	true;
		$entrega	=	array(
							'opcion'	=>	$_SUBMITTED[$dkey]['entrega'],
							'precio'	=>	($_SUBMITTED[$dkey]['entrega']=='domicilio'? 1500:0)
						);
		$form_data_platos[$dkey]['entrega']	=	$entrega;
	}
	$ids	=	implode(',', $selected_ids);
	$posts = get_posts( array(
							'include'   => $ids,
							'post_type' => 'alimento',
							'orderby'   => 'post__in',
						) );
	$alimentos	=	array();
	foreach($posts as $key=>$_post_alimento)
		$alimentos[$_post_alimento->ID]	=	$_post_alimento->post_title;
	$style['th']	=	'border-bottom:1px solid #000;padding:5px;background-color:#f1f1f1';
	$style['tbody_td']	=	'border:1px solid #f5f5f5;border-bottom-color:#333;padding:15px;';
	$style['tfoot_td']	=	'padding:15px;';
	$html	=	'';
	$html_new_email	=	'';
	$html_resumen	=	'<div style="border:1px dashed #ccc;padding: 50px 30px 30px;position: relative;margin-top: 50px;">';
	$html_resumen	.=	'<h2 style="position:absolute;top:-50px;background-color:#fff;padding:15px;margin-left:-15px;">Resumen</h2>';
	$html_resumen	.=	'<table style="border-collapse:collapse;color:#757575;max-width:300px;margin:50px auto;width:100%">';
	$html_resumen	.=	'<thead>';
	$html_resumen	.=	'<tr>';
	$html_resumen	.=		'<th style="' . $style['th'] . '">Dia</th>';
	$html_resumen	.=		'<th style="' . $style['th'] . '">Monto</th>';
	$html_resumen	.=	'</tr>';
	$html_resumen	.=	'</thead>';
	$html_resumen	.=	'<tbody>';
	$thead	=	'<thead>';
	$thead	.=	'<tr>';
	$thead	.=		'<th style="' . $style['th'] . '">#</th>';
	$thead	.=		'<th style="' . $style['th'] . '">Tipo</th>';
	foreach($_categorias as $key=>$taxonomy){
		$thead	.=		'<th style="' . $style['th'] . '">' . $taxonomy['name'] . '</th>';
	}
	$thead	.=		'<th style="' . $style['th'] . '">Precio</th>';
	$thead	.=	'</tr>';
	$thead	.=	'</thead>';
	$total	=	0;
	if($editing){
		if($_SUBMITTED['post_date'])
			$fecha_mensaje	=	_esc_getPeriodoEntregaPedido($_SUBMITTED['post_date']);
		else{
			global $post;/*_print($post);*/
			$fecha_mensaje	=	_esc_getPeriodoEntregaPedido($post->post_date);
		}
	}else{
		$fecha_mensaje	=	_esc_getPeriodoEntregaPedido();
	}
	/*$fecha_mensaje	=	_esc_getPeriodoEntregaPedido($_SUBMITTED['post_date']);*/
	foreach($form_data_platos as $dia=>$data){
		if(count($data['platos'])==0)
			continue ;

		$html	.=	'<div style="border:1px dashed #ccc;padding: 50px 30px 30px;position: relative;margin-top: 50px;">';
		$html	.=	'<h2 style="position:absolute;top:-50px;background-color:#fff;padding:15px;margin-left:-15px;">' . $dias[$dia] . '</h2>';
		$html	.=	'<table style="border-collapse:collapse;color:#757575;max-width:100%">';
		$html	.=	$thead;
		$html	.=	'<tbody>';
		$subtotal	=	0;
		$html_resumen	.=	'<tr>';
		$html_resumen	.=	'<td style="' . $style['tbody_td'] . '">' . $dias[$dia] . '</td>';
		$plato_num	=	1;
		$html_new_email	.=	_esc_emailHeadLine('Entrega ' . $fecha_mensaje[$dia]);
/*_print($dia);
_print($data);*/
		foreach($data['platos'] as $key=>$plato){
/*_print($plato);*/
			$html	.=	'<tr>';
			$html	.=	'<td style="' . $style['tbody_td'] . '">Plato ' . $plato_num . '</td>';
			$monto	=	0;
			$items	=	'';
			foreach($plato as $cat=>$alimento){
				if($cat=='tipo')
					$html	.=	'<td style="' . $style['tbody_td'] . '">' . ucfirst($alimento) . '</td>';
				else{
					/*$html	.=	'<td style="' . $style['tbody_td'] . '">' .stripslashes($alimentos[$alimento['alimento_id']]) . '</td>';*/
					/*$cantidad	=	0;*/
					$cantidad	=	empty($alimento)? 0:1;
					if($alimento['dieta'])
						$cantidad	=	$alimento['dieta'];
					/*if($alimento['precios']['alimento'])
						$monto +=	$alimento['precios']['alimento'] * $cantidad;
					else
						$monto +=	$alimento['precios']['cat'] * $cantidad;
					*/
					$precio	=	0;
					if($alimento['precios']['alimento'])
						$precio	=	$alimento['precios']['alimento'];
					else
						$precio	=	$alimento['precios']['cat'];
					$precio_plato	=	$precio * $cantidad;
					$monto +=	$precio_plato;
					$html	.=	'<td style="' . $style['tbody_td'] . '">';
/*
* Mostrar calculo por alimento
*
					$html	.=	'<small>(' . $cantidad . 'x' . $precio . '=</small><strong>' . $precio_plato . '</strong>)<br>';
					$html	.=	$alimento['alimento_id'] . ':';
*/
					if(!empty($alimento['alimento_id']))
						$items	.=	'<tr><td style="font-family: \'Open sans\', Arial, sans-serif; color:#7f8c8d; font-size:13px; line-height: 20px;">- ' . stripslashes($alimentos[$alimento['alimento_id']]) . '</td></tr>';
					$html	.=	stripslashes($alimentos[$alimento['alimento_id']]);
					$html	.=	'</td>';
				}
			}
			$_monto	=	'N/A';
			if($clienteConDietaAsignada){
				$_monto	=	number_format($monto);
				$html	.=	'<td style="' . $style['tbody_td'] . '">' . number_format($monto) . '</td>';
			}else
				$html	.=	'<td style="' . $style['tbody_td'] . '">N/A</td>';
			$html	.=	'</tr>';
			$html_new_email	.=	_esc_emailPlato('Plato ' . $plato_num, $plato['tipo'], $items, $_monto);
			$subtotal	+=	$monto;
			$plato_num++;
		}
		$subtotal	+=	$data['entrega']['precio'];
		if($clienteConDietaAsignada)
			$html_resumen	.=	'<th style="' . $style['tbody_td'] . '">' . number_format($subtotal) . '</th>';
		else
			$html_resumen	.=	'<th style="' . $style['tbody_td'] . '">N/A</th>';
		$html_resumen	.=	'</tr>';
		$html_new_email	.=	_esc_emailEntrega(number_format($data['entrega']['precio']) , $direcciones[$dia]);
		$total	+=	$subtotal;
		$html	.=	'<tr>';
		$html	.=	'<td style="' . $style['tbody_td'] . '" colspan="' . (count($_categorias)+2). '">Entrega: <strong>' . ucfirst($data['entrega']['opcion']) . '</strong></td>';
		$html	.=	'<td style="' . $style['tbody_td'] . '"><strong>' . number_format($data['entrega']['precio']) . '</strong></td>';
		$html	.=	'</tr>';
		$html	.=	'</tbody>';
		$html	.=	'<tfoot>';
		$html	.=	'<tr>';
		$html	.=	'<th style="' . $style['tfoot_td'] . '" colspan="' . (count($_categorias)+1). '">&nbsp;</th>';
		$html	.=	'<th style="' . $style['tfoot_td'] . '">Subtotal:</th>';
		if($clienteConDietaAsignada)
			$html	.=	'<td style="' . $style['tfoot_td'] . '"><strong>' . number_format($subtotal) . '</strong></td>';
		else
			$html	.=	'<td style="' . $style['tfoot_td'] . '"><strong>N/A</strong></td>';
		$html	.=	'</tr>';
		$html	.=	'</tfoot>';
		$html	.=	'</table>';
		$html	.=	'</div>';
	}
	$html_resumen	.=	'</tbody>';
	$html_resumen	.=	'<tfoot>';
	$html_resumen	.=	'<tr>';
	$html_resumen	.=	'<td style="' . $style['tfoot_td'] . '">Total</td>';
	$_total	=	'N/A';
	if($clienteConDietaAsignada){
		$_total	=	number_format($total);
		$html_resumen	.=	'<th style="' . $style['tfoot_td'] . '">' . number_format($total) . '</th>';
	}else
		$html_resumen	.=	'<th style="' . $style['tfoot_td'] . '">N/A</th>';
	$html_resumen	.=	'</tr>';
	$html_resumen	.=	'</tfoot>';
	$html_resumen	.=	'</table>';
	$html_resumen	.=	'</div>';
	$html	.=	$html_resumen;
	$html_new_email	.=	_esc_emailTotal( $_total );
	$email	=	file_get_contents(ESCHB_URL . '/includes/emails/orden_email.html');
	/*echo $email;exit;*/
	$html_new_email	=	str_replace('{{__CONTENT__}}', $html_new_email, $email);
	/*echo $html;*/
	/*echo $html_new_email;*/
	/*_print($form_data_platos);
	exit;*/
	$_esc_cliente_correo			=	get_post_meta($_clienteData['id'], '_esc_cliente_correo', true);
	$to	=	$_esc_cliente_correo;/*_print($_esc_cliente_correo);*/
	$headers[] = 'From: Healthy Box <info@saludablecr.com>';
	/*$headers[] = 'Cc: Danilo Mata <danilo@deudigital.com>';*/
	/*$headers[] = 'Bcc: servicioalcliente@gruposng.com,jaime@solutionswebonline.com';*/
	$headers[] = 'Bcc: servicioalcliente@gruposng.com';
	$message	.=	$html_new_email;
	add_filter('wp_mail_content_type', function( $content_type ) {
		return 'text/html';
	});
	/*date_default_timezone_set('America/Halifax');*/
	if($editing){
		update_post_meta($_SUBMITTED['post_ID'], '_esc_orden_args', $form_data_platos);
		if($_SUBMITTED['esc_orden_status']=='aprobado' && $with_platos)
			wp_mail( $to, 'Se ha Actualizado tu Orden', $message, $headers );
	}else{
		date_default_timezone_set('America/Halifax');
		$post_data = array(
			'post_title'   => 'Order #',
			'post_status'  => 'publish',
			'post_type' 	=> 'orden',
			'post_date' 	=> $_SUBMITTED['post_date'],
			'post_author'  => get_current_user_id(),
			'meta_input'   => array(
				'_esc_orden_cliente_id' => $_clienteData['id'],
				'_esc_orden_args' 		=> $form_data_platos,
				'_esc_orden_status'		=> 'pendiente',
			),
		);
		$post_id = wp_insert_post( $post_data );
		wp_update_post( array( 'ID' => $post_id, 'post_title' => 'Order #' . $post_id, 'post_slug' => 'Order ' . $post_id ) );
		wp_mail( $to, 'Se ha registrado una Nueva Orden', $message, $headers );
		wp_redirect( home_url('/gracias') );
		exit;
	}
}
function _esc_getPeriodoEntregaPedido($custom_date=false){
	if($custom_date){
		$_next_thursday		=	new DateTime($custom_date);
		$_next_thursday->modify('next thursday');
		/*$_next_thursday		=	new DateTime('next thursday');*/
	}else{
		/*
		* Fecha Pedido 06 Dic 2019
		*	Periodo
		*		Jueves 05 Dic 2019			Miercoles 11 Dic 2019
				Jueves 12 Dic 2019			Miercoles 18 Dic 2019
		* Fecha entrega:
				Lunes 16 Dic 2019	 y 		Miercoles 18 Dic 2019
		*/
		$_next_thursday		=	new DateTime('next thursday');
	}
	$_next_monday		=	clone $_next_thursday;
	$_next_wednesday	=	clone $_next_thursday;
	$_next_monday->modify('next monday');
	$_next_wednesday->modify('next wednesday');
	$meses	=	array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
	$date	=	$_next_monday->format('d m Y');
	$date	=	explode(' ', $date);
	$return['lunes']	=	'Lunes ' . $date[0] . ' de ' . $meses[$date[1]-1] . ' ' . $date[2];
	$date	=	$_next_wednesday->format('d m Y');
	$date	=	explode(' ', $date);
	$return['miercoles']=	'Mi&eacute;rcoles ' . $date[0] . ' de ' . $meses[$date[1]-1] . ' ' . $date[2];
	return $return;
}
function _esc_getPeriodos(){
/*	
* date	N	1 (para lunes) hasta 7 (para domingo)
* $zonelist = array(
		'Kwajalein' => -12.00, 'Pacific/Midway' => -11.00, 'Pacific/Honolulu' => -10.00, 'America/Anchorage' => -9.00, 'America/Los_Angeles' => -8.00, 'America/Denver' => -7.00, 
		'America/Tegucigalpa' => -6.00, 
		'America/New_York' => -5.00, 
		'America/Caracas' => -4.30, 
		'America/Halifax' => -4.00, 
		'America/St_Johns' => -3.30, 'America/Argentina/Buenos_Aires' => -3.00, 'America/Sao_Paulo' => -3.00, 'Atlantic/South_Georgia' => -2.00, 'Atlantic/Azores' => -1.00, 'Europe/Dublin' => 0, 'Europe/Belgrade' => 1.00, 'Europe/Minsk' => 2.00, 'Asia/Kuwait' => 3.00, 'Asia/Tehran' => 3.30, 'Asia/Muscat' => 4.00, 'Asia/Yekaterinburg' => 5.00, 'Asia/Kolkata' => 5.30, 'Asia/Katmandu' => 5.45, 'Asia/Dhaka' => 6.00, 'Asia/Rangoon' => 6.30, 'Asia/Krasnoyarsk' => 7.00, 'Asia/Brunei' => 8.00, 'Asia/Seoul' => 9.00, 'Australia/Darwin' => 9.30, 'Australia/Canberra' => 10.00, 'Asia/Magadan' => 11.00, 'Pacific/Fiji' => 12.00, 'Pacific/Tongatapu' => 13.00);
*/
	$daynumber	=	date('N');
	if($daynumber>=4){
		$period_end	=	new DateTime('next wednesday');
		$period_start	=	clone $period_end;
		$period_start->modify('previous thursday');
		$periodos['actual']	=	array(
							'jueves'	=>	$period_start,
							'miercoles'	=>	$period_end
						);
		$period_end	=	clone $period_start;
		$period_end->modify('-1 day');
		$period_start	=	clone $period_end;
		$period_start->modify('previous thursday');
		$periodos['anterior']	=	array(
							'jueves'	=>	$period_start,
							'miercoles'	=>	$period_end
						);
	}else{
		$period_start	=	new DateTime('previous thursday');
		$period_end	=	clone $period_start;
		$period_end->modify('next wednesday');
		$periodos['actual']	=	array(
							'jueves'	=>	$period_start,
							'miercoles'	=>	$period_end
						);
		$period_end	=	clone $period_start;
		$period_end->modify('-1 day');
		$period_start	=	clone $period_end;
		$period_start->modify('previous thursday');
		$periodos['anterior']	=	array(
							'jueves'	=>	$period_start,
							'miercoles'	=>	$period_end
						);
	}
	/*_print($periodos);*/
	return $periodos;
}
function _esc_parImpar($date=false, $output='result'){/*_print('_esc_parImpar');_print(func_get_args());*/
	if($date){
		$date		=	new DateTime($date);
	}else{
		$date	=	new DateTime();
		$weekNumber	=	$date->format('W');
	}
	$_fecha_actual	=	$date->format('l jS \of F Y');
	/*_print($date);*/
	$daynumber	=	$date->format('N');
	/*_print('$daynumber: ' . $daynumber);*/
	if( $daynumber != 4 ){
		/*$date	=	new DateTime('previous thursday');*/
		$date->modify('previous thursday');
		/*_print($date);*/
		$weekNumber =	$date->format('W');
	}
	/*_print('$weekNumber: ' . $weekNumber);_print($date);*/
	$week_par_impar	=	'impar';
	if($weekNumber%2==0)
		$week_par_impar	=	'par';
	/*_print('$week_par_impar: ' . $week_par_impar);*/
	$return	=	array(
					'result'		=>	$week_par_impar,
					'num_dia'	=>	intval($daynumber),
					/*'fecha_actual'	=>	date('l jS \of F Y'),*/
					'fecha_actual'	=>	$_fecha_actual,
					'periodo'	=>	array(
										'inicio'	=>	$date->format('l jS \of F Y'),
										'num_semana'	=>	intval($weekNumber),
									)
				);
	global $_parImpar;
	$_parImpar	=	$return;
	if($output=='result')
		return	$week_par_impar;
	/*
	if($output=='result')
		$return =	$week_par_impar;
	else{
		$return	=	array(
					'result'		=>	$week_par_impar,
					'week_number'	=>	$weekNumber,
					'date_thursday'	=>	$date,
					'day_number'	=>	$daynumber,
				);
	}*/
	/*return $week_par_impar;*/
	return $return;
}
function _esc_getComandoProduccion( $filter_cat='', $filter_dia='', $filter_periodo='' ) {
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
	$args	=	_esc_getArgsPeriodo($args, $filter_periodo);/*_print($args);exit;*/
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
	foreach($alimentos	as $key=>$alimento){
		$row	=	array();
		$row['nro']	=	($key+1);
		/*$row['title']	=	$alimento->ID . ': ' . $alimento->post_title;*/
		$row['title']	=	$alimento->post_title;
		$row['porciones']	=	$resumen[$alimento->ID]['count'];
		$result[]	=	$row;
	}
	return $result;
}
function _esc_filterDia($filter_dia=''){
	$dias	=	_esc_get_dias();
	$html	=	'<p class="search-box">';
	/*if(!$hide_label)*/
		$html	.=		'<label for="filter_dia">D&iacute;a de Entrega</label>';
	$html	.=		'<select name="filter_dia" onchange="this.form.submit()">';
	$html	.=			'<option value="">Todos</option>';
	foreach($dias as $key=>$dia){
		$selected	=	'';
		if($key==$filter_dia)
			$selected	=	' selected="selected"';
		$html	.=	'<option value="' . $key .'"' . $selected . '>' . $dia . '</option>';
	}
	$html	.=		'</select>';
	$html	.=	'</p>';
	return $html;
}
function _esc_filterSemana($filter_semana=''){
	$semanas=	array(
					'impar'	=>	'Impar',
					'par'	=>	'Par',
				);
	$html	=	'<p class="search-box">';
	/*if(!$hide_label)*/
		$html	.=		'<label for="filter_semana">Semana</label>';
	$html	.=		'<select name="filter_semana" onchange="this.form.submit()">';
	$html	.=			'<option value="">Todos</option>';
	foreach($semanas as $key=>$semana){
		$selected	=	'';
		if($key==$filter_semana)
			$selected	=	' selected="selected"';
		$html	.=	'<option value="' . $key .'"' . $selected . '>' . $semana . '</option>';
	}
	$html	.=		'</select>';
	$html	.=	'</p>';
	return $html;
}
function _esc_filterCategoriasAlimento($filter_categoria_alimento=''){
	$categorias		=	_esc_getCategorias();
	$html	=	'<p class="search-box">';
	if(!$hide_label)
		$html	.=		'<label for="filter_categoria_alimento">Categor&iacute;a</label>';
	$html	.=		'<select name="filter_categoria_alimento" onchange="this.form.submit()">';
	$html	.=			'<option value="">Todos</option>';
	foreach($categorias as $key=>$taxonomy){
		$selected	=	'';
		if($key==$filter_categoria_alimento)
			$selected	=	' selected="selected"';
		$html	.=	'<option value="' . $key .'"' . $selected . '>' . $taxonomy['name'] . '</option>';
	}
	$html	.=		'</select>';
	$html	.=	'</p>';
	return $html;
}

function _esc_filterPeriodos($filter_periodo='', $args=array() ) {
    $defaults = array (
        'actual'	=>	'',
        'anterior'	=>	''
    );
    // Parse incoming $args into an array and merge it with $defaults
    $args = wp_parse_args( $args, $defaults );
	
	
	$periodos	=	_esc_getPeriodos();
	$html	=	'<p class="search-box">';
	if(!$hide_label)
		$html	.=		'<label for="periodo">Periodo</label>';
	$html	.=		'<select name="periodo" onchange="this.form.submit()">';
	foreach($periodos as $key=>$periodo){
		$selected	=	'';
		if($key==$filter_periodo)
			$selected	=	' selected="selected"';
		
		$html	.=	'<option value="' . $key .'"' . $selected . '>';
		if(empty($args[$key]))
			$html	.=	$key .': ' . $periodo['jueves']->format('d/m/Y') . '-' . $periodo['miercoles']->format('d/m/Y');
		else
			$html	.=	$args[$key];
		
		$html	.=	'</option>';
	}
	$html	.=		'</select>';
	$html	.=	'</p>';
	return $html;
}
function _esc_filterPeriodos__original($filter_periodo='', $hide_label=false){
	$periodos	=	_esc_getPeriodos();
	$html	=	'<p class="search-box">';
	if(!$hide_label)
		$html	.=		'<label for="periodo">Periodo</label>';
	$html	.=		'<select name="periodo" onchange="this.form.submit()">';
	foreach($periodos as $key=>$periodo){
		$selected	=	'';
		if($key==$filter_periodo)
			$selected	=	' selected="selected"';
		$html	.=	'<option value="' . $key .'"' . $selected . '>' . $key .': ' . $periodo['jueves']->format('d/m/Y') . '-' . $periodo['miercoles']->format('d/m/Y') . '</option>';
	}
	$html	.=		'</select>';
	$html	.=	'</p>';
	return $html;
}
function _esc_getArgsPeriodo($args, $filter_periodo){
	if(!empty($filter_periodo)){
		$periodos	=	_esc_getPeriodos();
		$filter_periodo	=	$periodos[$filter_periodo]['jueves']->getTimestamp();
		$fecha	=	new DateTime();
		$fecha->setTimestamp($filter_periodo);
		/*
		 *	restamos 1 dia para tomar en cuenta el dia "desde"
		*/
		$fecha->modify('-1 day');
		$period_end	=	new DateTime('next wednesday');
		$date_from	=	$fecha->format('Y-m-d');
		/*$fecha->modify('next wednesday');*/
		/*
		 *	adicionamos 1 dia para tomar en cuenta el dia "Hasta"
		*/
		$fecha->modify('next wednesday + 1 day');
		$date_end	=	$fecha->format('Y-m-d');
		$args['date_query']	=	array(
							'column'=>	'post_date',
							'after'	=>	$date_from,
							'before'=>	$date_end 
						);
		/*_print($args['date_query']);*/
	}
	return $args;
}
function _esc_filterEstadosOrden($filter_estado_orden='', $hide_label=false){
	$estados	=	_esc_getEstadosOrder();
		$html	=	'<p class="search-box">';
	if(!$hide_label)
		$html	.=		'<label for="periodo">Estado</label>';
	$html		.=	'<select name="orden_status" onchange="this.form.submit()">';
	$html		.=		'<option value="">---</option>';
	foreach($estados as $key=>$estado)	:	
		$checked	=	'';
		if($filter_estado_orden==$key)
			$checked	=	' selected="selected"';
		$html	.=		'<option value="' . $key . '"' . $checked .'>' . $estado . '</option>';
	endforeach;
	$html	.=	'</select>';
	$html	.=	'</p>';
	echo $html;
}
function _esc_emailHeadLine($dia_entrega_literal){
	ob_start();
?>
<table class="full" bgcolor="#eceff3" align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td bgcolor="#FFFFFF" align="center">
                    <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td height="35"></td>
                      </tr>
                      <!--headline-->
                      <tr>
                        <td align="center" style="font-family: 'Open Sans', Arial, sans-serif; font-size: 22px;color:#FF7973;font-weight: bold;line-height: 28px;"><?php echo $dia_entrega_literal ?></td>
                      </tr>
                      <!--end headline-->
                      <tr>
                        <td height="20"></td>
                      </tr>
                      <!--dotted-->
                      <tr>
                        <td align="center">
                          <table border="0" align="center" cellpadding="0" cellspacing="0">
                            <tr>
                              <td align="center">
                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td bgcolor="#008F29" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                  </tr>
                                </table>
                              </td>
                              <td width="15"></td>
                              <td align="center">
                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td bgcolor="#008F29" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                  </tr>
                                </table>
                              </td>
                              <td width="15"></td>
                              <td align="center">
                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td bgcolor="#008F29" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                  </tr>
                                </table>
                              </td>
                              <td width="15"></td>
                              <td align="center">
                                <table align="center" border="0" cellpadding="0" cellspacing="0">
                                  <tr>
                                    <td bgcolor="#008F29" style="border-radius:5px;font-size:0px; line-height:0px;" height="5" width="5">&nbsp;</td>
                                  </tr>
                                </table>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                      <!--end dotted-->
                      <tr>
                        <td height="20"></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!--end Headline-->
<?php 
	return ob_get_clean();
}
function _esc_emailPlato($plato, $tiempo, $items, $monto){
	ob_start();
?>
<table class="full" bgcolor="#eceff3" align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td bgcolor="#FFFFFF" align="center">
                    <table align="center" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                          <!--right-->
                          <div style="display:inline-block;vertical-align:top;">
                            <table align="center" border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                <td width="300" align="center">
                                  <table class="table-full" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                                    <!--title-->
                                    <tr>
                                      <td style="font-family: 'Open sans', Arial, sans-serif; color:#3b3b3b; font-size:16px; line-height: 20px;font-weight: bold;"><?php echo $plato ?> - <?php echo $tiempo ?></td>
                                      <td style="font-family: 'Open sans', Arial, sans-serif; color:#3b3b3b; font-size:20px; line-height: 20px;font-style: italic; font-weight: bold;">₡<?php echo $monto ?></td>
                                    </tr>
                                    <!--end title-->
                                    <tr>
                                      <td height="5"></td>
                                    </tr>
                                    <!--content-->
                                    <?php echo $items ?>
                                    <!--end content-->
                                    <tr>
                                      <td height="10"></td>
                                    </tr>
                                    <tr>
                                      <td height="20"></td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </div>
                          <!--end right-->
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
<?php	
	return ob_get_clean();
}
function _esc_emailEntrega($precio, $direccion){
	ob_start();
?>
<!--domicilio-->
  <table class="full" bgcolor="#eceff3" align="center" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td bgcolor="#FFFFFF" align="center">
                    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="border-radius:6px;" bgcolor="#f8f8f8" align="center">
                          <table class="table-inner" align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                              <td height="15"></td>
                            </tr>
                            <tr>
                              <tr>
								  <td style="font-family: 'Open sans', Arial, sans-serif; color:#3b3b3b; font-size:16px; line-height: 20px;font-weight: bold;">Envío a Domicilio</td>
								  <td style="font-family: 'Open sans', Arial, sans-serif; color:#3b3b3b; font-size:20px; line-height: 20px;font-style: italic; font-weight: bold;">₡<?php echo $precio ?></td>
                                </tr>
                                <tr>
                              <td height="15"></td>
                            </tr>
							<?php if($precio)	: ?>
                                <tr >
								  <td colspan="2" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:13px; line-height: 20px;"><?php echo $direccion ?></td>
								</tr>
							<?php else : ?>
							<tr >
							  <td colspan="2" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:13px; line-height: 20px;">Recoger en sucursal.</td>
							</tr>
							<?php endif; ?>
                            <tr>
                              <td height="15"></td>
                            </tr>
							<?php if($precio)	: ?>
                            <tr>
							  <td colspan="2" style="font-family: 'Open sans', Arial, sans-serif; color:#7f8c8d; font-size:13px; line-height: 20px;">* hora de entrega sujeta a ruta</td>
							</tr>							
							<?php endif; ?>
                          </table>
                        </td>
                      </tr>
                      <tr>
                        <td height="20"></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!--end domicilio-->
<?php
	return ob_get_clean();
}
function _esc_emailTotal($total){
	ob_start();
?>
<!--total-->
  <table class="full" width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#eceff3">
    <tr>
      <td align="center">
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="350" align="center">
              <table class="table-inner" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
                <tr>
                  <td height="20" bgcolor="#FFFFFF"></td>
                </tr>
                <tr>
                  <td align="center" bgcolor="#FF7973" style="border-bottom-left-radius:6px;border-bottom-right-radius:6px;">
                    <table align="center" border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="20"></td>
                      </tr>
                      <tr>
                        <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                          <!--left-->
                          <div style="display:inline-block;vertical-align:top;">
                            <table align="center" border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                <td width="175" align="center">
                                  <table align="center" width="90%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                      <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#FFFFFF; font-size:16px; line-height: 20px;font-weight: bold; letter-spacing: 2px;">TOTAL ₡<?php echo $total ?></td>
                                    </tr>
                                    <tr>
                                      <td align="center" style="font-family: 'Open sans', Arial, sans-serif; color:#f1f1f1; font-size:12px; line-height: auto;">IVA incluido</td>
                                    </tr>
                                    <tr>
                                      <td height="15"></td>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </div>
                          <!--end left-->
                          <!--[if (gte mso 9)|(IE)]>
                    </td>
                    <td align="center" style="text-align:center;vertical-align:top;font-size:0;">
                    <![endif]-->
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <!--total-->
<?php 
	return ob_get_clean();
}
function _esc_boxPlato($dia, $cliente, $source='backend'){
	$conDieta	=	false;
	if($source!='backend')
		$conDieta			=	_esc_clienteTieneDieta( $cliente );
	$categorias_alimento = _esc_getCategorias();
	ob_start();
?>
<div class="esc-plato box-dashed">
	<a class="esc-plato-delete" href="#">X</a>
	<div class="tipo-plato">
		<div class="esc-form-group esc-tipo-plato">
			<label>Tipo de plato</label>
			<select name="<?php echo $dia ?>[tipo][]" data-categoria="tipo" class="esc-form-control tipo required">
				<option value="">---</option>
				<option value="desayuno">Desayuno</option>
				<option value="almuerzo">Almuerzo</option>
				<option value="cena">Cena</option>
			</select>
		</div>
		<div class="esc-price">
			&#8353;<span class="amount" data-amount="0">0</span>
		</div>
	</div>
	<div class="ingredientes">
<?php 
foreach($categorias_alimento as $key=>$cat)	:
?>
		<div class="esc-form-group esc-hide">
			<label><?php echo $cat['name'] ?><span class="dieta"></span></label>
			<select name="<?php echo $dia ?>[<?php echo $key ?>][]" data-categoria="<?php echo $key ?>" class="esc-form-control">
				<option value="">---</option>
			</select>
		</div>
<?php
endforeach;
?>
	</div>
	<?php if($source!='backend') :	?>
		<?php if(!$conDieta) :	?>
		<div class="message-no-dieta">
			No se puede mostrar el precio por no tener una dieta asignada, 
			favor contactar a servicio al cliente a <a href="tel:71043940">7104-3940</a></div>
		<?php endif;	?>
	<?php endif;	?>
</div>
<?php 
	return ob_get_clean();
}