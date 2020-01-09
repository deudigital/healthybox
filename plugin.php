<?php

/*
Plugin Name: Healthy Box
Plugin URI: https://deudigital.com/projects/healthy-box/
Description: Custom plugin for manage platos.
Version: 1.0
Author: Jaime Isidro
Author URI:  https://deudigital.com
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	
	require_once('wp-list-tables.php');
	require_once('includes/wp-list-tables/comando-produccion.php');
	require_once('includes/wp-list-tables/empaque.php');
	require_once('includes/wp-list-tables/direccion.php');
}
if (!defined('ESCHB_URL'))
	define('ESCHB_URL', plugin_dir_url( __FILE__ ));
if (!defined('ESCHB_DIR'))
	define('ESCHB_DIR', plugin_dir_url( __DIR__ ));
	
class HB_Plugin {

	// class instance
	static $instance;

	// cliente WP_List_Table object
	public $clientes_obj;
	public $comandoProduccion_obj;
	public $empaque_obj;
	public $direccion_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu_page_produccion' ] );
		add_action( 'admin_menu', [ $this, 'plugin_menu_page_empaque' ] );
		add_action( 'admin_menu', [ $this, 'plugin_menu_page_direccion' ] );
		add_action( 'admin_menu', array( $this, 'remove_profile_menu'));
		
		add_action( 'admin_bar_menu',    array( $this, 'esc_hb_fb_admin_bar_menu' ), 999 );
		add_action( 'wp_before_admin_bar_render', array( $this, 'ya_do_it_admin_bar_remove'));

		add_action( 'admin_init', array( $this, '_esc_admin_init'));		

		add_action( 'template_redirect', array($this, 'v_forcelogin') );
		
		add_action( 'init', array( $this, 'install' ), 5 );
		add_action( 'init', array($this,'init') );
		
		/*add_filter( 'login_redirect', array($this, '_esc_login_redirect'), 999, 3);*/
		add_filter( 'login_headerurl', array($this, '_eschb_login_headerurl'));
		add_action( 'login_head', array($this, '_esc_login_head') );
		add_filter( 'gettext', array($this, 'remove_lostpassword_text') );
		add_filter( 'login_link_separator', array($this, '_esc_login_link_separator') );
		add_filter( 'register', array($this, '_esc_register') );

		add_action('login_head', array($this, '_eschb_blog_favicon'));
		/*add_action('login_head', array($this, '_eschb_login_head_after_reset_password'));*/
		add_action( 'after_password_reset', array($this, '_eschb_login_head_after_reset_password' ));
		
		add_action( 'wp_loaded', array( __CLASS__, 'process_reset_password' ), 20 );
		add_action( 'woocommerce_customer_reset_password', 'action_woocommerce_reset_password', 10, 1 );
		
		add_action('wp_head', array($this, '_eschb_blog_favicon'));
		add_action('admin_head', array($this, '_eschb_blog_favicon'));
		/*add_action( 'admin_enqueue_scripts', 'rv_custom_wp_admin_style_enqueue' );*/
		add_action( 'register_form', array($this, 'crf_registration_form' ));
		
		add_filter( 'registration_redirect', array($this, '_esc_registration_redirect'));
		
		
		add_action( 'user_register', array($this, 'crf_user_register' ));
		
		add_filter( 'wp_new_user_notification_email', array($this, '_eschb_wp_new_user_notification_email'), 100, 3);
		add_filter( 'wp_mail_content_type', array($this, '_eschb_wp_mail_content_type'));
		
		require_once('includes/functions.php');
		require_once('includes/shortcodes.php');
		require_once('includes/custom-fields.php');
		require_once('includes/ajax.php');
		require_once('includes/wp-list-tables.php');

		add_action( 'wp_enqueue_scripts',  array($this, '_esc_enqueue_scripts'), 999 );		
        add_action('admin_enqueue_scripts', array($this, '_esc_admin_enqueue_scripts'));
		
		add_filter( 'editable_roles', array($this, '_eschb_editable_roles') );
	}

	function _eschb_editable_roles($all_roles ){
		$healthybox_roles	=	array('cliente','cocina', 'healthybox_servicio_cliente', 'healthybox_manager');
		$roles	=	array();
		foreach($all_roles as $key=>$role){
			if(in_array($key, $healthybox_roles))			
				$roles[$key]	=	$role;
		}
		return $roles;
	}
	function _eschb_blog_favicon() {
		/*echo '<link rel="shortcut icon" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon.png" >';*/
/*
?>
<link rel="apple-touch-icon" sizes="57x57" href="apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
<link rel="manifest" href="manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="ms-icon-144x144.png">
<meta name="theme-color" content="#ffffff">
<?php
*/
	
		$tags = '<link rel="apple-touch-icon" sizes="57x57" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-57x57.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="60x60" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-60x60.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="72x72" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-72x72.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="76x76" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-76x76.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="114x114" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-114x114.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="120x120" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-120x120.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="144x144" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-144x144.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="152x152" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-152x152.png">';
		$tags .= '<link rel="apple-touch-icon" sizes="180x180" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/apple-icon-180x180.png">';
		$tags .= '<link rel="icon" type="image/png" sizes="192x192"  href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/android-icon-192x192.png">';
		$tags .= '<link rel="icon" type="image/png" sizes="32x32" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/favicon-32x32.png">';
		$tags .= '<link rel="icon" type="image/png" sizes="96x96" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/favicon-96x96.png">';
		$tags .= '<link rel="icon" type="image/png" sizes="16x16" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/favicon-16x16.png">';
		$tags .= '<link rel="manifest" href="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/manifest.json">';
		$tags .= '<meta name="msapplication-TileColor" content="#ffffff">';
		$tags .= '<meta name="msapplication-TileImage" content="' . plugin_dir_url( __FILE__ ) . 'assets/images/favicon/ms-icon-144x144.png">';
		$tags .= '<meta name="theme-color" content="#ffffff">';
		echo $tags;
	}

	function _eschb_wp_mail_content_type( $content_type ) {
		return 'text/html';
	}
	function _eschb_wp_new_user_notification_email($wp_new_user_notification_email, $user, $blogname ){	
		/*$email	=	file(plugin_dir_url( __FILE__ ) . '/includes/emails/new_user_notification_email.html');*/
		$email	=	file_get_contents(plugin_dir_url( __FILE__ ) . '/includes/emails/new_user_notification_email.html');
		$key = get_password_reset_key( $user );
		$message	=	str_replace('{{EMAIL}}', $user->user_login, $email);
		$message	=	str_replace('{{LINK_CHANGE_PASSWORD}}', network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ), $message);
		$wp_new_user_notification_email['subject']	=	__( '[%s] Nuevo Usuario' );
		$wp_new_user_notification_email['message']	=	$message;
		return $wp_new_user_notification_email;
	}
	function _esc_login_redirect($redirect_to, $requested_redirect_to, $user ){
		/*_print(func_get_args());exit;*/
		$redirect_to	=	admin_url('/');
		if ( is_wp_error( $user ) )
			$redirect_to	=	admin_url();
		else{		
			/*if(is_user_logged_in()){*/
				$user		=	wp_get_current_user();_print($user);
				/*$valid_roles=	[ 'administrator', 'cliente' ];*/
				_print($user->roles);
				$valid_roles=	[ 'cliente'];
				$the_roles = array_intersect( $valid_roles, $user->roles );_print($the_roles);
				if (empty( $the_roles ) ) {
					/*wp_redirect( home_url( '/wp-login.php' ) );
					exit;*/
					
					$redirect_to	=	home_url();
				}
				_print($redirect_to);
				exit;
			/*}*/
		}
		return $redirect_to;
	}
	function _esc_login_head(){
		wp_enqueue_style( 'healthybox-gf', 'https://fonts.googleapis.com/css?family=Muli:200,300,400,600,700,800,900&display=swap', array(), '1.0' );
		wp_enqueue_style( 'healthybox-glbl', plugin_dir_url( __FILE__ ) . 'assets/css/global.css', array(), '1.0' );
		wp_enqueue_style( 'healthybox-login-css', plugin_dir_url( __FILE__ ) . 'assets/css/login.css', array(), '1.0' );
		
		wp_enqueue_script( 'jquery' );
		wp_localize_script( 'jquery', 'login_ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		wp_enqueue_script('healthybox-login-js', plugin_dir_url( __FILE__ ) . 'assets/js/login.js', array( 'jquery' ), '1.0', true );	
		wp_print_scripts();
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		$image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
		echo '<style>.login h1 a {background-image:url(' . $image[0] . ');}</style>';
	}
	function _eschb_login_headerurl($login_header_url ){
		return home_url();
	}
	
	// Remove lost password url from login page
	function remove_lostpassword_text ( $text ) {
		if ($text == 'Lost your password?'){$text = '';}
			return $text;
	 }
	function _esc_login_link_separator($separator){
		return '';
	}
	function _esc_register($registration_url){
		$registration_url	=	sprintf( '<a href="%s">%s</a>', esc_url( wp_registration_url() ), __( 'Registrarse' ) );
		return $registration_url;
	}
	function crf_registration_form() {
		/*$inputs	=	ob_get_clean();*/
		$esc_cliente_telefono = !empty( $_POST['esc_cliente_telefono'] ) ?  $_POST['esc_cliente_telefono']  : '';
		$esc_cliente_codigo_alianza = !empty( $_POST['esc_cliente_codigo_alianza'] ) ? $_POST['esc_cliente_codigo_alianza']  : '';

		?>	
		<div class="info-personal box-dashed">
			<input type="hidden" name="user_login" id="user_login" class="input" value="<?php echo esc_attr( wp_unslash( $user_login ) ); ?>" autocapitalize="off"  />
			<p>
				<input type="text" name="user_name" id="user_name" class="input" 
				value="<?php echo esc_attr( wp_unslash( $user_name ) ); ?>" size="50" autocapitalize="off" required placeholder="Nombre Completo" 				 
				/>
				<label for="user_name"><?php _e( 'Nombre Completo' ); ?></label>
			</p>
			<p>
				<input type="email" name="user_email" id="user_email" class="input" value="<?php echo esc_attr( wp_unslash( $user_email ) ); ?>" size="25" placeholder="Correo Electronico"
				onkeyup="document.registerform.user_login.value = this.value" 
				 onblur="document.registerform.user_login.value = this.value"
				 />
				<label for="user_email"><?php _e( 'Correo Electronico' ); ?></label>
			</p>
			
			<p>
				<input type="text" id="esc_cliente_telefono" name="esc_cliente_telefono" value="<?php echo esc_attr( $esc_cliente_telefono ); ?>" class="input" placeholder="Telefono"/>
				<label for="esc_cliente_telefono"><?php esc_html_e( 'Telefono', 'crf' ) ?></label>
			</p>
			<p>
				<input type="text" id="esc_cliente_codigo_alianza" name="esc_cliente_codigo_alianza" value="<?php echo esc_attr( $esc_cliente_codigo_alianza ); ?>" class="input" placeholder="Codigo de Alianza"/>
				<label for="esc_cliente_codigo_alianza"><?php esc_html_e( 'Codigo de Alianza', 'crf' ) ?></label>
			</p>
		</div>
<?php
$args	=	array(
				'copy_from_monday'	=>	true
			);
	_esc_form_entrega_platos($args);
?>
		<div class="box-dashed dieta-cliente">
			<p>Ingresa la cantidad de porciones en los diferentes grupos de alimentos 
			por tiempo de comida que tu nutricionista te indic&oacute;.</p>
<?php 
		_esc_form_dieta();
	?>
		</div>
		<div class="box-dashed container-privacity">
			<label for="acepto-politica-privacidad group-checkbox">
				<input type="checkbox" id="accept_privay_politic" name="acepto-politica-privacidad" />Acepto la <a href="<?php echo home_url('politica-privacidad') ?>" target="_blank">pol&iacute;tica de privacidad</a>
			</label>
		</div>
<?php

	}
	function crf_user_register( $user_id ) {
		$post_data = array(
			'post_title'   => $_POST['user_name'],
			'post_status'  => 'publish',
			'post_type' 	=> 'cliente',
			'meta_input'   => array(
				'_esc_cliente_telefono'			=>	$_POST['esc_cliente_telefono'],
				'_esc_cliente_codigo_alianza'	=>	$_POST['esc_cliente_codigo_alianza'],
				'_esc_cliente_direccion'		=>	$_POST['direccion'],
				'_esc_cliente_correo'			=>	$_POST['user_email'],
				'_esc_cliente_dieta'			=>	$_POST['dieta'],
				'_esc_cliente_user_id'			=>	$user_id
			),
		);
		$post_id = wp_insert_post( $post_data );
		
		update_user_meta( $user_id, 'first_name', $_POST[ 'user_name' ] );
		update_user_option($user_id, 'default_password_nag', true, true);
		/*$user_pass	=	wp_generate_password(12, false);*/
		$user_pass	=	'S9dcut&mOlHR(d3^DySSgj%(';
		
         // Set up the Password change nag.
         wp_new_user_notification($user_id, $user_pass);
		$creds	=	array(
							'user_login'	=>	$_POST['user_email'], 
							'user_password'	=>	$user_pass, 
							'remember' => true
						);
		$user	=	wp_signon($creds);
		if ( is_wp_error( $user ) )
			echo $user->get_error_message();
		
		wp_set_current_user( $user_id, $user_login );
		wp_set_auth_cookie( $user_id, true, false );
		wp_redirect( site_url('/pedido/') ); 
		exit;
	}	
	function _esc_registration_redirect( $registration_redirect ){
		$registration_redirect	=	site_url();
		return $registration_redirect;
	}
	function v_forcelogin() {

		// Exceptions for AJAX, Cron, or WP-CLI requests
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		// Redirect unauthorized visitors
		if ( ! is_user_logged_in() ) {
			// Get visited URL
			$url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
			$url .= '://' . $_SERVER['HTTP_HOST'];
			// port is prepopulated here sometimes
			if ( strpos( $_SERVER['HTTP_HOST'], ':' ) === FALSE ) {
				$url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
			}
			$url .= $_SERVER['REQUEST_URI'];

			/**
			 * Bypass filters.
			 *
			 * @since 3.0.0 The `$whitelist` filter was added.
			 * @since 4.0.0 The `$bypass` filter was added.
			 * @since 5.2.0 The `$url` parameter was added.
			 */
			$bypass = apply_filters( 'v_forcelogin_bypass', false, $url );
			$whitelist = apply_filters( 'v_forcelogin_whitelist', array() );

			if ( preg_replace( '/\?.*/', '', $url ) !== preg_replace( '/\?.*/', '', wp_login_url() ) && ! $bypass && ! in_array( $url, $whitelist ) ) {
				// Determine redirect URL
				$redirect_url = apply_filters( 'v_forcelogin_redirect', $url );
				// Set the headers to prevent caching
				nocache_headers();
				// Redirect
				wp_safe_redirect( wp_login_url( $redirect_url ), 302 ); exit;
			}
		}else{
			$user	=	wp_get_current_user();
			$roles	=	( array ) $user->roles;
			/*if(in_array('cliente', $roles)){
				wp_redirect(home_url('/', 'http'), 301);
				exit;
			}*/
			if(!is_admin() && !in_array('cliente', $roles)){
				wp_redirect(admin_url('/', 'http'), 301);
				exit;
			}	
		}
	}
	function _eschb_login_head_after_reset_password( $user ){
		$creds	=	array(
						'user_login'	=>	$user->user_login, 
						'user_password'	=>	$_POST['pass1'], 
						'remember' 		=>	true
					);
		$user	=	wp_signon($creds);
		if ( is_wp_error( $user ) )
			echo $user ->get_error_message();
		
		wp_set_current_user( $user->ID, $user->user_login );
		wp_set_auth_cookie( $user->ID, true, false );
		wp_redirect( site_url('/pedido/') );
		exit;
	}
	function _esc_admin_enqueue_scripts(){
		global $admin_page_hooks;
		$screen = get_current_screen();
		
		wp_enqueue_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'assets/css/admin/admin.css', false, '1.0.0' );
		
		if ( empty( $screen ) || empty( $admin_page_hooks['empaque'] ) || empty( $admin_page_hooks['produccion'] ) ) {
			return;
		}

		if ( $screen->base == 'toplevel_page_empaque' || $screen->base == 'toplevel_page_produccion' ) {
			wp_enqueue_script( 'healthybox-admin', plugins_url('/assets/js/admin/admin.js', __FILE__), array( 'jquery' ), '', true );
		}
		wp_enqueue_script('quick-edit-script', plugin_dir_url(__FILE__) . 'assets/js/admin/post-quick-edit-script.js', array('jquery','inline-edit-post' ));
	}
	function _esc_enqueue_scripts(){
		wp_enqueue_style( 'healthybox-glbl', plugin_dir_url( __FILE__ ) . 'assets/css/global.css', array(), '1.0' );
		global $post;
		if ( !has_shortcode( $post-> post_content, '_form_order' ) )
			return ;

		wp_enqueue_style( 'healthybox-steps-css', plugin_dir_url( __FILE__ ) . 'assets/css/jquery.steps.css', array(), '1.0' );
		wp_enqueue_style( 'healthybox-css', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', array(), '1.0' );
		wp_enqueue_style( 'healthybox-gf', 'https://fonts.googleapis.com/css?family=Muli:200,300,400,600,700,800,900&display=swap', array(), '1.0' );
		
		wp_enqueue_script('healthybox-steps-js', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.steps.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script('healthybox-validate-js', plugin_dir_url( __FILE__ ) . 'assets/js/jquery.validate.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script('healthybox-wizard-js', plugin_dir_url( __FILE__ ) . 'assets/js/wizard.js', array( 'healthybox-steps-js' ), '1.0', true );


		wp_enqueue_script('healthybox-frontend-js', plugin_dir_url( __FILE__ ) . 'assets/js/frontend.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'healthybox-frontend-js', 'my_ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

		wp_enqueue_script('healthybox-quick-edit-script', plugin_dir_url(__FILE__) . '/post-quick-edit-script.js', array('jquery','inline-edit-post' ));

	}	
	public function init(){
		date_default_timezone_set('America/Halifax');
		$posttypes	=	array(	'alimento'	=>	array(
													'singular'	=>	'Alimento',
													'plural'	=>	'Alimentos',
													'supports' 	=> array('title'),
													'rewrite' 	=> array(	'slug' => 'alimentos','with_front' => false),
													'show_in_menu'	=>	true
												),
								'orden'	=>	array(
													'singular'	=>	'Orden',
													'plural'		=>	'Ordenes',
													'supports' 	=> array('title'),
													'rewrite' 			=> array(	'slug' => 'ordenes','with_front' => false),
													'show_in_menu'	=>	true
												),
								'cliente'	=>	array(
													'singular'	=>	'Cliente',
													'plural'		=>	'Clientes',
													'supports' 	=> array('title'),
													'rewrite' 			=> array(	'slug' => 'clientes','with_front' => false),
													'show_in_menu'	=>	true
												),
								'alianza'	=>	array(
													'singular'	=>	'Alianza',
													'plural'		=>	'Alianzas',
													'supports' 	=> array('title'),
													'rewrite' 			=> array(	'slug' => 'alianzas','with_front' => false),
													'show_in_menu'	=>	true
												),
								);
		foreach($posttypes as $slug=>$values){
			register_post_type( $slug, array(	'labels' => array(
															'name' 			=> __($values['plural'] ),
															'singular_name' => __($values['singular'] ),
															'add_new' 		=> __( 'Add New '.$values['singular'] ),
															'add_new_item' 	=> __( 'Add New '.$values['singular']  ),
															'not_found' 	=> __( 'There are no '.$values['plural'].' yet' ),
														),
												'public'				=>	true,
												'show_ui' 			=>	true,
												'menu_position' 		=> 25,
												'show_in_menu' 			=>	$values['show_in_menu'],
												'publicly_queryable' 	=>	true,
												'can_export' 			=>	true,
												'capability_type' 	=>	$slug,
												'exclude_from_search' 	=>	false,
												'hierarchical'		=>	false,
												'supports' 			=>	$values['supports'],
												'rewrite' 			=>	$values['rewrite'],
												'has_archive'			=>	false
											)
									);
		}
		
		$labels = array(
				'name'             => _x( 'Tiempos', 'taxonomy general name' ),
				'singular_name'    => _x( 'Tiempo de Alimento', 'taxonomy singular name' ),
				'search_items'     =>  __( 'Buscar por Tiempo de Alimento' ),
				'all_items'        => __( 'Todos los Tiempos de Alimento' ),
				'parent_item'      => __( 'Tiempo de Alimento padre' ),
				'parent_item_colon'=> __( 'Tiempo de Alimento padre:' ),
				'edit_item'        => __( 'Editar Tiempo de Alimento' ),
				'update_item'      => __( 'Actualizar Tiempo de Alimento' ),
				'add_new_item'     => __( 'Añadir nuevo Tiempo de Alimento' ),
				'new_item_name'    => __( 'Nombre del nuevo Tiempo de Alimento' ),
			  );			  
		register_taxonomy( 'tiempo_alimento', array( 'alimento' ), array(
			'hierarchical'       => true,
			'labels'             => $labels,
			'show_ui'            => true,
			'query_var'          => true,
			'meta_box_cb'           => false,
		));

		$labels = array(
			'name'             => _x( 'Categorias', 'taxonomy general name' ),
			'singular_name'    => _x( 'Categoria de Alimento', 'taxonomy singular name' ),
			'search_items'     =>  __( 'Buscar por Categoria de Alimento' ),
			'all_items'        => __( 'Todos los Categorias de Alimento' ),
			'parent_item'      => __( 'Categoria de Alimento padre' ),
			'parent_item_colon'=> __( 'Categoria de Alimento padre:' ),
			'edit_item'        => __( 'Editar Categoria de Alimento' ),
			'update_item'      => __( 'Actualizar Categoria de Alimento' ),
			'add_new_item'     => __( 'Añadir nuevo Categoria de Alimento' ),
			'new_item_name'    => __( 'Nombre del nuevo Categoria de Alimento' ),
		);
		register_taxonomy( 'categoria_alimento', array( 'alimento' ), array(
			'hierarchical'       => true,
			'labels'             => $labels,
			'show_ui'            => true,
			'query_var'          => true,
			'meta_box_cb'           => false,
		));
		add_filter( 'rwmb_meta_boxes', 'your_prefix_get_meta_box' );		
	}
	/**
	 * Removes the "W" logo from the admin menu
	 *
	 * @access public
	 */
	function esc_hb_fb_admin_bar_menu( $admin_bar ) {
		/*if(current_user_can('update_core') || current_user_can('manage_healthybox'))*/
		if(current_user_can('update_core'))
			return ;

		$admin_bar->remove_node( 'wp-logo' );
		$admin_bar->remove_node( 'site-name' );
		$admin_bar->remove_node( 'search' );
		$admin_bar->remove_node( 'my-account' );
		$args = array(
					'id'     => 'logout',           // id of the existing child node (New > Post)
					'title'  => 'Cerrar Session',   // alter the title of existing node
					'parent' => 'top-secondary',    // set parent
				);
		$admin_bar->add_node( $args );
	}
	function ya_do_it_admin_bar_remove() {
		/*if(current_user_can('update_core') || current_user_can('manage_healthybox'))*/
		if(current_user_can('update_core') || current_user_can('manage_healthybox'))
			return ;

        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('edit-profile');
	}
	function remove_profile_menu() {
		if(current_user_can('update_core'))
			return ;
		if( current_user_can('manage_healthybox')){
			$user	=	wp_get_current_user();
			$roles	=	( array ) $user->roles;
			if(in_array('healthybox_manager', $roles)){
				remove_submenu_page('edit-tags.php?taxonomy=link_category', 'edit-tags.php?taxonomy=link_category');
				remove_submenu_page('edit-tags.php?taxonomy=category', 'edit-tags.php?taxonomy=category');
				remove_submenu_page('edit-tags.php?taxonomy=category', 'edit-tags.php?taxonomy=post_tag');
			}
			/*return ;*/
		}
		remove_submenu_page('users.php', 'profile.php');
		remove_menu_page('profile.php');
		remove_menu_page('index.php');
		
	}
	function _esc_admin_init() {
		if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}
		if(current_user_can('update_core'))
			return ;
		
		$user	=	wp_get_current_user();
		$roles	=	( array ) $user->roles;
		if(in_array('cliente', $roles)){
			wp_redirect(home_url('/', 'http'), 301);
			exit;
		}				
		global $pagenow;
		if(in_array($pagenow, array('index.php','profile.php'))){
			if(current_user_can('manage_healthybox')) {
				wp_redirect(admin_url('/edit.php?post_type=orden', 'http'), 301);
				exit;
			}
			if(current_user_can('manage_empaques')) {
				wp_redirect(admin_url('/admin.php?page=empaque', 'http'), 301);
				exit;
			}
			if(current_user_can('manage_produccions')) {
				wp_redirect(admin_url('/admin.php?page=produccion', 'http'), 301);
				exit;
			}
		}
	}
	/**
	 * Install
	 */
	public static function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Check if we are not already running this routine.
		/*if ( 'yes' === get_transient( 'healthybox_installing' ) ) {
			return;
		}*/
		/*set_transient( 'healthybox_installing', 'yes', MINUTE_IN_SECONDS * 1 );*/

		set_transient( 'healthybox_installing', 'yes', MINUTE_IN_SECONDS * 0.1 );
		self::create_roles();
		delete_transient( 'healthybox_installing' );
	}
	
	public static function _eschb_remove_roles() {
		
		remove_role('healthybox_servicio_cliente');
		remove_role('healthybox_cocina');

		remove_role('cliente');
		remove_role('servicio_cliente');
		remove_role('cocina');
		remove_role('empaque');
		remove_role('produccion');
		remove_role('healthybox_manager');
		
		
		remove_role( 'editor' );
		remove_role( 'author' );
		remove_role( 'contributor' );
		remove_role( 'subscriber' );
	}

	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
		}
		/*self::_eschb_remove_roles();
		exit;*/
		add_role(
				'cliente', 
				'Cliente', 
				array(
					'read'	=>	true
				)
			);
		add_role(
			'cocina',
			'Cocina',
			array(
				'read'						=>	true,
				'manage_produccions'		=>	true,
				'manage_empaques'			=>	true
			)
		);
		add_role(
			'healthybox_servicio_cliente',
			'Servicio al Cliente',
			array(
				'read'						=>	true,
				'manage_produccions'		=>	true,
				'manage_direccions'			=>	true,
				'manage_healthybox'			=>	true,
				
				'edit_alimento'         	=>	true,
				'read_alimento'         	=>	true, 
				'delete_alimento'       	=>	true, 
				'create_alimentos'      	=>	true,
				'delete_alimentos'			=>	true,
				'delete_others_alimentos'	=>	true,
				'delete_private_alimentos'	=>	true,
				'delete_published_alimentos'=>	true,
				'edit_alimentos'			=>	true,
				'edit_others_alimentos'		=>	true,
				'edit_private_alimentos'	=>	true,
				'edit_published_alimentos'	=>	true,
				'publish_alimentos'			=>	true,
				'read_private_alimentos'	=>	true,
				
				'edit_cliente'         		=>	true,
				'read_cliente'         		=>	true, 
				'delete_cliente'      		=>	true, 
				'create_clientes'      		=>	true,
				'delete_clientes'			=>	true,
				'delete_others_clientes'	=>	true,
				'delete_private_clientes'	=>	true,
				'delete_published_clientes'	=>	true,
				'edit_clientes'				=>	true,
				'edit_others_clientes'		=>	true,
				'edit_private_clientes'		=>	true,
				'edit_published_clientes'	=>	true,
				'publish_clientes'			=>	true,
				'read_private_clientes'		=>	true,
				
				'edit_alianza'         		=>	true,
				'read_alianza'         		=>	true, 
				'delete_alianza'       		=>	true, 
				'create_alianzas'      		=>	true,
				'delete_alianzas'			=>	true,
				'delete_others_alianzas'	=>	true,
				'delete_private_alianzas'	=>	true,
				'delete_published_alianzas'	=>	true,
				'edit_alianzas'				=>	true,
				'edit_others_alianzas'		=>	true,
				'edit_private_alianzas'		=>	true,
				'edit_published_alianzas'	=>	true,
				'publish_alianzas'			=>	true,
				'read_private_alianzas'		=>	true,
				
				'edit_orden'         		=>	true,
				'read_orden'         		=>	true, 
				'delete_orden'       		=>	true, 
				'create_ordens'      		=>	true,
				'delete_ordens'				=>	true,
				'delete_others_ordens'		=>	true,
				'delete_private_ordens'		=>	true,
				'delete_published_ordens'	=>	true,
				'edit_ordens'				=>	true,
				'edit_others_ordens'		=>	true,
				'edit_private_ordens'		=>	true,
				'edit_published_ordens'		=>	true,
				'publish_ordens'			=>	true,
				'read_private_ordens'		=>	true,
				
			)
		);
		add_role(
			'healthybox_manager',
			'HealthyBox Administrador',
			array(
				'read'						=>	true,
				'manage_empaques'			=>	true,
				'manage_produccions'		=>	true,
				'manage_direccions'			=>	true,
				'manage_healthybox'			=>	true,
				'manage_users'				=>	true,
				'create_users'				=>	true,
				'edit_user'					=>	true,
				'read_user'         		=>	true, 
				'delete_user'	     		=>	true, 
				
				'edit_users'				=>	true,
				'delete_users'				=>	true,
				'list_users'				=>	true,
				'manage_categories'      	=> true,
				
				'edit_alimento'         	=>	true,
				'read_alimento'         	=>	true, 
				'delete_alimento'       	=>	true, 
				'create_alimentos'      	=>	true,
				'delete_alimentos'			=>	true,
				'delete_others_alimentos'	=>	true,
				'delete_private_alimentos'	=>	true,
				'delete_published_alimentos'=>	true,
				'edit_alimentos'			=>	true,
				'edit_others_alimentos'		=>	true,
				'edit_private_alimentos'	=>	true,
				'edit_published_alimentos'	=>	true,
				'publish_alimentos'			=>	true,
				'read_private_alimentos'	=>	true,
				
				'edit_cliente'         		=>	true,
				'read_cliente'         		=>	true, 
				'delete_cliente'      		=>	true, 
				'create_clientes'      		=>	true,
				'delete_clientes'			=>	true,
				'delete_others_clientes'	=>	true,
				'delete_private_clientes'	=>	true,
				'delete_published_clientes'	=>	true,
				'edit_clientes'				=>	true,
				'edit_others_clientes'		=>	true,
				'edit_private_clientes'		=>	true,
				'edit_published_clientes'	=>	true,
				'publish_clientes'			=>	true,
				'read_private_clientes'		=>	true,
				
				'edit_alianza'         		=>	true,
				'read_alianza'         		=>	true, 
				'delete_alianza'       		=>	true, 
				'create_alianzas'      		=>	true,
				'delete_alianzas'			=>	true,
				'delete_others_alianzas'	=>	true,
				'delete_private_alianzas'	=>	true,
				'delete_published_alianzas'	=>	true,
				'edit_alianzas'				=>	true,
				'edit_others_alianzas'		=>	true,
				'edit_private_alianzas'		=>	true,
				'edit_published_alianzas'	=>	true,
				'publish_alianzas'			=>	true,
				'read_private_alianzas'		=>	true,
				
				'edit_orden'         		=>	true,
				'read_orden'         		=>	true, 
				'delete_orden'       		=>	true, 
				'create_ordens'      		=>	true,
				'delete_ordens'				=>	true,
				'delete_others_ordens'		=>	true,
				'delete_private_ordens'		=>	true,
				'delete_published_ordens'	=>	true,
				'edit_ordens'				=>	true,
				'edit_others_ordens'		=>	true,
				'edit_private_ordens'		=>	true,
				'edit_published_ordens'		=>	true,
				'publish_ordens'			=>	true,
				'read_private_ordens'		=>	true,
				
			)
		);
		
		
		$role = get_role('healthybox_manager');

		// add a new capability
		/*$capabilities = array( 
			'manage_alimento_terms', 
			'manage_categoria_alimento', 
			'edit_categoria_alimento', 
			'delete_categoria_alimento', 
			'assign_categoria_alimento', 
			'manage_tiempo_alimento', 
			'edit_tiempo_alimento', 
			'delete_tiempo_alimento', 
			'assign_tiempo_alimento' );
			



		foreach( $capabilities as $cap ) {
			$role->add_cap( $cap );
		}*/
		
		
		$administrator     = get_role('administrator');
		$administrator->add_cap( 'manage_healthybox' );
		$administrator->add_cap( 'manage_produccions' );
		$administrator->add_cap( 'manage_empaques' );
		$administrator->add_cap( 'manage_direccions' );
		$administrator->add_cap( 'manage_clientes' );
		$administrator->add_cap( 'manage_ordens' );
		$administrator->add_cap( 'edit_orden' );
		$administrator->add_cap( 'delete_orden' );
		$administrator->add_cap( 'create_ordens' );
		$administrator->add_cap( 'delete_ordens' );
		$administrator->add_cap( 'delete_others_ordens' );
		$administrator->add_cap( 'delete_private_ordens' );
		$administrator->add_cap( 'delete_published_ordens' );
		$administrator->add_cap( 'edit_ordens' );
		$administrator->add_cap( 'edit_others_ordens' );
		$administrator->add_cap( 'edit_private_ordens' );
		$administrator->add_cap( 'edit_published_ordens' );
		$administrator->add_cap( 'publish_ordens' );
		$administrator->add_cap( 'read_private_ordens' );
		
		$administrator->add_cap( 'edit_alimento');
		$administrator->add_cap( 'read_alimento'); 
		$administrator->add_cap( 'delete_alimento' );
		$administrator->add_cap( 'create_alimentos' );
		$administrator->add_cap( 'delete_alimentos' );
		$administrator->add_cap( 'delete_others_alimentos' );
		$administrator->add_cap( 'delete_private_alimentos' );
		$administrator->add_cap( 'delete_published_alimentos' );
		$administrator->add_cap( 'edit_alimentos' );
		$administrator->add_cap( 'edit_others_alimentos' );
		$administrator->add_cap( 'edit_private_alimentos' );
		$administrator->add_cap( 'edit_published_alimentos' );
		$administrator->add_cap( 'publish_alimentos' );
		$administrator->add_cap( 'read_private_alimentos' );
		
		
		$administrator->add_cap( 'edit_cliente');
		$administrator->add_cap( 'read_cliente'); 
		$administrator->add_cap( 'delete_cliente' );
		$administrator->add_cap( 'create_clientes' );
		$administrator->add_cap( 'delete_clientes' );
		$administrator->add_cap( 'delete_others_clientes' );
		$administrator->add_cap( 'delete_private_clientes' );
		$administrator->add_cap( 'delete_published_clientes' );
		$administrator->add_cap( 'edit_clientes' );
		$administrator->add_cap( 'edit_others_clientes' );
		$administrator->add_cap( 'edit_private_clientes' );
		$administrator->add_cap( 'edit_published_clientes' );
		$administrator->add_cap( 'publish_clientes' );
		$administrator->add_cap( 'read_private_clientes' );
	}
	public static function _esc_get_healthybox_capabilities(){
		$capability_types = array( 'alimento', 'alianza', 'orden', 'cliente');
		$ar=array();
		foreach ( $capability_types as $capability_type ) {

			$capabilities = array(
				"edit_{$capability_type}"          		=>	true, 
				"read_{$capability_type}"          		=>	true, 
				"delete_{$capability_type}"        		=>	true, 
				"create_{$capability_type}s"       		=>	true,
				"delete_{$capability_type}s"			=>	true,
				"delete_others_{$capability_type}s"		=>	true,
				"delete_private_{$capability_type}s"	=>	true,
				"delete_published_{$capability_type}s"	=>	true,
				"edit_{$capability_type}s"				=>	true,
				"edit_others_{$capability_type}s"		=>	true,
				"edit_private_{$capability_type}s"		=>	true,
				"edit_published_{$capability_type}s"	=>	true,
				"publish_{$capability_type}s"			=>	true,
				"read_private_{$capability_type}s"		=>	true
			);
			$ar	=	array_merge($ar, $capabilities);
		}

		return $ar;
	}
	/**
	 * Get capabilities for WooCommerce - these are assigned to admin/shop manager during installation or reset.
	 *
	 * @return array
	 */
	private static function get_core_capabilities() {
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_healthybox',
			'view_healthybox_reports',
		);
		$capability_types = array( 'alimento', 'alianza', 'orden', 'cliente');
		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				"edit_{$capability_type}"          		=>	true, 
				"read_{$capability_type}"          		=>	true, 
				"delete_{$capability_type}"        		=>	true, 
				"create_{$capability_type}s"       		=>	true,
				"delete_{$capability_type}s"			=>	true,
				"delete_others_{$capability_type}s"		=>	true,
				"delete_private_{$capability_type}s"	=>	true,
				"delete_published_{$capability_type}s"	=>	true,
				"edit_{$capability_type}s"				=>	true,
				"edit_others_{$capability_type}s"		=>	true,
				"edit_private_{$capability_type}s"		=>	true,
				"edit_published_{$capability_type}s"	=>	true,
				"publish_{$capability_type}s"			=>	true,
				"read_private_{$capability_type}s"		=>	true
			);
		}
		return $capabilities;
	}
	function your_prefix_get_meta_box( $meta_boxes ) {
				$prefix = 'esc_';
				$meta_boxes[] = array(
					'id' => 'plato_fields',
					'title' => esc_html__( 'Fields', 'metabox-online-generator' ),
					'post_types' => array('alimento' ),
					'context' => 'advanced',
					'priority' => 'default',
					'autosave' => 'false',
				);

				return $meta_boxes;
			}
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}
	public function plugin_menu_page_produccion() {
		$hook = add_menu_page(
			'Comando Produccion',
			'Comando Produccion',
			'manage_produccions',
			'produccion',
			[ $this, 'plugin_settings_page_produccion' ],
			'',
			59
		);
		add_action( "load-$hook", [ $this, 'screen_option_produccion' ] );
	}
	public function plugin_menu_page_direccion() {
		$hook = add_menu_page(
			'Direcciones',
			'Direcciones',
			'manage_direccions',
			'direccion',
			[ $this, 'plugin_settings_page_direccion' ],
			'',
			59
		);
		add_action( "load-$hook", [ $this, 'screen_option_direccion' ] );
	}		
	public function plugin_menu_page_empaque() {
		$hook = add_menu_page(
			'Empaque',
			'Empaque',
			'manage_empaques',
			'empaque',
			[ $this, 'plugin_settings_page_empaque' ],
			'',
			59
		);
		add_action( "load-$hook", [ $this, 'screen_option_empaque' ] );
	}		
	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page_empaque() {
?>
		<div class="wrap">
			<h2>Empaque</h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->empaque_obj->filter_box( __( 'Find', $this->plugin_text_domain ), 'nds-user-find');
								$this->empaque_obj->prepare_items();
								$this->empaque_obj->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}		
	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page_produccion() {
?>
		<div class="wrap">
			<h2>Comando Produccion</h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->comandoProduccion_obj->filter_box( __( 'Find', $this->plugin_text_domain ), 'nds-user-find');
								$this->comandoProduccion_obj->prepare_items();
								$this->comandoProduccion_obj->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}
	public function plugin_settings_page_direccion() {
?>
		<div class="wrap">
			<h2>Direcciones de Entrega</h2>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->direccionEntrega_obj->filter_box( __( 'Find', $this->plugin_text_domain ), 'nds-user-find');
								$this->direccionEntrega_obj->prepare_items();
								$this->direccionEntrega_obj->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}
	/**
	 * Screen options
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = [
			'label'   => 'Customers',
			'default' => 5,
			'option'  => 'clientes_per_page'
		];
		add_screen_option( $option, $args );
		$this->clientes_obj = new Customers_List();
	}
	/**
	 * Screen options
	 */
	public function screen_option_produccion() {
		$option = 'per_page';
		$args   = [
			'label'   => 'Comando Produccion',
			'default' => 5,
			'option'  => 'comandoProduccion_per_page'
		];
		add_screen_option( $option, $args );
		$this->comandoProduccion_obj = new ComandoProduccion_List();
	}
	public function screen_option_direccion() {
		$option = 'per_page';
		$args   = [
			'label'   => 'Direcciones de Entrega',
			'default' => 5,
			'option'  => 'direccionEntrega_per_page'
		];
		add_screen_option( $option, $args );
		$this->direccionEntrega_obj = new Direccion_List();
	}
	/**
	 * Screen options
	 */
	public function screen_option_empaque() {
		$option = 'per_page';
		$args   = [
			'label'   => 'Empaque',
			'default' => 5,
			'option'  => 'empaques_per_page'
		];
		add_screen_option( $option, $args );
		$this->empaque_obj = new Empaque_List();
	}
	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
add_action( 'plugins_loaded', function () {
	HB_Plugin::get_instance();
} );
