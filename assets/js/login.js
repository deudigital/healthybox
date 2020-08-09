jQuery(document).ready(function(){
	function _isAcceptedPrivacyPolitic(){
		var _checked	=	jQuery('#accept_privay_politic').is(':checked');
		if(!_checked){
			jQuery('#accept_privay_politic').closest('.container-privacity').addClass('required');
		}
		return _checked;
	}
	jQuery('#registerform #wp-submit').attr('disabled', true);
	jQuery( '.info-personal' ).prevAll().remove();
	var text_register_form	=	'<div class="register-description">';
	text_register_form	+=	'Nos encanta que estés aquí, paga solo por lo que comes, te personalizamos hasta el valor de tu plato. Completa tu registro para comenzar a recibir tus comidas a un precio justo y personalizado. ';
	text_register_form	+=	'Si tienes alguna duda escríbenos a nuestro <a href="https://wa.me/50671043940">WhatsApp</a> y con todo gusto te ayudaremos';
	text_register_form	+=	'</div>';
	jQuery( '#registerform' ).prepend(text_register_form);
	jQuery( '#registerform #wp-submit' ).val('REGISTRARSE');	
	jQuery( '#backtoblog > a' ).html('&larr; Regresar');
	var text_login_form	=	'<div class="social-icons">';
	text_login_form	+=	'<a href="https://www.facebook.com/HealthyBoxcr/" target="_blank" class="facebook">facebook</a>';
	text_login_form	+=	'<a href="https://www.instagram.com/healthyboxcr/" target="_blank" class="instagram">Instagram</a>';
	text_login_form	+=	'</div>';
	text_login_form	+=	'<div class="register-description">';
	text_login_form	+=	'Con Healthy Box comerás comida que vas a amar por su sabor y porque te ayudará a lograr tus objetivos, siempre de la mano de tu nutricionista.';
	text_login_form	+=	'</div>';
	jQuery( text_login_form ).insertBefore( "#loginform" );
	jQuery( '#loginform #wp-submit' ).val('INGRESAR');
	jQuery( '#loginform  + #nav' ).prepend('<h3>¿NO TIENES CUENTA?</h3>');
/*	jQuery( "a[href*='lostpassword'" ).hide();*/
	jQuery('[for="user_login"]').html('Correo electr&oacute;nico');
	jQuery('[for="user_pass"]').html('Contraseña');
	jQuery("body").on("change", "#accept_privay_politic", {}, function(event){
		if(jQuery(this).is(':checked'))
			jQuery('#registerform #wp-submit').attr('disabled', false);
		else
			jQuery('#registerform #wp-submit').attr('disabled', true);	
	});	
});