<?php	
if ( !defined('ABSPATH') )
    die ( 'No direct script access allowed' );
 
/*
* Toolpress - Custom Fields Wordpress
* V1.0
* By E-Solutions consulting
* http://solutionswebonline.com/tools/toolpress
*/
function esc_add_meta_box(){
	extract( array(	'id'			=>	'esc_meta_box', 
					'title'			=>	__(' Fields','nas'), 
					'callback'		=>	'esc_meta_box_form', 
					'screen'		=>	'plato', 
					'context'		=>	'normal', 
					'priority'		=>	'default', //array('high', 'core', 'default', 'low')
					'callback_args'	=>	NULL
				)
			);
	add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args);
	extract( array(	'id'			=>	'esc_meta_box_alianza', 
					'title'			=>	__(' Fields','nas'), 
					'callback'		=>	'esc_meta_box_alianza_form', 
					'screen'		=>	'alianza'
				)
			);
	add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args);
	extract( array(	'id'			=>	'esc_meta_box_cliente', 
					'title'			=>	__(' Fields','nas'), 
					'callback'		=>	'esc_meta_box_cliente_form', 
					'screen'		=>	'cliente', 
					'context'		=>	'normal', 
					'priority'		=>	'default', //array('high', 'core', 'default', 'low')
					'callback_args'	=>	NULL
				)
			);
	add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args);
	extract( array(	'id'			=>	'esc_meta_box_orden', 
					'title'			=>	__(' Fields','nas'), 
					'callback'		=>	'esc_meta_box_orden_form', 
					'screen'		=>	'orden', 
					'context'		=>	'normal', 
					'priority'		=>	'default', //array('high', 'core', 'default', 'low')
					'callback_args'	=>	NULL
				)
			);
	add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args);
	extract( array(	'id'			=>	'esc_meta_box_status_orden', 
					'title'			=>	__(' Estado','nas'), 
					'callback'		=>	'esc_meta_box_status_orden_form', 
					'screen'		=>	'orden', 
					'context'		=>	'side', 
					'priority'		=>	'default', //array('high', 'core', 'default', 'low')
					'callback_args'	=>	NULL
				)
			);
	add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args);
	extract( array(	'id'			=>	'esc_meta_box_alimento', 
					'title'			=>	__(' Fields','nas'), 
					'callback'		=>	'esc_meta_box_alimento_form', 
					'screen'		=>	'alimento', 
					'context'		=>	'normal', 
					'priority'		=>	'default', //array('high', 'core', 'default', 'low')
					'callback_args'	=>	NULL
				)
			);
	add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args);
}
add_action('add_meta_boxes', 'esc_add_meta_box');
function esc_save_meta_box($post_id){
	if (isset($_POST['esc_save_meta_box'])){
		if ( !wp_verify_nonce($_POST['esc_save_meta_box'], 'esc_save_meta_box')){
			  return $post_id;
		}
	}else{
		return $post_id;
	}
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	  return $post_id;
	/* Check permissions */
	if ('page' == $_POST['post_type']){
	  if (!current_user_can('edit_page', $post_id))
		return $post_id;
	}else{
	  if (!current_user_can('edit_post', $post_id))
		return $post_id;
	}
	/*echo '<pre>' . print_r($_POST,true) . '</pre>';exit;*/
		update_post_meta($post_id, '_esc_week', $_POST['esc_week']);
		update_post_meta($post_id, '_esc_plato_categoria', $_POST['esc_plato_categoria']);
		update_post_meta($post_id, '_esc_precio', stripcslashes($_POST['esc_precio']));
}
add_action('save_post', 'esc_save_meta_box');
function esc_meta_box_form($post){ 
	wp_nonce_field('esc_save_meta_box', 'esc_save_meta_box');
	$_esc_week	=	get_post_meta($post->ID, '_esc_week', true);
	$_week_checked['even']	=	$_esc_week=='even'? ' checked="checked"':'';
	$_week_checked['odd']	=	$_esc_week=='odd'? ' checked="checked"':'';
	$_esc_plato_categoria	=	get_post_meta($post->ID, '_esc_plato_categoria', true);
	$_esc_precio	=	esc_textarea(get_post_meta($post->ID, '_esc_precio', true));
	$_tipos_de_plato	=	array(
								'desayuno'	=> 'Desayuno',
								'almuerzo'	=> 'Almuerzo',
								'cena'		=> 'Cena'
							);
	$_categoria_de_plato	=	array(
								'harina'	=> 'Harina',
								'carne'		=> 'Carne',
								'vegetal'	=> 'Vegetal'
							);
$terms = wp_get_post_terms( $post->ID, array( 'categoria_alimento', 'tipo_plato' ) );
/*_print($terms);*/
?>
<table class="form-table esc">
<tr>
	<th scope="row"><label for="esc_precio">Precio</label></th>
	<th scope="row"><label for="esc_week">Semana</label></th>
	<th colspan="3" scope="row">Categoria</th>
</tr>
<tr>
	<td><input id="esc_precio" name="esc_precio" type="number" value="<?php echo  $_esc_precio ?>" /></td>
	<td>
		<input name="esc_week" type="radio" value="even" <?php echo  $_week_checked['even'] ?> />Par
		<br>
		<br>
		<input name="esc_week" type="radio" value="odd" <?php echo  $_week_checked['odd'] ?> />Impar
	</td>
	<td colspan="3">
		<table class="form-table">
<?php
		$col_th	=	'';
		$col_td	=	'';
		if($_tipos_de_plato){
			foreach($_tipos_de_plato as $key=>$tipo){
				$col_th	.=	'<th>' . $tipo . '</th>';
			}
			echo '<tr>' . $col_th . '</tr>';
			foreach($_categoria_de_plato as $keyc=>$categoria){
				$col_td	=	'';
				foreach($_tipos_de_plato as $key=>$tipo){
					$checked	=	'';			
					if(isset($_esc_plato_categoria[$key])){
						if(in_array($keyc, $_esc_plato_categoria[$key]))
							$checked	=	' checked="checked"';
					}
					$col_td	.=	'<td><input type="checkbox" name="esc_plato_categoria[' . $key. '][]" value="' . $keyc .'"' . $checked . '>' . $categoria . '</td>';
				}
				echo '<tr>' . $col_td . '</tr>';
			}
		}			
?>
		</table>
	</td>
</tr>
</table>
<?php 
}
function esc_meta_box_alianza($post_id){
	if (isset($_POST['esc_meta_box_alianza'])){
		if ( !wp_verify_nonce($_POST['esc_meta_box_alianza'], 'esc_meta_box_alianza')){
			  return $post_id;
		}
	}else{
		return $post_id;
	}
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	  return $post_id;
	/* Check permissions */
	if ('page' == $_POST['post_type']){
	  if (!current_user_can('edit_page', $post_id))
		return $post_id;
	}else{
	  if (!current_user_can('edit_post', $post_id))
		return $post_id;
	}
	update_post_meta($post_id, '_esc_alianza_correo', stripcslashes($_POST['esc_alianza_correo']));
	update_post_meta($post_id, '_esc_alianza_telefono', stripcslashes($_POST['esc_alianza_telefono']));
	update_post_meta($post_id, '_esc_alianza_codigo', stripcslashes($_POST['esc_alianza_codigo']));
}
add_action('save_post', 'esc_meta_box_alianza');
function esc_meta_box_alianza_form($post){
	wp_nonce_field('esc_meta_box_alianza', 'esc_meta_box_alianza');
	$_esc_alianza_correo	=	esc_textarea(get_post_meta($post->ID, '_esc_alianza_correo', true));
	$_esc_alianza_telefono	=	esc_textarea(get_post_meta($post->ID, '_esc_alianza_telefono', true));
	$_esc_alianza_codigo	=	esc_textarea(get_post_meta($post->ID, '_esc_alianza_codigo', true));
?>
<table class="form-table esc">
<tr>
	<th scope="row"><label for="esc_alianza_correo">Correo</label></th>
	<td><input id="esc_alianza_correo" name="esc_alianza_correo" type="text" class="regular-text" value="<?php echo  $_esc_alianza_correo?>" /></td>
</tr>
<tr>
	<th scope="row"><label for="esc_alianza_telefono">Telefono</label></th>
	<td><input id="esc_alianza_telefono" name="esc_alianza_telefono" type="text" class="regular-text" value="<?php echo  $_esc_alianza_telefono?>" /></td>
</tr>
<tr>
	<th scope="row"><label for="esc_alianza_codigo">Codigo de alianza</label></th>
	<td><input id="esc_alianza_codigo" name="esc_alianza_codigo" type="text" class="regular-text" value="<?php echo  $_esc_alianza_codigo ?>" /></td>
</tr>
</table>
<?php 
}
function esc_meta_box_cliente($post_id){
	if (isset($_POST['esc_meta_box_cliente'])){
		if ( !wp_verify_nonce($_POST['esc_meta_box_cliente'], 'esc_meta_box_cliente')){
			  return $post_id;
		}
	}else{
		return $post_id;
	}
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	  return $post_id;
	/* Check permissions */
	if ('page' == $_POST['post_type']){
	  if (!current_user_can('edit_page', $post_id))
		return $post_id;
	}else{
	  if (!current_user_can('edit_post', $post_id))
		return $post_id;
	}
	/*_print($_POST);*/
	$user_login	=	stripcslashes($_POST['esc_cliente_correo']);
	$user_email	=	stripcslashes($_POST['esc_cliente_correo']);
	/*_print($user_login);_print($user_email);*/
	if($_POST['current_action']=='new'){
		if ( ! username_exists( $user_login ) && !email_exists($user_email) ) {
			$user_pass	=	wp_generate_password(16, false);
			$user_id	=	wp_create_user( $user_login, $user_pass, $user_email );
			update_post_meta($post_id, '_esc_cliente_correo', $user_login);
			update_user_meta( $user_id, 'first_name', $_POST[ 'post_title' ] );
		}
	}	
	update_post_meta($post_id, '_esc_cliente_telefono', stripcslashes($_POST['esc_cliente_telefono']));
	update_post_meta($post_id, '_esc_cliente_codigo_alianza', stripcslashes($_POST['esc_cliente_codigo_alianza']));
	update_post_meta($post_id, '_esc_cliente_direccion', $_POST['direccion']);
	$_esc_cliente_dieta	=	get_post_meta($post_id, '_esc_cliente_dieta', true);
	$_old_dieta			=	serialize($_esc_cliente_dieta);
	$_new_dieta			=	serialize($_POST['dieta']);
	if($_new_dieta!=$_old_dieta){
		update_post_meta($post_id, '_esc_cliente_dieta', $_POST['dieta']);
		_esc_actualizarDietaEnOrdenesPendientes($post_id);
	}	
}
add_action('save_post', 'esc_meta_box_cliente');
function esc_meta_box_cliente_form($post){
	global $pagenow;
	wp_nonce_field('esc_meta_box_cliente', 'esc_meta_box_cliente');
	$_esc_cliente_correo			=	get_post_meta($post->ID, '_esc_cliente_correo', true);
	$_esc_cliente_telefono			=	get_post_meta($post->ID, '_esc_cliente_telefono', true);
	$_esc_cliente_codigo_alianza	=	get_post_meta($post->ID, '_esc_cliente_codigo_alianza', true);
	$_esc_cliente_dieta				=	get_post_meta($post->ID, '_esc_cliente_dieta', true);
	$_esc_cliente_direccion			=	get_post_meta($post->ID, '_esc_cliente_direccion', true);
	$args	=	array(
					'taxonomy' => 'categoria_alimento',
					'hide_empty'   => 0
			   );
   $categoria_alimentos = get_categories($args);
   $_aCategorias	=	array();
   foreach($categoria_alimentos as $key=>$categoria){
		$_aCategorias[$categoria->slug]	=	$categoria->name;
   }

	$current_action	=	'edit';
	if($pagenow=='post-new.php')
		$current_action	=	'new';
?>
<input type="hidden" name="current_action" value="<?php echo $current_action ?>" />
<style>
.fieldset {margin-top:50px;}
.fieldset legend{font-size:1.5em;}
.form-table.esc .fieldset {border: 1px solid #000;padding: 10px;position:relative;margin-top:20px}
.form-table.esc .fieldset legend{position:absolute;top:-15px;background-color:#fff;padding:5px;font-weight:700}
.form-table.esc label{display: block;margin-top:10px;margin-bottom:5px;}
.direccion > .dia {margin:10px 0;}
.direccion-entrega {background-color: #eee;padding:15px;}
.direccion-entrega > .dropdowns {display: grid;grid-template-columns: 33% 33% 33%;width:100%}
.direccion-entrega > .dropdowns .combo:not(:first-child) {padding-left: 30px;}
.direccion-entrega > .dropdowns .combo{}
.direccion-entrega > label{display:block;margin-bottom:10px}
.direccion-entrega > .dropdowns .combo > select,
.direccion-entrega > textarea{display: block;width:100%}
.col {display: inline-block;max-width: 45%;min-width: 400px;width: 100%;}
.col.c {max-width: 80%;}
.dieta{max-width:200px}
.dieta thead th{background-color:#e5e5e5;}
.dieta th,
.dieta td{padding:3px;}
.dieta tr th,
.dieta tr td{border:1px solid #000;}
.dieta tbody tr:nth-child(odd) th,
.dieta tbody tr:nth-child(odd) td{background-color:#eee}
.dieta tbody tr th,
.dieta tbody tr td{padding:10px 5px}
.dieta input{text-align:center}
#direcciones{margin-left:15px}
@media(min-width:992px){
	.inner{float:right}
}
</style>
<div class="row">
	<div class="col a">
		<table class="form-table esc">
		<tr>
			<td>
				<label for="esc_cliente_correo">Correo</label>
				<?php if($pagenow=='post-new.php')	: ?>
				<input id="esc_cliente_correo" name="esc_cliente_correo" type="text" class="regular-text" value="<?php echo  $_esc_cliente_correo ?>" required />
				<?php else:
					echo $_esc_cliente_correo;
				endif; ?>
			</td>
		</tr>
		<tr>
			<td>
				<label for="esc_cliente_telefono">Telefono</label>
				<input id="esc_cliente_telefono" name="esc_cliente_telefono" type="text" class="regular-text" value="<?php echo  $_esc_cliente_telefono ?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label for="esc_cliente_correo">Codigo de Alianza</label>
				<input id="esc_cliente_codigo_alianza" name="esc_cliente_codigo_alianza" type="text" class="regular-text" value="<?php echo  $_esc_cliente_codigo_alianza ?>" autocomplete="new-password" />
			</td>
		</tr>
		</table>
	</div>
	<div class="col b">
		<div class="inner">
		<label>Dieta</label>
	<?php 
		/*_esc_form_dieta($_esc_cliente_dieta, true,false);*/
		_esc_form_dieta($_esc_cliente_dieta);
	?>
		</div>
	</div>
	<div class="col c">
		<div class="fieldset">
					<legend>Entrega de Platos</legend>
<?php 
$args	=	array(
				'ajax_url'			=>	'ajaxurl',
				'values'			=>	$_esc_cliente_direccion,
				'copy_from_monday'	=>	true
			);
_esc_form_entrega_platos($args);
?>
		</div>
	</div>
</div>
<?php 
	wp_enqueue_script('suggest');
?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery('#esc_cliente_codigo_alianza').suggest(
			ajaxurl + '?action=se_lookup');
    });
</script>
<?php
}
function esc_meta_status_box_orden($post_id){
	if (isset($_POST['esc_meta_status_box_orden'])){
		if ( !wp_verify_nonce($_POST['esc_meta_box_orden'], 'esc_meta_box_orden')){
			  return $post_id;
		}
	}else{
		return $post_id;
	}
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	  return $post_id;
	/* Check permissions */
	if ('page' == $_POST['post_type']){
	  if (!current_user_can('edit_page', $post_id))
		return $post_id;
	}else{
	  if (!current_user_can('edit_post', $post_id))
		return $post_id;
	}
	update_post_meta($post_id, '_esc_orden_status', $_POST['esc_orden_status']);
	if(isset($_POST['esc_orden_cliente_id']))
		update_post_meta($post_id, '_esc_orden_cliente_id', $_POST['esc_orden_cliente_id']);
}
add_action('save_post', 'esc_meta_status_box_orden');
function esc_meta_box_status_orden_form($post){
	wp_nonce_field('esc_meta_status_box_orden', 'esc_meta_status_box_orden');	
	$_esc_orden_cliente_id		=	get_post_meta($post->ID, '_esc_orden_cliente_id', true);
	$_esc_orden_status			=	get_post_meta($post->ID, '_esc_orden_status', true);
	$estados	=	_esc_getEstadosOrder();

	$output	=	'';
	if(!$_esc_orden_cliente_id){
		$args = array(
			'post_type'   	=>	'cliente',
			'post_status'  	=>	'publish',
			'numberposts'	=>	-1,
		);
		$clientes	=	get_posts( $args );
		$options	=	'';
		if(count($clientes)>0){
			$options	.=	'<option value="">---<option>';
			foreach($clientes as $client){
				$_esc_cliente_correo	=	get_post_meta($client->ID, '_esc_cliente_correo', true);
				$options				.=	'<option value="' . $client-> ID . '">' . $client->post_title . ' &lt;' . $_esc_cliente_correo . '&gt;</option>';
			}
		}
		if(!empty($options)){
			$output	=	'<strong>Cliente</strong><br><select id="esc_orden_cliente_id" name="esc_orden_cliente_id" style="width:100%" required>' . $options . '</select>';
			$output	.=	'<button id="get-dieta" class="esc-button button-secondary btn-ajax" type="button">Cargar Dieta</button>';
		}
	}else{
		$fullname	=	_esc_getFullnameOfCliente($_esc_orden_cliente_id);
		$output	=	'Cliente: ' . $_esc_orden_cliente_id . '<strong>' . $fullname . '</strong>';
	}
	
/*<div class="misc-pub-section misc-pub-post-statuss">
	Cliente: <?php echo $_esc_orden_cliente_id ?><strong><?php echo $fullname; ?></strong>
</div>*/
?>
<div class="misc-pub-section misc-pub-post-statuss"><?php echo $output;	?></div>
<div class="misc-pub-section misc-pub-visibility">
	<strong>Estado</strong><br>
	<select name="esc_orden_status" style="width:100%" required>
						<option value="">---</option>
	<?php	foreach($estados as $key=>$estado)	:	
				$checked	=	'';
				if($_esc_orden_status==$key)
					$checked	=	' selected="selected"';
	?>
		<option value="<?php echo $key ?>"<?php echo $checked ?>><?php echo $estado ?></option>
	<?php 	endforeach; ?>
	</select>
</div>	
<?php 
}
function esc_meta_box_orden($post_id){
	if (isset($_POST['esc_meta_box_orden'])){
		if ( !wp_verify_nonce($_POST['esc_meta_box_orden'], 'esc_meta_box_orden')){
			  return $post_id;
		}
	}else{
		return $post_id;
	}
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	  return $post_id;
	/* Check permissions */
	if ('page' == $_POST['post_type']){
	  if (!current_user_can('edit_page', $post_id))
		return $post_id;
	}else{
	  if (!current_user_can('edit_post', $post_id))
		return $post_id;
	}
	/*update_post_meta($post_id, '_esc_orden_cliente_id', stripcslashes($_POST['esc_orden_cliente_id']));*/
	_esc_processSubmittedFormPedido($_POST);
	update_post_meta($post_id, '_esc_orden_status', $_POST['esc_orden_status']);
}
add_action('save_post', 'esc_meta_box_orden');
function esc_meta_box_orden_form($post){
	wp_nonce_field('esc_meta_box_orden', 'esc_meta_box_orden');	
	$_esc_orden_cliente_id		=	get_post_meta($post->ID, '_esc_orden_cliente_id', true);
	$_esc_orden_args			=	get_post_meta($post->ID, '_esc_orden_args', true);
	$_esc_orden_status			=	get_post_meta($post->ID, '_esc_orden_status', true);
	$conDieta	=	_esc_clienteTieneDieta( $_esc_orden_cliente_id );
	/*_print($_esc_orden_args);*/
	if(!$_esc_orden_args){
		$_esc_orden_args	=	array(
									'lunes'	=>	array(),
									'miercoles'	=>	array(),
								);
	}
	$args = array(
               'taxonomy' => 'categoria_alimento',
			   'hide_empty'   => 0,
           );
	$categorias_plato = get_categories($args);
	$_aCategorias	=	array();
	foreach($categorias_plato as $key=>$categoria){
		$_aCategorias[$categoria->slug]	=	$categoria->name;
	}
	foreach($_esc_orden_args as $dia=>$platos){
		foreach($platos['platos'] as $key=>$plato){
			if(is_array($plato)){
				foreach($plato as $cat=>$alimento){
					if($cat!='tipo' && isset($alimento['alimento_id'])){
						$selected_ids[]	=	$alimento['alimento_id'];
					}
				}
			}
		}
	}
	$ids	=	implode(',', array_unique($selected_ids));
	$posts = get_posts( array(
							'include'   => $ids,
							'post_type' => 'alimento',
							'orderby'   => 'post__in',
						) );
	$alimentos	=	array();
	foreach($posts as $key=>$_post)
		$alimentos[$_post->ID]	=	$_post->post_title;
	/*_print($post->post_date);*/
	$_alimentos	=	_esc_getAlimentos( $post->post_date );
	global $_parImpar;
	if(isset($_REQUEST['test'])){
		_print($_parImpar);
	}
	//_print($_alimentos);
	$dias	=	_esc_get_dias();
	$total	=	0;
	$tiempo	=	array(
					'desayuno'	=>	'Desayuno',
					'almuerzo'	=>	'Almuerzo',
					'cena'	=>	'Cena',
					);
	$detalle_total	=	'';
	$subtotales	=	array();
	$aDetalle_subtotal	=	array();
	/*_print('$conDieta: ' . $conDieta);*/
echo '<div id="container-backend"' . ($conDieta? '':' class="without-dieta"') .'">';
echo '<div id="esc-message" class="update-nag esc-hide"></div>';
	foreach($_esc_orden_args as $dia=>$platos){
		echo '<h2 class="title-entrega">Entrega ' . $dia . '</h2>
				<section>
					<div class="esc-container">
						<div id="' . $dia . '" class="esc-platos boxes">
							<div class="esc-platos-container">';
		$subtotal	=	0;
		$num_plato	=	1;
		$detalle_subtotal	=	'';
		$plato_subtotal	=	'';
		foreach($platos['platos'] as $key=>$plato){/*_print($key);_print($plato);*/
			$monto	=	0;
			$html_tipo	=	'';
			$html_cat_selects	=	'';
			$__tipo	=	$plato['tipo'];
			/*_print($plato);*/
			foreach($plato as $cat=>$alimento){
				if($cat=='tipo'){
						$output	=	'<select name="' .$dia . '[tipo][]" data-categoria="tipo" class="esc-form-control tipo required">';
						$output	.=	'<option value="">---</option>';
						foreach($tiempo as $tkey=>$tvalue){
							$selected	=	'';
							if($alimento==$tkey)
								$selected	=	' selected="selected"';
							$output	.=	'<option value="' . $tkey . '"' . $selected .'>' . $tvalue. '</option>';
						}
						$output	.=	'</select>';
					$html_tipo	.=	'<div class="esc-form-group esc-tipo-plato">';
					$html_tipo	.=		'<label>Tipo de plato</label>';
					/*$html_tipo	.=		'<input type="text" name="' . $dia . '[tipo][]" data-categoria="tipo" class="esc-form-control tipo required" value="' . $cat . '-' . $alimento . '"/>';*/
					$html_tipo	.=		$output;
					$html_tipo	.=	'</div>';
				}else{
					if(!$alimento)
						continue ;
					/*_print('$alimento');_print($alimento);_print($cat);*/
					$alimento_id	=	$alimento['alimento_id'];
					/*_print('$alimento_id-> ' . $alimento_id);*/
					$option_html	=	'<option value="">---</option>';
					/*_print('$alimento_id:' . $alimento_id);*/
					/*if(!empty($alimento_id)){*/
						foreach($_alimentos[$dia][$__tipo][$cat] as $__key=>$_alimento){
							$selected	=	'';
							if($alimento_id==$_alimento['alimento_id'])
								$selected	=	' selected="selected"';
							$option_html	.=	'<option value="' . $_alimento['alimento_id'] . '"' . $selected . '>';
							if(isset($_REQUEST['test']))
								$option_html	.=	$_alimento['alimento_id'] . ':';
							$option_html	.=	$_alimento['alimento_title'] . '</option>';
							$option_html	.=	'</option>';
						}
					/*}*/
					$_add_info	=	'';
					if(isset($_REQUEST['test']))
						$_add_info	=	' - ' . $alimento_id;
					$html_cat_selects	.=	'
					<div class="esc-form-group">
							<label>' . $_aCategorias[$cat] . $_add_info . '<span class="dieta"></span></label>
							<select name="' . $dia . '[' . $cat . '][]" 
									data-value="' . $alimento_id . '" 
									data-categoria="' . $cat . '" 
									class="esc-form-control">								
									' . $option_html . '
							</select>
						</div>';
					$cantidad	=	empty($alimento_id)? 0:1;
					if($alimento['dieta'])
						$cantidad	=	$alimento['dieta'];
					if(isset($alimento['precios']['alimento']) && !empty($alimento['precios']['alimento']))
						$monto +=	$alimento['precios']['alimento'] * $cantidad;
					else
						$monto +=	$alimento['precios']['cat'] * $cantidad;
				}
			}
			echo '<div class="esc-plato box-dashed">
					<a class="esc-plato-delete" href="#">X</a>';
			echo 	'<div class="tipo-plato">
						' . $html_tipo . '
						<div class="esc-price">
							&#8353;<span class="amount" data-amount="' . $monto . '">' . number_format($monto) .'</span>
						</div>
					</div>';
			echo 	'<div class="ingredientes">' .$html_cat_selects . '</div>';
			echo '</div>';
			$detalle_subtotal	.=	'<tr><th>Plato ' . $num_plato . '</th><td>&#8353;' . number_format($monto) . '</td></tr>';
			$aDetalle_subtotal[$dia]['Plato ' . $num_plato]	=	number_format($monto);
			$num_plato++;
			$subtotal	+=	$monto;
		}
		$detalle_total	.=	'<tr><th>' . $dias[$dia] . '</th><td>&#8353;' . number_format($subtotal) . '</td></tr>';
		$total	+=	$subtotal;
		/*_print($dia);*/
		echo '			</div>
						<div class="container-button">
							<button href="javascript:void(0);" type="button" class="btn btn-primary btn-add-plato">AÑADIR OTRO PLATO &plus;</button>
						</div>
					</div>
				</div>
			</section>';
		$total	+=	$platos['entrega']['precio'];
		/*_print($dia . '->' . $subtotal);*/
		$subtotales[$dia]	=	$subtotal;
	}
echo '</div>';
?>
<section>
	<div class="esc-container">
		<div id="entrega" class="esc-platos">
<?php 
/*_print($subtotales);*/
foreach($dias as $dkey=>$vdia)	:	/*_print($dkey);*/
?>
						<div class="box-dashed entrega">
							<h3 class="box-dashed-title">Entrega <?php echo $vdia ?></h3>
							<ul id="entrega-<?php echo $dkey ?>" class="opcion-entrega group-radio">
								<li>
									<label>
										<input type="radio" class="esc-form-control required" 
											name="<?php echo $dkey ?>[entrega]" 
											value="recoger" 
											<?php echo ($_esc_orden_args[$dkey]['entrega']['opcion']=='recoger'? 'checked':'' ) ?>/> Recoger
										<span></span>
									</label>
								</li>
								<li>
									<label>
										<input type="radio" class="esc-form-control required" 
											name="<?php echo $dkey ?>[entrega]" 
											value="domicilio" 
											<?php echo ($_esc_orden_args[$dkey]['entrega']['opcion']=='domicilio'? 'checked':'' ) ?>/> Envío a Domicilio (+ &#8353;1,500)
										<span></span>
									</label>
								</li>
							</ul>
							<table class="table-resumen">
							<tbody id="resumen-platos-<?php echo $dkey ?>">
							<?php 
							/*_print('$aDetalle_subtotal');
							_print($aDetalle_subtotal);*/
								foreach($aDetalle_subtotal[$dkey] as $kdet=>$vdetalle_subtotal){/*_print($kdet);_print($vdetalle_subtotal);*/
									echo '<tr>
											<td colspan="6">'.$kdet.'</td>';
									if($conDieta)
										echo 	'<td data-column="'.$kdet.'"><span class="currency-symbol">&#8353;</span><span class="subtotalamount">'.$vdetalle_subtotal.'</span></td>';
									else
										echo '<td data-column="'.$kdet.'">N/A</td>';
									echo '</tr>';
								}
								$__precio	=	$subtotales[$dkey];
								if($_esc_orden_args[$dkey]['entrega']['precio']>0){
									/*$__precio	=	$subtotales[$dkey] + $_esc_orden_args[$dkey]['entrega']['precio'];*/
									$__precio	+=	$_esc_orden_args[$dkey]['entrega']['precio'];
							?>
								<tr class="entrega">
									<td colspan="6">Entrega</td>
									<td data-column="Subtotal"><span class="currency-symbol">&#8353;</span><span class="subtotalamount"><?php echo number_format($_esc_orden_args[$dkey]['entrega']['precio']) ?></span></td>
								</tr>
								<?php } ?>
								<tr class="subtotal">
									<td colspan="6"><strong>Subtotal</strong></td>
									<?php if($conDieta) : ?>
									<td data-column="Subtotal">
										<span class="currency-symbol">&#8353;</span>
										<span class="subtotalamount"><?php echo number_format($__precio) ?></span>
									</td>
									<?php else :?>
									<td data-column="Subtotal">N/A</td>
									<?php endif;?>
								</tr>
							</tbody>
							</table>
						</div>
<?php 
endforeach;
?>
						<div class="box-dashed resume total">
							<table class="table resume-plato">							
								<tbody>
									<tr>			
										<th>Gran Total</th>
										<td>
											<span class="currency-symbol<?php echo ($conDieta? '':' esc-hide') ?>">&#8353;</span>
											<span id="grantotalamount">
											<?php 
												if($conDieta)
													echo number_format($total);											
												else
													echo 'N/A';
											?>
											</span>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
		</div>
	</div>
</section>
<style>
body{color:#a2a2a2;}
.esc-hide{display:none !important}
.esc-container{font-family: 'Muli', sans-serif;counter-reset: esc_plato;}
.box-dashed {border: 1px dashed #ccc;padding:30px 15px;position: relative;margin-bottom:15px}
.box-dashed-title, 
.box-dashed::before {display: block;position: absolute;content: "";top: -15px;background-color: #fff;font-size: 1em;color: #6f6f6f;font-weight: 600;}
.esc-plato {font-size: 16px;}
.esc-plato:before {counter-increment: esc_plato;content: "Plato " counter(esc_plato);}
.periodo {background-color: #dfdfdf;padding: 5px 40px;border-radius: 25px;display: table;margin: 0 auto 75px;}
a.esc-plato-delete {	position: absolute;right: 25px;top: 20px;line-height: 1;text-decoration: none !important;font-weight: 200;font-size:22px;z-index:1;}
.tipo-plato {}
.ingredientes {margin-top: 5px;}
.title-entrega {text-align: center;font-size: 2em !important;}
.title-entrega {margin-bottom: 30px !important;}
.esc-form-group > label {display: block;font-weight:400;margin-bottom:0 !important;}
.esc-form-group.address{text-align:center;padding:30px 0}
.esc-form-control{width:100%;height:auto;font-family:inherit;font-size:15px;}
.esc-form-control.required.notfound {pointer-events: none;background-color:#ccc}
.esc-form-group.is_required > label::after {color: #96b654;content: " *";}
.esc-form-group.is_required.has-error > label::after {color: #ed7260;}
label.has-error{color:#ed7260}
.esc-price {font-weight:800;font-size:1.25em;color:#6f6f6f;}
.has-error:not(label):not(.notfound){border:1px solid #ed7260}
button, [type="button"], [type="reset"], [type="submit"] {-webkit-appearance: button;}
.btn{border-radius: 50px;text-align: center;text-decoration: none;text-transform: uppercase;font-weight: bold;max-width:100%;cursor:pointer;transition: background 150ms ease-in-out;border: none;box-sizing: border-box;color: #fff;}
.btn {background-color: #ed7260 !important;font-family: inherit;padding: 13px 25px;margin: auto;display: block;outline: none !important;max-width: 100%;}
.resume * {font-family: 'Muli', sans-serif;}
.resume::before {content: "Subtotal";}
.resume > .table {border: none;max-width:700px;width:100%;font-size:1.5em}
.resume > .table td, .resume > .table th {border: none;text-align:left;padding: 0;}
.resume > .table th{font-weight: 700;color:#757575}
.resume > .table td{text-align:right;}
.resume > .table td:first-child{text-align:left;}
.resume > .table th, .resume > .table td {padding: 20px 0 0;}
.resume > .table tfoot th, 
.resume > .table tfoot td {padding-top: 35px;}
.resume > .table tr {border-bottom: 1px solid #757575;}
.table-resumen {width:100%;font-size:1.25em;padding:15px;max-width:500px;margin:auto;}
.table-resumen thead tr th {min-width:15%;border-bottom: 1px solid #000 !important; word-break: normal;}
.table-resumen thead tr th:first-child {min-width:50px;}
.table-resumen thead tr th:nth-child(2) {width:90px;}
.table-resumen thead tr th:last-child {width:90px;}
.table-resumen td, .table-resumen  th {border: none;word-break: break-word;}
.table-resumen td {line-height:1.25}
.table-resumen td:nth-child(2) {text-align:right}
.table-resumen tfoot tr th:first-child {text-align: left;}
.table-resumen + h4 {font-weight: 900;margin-bottom: 20px !important;}
.resume.total:before {content: "Total";}
.table-resumen-plato td{text-align:right}
.opcion-entrega {}
.opcion-entrega > li {list-style-type: none;}
.opcion-entrega > li  a {color: #e47263;display:block;}
.opcion-entrega > li > label {position: relative;padding-left: 23px;overflow:hidden;cursor:pointer;}
.opcion-entrega > li > label input[value="domicilio"]:not(:checked) ~ .disclaimer {display:none;}
.disclaimer {background-color: #f3f3f3;font-size: 14px;color: #6f6f6f;font-style: italic;position:absolute}
input[type=radio]:checked::before {background-color: #e47263;}
.without-dieta .esc-price {text-indent: -9999px;text-align: left;position:relative}
.without-dieta .esc-price::before {content: "N/A";position: absolute;text-indent: 0;display: block;}
.without-dieta .currency-symbol,
.without-dieta .resume:not(.total) {display:none;}
@media(min-width:768px){
	.tipo-plato {display: grid;grid-template-columns: 40% 60%;}
	.esc-platos:not(.boxes) .ingredientes {display: grid;grid-template-columns: repeat(3,32.5%);grid-gap: 10px;}
	.esc-platos:not(.boxes) .tipo-plato .esc-price {text-align: right;padding-top: 35px;}
	a.esc-plato-delete {right: 15px;top: 30px;}
	.box-dashed-title, .box-dashed::before{font-size: 1.25em;top:-15px;margin:0}
	.dropdowns {display: grid;grid-template-columns: 30% 30% 30%;width: 100%;grid-column-gap: 5%;width:100%}
	.btn{min-width: 260px;}
	.opcion-entrega {display: flex;justify-content: center;}
}
.esc-platos.boxes > .esc-platos-container {display: grid;grid-template-columns: 31% 31% 31%;grid-gap: 3%;width: 100%;}
.esc-platos.boxes .tipo-plato {display: flex;flex-direction: column-reverse;margin-bottom:15px;}
.container-button {margin: 15px 0 30px;}
.table-resumen tr.subtotal th, .table-resumen tr.subtotal td {padding-top: 15px;border-top: 1px solid #000;}
.form-table.esc{max-width:100%}
.form-table.esc tbody tr td {vertical-align: top;}
.form-table.esc tr th,
.form-table.esc tr td {border: 1px solid #eee;padding-top: 10px;padding-bottom: 10px;}
.form-table.esc tr td:not(:first-child):not(:nth-child(2)){width:30%}
.capital{text-transform:capitalize;}
</style>
<script type="text/javascript">
var _sin_dieta	=	<?php echo $conDieta? 'false':'true'	?>;
var _with_dieta	=	<?php echo $conDieta? 'true':'false'	?>;
	jQuery(document).ready(function($){
		const new_title	=	'Order #' + jQuery('#post_ID').val();
		jQuery('input[name="post_title"]').val(new_title);
		jQuery('input[name="post_title"]').attr('readonly', true);
		
			$.ajax({
				url: ajaxurl,
				data: {
					action: "get_data",
					cliente_id: '<?php echo $_esc_orden_cliente_id ?>',
					security: "<?php echo wp_create_nonce('get_data') ?>",
					week: "<?php echo  $_parImpar['result']; ?>",
				},
				dataType: "json"
			}).done(function( response ){				
				if ( 'finished' == response.status ) {
					_data	=	response.data;
					console.log(response.data);
					_setGlobal();
				};
			});
	});
</script>
<?php
wp_enqueue_style( 'healthybox-gf', 'https://fonts.googleapis.com/css?family=Muli:200,300,400,600,700,800,900&display=swap', array(), '1.0' );
wp_enqueue_script('healthybox-frontend-js', plugin_dir_url( __DIR__ ) . 'assets/js/backend.js', array( 'jquery' ), '1.0', true );
}
function esc_save_meta_box_alimento($post_id){
	if (isset($_POST['esc_save_meta_box_alimento'])){
		if ( !wp_verify_nonce($_POST['esc_save_meta_box_alimento'], 'esc_save_meta_box_alimento')){
			  return $post_id;
		}
	}else{
		return $post_id;
	}
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
	  return $post_id;
	/* Check permissions */
	if ('page' == $_POST['post_type']){
	  if (!current_user_can('edit_page', $post_id))
		return $post_id;
	}else{
	  if (!current_user_can('edit_post', $post_id))
		return $post_id;
	}
	update_post_meta($post_id, '_esc_alimento_semana', $_POST['esc_alimento_semana']);
	update_post_meta($post_id, '_esc_alimento_dias', $_POST['esc_alimento_dias']);
	update_post_meta($post_id, '_esc_alimento_categoria', $_POST['esc_alimento_categoria']);
	update_post_meta($post_id, '_esc_alimento_precio', $_POST['esc_alimento_precio']);
	update_post_meta($post_id, '_esc_alimento_semana_dias', $_POST['esc_alimento_semana_dias']);
}
add_action('save_post', 'esc_save_meta_box_alimento');
function esc_meta_box_alimento_form($post){
	wp_nonce_field('esc_save_meta_box_alimento', 'esc_save_meta_box_alimento');
	/*$metas	=	get_post_meta($post->ID);/*_print($metas);*/
	$_esc_alimento_semana_dias	=	get_post_meta($post->ID, '_esc_alimento_semana_dias', true);
	/*_print($_esc_alimento_semana_dias);*/
	$_esc_alimento_categoria	=	get_post_meta($post->ID, '_esc_alimento_categoria', true);
	$_esc_alimento_precio		=	get_post_meta($post->ID, '_esc_alimento_precio', true);
	$_tipos_de_alimento	=	array(
								'desayuno'	=> 'Desayuno',
								'almuerzo'	=> 'Almuerzo',
								'cena'		=> 'Cena'
							);
	$_categoria_de_alimento	=	_esc_getCategorias();
?>
<table class="form-table esc alimento-edit">
<tr>
	<th scope="row">Precio</th>
	<th scope="row">Dias</th>
	<th colspan="3" scope="row">Categoria</th>
</tr>
<tr>
	<td>
		<input name="esc_alimento_precio" type="number" value="<?php echo  $_esc_alimento_precio ?>" />		
	</td>
<?php 
	$_semanas	=	array('par', 'impar');
	/*$_dias		=	array('lunes', 'miercoles');*/
	$_dias		=	_esc_get_dias();
	$html		=	'<td><ul class="asignacion-dias">';
	foreach($_semanas as $semana){
		$html	.=	'<li><h3>Semana ' . ucfirst($semana) . '</h3>';
		foreach($_dias as $dia_id=>$dia_literal){
			$html	.=	'<label>';
			$html	.=	'<input name="esc_alimento_semana_dias[' . $semana . '][]" type="checkbox" value="' . $dia_id . '"';
			if(in_array($dia_id, $_esc_alimento_semana_dias[$semana]))
				$html	.=	' checked="checked"';
			/*$html	.=	'/>' . ucfirst($dia);*/
			$html	.=	'/>' . $dia_literal;
			$html	.=	'</label>';
		}
		$html	.=	'</li>';
	}
	$html	.=	'</ul></td>';
	echo $html;
?>
	<td colspan="3">
		<table class="form-table">
<?php
$col_th	=	'';
$col_td	=	'';
if($_tipos_de_alimento){
	foreach($_tipos_de_alimento as $key=>$tipo){
		$col_th	.=	'<th>' . $tipo . '</th>';
	}
	echo '<tr>' . $col_th . '</tr>';
	foreach($_categoria_de_alimento as $keyc=>$categoria){
		$col_td	=	'';
		foreach($_tipos_de_alimento as $key=>$tipo){
			$checked	=	'';			
			if(isset($_esc_alimento_categoria[$key])){				
				if(in_array($keyc, $_esc_alimento_categoria[$key]))
					$checked	=	' checked="checked"';
			}
			$col_td	.=	'<td><input type="checkbox" name="esc_alimento_categoria[' . $key. '][]" value="' . $keyc .'"' . $checked . '>' . $categoria['name'] . '</td>';
		}
		echo '<tr>' . $col_td . '</tr>';
	}
}	
?>
		</table>
	</td>
</tr>
</table>
<?php 
}
add_action( 'categoria_alimento_add_form_fields', 'add_categoria_alimento_field', 10, 2 );
function add_categoria_alimento_field($taxonomy) {
?>
<div class="form-field term-group">
	<label for="featuret-group">Precios</label>
	<ul class="inputs">
		<li>
			<input type="text" class="postform" name="categoria-alimento-precio-desayuno" placeholder="Desayuno">
		</li>
		<li>
			<input type="text" class="postform" name="categoria-alimento-precio-almuerzo" placeholder="Almuerzo">
		</li>
		<li>
			<input type="text" class="postform" name="categoria-alimento-precio-cena" placeholder="Cena">
		</li>
	</ul>
</div>
<?php
}
add_action( 'created_categoria_alimento', 'save_feature_meta', 10, 2 );
function save_feature_meta( $term_id, $tt_id ){
    if( isset( $_POST['categoria-alimento-precio-desayuno'] ) && ’ !== $_POST['categoria-alimento-precio-desayuno'] ){
        $precio = sanitize_title( $_POST['categoria-alimento-precio-desayuno'] );
        add_term_meta( $term_id, 'categoria_alimento_precio_desayuno', $precio, true );
    }
    if( isset( $_POST['categoria-alimento-precio-almuerzo'] ) && ’ !== $_POST['categoria-alimento-precio-almuerzo'] ){
        $precio = sanitize_title( $_POST['categoria-alimento-precio-almuerzo'] );
        add_term_meta( $term_id, 'categoria_alimento_precio_almuerzo', $precio, true );
    }
    if( isset( $_POST['categoria-alimento-precio-cena'] ) && ’ !== $_POST['categoria-alimento-precio-cena'] ){
        $precio = sanitize_title( $_POST['categoria-alimento-precio-cena'] );
        add_term_meta( $term_id, 'categoria_alimento_precio_cena', $precio, true );
    }
}
add_action( 'categoria_alimento_edit_form_fields', 'edit_categoria_alimento_field', 10, 2 );
function edit_categoria_alimento_field( $term, $taxonomy ){
    $categoria_alimento_precio_desayuno	=	get_term_meta( $term->term_id, 'categoria_alimento_precio_desayuno', true );
    $categoria_alimento_precio_almuerzo	=	get_term_meta( $term->term_id, 'categoria_alimento_precio_almuerzo', true );
    $categoria_alimento_precio_cena	=	get_term_meta( $term->term_id, 'categoria_alimento_precio_cena', true );
?>
<tr class="form-field term-group-wrap">
	<th scope="row"><label for="categoria-alimento-precio-desayuno">Precio Desayuno</label></th>
	<td>
		<input type="text" class="postform" name="categoria-alimento-precio-desayuno" value="<?php echo $categoria_alimento_precio_desayuno ?>">		
	</td>
</tr>
<tr class="form-field term-group-wrap">
	<th scope="row"><label for="categoria-alimento-precio-almuerzo">Precio Almuerzo</label></th>
	<td>
		<input type="text" class="postform" name="categoria-alimento-precio-almuerzo" value="<?php echo $categoria_alimento_precio_almuerzo ?>">		
	</td>
</tr>
<tr class="form-field term-group-wrap">
	<th scope="row"><label for="categoria-alimento-precio-cena">Precio Cena</label></th>
	<td>
		<input type="text" class="postform" name="categoria-alimento-precio-cena" value="<?php echo $categoria_alimento_precio_cena ?>">		
	</td>
</tr>
<?php
}
add_action( 'edited_categoria_alimento', 'update_categoria_alimento_meta', 10, 2 );
function update_categoria_alimento_meta( $term_id, $tt_id ){
	if( isset( $_POST['categoria-alimento-precio-desayuno'] ) && ’ !== $_POST['categoria-alimento-precio-desayuno'] ){
        $precio = sanitize_title( $_POST['categoria-alimento-precio-desayuno'] );
        update_term_meta( $term_id, 'categoria_alimento_precio_desayuno', $precio );
    }
	if( isset( $_POST['categoria-alimento-precio-almuerzo'] ) && ’ !== $_POST['categoria-alimento-precio-almuerzo'] ){
        $precio = sanitize_title( $_POST['categoria-alimento-precio-almuerzo'] );
        update_term_meta( $term_id, 'categoria_alimento_precio_almuerzo', $precio );
    }
	if( isset( $_POST['categoria-alimento-precio-cena'] ) && ’ !== $_POST['categoria-alimento-precio-cena'] ){
        $precio = sanitize_title( $_POST['categoria-alimento-precio-cena'] );
        update_term_meta( $term_id, 'categoria_alimento_precio_cena', $precio );
    }
}
add_filter('manage_edit-categoria_alimento_columns', 'add_categoria_alimento_column' );
function add_categoria_alimento_column( $columns ){
    $columns['categoria_alimento_precio_desayuno'] = __( 'Precio Desayuno', 'my_plugin' );
    $columns['categoria_alimento_precio_almuerzo'] = __( 'Precio Almuerzo', 'my_plugin' );
    $columns['categoria_alimento_precio_cena'] = __( 'Precio Cena', 'my_plugin' );
    return $columns;
}
add_filter('manage_categoria_alimento_custom_column', 'add_categoria_alimento_column_content', 10, 3 );
function add_categoria_alimento_column_content( $content, $column_name, $term_id ){
/*_print(func_get_args());*/
	if(!in_array($column_name, array('categoria_alimento_precio_desayuno', 'categoria_alimento_precio_almuerzo', 'categoria_alimento_precio_cena'))){
		return $content;
	}
    /*if( $column_name !== 'categoria_alimento_precio' ){
        return $content;
    }*/
    $term_id = absint( $term_id );	
	switch($column_name){
		case 'categoria_alimento_precio_desayuno':
			$content	=	get_term_meta( $term_id, 'categoria_alimento_precio_desayuno', true );
			break;
		case 'categoria_alimento_precio_almuerzo':
			$content	=	get_term_meta( $term_id, 'categoria_alimento_precio_almuerzo', true );
			break;
		case 'categoria_alimento_precio_cena':
			$content	=	get_term_meta( $term_id, 'categoria_alimento_precio_cena', true );
			break;		
	}	
    /*return get_term_meta( $term_id, 'categoria_alimento_precio', true );*/
    return $content;
}
add_filter( 'manage_edit-categoria_alimento_sortable_columns', 'add_categoria_alimento_column_sortable' );
function add_categoria_alimento_column_sortable( $sortable ){
    $sortable[ 'categoria_alimento_precio_desayuno' ] = 'Precio Desayuno';
    $sortable[ 'categoria_alimento_precio_almuerzo' ] = 'Precio Almuerzo';
    $sortable[ 'categoria_alimento_precio_cena' ] = 'Precio Cena';
    return $sortable;
}