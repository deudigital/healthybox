jQuery(function($){
 
	// it is a copy of the inline edit function
	var wp_inline_edit_function = inlineEditPost.edit;
 
	// we overwrite the it with our own
	inlineEditPost.edit = function( post_id ) {
 
		// let's merge arguments of the original function
		wp_inline_edit_function.apply( this, arguments );
 
		// get the post ID from the argument
		var id = 0;
		if ( typeof( post_id ) == 'object' ) { // if it is object, get the ID number
			id = parseInt( this.getId( post_id ) );
		}
 
		//if post id exists
		if ( id > 0 ) {
 
			// add rows to variables
			var specific_post_edit_row = $( '#edit-' + id ),
			    specific_post_row = $( '#post-' + id ),
			    product_price = $( '.column-estado', specific_post_row ).find('span').data('status');
			    /*featured_product = false; // let's say by default checkbox is unchecked*/
 
			// check if the Featured Product column says Yes
			/*if( $( '.column-featured', lo ).text() == 'Yes' ) featured_product = true;*/
 
			// populate the inputs with column data
			$( ':input[name="esc_orden_status"]', specific_post_edit_row ).val( product_price );
			/*$( ':input[name="featured"]', specific_post_edit_row ).prop('checked', featured_product );*/
		}
	}
});
var _current_plato_empaquetado;
jQuery(document).ready(function($){
	function _set_empaquetado(_this){
		_current_plato_empaquetado	=	_this;
		var _orden_id	=	_this.data('orden');
		var _plato_id	=	_this.val();
		var _status		=	_this.attr('checked');
		console.log(_orden_id, _plato_id, _status);
		$.ajax({
			url: ajaxurl,
			data: {
				action: "set_plato_empaquetado",
				orden_id: _orden_id,
				plato_id: _plato_id,
				status: _status,
				/*security: "<?php echo wp_create_nonce('set_empaquetado') ?>",*/
			},
			dataType: "json"
		}).done(function( response ){				
			if ( 'finished' == response.status ) {
				console.log(response);
				/*_data	=	response.data;*/
				/*console.log(response.data);*/
				if(response.action=='add')
					_current_plato_empaquetado.closest('ul.platos').addClass('empaquetado');
				else
					_current_plato_empaquetado.closest('ul.platos').removeClass('empaquetado');
			};
		});
	}
	jQuery('.toggle-estado-empaquetado').on('click', function(){
		_set_empaquetado(jQuery(this));
	});
});