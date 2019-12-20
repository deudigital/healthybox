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
	var msg	=	'<div class="register-description">';
	msg	+=	'<strong>Lorem Ipsum is simply dummy text of the printing</strong>';
	msg	+=	'<br/>';
	msg	+=	'been the industry\'s standard dummy text ever since the 1500s, when an unkno';
	msg	+=	'</div>';

	jQuery( '#registerform' ).prepend(msg);
	jQuery( '#registerform #wp-submit' ).val('REGISTRARSE');
	
	jQuery( '#backtoblog > a' ).html('&larr; Regresar');
	
	jQuery( msg ).insertBefore( "#loginform" );
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