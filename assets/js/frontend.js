var _data=[];
var platos={};
var data;
var aliments	=	['harinas', 'proteina', 'fruta', 'vegetal'];
var _montos	=	[];
var _current_dia	=	'';
var _current_tipo	=	'';
var _current_address_dia	=	'';
var _container_ids	=	['lunes', 'miercoles'];
function _setGlobal(){
	aliments	=	[];
	jQuery.each( _data.categorias, function( cat, tax ) {
		aliments.push(cat)
	});
}
function resizeJquerySteps(container=''){
	container	=	container || '.wizard .body.current .esc-container';
	jQuery('.wizard .content').animate({
			height: jQuery( container ).height()+150
		}, "slow");
}
(function( $ ){
	var form = $("#form-wizard");
	if (typeof form == 'undefined')
		form	=	false
	function _withPlatos(){
		var _dayWithPlato	=	true;
		var _count=0;
		jQuery.each(_container_ids, function( index, ele ) {
			/*console.log( index, ele, platos[ele].length, platos[ele] );*/
			/*_dayWithPlato	=	true;*/
			if(platos[ele].length>0){
				_count++;
				/*return true;*/
				/*if(_dayWithPlato)
					_dayWithPlato	=	false;*/
			}
		});
		/*console.log('_withPlatos', notEmpty);*/
		/*return false;*/
		/*return _dayWithPlato;*/
		return _count>0;
	}
	function _isPlatoEmpty(elements, container_id){
		var containers	=	['lunes', 'miercoles'];
		let _index	=	containers.indexOf( container_id );
		if( _index==-1 )
			return false;
		var platos	=	jQuery('#' + container_id + ' .esc-plato');
		if(platos.length>1)
			return false;
		var elements	=	jQuery('#' + container_id + ' .required').not('.notfound');	
		var empty	=	true;
		elements.each(function(){
			if(jQuery(this).val().length>0){
				if(empty)
					empty	=	false;
			}		
		});
		return empty;
	}
	function _isValid(container_id){
		var elements	=	jQuery('#' + container_id + ' .required').not('.notfound');	
		if(_isPlatoEmpty( elements, container_id ))
			return true;
		elements.removeClass('has-error');
		var valid	=	true;
		var tagName	=	'';
		var type	=	'';
		var closest_group	=	'';
		var inputs	=	['checkbox', 'radio'];
		elements.each(function(){
			type	=	jQuery(this).attr('type');
			tagName	=	jQuery(this).get(0).tagName.toLowerCase();
			switch( tagName ){
				case 'textarea':
				case 'select':
						if(jQuery(this).val().length==0){
							jQuery(this).addClass('has-error');
							if(valid)
								valid	=	false;
						}
					break;
				case 'input':
						let _index	=	inputs.indexOf( type );
						if( _index>-1 ){
							closest_group	=	jQuery(this).closest('.group-' + type);
							let checked	=	closest_group.find('.required:checked');
							if(checked.length==0){
								jQuery(this).parent().addClass('has-error');
								if(valid)
									valid	=	false;
							}
						}else{
							if(jQuery(this).val().length==0){
								jQuery(this).addClass('has-error');
								if(valid)
									valid	=	false;
							}
						}
					break;
			}			
		});
		return valid;
	}
	function _isElementsValidForSave(container_id){
		var _with_dieta	=	_data.cliente.dieta || false;
		if( _with_dieta )
			var elements	=	jQuery('#' + container_id + ' .required').not('.notfound');
		else
			var elements	=	jQuery('#' + container_id + ' select.esc-form-control').not('.esc-hide');
		
		var valid	=	true;
		var tagName	=	'';
		var type	=	'';
		var closest_group	=	'';
		var inputs	=	['checkbox', 'radio'];
		/*console.log(elements);*/
		elements.each(function(){
			type	=	jQuery(this).attr('type');
			tagName	=	jQuery(this).get(0).tagName.toLowerCase();
			
			switch( tagName ){
				case 'textarea':
				case 'select':
						if(jQuery(this).val().length==0){
							/*jQuery(this).addClass('has-error');*/
							if(valid)
								valid	=	false;
						}
					break;
				case 'input':
						let _index	=	inputs.indexOf( type );
						if( _index>-1 ){
							closest_group	=	jQuery(this).closest('.group-' + type);
							let checked	=	closest_group.find('.required:checked');
							if(checked.length==0){
								/*jQuery(this).parent().addClass('has-error');*/
								if(valid)
									valid	=	false;
							}
						}else{
							if(jQuery(this).val().length==0){
								/*jQuery(this).addClass('has-error');*/
								if(valid)
									valid	=	false;
							}
						}
					break;
			}			
			console.log(type, tagName, valid);
		});
		/*console.log('_isElementsValidForSave', valid, container_id);*/
		return valid;
	}
	function _saveInfoPlatos(container_id){
		var _platos	=	jQuery('#' + container_id + ' .esc-plato');
		var elements;
		var data	=	[];
		platos[container_id]=	[];
		
		if(!_isElementsValidForSave(container_id))
			return ;
		
		_platos.each(function(){
			elements	=	jQuery(this).find('.esc-form-control');	
			elements.each(function(){
				data[jQuery(this).data( 'categoria' )]	=	jQuery(this).val();
			});
			platos[container_id].push(data);
			data	=	[];
		});
	}
	function _saveInfoPlatos__original(container_id){
		var _platos	=	jQuery('#' + container_id + ' .esc-plato');
		var elements;
		var data	=	[];
		platos[container_id]=	[];
		_platos.each(function(){
			elements	=	jQuery(this).find('.esc-form-control');	
			elements.each(function(){
				data[jQuery(this).data( 'categoria' )]	=	jQuery(this).val();
			});
			platos[container_id].push(data);
			data	=	[];
		});
	}
	function _confirmarPedido(){/*console.log('platos', platos)*/
		var _row;
		var _plato;
		var monto	=	0;
		var subtotal	=	0;
		var total	=	0;
		var _with_dieta	=	_data.cliente.dieta || false;
		
/*console.log(platos);*/
		/*jQuery.each( ['lunes', 'miercoles'], function( _key, dia ) {*/
		
		jQuery.each( _container_ids, function( _key, dia ) {
			jQuery('#resumen-platos-' + dia).html('');
			_row	=	'';
			subtotal=	0;
/*console.log( _key, dia, platos[dia]);*/
			jQuery.each( platos[dia], function( key, obj ) {/*console.log('key', key, 'obj', obj);*/		
				monto	=	0;
				_row	+=	'<tr>';
				_row	+=	'<td data-column="plato">' + (key+1) + '</td>';
				k=0;
				var res = Object.keys(obj)				
					.map(function(k){
						let _return	=	[k, obj[k]];
						k++;
						return _return;
					});
				let tiempo	=	res[0][1];
				jQuery.each( res, function(keey, el){/*console.log('keey', keey, 'el', el);*/
					if(el[0]=='tipo')
						_row	+=	'<td data-column="tipo">' + el[1] + '</td>';
					else{
						var alimento	=	_getDataValue(dia, tiempo, el[0], el[1] );
						if(alimento){
							_row	+=	'<td data-column="' + el[0] + '">' + alimento.alimento_title + '</td>';
							let _precio	=	_getPrecio(el[1], el[0], dia, obj.tipo)
							monto	+=	parseFloat(_precio);
						}else
							_row	+=	'<td data-column="' + el[0] + '">---</td>';
					}
				});
				/*if(!_sin_dieta){*/
				if(_with_dieta){
					_row	+=	'<td data-column="Precio">&#8353;' + _formatThisNumber(monto) + '</td>';
					subtotal	+=	parseFloat( monto );
				}else
					_row	+=	'<td data-column="Precio">N/A</td>';
				_row	+=	'</tr>';
			});
			jQuery('#resumen-platos-' + dia).append( _row );
			/*if(!_sin_dieta)*/
			if(_with_dieta)
				jQuery('#resumen-platos-' + dia).next().find( '.subtotalamount:first' ).html(_formatThisNumber(subtotal));
			else
				jQuery('#resumen-platos-' + dia).next().find( '.subtotalamount:first' ).html('N/A');
			total	+=	parseFloat( subtotal );
			if(jQuery('#entrega-' + dia + ' input:checked').val()=='domicilio')
				total	+=	1500;
		});
		/*if(!_sin_dieta)*/
		if(_with_dieta)
			jQuery('#grantotalamount').html( _formatThisNumber(total) );
		else
			jQuery('#grantotalamount').html( 'N/A' );
		
		jQuery.each( _container_ids, function( _key, dia ) {
			if(platos[dia].length==0){
				let _input	=	jQuery('#entrega-' + dia).find('input[value=\'recoger\']');
				_input.prop('checked', true);
			/*	_input.change();*/
				jQuery('#entrega-' + dia).hide();
				jQuery('#entrega-' + dia).prev().hide();
			}	
			else{
				jQuery('#entrega-' + dia).show();
				jQuery('#entrega-' + dia).prev().show();
			}
		});
		setTimeout(function(){
			resizeJquerySteps('#entrega');			
		}, 500);
	}
	function _getDataValue(dia, tiempo, categoria, alimento_id ){
		if(!tiempo)
			return {'alimento_title':'', 'alimento_precio':0};
		const alimentos	=	_data.alimentos[dia][tiempo][categoria];
		if(!alimentos)
			return {'alimento_title':'', 'alimento_precio':0};
		var row	=	alimentos.filter(x => x.alimento_id === parseInt(alimento_id));
		return row[0];
	}
	function _checkGlobalVar(_this){
		dia	=	_this.closest('.esc-platos').attr('id');
		tipo=	_this.closest('.esc-plato').find('select[data-categoria="tipo"]').val();
		if(!_data.alimentos[dia] || !_data.alimentos[dia][tipo])
			return ;
		_current_dia	=	dia;
		_current_tipo	=	tipo;
	}
	function _fillDropdown(parent, tipo){
		dia	=	parent.closest('.esc-platos').attr('id');
		if(!_data.alimentos[dia] || !_data.alimentos[dia][tipo])
			return ;
		_current_dia	=	dia;
		_current_tipo	=	tipo;
		var _amount	=	parent.find('.amount:first');
		_amount.data( 'amount', 0 );
		_amount.html( 0 );
		var _with_dieta	=	_data.cliente.dieta || false;
		var _select;
		jQuery.each(aliments, function( index, ele ) {
			var option = new Option('Loading...', '');
			_select	=	parent.find('select[data-categoria="' + ele + '"]');
			_select.html('').append(option);
			_select.removeClass('has-error');
			_select.removeClass('notfound');
			_select.removeClass('required');
			_select.closest('.esc-form-group').removeClass('esc-hide');
			_select.parent().find('.dieta').html('');
			if(_data.alimentos[dia][tipo][ele]){
				option = new Option('-- Seleccionar --', '');
				_select.html('').append(option);
				if(_with_dieta){
					if(parseInt(_data.cliente.dieta[tipo][ele])>0){
						console.log('dieta',tipo,ele, _data.cliente.dieta[tipo][ele]);
						_select.addClass('required');
						let _qty	=	parseInt(_data.cliente.dieta[tipo][ele]);
						if(_qty>1)
							_qty	=	' - ' + _qty + ' porciones';
						else
							_qty	=	' - ' + _qty + ' porci&oacute;n';
						_select.parent().find('.dieta').html( _qty );						
					}else{
						_select.addClass('notfound');
						_select.closest('.esc-form-group').addClass('esc-hide');
					}
				}			
				jQuery.each(_data.alimentos[dia][tipo][ele], function(key, alimento){
					let _value	=	alimento.alimento_id;
					/*let _text	=	alimento.alimento_id + ':'+ alimento.alimento_title;*/
					let _text	=	alimento.alimento_title;
					option = new Option(_text, _value);
					_select.append(option);
				});
			}else{
				_select.addClass('notfound');
				_select.closest('.esc-form-group').addClass('esc-hide');
				var option = new Option('Sin Datos', '');
				_select.html('').append(option);			
			}
		});
	}
	function _calculatePlatoAmount(_this){
		var plato	=	_this.closest(".esc-plato");
		var elems	=	plato.find('.ingredientes select');
		var monto	=	0;
		_checkGlobalVar(_this);
		console.log('----- Plato -----');
		jQuery.each(elems,function(){
			let _precio	=	_getPrecio( jQuery(this).val(), jQuery(this).data( 'categoria' ) );
			monto = monto + parseFloat( _precio );
		});
		var _amount	=	plato.find('.amount:first');
		_amount.data( 'amount', monto );
		_amount.html( _formatThisNumber(monto));
	}
	function _calculateResumeSubtotalPlatos( platos ){
		var amounts	=	platos.find('.amount');
		var monto	=	0;
		var plato_id	=	1;
		var resume	=	platos.next().find('.resume-plato:first').html('');
		resume.html('');
		_row	=	'<tbody>';
		jQuery.each(amounts,function(){
			if(jQuery(this).data('amount')!=''){
				var value = parseFloat(jQuery(this).data('amount'));
				monto = monto + value;
				_row	+=	'<tr>';
				_row	+=	'<th>Plato ' + plato_id + '</th>';
				_row	+=	'<td>&#8353;' + _formatThisNumber(value) + '</td>';
				_row	+=	'</tr>';
			}
			plato_id++;
		});
		_row	+=	'</tbody>';
		_row	+=	'<tfoot>';
		_row	+=	'<tr>';
		_row	+=	'<th>SubTotal' + '</th>';
		_row	+=	'<td><span class="subtotal">&#8353;' + _formatThisNumber(monto) + '</span></td>';
		_row	+=	'</tr>';
		_row	+=	'</tfoot>';
		resume.append( _row );		
		_montos[platos.attr('id')]	=	monto;
	}
	function _calculateResumeSubtotal(_this){
		var platos	=	_this.closest(".esc-platos");
		var amounts	=	platos.find('.amount');
		var monto	=	0;
		var plato_id	=	1;
		var resume	=	platos.next().find('.resume-plato:first').html('');
		resume.html('');
		_row	=	'<tbody>';
		jQuery.each(amounts,function(){
			if(jQuery(this).data('amount')!=''){
				var value = parseFloat(jQuery(this).data('amount'));
				monto = monto + value;
				_row	+=	'<tr>';
				_row	+=	'<th>Plato ' + plato_id + '</th>';
				_row	+=	'<td>&#8353;' + _formatThisNumber(value) + '</td>';
				_row	+=	'</tr>';
			}
			plato_id++;
		});
		_row	+=	'</tbody>';
		_row	+=	'<tfoot>';
		_row	+=	'<tr>';
		_row	+=	'<th>SubTotal' + '</th>';
		_row	+=	'<td><span class="subtotal">&#8353;' + _formatThisNumber(monto) + '</span></td>';
		_row	+=	'</tr>';
		_row	+=	'</tfoot>';
		resume.append( _row );		
		_montos[platos.attr('id')]	=	monto;
	}
	function _formatThisNumber(num){
		var nStr	=	num.toFixed(0);
		nStr += '';
		x = nStr.split('.');
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}
	function _getPrecio(alimento_id, categoria, dia, tipo){
		let _dia	=	dia || _current_dia;
		let _tipo	=	tipo || _current_tipo;
		if(!_tipo)
			return 0;
		let alimentos	=	_data.alimentos[_dia][_tipo][categoria];
		if(!alimentos)
			return 0;
		var row	=	alimentos.filter(x => x.alimento_id === parseInt(alimento_id));
		if(row.length==0)
			return 0;
		_alimento	=	row[0];
		var msg_precio	=	categoria + '[' + _tipo + ']'
		var _precio	=	_data.categorias[categoria]['precios'][_tipo];
		var _origin_precio	=	'Precio de categoria:';
		if(_alimento.alimento_precio!=''){
			_precio	=	_alimento.alimento_precio;
			_origin_precio	=	'Precio de alimento:';
		}
		var cantidad	=	0;
		if(_data.cliente.dieta){
			cantidad	=	parseFloat(_data.cliente.dieta[_tipo][categoria]);
		}
		if(cantidad>0){
			console.log('(' + alimento_id + ') ', _origin_precio, _precio + ' * ' + cantidad + ' = ' + (_precio * cantidad));
			_precio	=	_precio * cantidad;
		}else
			console.log('(' + alimento_id + ') ',_origin_precio,  _precio , 'sin dieta' );
		return _precio;
	}
	function _displayAddress(input){
		let _input_closest_address	=	input.closest('.opcion-entrega').find('.address');
		console.log(input.val());
		if(input.is(':checked') && input.val()=='domicilio'){
			_input_closest_address.removeClass('esc-hide');
		}else{
			_input_closest_address.addClass('esc-hide');
		}
	}
	function _enableEditDirection(dia){
		jQuery('#container-address-' + dia ).removeClass('esc-hide');
		_current_address_dia	=	dia
	}
	function _updatePrices(){
		jQuery.each( ['lunes', 'miercoles'], function( _key, dia ) {
			_saveInfoPlatos(dia);
			let _platos	=	jQuery('#' + dia + ' .esc-plato');
			jQuery.each( _platos, function( _key, dia ) {
				_updateDropdownMessageDieta(jQuery(this), jQuery(this).find('select.tipo:first').val());
				_calculatePlatoAmount(jQuery(this).find(' .ingredientes select:first'));
			});			
			_calculateResumeSubtotalPlatos( jQuery('#' + dia) );
		})
		if(_with_dieta)
			jQuery('#wizard').removeClass('without-dieta');
		else
			jQuery('#wizard').addClass('without-dieta');
	}
	function _updateDropdownMessageDieta(parent, tipo){
		dia	=	parent.closest('.esc-platos').attr('id');
		if(!_data.alimentos[dia] || !_data.alimentos[dia][tipo])
			return ;
/*
		_current_dia	=	dia;
		_current_tipo	=	tipo;
		*/
		var _amount	=	parent.find('.amount:first');
		_amount.data( 'amount', 0 );
		_amount.html( 0 );
		var _with_dieta	=	_data.cliente.dieta || false;
		var _select;
		jQuery.each(aliments, function( index, ele ) {
			_select	=	parent.find('select[data-categoria="' + ele + '"]');
			_select.parent().find('.dieta').html( '' );
			if(_data.alimentos[dia][tipo][ele]){
				if(_with_dieta){
					if(parseInt(_data.cliente.dieta[tipo][ele])>0){
						console.log('dieta',tipo,ele, _data.cliente.dieta[tipo][ele]);
						let _qty	=	parseInt(_data.cliente.dieta[tipo][ele]);
						if(_qty>1)
							_qty	=	' - ' + _qty + ' porciones';
						else
							_qty	=	' - ' + _qty + ' porci&oacute;n';
						_select.parent().find('.dieta').html( _qty );
						_select.closest('.esc-form-group').removeClass('esc-hide');
					}else
						_select.closest('.esc-form-group').addClass('esc-hide');
				}			
			}
		});
	}
if(form.length){
	form.steps({
		headerTag: "h2",
		bodyTag: "section",
		transitionEffect: "slideLeft",
		titleTemplate: "<span class=\"number\">#index#.</span><span class=\"overtitle\">#title#</span>",
		labels: {
			finish: "CONFIRMAR",
			next: "SIGUIENTE",
			previous: "ANTERIOR",
			loading: "Cargando ..."
		},
		onInit: function (event, currentIndex) {
			setTimeout(function(){
				resizeJquerySteps();			
			}, 500);
		},
		onStepChanging: function (event, currentIndex, newIndex){
			if (currentIndex > newIndex){
				return true;
			}
			if (currentIndex < newIndex){
				$(".body:eq(" + newIndex + ") label.error", form).remove();
				$(".body:eq(" + newIndex + ") .error", form).removeClass("error");
			}
			var valid	=	false;
			if(_isValid(jQuery('.body:eq(' + currentIndex + ') .esc-platos:first').attr('id'))){
				_saveInfoPlatos(jQuery('.body:eq(' + currentIndex + ') .esc-platos:first').attr('id'));
				valid	=	true;
			}
			if(valid && newIndex==2 ){
				if(!_withPlatos()){
					alert('Debe adicionar minimo un plato para continuar!');
					return false;
				}
				/*console.log('platos', platos);*/
				/*console.log('onStepChanging');*/
				_confirmarPedido();
			}
			return valid;
		},
		onStepChanged: function (event, currentIndex, priorIndex) {
			resizeJquerySteps();
		},
		onFinishing: function (event, currentIndex){
			return _isValid(jQuery('.body:eq(' + currentIndex + ') .esc-platos:first').attr('id'));
		},
		onFinished: function (event, currentIndex){
			form.submit();
		}
	});
}	
	jQuery('.btn-add-plato').on('click',function(){
		var _closest_platos	=	jQuery(this).closest('.esc-platos');
		var newel = _closest_platos.find('.esc-plato:last').clone();
		jQuery(newel).find('.has-error').removeClass('has-error')
		jQuery(newel).find('.notfound').removeClass('notfound')		
		jQuery(newel).find('.tipo').val('')
		jQuery(newel).find('.amount').html('0')
		jQuery(newel).find('.amount').data( 'amount', 0 );
		jQuery(newel).find('.ingredientes').find('.esc-form-group').addClass('esc-hide')
		jQuery(newel).insertAfter('#' + _closest_platos.attr('id') + ' .esc-plato:last');
		setTimeout(function(){
			resizeJquerySteps();			
		}, 500);
	});
	jQuery("body").on("click", ".open-modal", {}, function(event){
		var _modal	=	jQuery(this).data('modal');
		jQuery('body').addClass('esc-open-modal ' + _modal);
		jQuery('.esc-modal').addClass('esc-hide');
		jQuery('.esc-modal.'+ _modal).removeClass('esc-hide');
		console.log(_modal);
		switch(_modal){
			case 'dieta-cliente':
				jQuery('#form-dieta').html('cargando...');
				jQuery.ajax({
					url: my_ajax_object.ajax_url,
					data: {
						action: "get_box_dieta",
						cliente: _data.cliente.id,
					},
					dataType: "json"
				}).done(function( response ){				
					if ( 'finished' == response.status ) {					
						/*console.log(response);*/
						jQuery('#form-dieta').html(response.html);				
					};
				}).fail(function() {
					jQuery('#form-dieta').html('');
				}).always(function() {
				});
				break;
			case 'direcciones-entrega':
				jQuery('#direcciones .direccion').addClass('esc-hide');
				_enableEditDirection(jQuery(this).data('dia'));
				break;
		}
		jQuery("html, body").animate({ scrollTop: 0 }, "slow");
		return false;
    });
/*	jQuery("body").on("click", ".open-modal-dieta-cliente", {}, function(event){
		jQuery('body').addClass('esc-open-modal dieta-cliente');
		jQuery('#form-dieta').html('cargando...');
		$.ajax({
			url: my_ajax_object.ajax_url,
			data: {
				action: "get_box_dieta",
				cliente: _data.cliente.id,
			},
			dataType: "json"
		}).done(function( response ){				
			if ( 'finished' == response.status ) {					
				jQuery('#form-dieta').html(response.html);				
			};
		}).fail(function() {
			jQuery('#dieta').html('');
	  }).always(function() {
			jQuery("html, body").animate({ scrollTop: 0 }, "slow");
		  });
		return false;
    });*/
	jQuery("body").on("click", "#save-dieta", {}, function(event){
		jQuery.ajax({
			url: my_ajax_object.ajax_url,
			type: 'post',
			dataType: 'json',
			contentType: 'application/x-www-form-urlencoded',
			data:jQuery('#form-dieta').serialize() + '&action=update_dieta&cliente=' + _data.cliente.id,
			beforeSend: function( xhr ) {
				jQuery('#save-dieta').addClass('processing');
			}
		})
		.done(function( response ){
				console.log(response);
				console.log(response.status);
				console.log(response.dieta);
			if ( 'finished' == response.status ) {
				_data.cliente.dieta	=	response.dieta;
				_with_dieta	=	response.with_dieta;
				_updatePrices();
				/*jQuery('#entrega-' + _current_address_dia).find('.address-detalles:first').html(response.detalles);
				jQuery('body').removeClass('esc-open-modal');*/
			};
		}).always(function() {
			jQuery('#save-dieta').removeClass('processing');
			jQuery('body').removeClass('esc-open-modal');
		});;
		return false;
    });
	jQuery("body").on("click", "#save-address", {}, function(event){
		var direccion	=	{};
		direccion['provincia']	=	jQuery('select[name="direccion[' + _current_address_dia + '][provincia]"]').val();
		direccion['canton']		=	jQuery('select[name="direccion[' + _current_address_dia + '][canton]"]').val();
		direccion['distrito']	=	jQuery('select[name="direccion[' + _current_address_dia + '][distrito]"]').val();
		direccion['detalles']	=	jQuery('textarea[name="direccion[' + _current_address_dia + '][detalles]"]').val();
		console.log(direccion);
		jQuery.ajax({
			url: my_ajax_object.ajax_url,
			data: {
				action: "update_direccion",
				dia: _current_address_dia,
				direccion: direccion
			},
			dataType: "json"
		}).done(function( response ){				
			if ( 'finished' == response.status ) {
				console.log(response);
				jQuery('#entrega-' + _current_address_dia).find('.address-detalles:first').html(response.detalles);
				jQuery('body').removeClass('esc-open-modal');
			};
		});
		return false;
    });
	jQuery("body").on("click", ".esc-modal-overlay, .esc-modal-close", {}, function(event){
		jQuery('body').removeClass('esc-open-modal');
		return false;
    });
	jQuery("body").on("click", "a.esc-plato-delete", {}, function(event){		
		var platos	=	jQuery(this).closest(".esc-platos");
		let _height_plato	=	jQuery(this).closest().height();
		jQuery(this).closest(".esc-plato").remove();
		let current_height_content	=	jQuery('.wizard .content:first').height();
		jQuery('.wizard .content:first').height( current_height_content - _height_plato );
		_calculateResumeSubtotalPlatos( platos )
		resizeJquerySteps();				
		return false;
    });
	jQuery('body').on("change", "select.tipo", {}, function(event){
		_fillDropdown(jQuery(this).closest('.esc-plato'), jQuery(this).val());
	});
	jQuery("body").on("change", ".ingredientes select", {}, function(event){
		_calculatePlatoAmount(jQuery(this));
		_calculateResumeSubtotal(jQuery(this));
		setTimeout(function(){
			resizeJquerySteps();			
		}, 500);
	});	
	jQuery("body").on("change", ".opcion-entrega input", {}, function(event){
		_confirmarPedido();
		_displayAddress(jQuery(this));
	});	
})( jQuery );