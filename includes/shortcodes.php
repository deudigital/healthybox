<?php	
if ( !defined('ABSPATH') )
    die ( 'No direct script access allowed' );

add_action('init', '_esc_init_shortcode_form_submitted');
function _esc_init_shortcode_form_submitted(){
	if(is_admin() || !$_POST )
		return;
	
	/*_print($_POST);*/
}
function pre_process_shortcode() {
	if (!$_POST) return;
	
	_esc_processSubmittedFormPedido($_POST);
}
add_action('template_redirect','pre_process_shortcode',1);
add_shortcode('_form_order', '_esc_shortcode_form_order' );
function _esc_shortcode_form_order(){
	$_tipos_de_alimento	=	array(
								'desayuno'	=> 'Desayuno',
								'almuerzo'	=> 'Almuerzo',
								'cena'		=> 'Cena'
							);
	$dias	=	array(
								'lunes'	=> 'Lunes',
								'miercoles'	=> 'Miercoles',
							);
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

	$_direccion		=	get_post_meta($cliente->ID, '_esc_cliente_direccion', true);
	$direcciones	=	_esc_getDireccionesFormateadas($_direccion);
	$parimpar		=	_esc_parImpar();
	$conDieta		=	_esc_clienteTieneDieta( $cliente->ID );
	$fecha_mensaje	=	_esc_getPeriodoEntregaPedido();
	ob_start();
?>
<div id="container-wizard">
	<button id="button-edit-dieta" type="button" href="javascript:void(0)" class="button-edit-dieta open-modal" data-modal="dieta-cliente" disabled>
		<?php if($conDieta)	:	?>
			Editar 
		<?php else:	?>
			AÑADIR
		<?php endif;?>
		
		Dieta
	</button>
	<div class="info-updated">
	ultima actualicion: 20/12/2019 11:00am
	</div>
	<div id="wizard"<?php echo $conDieta? '':' class="without-dieta"' ?>>
	
		<form id="form-wizard" method="post" action="<?php the_permalink(); ?>">
<?php 

foreach($dias as $dkey=>$vdia)	:
?>
			<h2>Entrega <?php echo $vdia ?></h2>
			<section>
				<div class="periodo">Estos platos se entregar&aacute;n el <strong><?php echo $fecha_mensaje[$dkey] ?></strong></div>
				<div class="esc-container">
					<h3 class="step-title">Platos</h3>
					<div id="<?php echo $dkey ?>" class="esc-platos">
						<div class="esc-plato box-dashed">
							<a class="esc-plato-delete" href="#">X</a>
							<div class="tipo-plato">
								<div class="esc-form-group esc-tipo-plato">
									<label>Tipo de plato</label>
									<select name="<?php echo $dkey ?>[tipo][]" data-categoria="tipo" class="esc-form-control tipo required">
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
	foreach($_categoria_de_plato as $key=>$cat)	:
?>
								<div class="esc-form-group esc-hide">
									<label><?php echo $cat ?><span class="dieta"></span></label>
									<select name="<?php echo $dkey ?>[<?php echo $key ?>][]" data-categoria="<?php echo $key ?>" class="esc-form-control">
										<option value="">---</option>
									</select>
								</div>
<?php
	endforeach;
?>
							</div>
							<?php if(!$conDieta) :	?>
							<div class="message-no-dieta">No se puede mostrar el precio por no tener una dieta asignada, favor contactar a servicio al cliente a <a href="tel:22222222">2222-2222</a></div>
							<?php endif;	?>
						</div>
						<button type="button" class="btn btn-primary btn-add-plato">AÑADIR OTRO PLATO &plus;</button>
					</div>
					<div class="box-dashed resume">
						<table class="table resume-plato">							
							<tfoot>
								<tr>			
									<th>Subtotal</th>
									<td><span class="subtotal">&#8353;0</span></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</section>
<?php 
endforeach;

?>
			<h2>Confirmar</h2>
			<section>
				<div class="esc-container">
					<div id="entrega" class="esc-platos">
<?php 
foreach($dias as $dkey=>$vdia)	:
?>
						<div class="box-dashed entrega">
							<h3 class="box-dashed-title">Entrega <?php echo $vdia ?> 21 de octubre</h3>
							<table class="table-resumen">
							<thead>
								<tr>
									<th>Plato</th>
									<th>Tipo</th>
<?php			foreach($_categoria_de_plato as $key=>$cat)	:	?>
									<th><?php echo $cat ?></th>
<?php 			endforeach;	?>									
									<th>Precio</th>
								</tr>
							</thead>
							<tbody id="resumen-platos-<?php echo $dkey ?>"></tbody>
							<tfoot>
								<tr>
									<th colspan="6">Subtotal</th>
									<td data-column="Subtotal"><span class="currency-symbol">&#8353;</span><span class="subtotalamount">0</span></td>
								</tr>
							</tfoot>
							</table>

							<h4>Entrega</h4>
							<ul id="entrega-<?php echo $dkey ?>" class="opcion-entrega group-radio">
								<li>
									<label>
										<input type="radio" class="esc-form-control required" name="<?php echo $dkey ?>[entrega]" value="recoger" checked/> Recoger
										<span></span>
									</label>
								</li>
								<li>
									<label>
										<input type="radio" class="esc-form-control required" name="<?php echo $dkey ?>[entrega]" value="domicilio"/> Envío a Domicilio (+ &#8353;1,500)
										<span></span>
										<div class="disclaimer">Hora de entrega sujeta a ruta.</div>
									</label>
								</li>
								<li class="address esc-hide">
									<!--<span class="address-detalles" data-dia="<?php echo $dkey ?>"><?php echo $_direccion[$dkey]['detalles'] ?></span>-->
									<span class="address-detalles" data-dia="<?php echo $dkey ?>"><?php echo $direcciones[$dkey] ?></span>
									<a class="open-modal" data-dia="<?php echo $dkey ?>" href="#" data-modal="direcciones-entrega">Editar</a>
								</li>
							</ul>
						</div>
<?php 
endforeach;
?>

						
						<div class="box-dashed resume total">
							<table class="table resume-plato">							
								<tbody>
									<tr>			
										<th>Gran Total</th>
										<td><span class="currency-symbol">&#8353;</span><span id="grantotalamount">0</span></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					
					
				</div>
			</section>
		</form>
	</div>
</div>
<div class="esc-modal-overlay"></div>
<div class="esc-modal direcciones-entrega esc-hide">
<a href="javascript:void(0)" class="esc-modal-close">X</a>
<?php 
$args	=	array(
				'ajax_url'		=>	'my_ajax_object.ajax_url',
				'button_save'	=>	true,
				'values'		=>	$_direccion
			);
_esc_form_entrega_platos($args);

?>

</div>
<div class="esc-modal dieta-cliente esc-hide">
	<a href="javascript:void(0)" class="esc-modal-close">X</a>
	<div class="box-dashed edit-dieta">
	<form id="form-dieta"></form>
		<div class="esc-form-group address">
			<button id="save-dieta" type="button" class="esc-button btn btn-ajax">Guardar Cambios en Dieta</button>
		</div>
	</div>
</div>
<script type="text/javascript">	
var _sin_dieta	=	<?php echo $conDieta? 'false':'true'	?>;
jQuery(document).ready(function($){
	$.ajax({
		url: my_ajax_object.ajax_url,
		data: {
			action: "get_data",
			security: "<?php echo wp_create_nonce('get_data') ?>",
<?php if(isset($_REQUEST['semana']))	:	?>
			week: "<?php echo $_REQUEST['semana'] ?>"
<?php endif; ?>
		},
		dataType: "json"
	}).done(function( response ){				
		if ( 'finished' == response.status ) {
			_data	=	response.data;
			console.log(response.data);
			_setGlobal();
			jQuery('#button-edit-dieta').removeAttr('disabled');
		};
	});
});
</script>
<?php
	return ob_get_clean();

}


